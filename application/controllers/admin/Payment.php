<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payment extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_ADMIN, ROLE_SECRETARY]);
        $this->load->model(['Payment_model', 'Booking_model', 'Certificate_model', 'Donation_model', 'Notification_model']);
    }

    public function index()
    {
        $this->render_app('admin/payment_list', [], 'layouts/app_admin');
    }

    public function datatable()
    {
        $request = $this->input->post();
        $total = $this->Payment_model->datatable_query($request, true);
        $rows  = $this->Payment_model->datatable_query($request, false);

        $data = [];
        foreach ($rows as $r) {
            $payment_for = ucwords(str_replace('_', ' ', $r['payable_type'])) . ' #' . $r['payable_id'];
            if ($r['payable_type'] === 'donation') {
                $donation = $this->Donation_model->get($r['payable_id']);
                $payment_for = 'Donation — ' . ($donation['campaign_title'] ?? 'General Parish Fund');
            }

            $data[] = [
                'payment_code' => $r['payment_code'],
                'payer'        => $r['first_name'] . ' ' . $r['last_name'],
                'for'          => $payment_for,
                'amount'       => peso($r['amount']),
                'reference'    => $r['gcash_reference_no'] ?: '—',
                'status'       => '<span class="px-2.5 py-1 rounded-full text-xs font-medium ' . status_badge_class($r['status']) . '">' . status_label($r['status']) . '</span>',
                'actions'      => '<div class="flex items-center justify-center gap-1.5 whitespace-nowrap">'
                    . dt_icon_button(
                        'ph-eye',
                        $r['status'] === 'submitted' ? 'Review payment' : 'View payment',
                        'viewPayment(' . (int) $r['id'] . ')',
                        $r['status'] === 'submitted' ? 'primary' : 'neutral'
                    )
                    . '</div>',
            ];
        }

        $this->json(['draw' => (int) ($request['draw'] ?? 1), 'recordsTotal' => $total, 'recordsFiltered' => $total, 'data' => $data]);
    }

    public function get($id)
    {
        $this->json(['success' => true, 'data' => $this->Payment_model->get($id)]);
    }

    /** AJAX: verify GCash payment -> generates OR number, cascades status on the payable */
    public function verify()
    {
        $id = (int) $this->input->post('id');

        $this->load->library('payment_workflow');
        $result = $this->payment_workflow->verify($id, $this->current_user['id']);

        if (!$result['success']) {
            return $this->json(['success'=>false,'message'=>$result['message']]);
        }

        $payment = $result['payment'] ?? $this->Payment_model->get($id);
        if (empty($result['already_verified']) && $payment) {
            $this->log_activity(
                'Verified GCash payment',
                'payment',
                $payment['payment_code'] . ' — ' . ($result['receipt_no'] ?? '')
            );
        }

        $this->json([
            'success'=>true,
            'message'=>$result['message'],
            'receipt_no'=>$result['receipt_no'] ?? null,
        ]);
    }

    /** AJAX: reject a payment submission (e.g. wrong reference / unclear screenshot) */
    public function reject()
    {
        $id = (int) $this->input->post('id');
        $reason = trim((string)$this->input->post('reason', true));

        $this->load->library('payment_workflow');
        $result = $this->payment_workflow->reject(
            $id,
            $this->current_user['id'],
            $reason
        );

        if (!$result['success']) {
            return $this->json(['success'=>false,'message'=>$result['message']]);
        }

        $payment = $result['payment'] ?? $this->Payment_model->get($id);
        if ($payment) {
            $this->log_activity('Rejected GCash payment', 'payment', $payment['payment_code']);
        }

        $this->json(['success'=>true,'message'=>$result['message']]);
    }
}
