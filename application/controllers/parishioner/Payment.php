<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payment extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_PARISHIONER]);
        $this->load->model(['Payment_model', 'Booking_model', 'Certificate_model', 'Donation_model']);
    }

    /** GET /my/payments - payment history */
    public function index()
    {
        $data['payments'] = $this->Payment_model->for_user($this->current_user['id']);
        $this->render_app('parishioner/payment_list', $data, 'layouts/app_parishioner');
    }

    /**
     * GET /my/payments/pay/{type}/{id} - GCash payment screen
     * $type: service_booking | certificate_request | donation
     */
    public function pay($type, $id)
    {
        [$payable, $amount, $title] = $this->_resolve_payable($type, $id);
        if (!$payable) show_404();

        $data['type']    = $type;
        $data['id']      = $id;
        $data['amount']  = $amount;
        $data['title']   = $title;
        $data['gcash']   = $this->_gcash_settings();
        $data['existing'] = $this->Payment_model->for_payable($type, $id);

        $this->render_app('parishioner/payment_pay', $data, 'layouts/app_parishioner');
    }

    /** AJAX: submit GCash reference number + proof of payment screenshot */
    public function submit()
    {
        $type = $this->input->post('payable_type');
        $id   = (int) $this->input->post('payable_id');

        [$payable, $amount] = $this->_resolve_payable($type, $id);
        if (!$payable) {
            return $this->json(['success' => false, 'message' => 'This item is not currently payable.']);
        }

        if (in_array($type, ['service_booking', 'certificate_request'], true)
            && ($payable['status'] ?? null) !== 'awaiting_payment') {
            return $this->json([
                'success' => false,
                'message' => ($payable['status'] ?? null) === 'payment_verification'
                    ? 'A payment proof is already waiting for parish verification.'
                    : 'This item is no longer awaiting payment.'
            ]);
        }

        $this->form_validation->set_rules('gcash_reference_no', 'GCash Reference Number', 'required|min_length[6]');
        if ($this->form_validation->run() === FALSE) {
            return $this->json(['success' => false, 'message' => strip_tags(validation_errors())]);
        }
        if (empty($_FILES['proof']['name'])) {
            return $this->json(['success' => false, 'message' => 'Please upload a screenshot of your GCash payment.']);
        }

        $this->load->library('secure_upload');

        $target_dir = FCPATH . UPLOAD_PAYMENTS;
        $stored = $this->secure_upload->store(
            $_FILES['proof'],
            $target_dir,
            'pay_' . preg_replace('/[^a-z0-9_-]/i', '_', (string) $type) . '_' . $id,
            5 * 1024 * 1024
        );

        if (!$stored['success']) {
            return $this->json(['success' => false, 'message' => $stored['message']]);
        }

        $safe_name = $stored['filename'];

        $stored_path = FCPATH . UPLOAD_PAYMENTS . $safe_name;
        $old_proof = null;

        $this->db->trans_begin();

        // Lock the payable itself first. Concurrent submissions for the same
        // booking/certificate/donation are serialized before the payment row
        // is inspected or created.
        $payable_table = [
            'service_booking' => 'service_bookings',
            'certificate_request' => 'certificate_requests',
            'donation' => 'donations',
        ][$type] ?? null;

        if (!$payable_table) {
            $this->db->trans_rollback();
            @unlink($stored_path);
            return $this->json(['success'=>false,'message'=>'Unsupported payment type.']);
        }

        $locked_payable = $this->db->query(
            'SELECT id FROM ' . $payable_table . ' WHERE id = ? FOR UPDATE',
            [$id]
        )->row_array();

        if (!$locked_payable) {
            $this->db->trans_rollback();
            @unlink($stored_path);
            return $this->json(['success'=>false,'message'=>'The related parish transaction no longer exists.']);
        }

        // Re-check ownership/status after obtaining the database lock so the
        // browser cannot submit against a booking that changed meanwhile.
        [$payable, $amount] = $this->_resolve_payable($type, $id);
        if (!$payable || (in_array($type, ['service_booking','certificate_request'], true)
            && ($payable['status'] ?? null) !== 'awaiting_payment')) {
            $this->db->trans_rollback();
            @unlink($stored_path);
            return $this->json(['success'=>false,'message'=>'This item is no longer awaiting payment.']);
        }

        $existing = $this->db->query(
            'SELECT * FROM payments WHERE payable_type = ? AND payable_id = ? FOR UPDATE',
            [$type, $id]
        )->row_array();

        if ($existing && $existing['status'] === 'payment_verified') {
            $this->db->trans_rollback();
            @unlink($stored_path);
            return $this->json(['success' => false, 'message' => 'This payment has already been verified.']);
        }

        if ($existing && $existing['status'] === 'submitted') {
            $this->db->trans_rollback();
            @unlink($stored_path);
            return $this->json(['success' => false, 'message' => 'A payment proof is already waiting for parish verification.']);
        }

        $payload = [
            'user_id'             => $this->current_user['id'],
            'payable_type'        => $type,
            'payable_id'          => $id,
            'amount'              => $amount,
            'method'              => 'gcash',
            'gcash_reference_no'  => trim((string)$this->input->post('gcash_reference_no', true)),
            'proof_of_payment'    => UPLOAD_PAYMENTS . $safe_name,
            'status'              => 'submitted',
            'verified_by'         => null,
            'verified_at'         => null,
            'receipt_no'          => null,
            'remarks'             => null,
            'updated_at'          => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $old_proof = !empty($existing['proof_of_payment'])
                ? FCPATH . ltrim($existing['proof_of_payment'], '/\\')
                : null;
            $saved = $this->Payment_model->update($existing['id'], $payload);
        } else {
            $payload['payment_code'] = $this->Payment_model->generate_code();
            $payload['created_at'] = date('Y-m-d H:i:s');
            $saved = (bool)$this->Payment_model->create($payload);
        }

        if (!$saved) {
            $this->db->trans_rollback();
            @unlink($stored_path);
            return $this->json(['success'=>false,'message'=>'The payment submission could not be saved.']);
        }

        if ($type === 'service_booking') {
            $advanced = $this->Booking_model->change_status(
                $id,
                'payment_verification',
                $this->current_user['id'],
                'Payment proof submitted'
            );
            $workflow_error = $this->Booking_model->transition_error();
        } elseif ($type === 'certificate_request') {
            $advanced = $this->Certificate_model->change_status(
                $id,
                'payment_verification',
                $this->current_user['id']
            );
            $workflow_error = $this->Certificate_model->transition_error();
        } else {
            $advanced = true;
            $workflow_error = '';
        }

        if (!$advanced || $this->db->trans_status() === false) {
            $this->db->trans_rollback();
            @unlink($stored_path);
            return $this->json([
                'success'=>false,
                'message'=>$workflow_error ?: 'The related parish transaction is not ready for payment verification.'
            ]);
        }

        $this->db->trans_commit();

        if ($old_proof && is_file($old_proof)
            && realpath(dirname($old_proof)) === realpath(FCPATH . UPLOAD_PAYMENTS)) {
            @unlink($old_proof);
        }

        $this->flash_success('Payment submitted. The parish secretary will verify it shortly.');
        if ($type === 'service_booking') {
            $redirect = site_url('my/bookings/' . $id);
        } elseif ($type === 'certificate_request') {
            $redirect = site_url('my/certificates/' . $id);
        } else {
            $redirect = site_url('my/donations/new');
        }
        $this->json(['success' => true, 'redirect' => $redirect]);
    }

    private function _resolve_payable($type, $id)
    {
        if ($type === 'service_booking') {
            $b = $this->Booking_model->get($id);
            if (!$b || (int) $b['user_id'] !== (int) $this->current_user['id']) return [null, 0, null];
            if (!in_array($b['status'], ['awaiting_payment', 'payment_verification'], true)) return [null, 0, null];
            if ((float) $b['fee_amount'] <= 0) return [null, 0, null];
            return [$b, $b['fee_amount'], $b['service_name'] . ' — ' . $b['booking_code']];
        }
        if ($type === 'certificate_request') {
            $c = $this->Certificate_model->get($id);
            if (!$c || (int) $c['user_id'] !== (int) $this->current_user['id']) return [null, 0, null];
            if (!in_array($c['status'], ['awaiting_payment', 'payment_verification'], true)) return [null, 0, null];
            if ((float) $c['fee_amount'] <= 0) return [null, 0, null];
            return [$c, $c['fee_amount'], 'Certificate Request — ' . $c['request_code']];
        }
        if ($type === 'donation') {
            $d = $this->Donation_model->get($id);
            if (!$d || (int) $d['user_id'] !== (int) $this->current_user['id']) return [null, 0, null];
            $title = !empty($d['campaign_title']) ? 'Donation — ' . $d['campaign_title'] : 'General Parish Donation';
            return [$d, $d['amount'], $title];
        }
        return [null, 0, null];
    }

    private function _gcash_settings()
    {
        $rows = $this->db->where_in('setting_key', ['gcash_qr_image', 'gcash_account_name', 'gcash_account_number'])
            ->get('system_settings')->result_array();
        $out = [];
        foreach ($rows as $r) $out[$r['setting_key']] = $r['setting_value'];
        return $out;
    }
}
