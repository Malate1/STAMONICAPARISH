<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Central payment verification/rejection workflow shared by Administrator and
 * Parish Secretary controllers.
 */
class Payment_workflow
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model([
            'Payment_model',
            'Booking_model',
            'Certificate_model',
            'Donation_model',
            'Notification_model',
        ]);
    }

    public function verify($payment_id, $staff_user_id)
    {
        $payment_id = (int)$payment_id;
        $staff_user_id = (int)$staff_user_id;

        $this->CI->db->trans_begin();

        $locked = $this->CI->db->query(
            'SELECT * FROM payments WHERE id = ? FOR UPDATE',
            [$payment_id]
        )->row_array();

        if (!$locked) {
            $this->CI->db->trans_rollback();
            return ['success'=>false,'message'=>'Payment not found.'];
        }

        if ($locked['status'] === 'payment_verified') {
            $this->CI->db->trans_commit();
            return [
                'success'=>true,
                'already_verified'=>true,
                'receipt_no'=>$locked['receipt_no'],
                'payment'=>$locked,
                'message'=>'Payment was already verified. Receipt: ' . ($locked['receipt_no'] ?: '—'),
            ];
        }

        if ($locked['status'] !== 'submitted') {
            $this->CI->db->trans_rollback();
            return ['success'=>false,'message'=>'Only a submitted payment proof can be verified.'];
        }

        $receipt_no = $this->CI->Payment_model->generate_receipt_no($payment_id);

        if (!$this->CI->Payment_model->update($payment_id, [
            'status'=>'payment_verified',
            'verified_by'=>$staff_user_id,
            'verified_at'=>date('Y-m-d H:i:s'),
            'receipt_no'=>$receipt_no,
            'remarks'=>null,
        ])) {
            $this->CI->db->trans_rollback();
            return ['success'=>false,'message'=>'The payment verification could not be saved.'];
        }

        $transition = $this->advance_payable_after_verification($locked, $staff_user_id, $receipt_no);
        if (!$transition['success']) {
            $this->CI->db->trans_rollback();
            return $transition;
        }

        if ($this->CI->db->trans_status() === false) {
            $this->CI->db->trans_rollback();
            return ['success'=>false,'message'=>'Payment verification failed and was rolled back.'];
        }

        $this->CI->db->trans_commit();
        $this->notify_verified($locked, $receipt_no);

        return [
            'success'=>true,
            'already_verified'=>false,
            'receipt_no'=>$receipt_no,
            'payment'=>$locked,
            'message'=>'Payment verified. Receipt: ' . $receipt_no,
        ];
    }

    public function reject($payment_id, $staff_user_id, $reason)
    {
        $payment_id = (int)$payment_id;
        $staff_user_id = (int)$staff_user_id;
        $reason = trim((string)$reason);

        if ($reason === '') {
            return ['success'=>false,'message'=>'Enter a reason for rejecting the payment proof.'];
        }
        if (mb_strlen($reason) > 255) {
            return ['success'=>false,'message'=>'Rejection reason must be 255 characters or fewer.'];
        }

        $this->CI->db->trans_begin();

        $locked = $this->CI->db->query(
            'SELECT * FROM payments WHERE id = ? FOR UPDATE',
            [$payment_id]
        )->row_array();

        if (!$locked) {
            $this->CI->db->trans_rollback();
            return ['success'=>false,'message'=>'Payment not found.'];
        }

        if ($locked['status'] !== 'submitted') {
            $this->CI->db->trans_rollback();
            return ['success'=>false,'message'=>'Only a submitted payment proof can be rejected.'];
        }

        if (!$this->CI->Payment_model->update($payment_id, [
            'status'=>'rejected',
            'verified_by'=>$staff_user_id,
            'verified_at'=>date('Y-m-d H:i:s'),
            'remarks'=>$reason,
            'receipt_no'=>null,
        ])) {
            $this->CI->db->trans_rollback();
            return ['success'=>false,'message'=>'The payment rejection could not be saved.'];
        }

        $transition = $this->return_payable_after_rejection($locked, $staff_user_id, $reason);
        if (!$transition['success']) {
            $this->CI->db->trans_rollback();
            return $transition;
        }

        if ($this->CI->db->trans_status() === false) {
            $this->CI->db->trans_rollback();
            return ['success'=>false,'message'=>'Payment rejection failed and was rolled back.'];
        }

        $this->CI->db->trans_commit();
        $this->notify_rejected($locked, $reason);

        return [
            'success'=>true,
            'payment'=>$locked,
            'message'=>'Payment rejected. The parishioner can submit a corrected proof.',
        ];
    }

    private function advance_payable_after_verification(array $payment, $staff_user_id, $receipt_no)
    {
        switch ($payment['payable_type']) {
            case 'service_booking':
                if (!$this->CI->Booking_model->change_status(
                    $payment['payable_id'],
                    'approved',
                    $staff_user_id,
                    'Payment verified — ' . $receipt_no
                )) {
                    return [
                        'success'=>false,
                        'message'=>$this->CI->Booking_model->transition_error()
                            ?: 'The related booking is not ready for payment verification.'
                    ];
                }
                break;

            case 'certificate_request':
                if (!$this->CI->Certificate_model->change_status(
                    $payment['payable_id'],
                    'preparing',
                    $staff_user_id
                )) {
                    return [
                        'success'=>false,
                        'message'=>$this->CI->Certificate_model->transition_error()
                            ?: 'The related certificate request is not ready for payment verification.'
                    ];
                }
                break;

            case 'donation':
                // Donation totals are derived from verified payment status.
                break;

            default:
                return [
                    'success'=>false,
                    'message'=>'This payment type is not yet supported by the verification workflow.'
                ];
        }

        return ['success'=>true];
    }

    private function return_payable_after_rejection(array $payment, $staff_user_id, $reason)
    {
        switch ($payment['payable_type']) {
            case 'service_booking':
                if (!$this->CI->Booking_model->change_status(
                    $payment['payable_id'],
                    'awaiting_payment',
                    $staff_user_id,
                    'Payment rejected: ' . $reason
                )) {
                    return [
                        'success'=>false,
                        'message'=>$this->CI->Booking_model->transition_error()
                            ?: 'The related booking is not in payment verification.'
                    ];
                }
                break;

            case 'certificate_request':
                if (!$this->CI->Certificate_model->change_status(
                    $payment['payable_id'],
                    'awaiting_payment',
                    $staff_user_id
                )) {
                    return [
                        'success'=>false,
                        'message'=>$this->CI->Certificate_model->transition_error()
                            ?: 'The related certificate request is not in payment verification.'
                    ];
                }
                break;

            case 'donation':
                break;

            default:
                return [
                    'success'=>false,
                    'message'=>'This payment type is not yet supported by the rejection workflow.'
                ];
        }

        return ['success'=>true];
    }

    private function notify_verified(array $payment, $receipt_no)
    {
        if ($payment['payable_type'] === 'service_booking') {
            $booking = $this->CI->Booking_model->get($payment['payable_id']);
            if ($booking) {
                $this->CI->Notification_model->push(
                    $booking['user_id'],
                    'Payment Verified',
                    'Your payment for ' . $booking['booking_code'] . ' has been verified. Official Receipt: ' . $receipt_no,
                    site_url('my/bookings/' . $payment['payable_id'])
                );
            }
            return;
        }

        if ($payment['payable_type'] === 'certificate_request') {
            $cert = $this->CI->Certificate_model->get($payment['payable_id']);
            if ($cert) {
                $this->CI->Notification_model->push(
                    $cert['user_id'],
                    'Payment Verified',
                    'Your certificate payment has been verified. Official Receipt: ' . $receipt_no,
                    site_url('my/certificates/' . $payment['payable_id'])
                );
            }
            return;
        }

        if ($payment['payable_type'] === 'donation') {
            $donation = $this->CI->Donation_model->get($payment['payable_id']);
            if ($donation && !empty($donation['user_id'])) {
                $target = !empty($donation['campaign_title'])
                    ? $donation['campaign_title']
                    : 'the parish';

                $this->CI->Notification_model->push(
                    $donation['user_id'],
                    'Donation Verified',
                    'Thank you. Your donation to ' . $target . ' has been verified. Official Receipt: ' . $receipt_no,
                    site_url('my/donations/new')
                );
            }
        }
    }

    private function notify_rejected(array $payment, $reason)
    {
        if ($payment['payable_type'] === 'service_booking') {
            $booking = $this->CI->Booking_model->get($payment['payable_id']);
            if ($booking) {
                $this->CI->Notification_model->push(
                    $booking['user_id'],
                    'Payment Needs Attention',
                    'Your payment proof for ' . $booking['booking_code'] . ' was not verified. Reason: ' . $reason,
                    site_url('my/payments/pay/service_booking/' . $payment['payable_id'])
                );
            }
            return;
        }

        if ($payment['payable_type'] === 'certificate_request') {
            $cert = $this->CI->Certificate_model->get($payment['payable_id']);
            if ($cert) {
                $this->CI->Notification_model->push(
                    $cert['user_id'],
                    'Certificate Payment Needs Attention',
                    'Your certificate payment proof was not verified. Reason: ' . $reason,
                    site_url('my/payments/pay/certificate_request/' . $payment['payable_id'])
                );
            }
            return;
        }

        if ($payment['payable_type'] === 'donation') {
            $donation = $this->CI->Donation_model->get($payment['payable_id']);
            if ($donation && !empty($donation['user_id'])) {
                $this->CI->Notification_model->push(
                    $donation['user_id'],
                    'Donation Payment Needs Attention',
                    'Your donation payment proof was not verified. Reason: ' . $reason,
                    site_url('my/payments/pay/donation/' . $payment['payable_id'])
                );
            }
        }
    }
}
