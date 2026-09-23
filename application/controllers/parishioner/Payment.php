<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payment extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_PARISHIONER]);
        $this->load->model(['Payment_model', 'Booking_model', 'Certificate_model']);
    }

    /** GET /my/payments - payment history */
    public function index()
    {
        $data['payments'] = $this->Payment_model->for_user($this->current_user['id']);
        $this->render_app('parishioner/payment_list', $data, 'layouts/app_parishioner');
    }

    /**
     * GET /my/payments/pay/{type}/{id} - GCash payment screen
     * $type: service_booking | certificate_request
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
            return $this->json(['success' => false, 'message' => 'Invalid payment target.']);
        }

        $this->form_validation->set_rules('gcash_reference_no', 'GCash Reference Number', 'required|min_length[6]');
        if ($this->form_validation->run() === FALSE) {
            return $this->json(['success' => false, 'message' => strip_tags(validation_errors())]);
        }
        if (empty($_FILES['proof']['name'])) {
            return $this->json(['success' => false, 'message' => 'Please upload a screenshot of your GCash payment.']);
        }

        $target_dir = FCPATH . UPLOAD_PAYMENTS;
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

        $ext = pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        if (!in_array(strtolower($ext), $allowed, true)) {
            return $this->json(['success' => false, 'message' => 'Only JPG, PNG or PDF files are allowed.']);
        }

        $safe_name = 'pay_' . $type . '_' . $id . '_' . uniqid() . '.' . $ext;
        $dest = $target_dir . $safe_name;

        if (!move_uploaded_file($_FILES['proof']['tmp_name'], $dest)) {
            return $this->json(['success' => false, 'message' => 'Upload failed. Please try again.']);
        }

        $existing = $this->Payment_model->for_payable($type, $id);
        $payload = [
            'user_id'             => $this->current_user['id'],
            'payable_type'        => $type,
            'payable_id'          => $id,
            'amount'              => $amount,
            'method'              => 'gcash',
            'gcash_reference_no'  => $this->input->post('gcash_reference_no', true),
            'proof_of_payment'    => UPLOAD_PAYMENTS . $safe_name,
            'status'              => 'submitted',
            'updated_at'          => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $this->Payment_model->update($existing['id'], $payload);
        } else {
            $payload['payment_code'] = $this->Payment_model->generate_code();
            $payload['created_at']   = date('Y-m-d H:i:s');
            $this->Payment_model->create($payload);
        }

        // Move the related booking/certificate into "payment verification" status
        if ($type === 'service_booking') {
            $this->Booking_model->change_status($id, 'payment_verification', $this->current_user['id'], 'Payment proof submitted');
        } else {
            $this->Certificate_model->update($id, ['status' => 'payment_verification']);
        }

        $this->flash_success('Payment submitted. The parish secretary will verify it shortly.');
        $this->json(['success' => true, 'redirect' => $type === 'service_booking' ? site_url('my/bookings/' . $id) : site_url('my/certificates/' . $id)]);
    }

    private function _resolve_payable($type, $id)
    {
        if ($type === 'service_booking') {
            $b = $this->Booking_model->get($id);
            if (!$b || (int) $b['user_id'] !== (int) $this->current_user['id']) return [null, 0, null];
            return [$b, $b['fee_amount'], $b['service_name'] . ' — ' . $b['booking_code']];
        }
        if ($type === 'certificate_request') {
            $c = $this->Certificate_model->get($id);
            if (!$c || (int) $c['user_id'] !== (int) $this->current_user['id']) return [null, 0, null];
            return [$c, $c['fee_amount'], 'Certificate Request — ' . $c['request_code']];
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
