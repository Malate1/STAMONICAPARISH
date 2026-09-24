<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payment extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_ADMIN, ROLE_SECRETARY]);
        $this->load->model(['Payment_model', 'Booking_model', 'Certificate_model', 'Notification_model']);
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
            $data[] = [
                'payment_code' => $r['payment_code'],
                'payer'        => $r['first_name'] . ' ' . $r['last_name'],
                'for'          => ucwords(str_replace('_', ' ', $r['payable_type'])) . ' #' . $r['payable_id'],
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
        $payment = $this->Payment_model->get($id);
        if (!$payment) return $this->json(['success' => false, 'message' => 'Not found.']);

        $receipt_no = $this->Payment_model->generate_receipt_no();
        $this->Payment_model->update($id, [
            'status'      => 'payment_verified',
            'verified_by' => $this->current_user['id'],
            'verified_at' => date('Y-m-d H:i:s'),
            'receipt_no'  => $receipt_no,
        ]);

        if ($payment['payable_type'] === 'service_booking') {
            $this->Booking_model->change_status($payment['payable_id'], 'approved', $this->current_user['id'], 'Payment verified — ' . $receipt_no);
            $booking = $this->Booking_model->get($payment['payable_id']);
            $this->Notification_model->push($booking['user_id'], 'Payment Verified', 'Your payment for ' . $booking['booking_code'] . ' has been verified. Official Receipt: ' . $receipt_no, site_url('my/bookings/' . $payment['payable_id']));
        } elseif ($payment['payable_type'] === 'certificate_request') {
            $this->Certificate_model->update($payment['payable_id'], ['status' => 'preparing']);
            $cert = $this->Certificate_model->get($payment['payable_id']);
            $this->Notification_model->push($cert['user_id'], 'Payment Verified', 'Your certificate payment has been verified. Official Receipt: ' . $receipt_no, site_url('my/certificates/' . $payment['payable_id']));
        }

        $this->log_activity('Verified GCash payment', 'payment', $payment['payment_code'] . ' — ' . $receipt_no);
        $this->json(['success' => true, 'message' => 'Payment verified. Receipt: ' . $receipt_no]);
    }

    /** AJAX: reject a payment submission (e.g. wrong reference / unclear screenshot) */
    public function reject()
    {
        $id = (int) $this->input->post('id');
        $reason = $this->input->post('reason', true);
        $payment = $this->Payment_model->get($id);
        if (!$payment) return $this->json(['success' => false, 'message' => 'Not found.']);

        $this->Payment_model->update($id, [
            'status'      => 'rejected',
            'verified_by' => $this->current_user['id'],
            'verified_at' => date('Y-m-d H:i:s'),
            'remarks'     => $reason,
        ]);

        if ($payment['payable_type'] === 'service_booking') {
            $this->Booking_model->change_status($payment['payable_id'], 'awaiting_payment', $this->current_user['id'], 'Payment rejected: ' . $reason);
        } else {
            $this->Certificate_model->update($payment['payable_id'], ['status' => 'awaiting_payment']);
        }

        $this->log_activity('Rejected GCash payment', 'payment', $payment['payment_code']);
        $this->json(['success' => true, 'message' => 'Payment rejected. The parishioner will be asked to resubmit.']);
    }
}
