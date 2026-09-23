<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Booking extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_ADMIN, ROLE_SECRETARY]);
        $this->load->model(['Booking_model', 'ServiceType_model', 'Payment_model', 'Notification_model']);
    }

    public function index()
    {
        $data['service_types'] = $this->ServiceType_model->all_active();
        $this->render_app('admin/booking_list', $data, 'layouts/app_admin');
    }

    public function datatable()
    {
        $request = $this->input->post();
        $total = $this->Booking_model->datatable_query($request, [], true);
        $rows  = $this->Booking_model->datatable_query($request, [], false);

        $data = [];
        foreach ($rows as $r) {
            $data[] = [
                'booking_code' => '<a href="' . site_url('admin/booking/view/' . $r['id']) . '" class="font-medium text-emerald-700 hover:underline">' . $r['booking_code'] . '</a>',
                'service_name' => $r['service_name'],
                'applicant'    => $r['first_name'] . ' ' . $r['last_name'] . '<div class="text-xs text-gray-400">' . $r['email'] . '</div>',
                'preferred_date' => ($r['confirmed_date'] ? format_datetime($r['confirmed_date']) : format_date($r['preferred_date']))
                    . '<div class="text-[11px] mt-0.5 ' . (($r['booking_type'] ?? 'special') === 'regular' ? 'text-emerald-600' : 'text-amber-600') . '">' . (($r['booking_type'] ?? 'special') === 'regular' ? 'Regular / Parish Schedule' : 'Special Booking') . '</div>',
                'status'       => '<span class="px-2.5 py-1 rounded-full text-xs font-medium ' . status_badge_class($r['status']) . '">' . status_label($r['status']) . '</span>',
                'created_at'   => format_date($r['created_at']),
                'actions'      => '<a href="' . site_url('admin/booking/view/' . $r['id']) . '" class="text-emerald-700 hover:underline font-medium">Review</a>',
            ];
        }

        $this->json(['draw' => (int) ($request['draw'] ?? 1), 'recordsTotal' => $total, 'recordsFiltered' => $total, 'data' => $data]);
    }

    public function view($id)
    {
        $booking = $this->Booking_model->get($id);
        if (!$booking) show_404();

        $data['booking']   = $booking;
        $data['documents'] = $this->Booking_model->documents($id);
        $data['history']   = $this->Booking_model->status_history($id);
        $data['payment']   = $this->Payment_model->for_payable('service_booking', $id);
        $data['priests']   = $this->Booking_model->priests_list();

        $this->render_app('admin/booking_view', $data, 'layouts/app_admin');
    }

    /** AJAX: change status (approve, return, mark requirements complete, etc.) */
    public function update_status()
    {
        $id = (int) $this->input->post('id');
        $status = $this->input->post('status');
        $remarks = $this->input->post('remarks', true);

        $booking = $this->Booking_model->get($id);
        if (!$booking) return $this->json(['success' => false, 'message' => 'Not found.']);

        $this->Booking_model->change_status($id, $status, $this->current_user['id'], $remarks);

        if ($status === 'missing_requirements' && $remarks) {
            $this->Booking_model->update($id, ['rejection_reason' => $remarks]);
        }

        $this->Notification_model->push(
            $booking['user_id'],
            'Application Update',
            'Your ' . $booking['service_name'] . ' application (' . $booking['booking_code'] . ') is now ' . status_label($status) . '.',
            site_url('my/bookings/' . $id)
        );

        $this->log_activity('Updated booking status to ' . $status, 'booking', $booking['booking_code']);
        $this->json(['success' => true, 'message' => 'Status updated.']);
    }

    /** AJAX: assign a priest & confirm date/time */
    public function assign()
    {
        $id = (int) $this->input->post('id');
        $priest_id = $this->input->post('priest_id') ?: null;
        $confirmed_date = $this->input->post('confirmed_date');

        $booking = $this->Booking_model->get($id);
        if (!$booking) return $this->json(['success' => false, 'message' => 'Not found.']);

        if ($confirmed_date) {
            // If staff changes the reserved schedule, validate it against the same
            // availability rules used by parishioners and against other church bookings.
            $current_normalized = $booking['confirmed_date'] ? date('Y-m-d H:i:s', strtotime($booking['confirmed_date'])) : null;
            $new_normalized = date('Y-m-d H:i:s', strtotime($confirmed_date));
            if ($new_normalized !== $current_normalized) {
                $slot = $this->Booking_model->resolve_slot(
                    $booking['service_type_id'],
                    $booking['booking_type'] ?? 'special',
                    $new_normalized,
                    $booking['schedule_rule_id'] ?? null,
                    $id
                );
                if (empty($slot['valid'])) {
                    return $this->json(['success' => false, 'message' => $slot['message'] ?? 'That schedule is not available.']);
                }
            }

            if ($priest_id) {
                $priest_conflict = $this->Booking_model->priest_has_conflict(
                    $priest_id,
                    $new_normalized,
                    $booking['service_type_id'],
                    $id
                );
                if ($priest_conflict) {
                    return $this->json([
                        'success' => false,
                        'message' => 'This priest already has ' . $priest_conflict['service_name'] . ' during that time. Please choose another priest or schedule.'
                    ]);
                }
            }
        }

        $this->Booking_model->update($id, [
            'assigned_priest_id' => $priest_id,
            'confirmed_date'     => $confirmed_date ?: null,
        ]);

        if ($confirmed_date) {
            $this->Booking_model->change_status($id, 'scheduled', $this->current_user['id'], 'Schedule confirmed');
        }

        $this->log_activity('Assigned priest / confirmed schedule', 'booking', $booking['booking_code']);
        $this->json(['success' => true, 'message' => 'Assignment saved.']);
    }

    /** AJAX: verify an uploaded requirement document */
    public function verify_document()
    {
        $id = (int) $this->input->post('document_id');
        $this->db->where('id', $id)->update('booking_documents', ['verified' => 1]);
        $this->json(['success' => true]);
    }
}
