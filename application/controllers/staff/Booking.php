<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Booking extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_SECRETARY]);
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
                'booking_code' => '<a href="' . site_url('staff/booking/view/' . $r['id']) . '" class="font-medium text-emerald-700 hover:underline">' . $r['booking_code'] . '</a>',
                'service_name' => $r['service_name'],
                'applicant'    => $r['first_name'] . ' ' . $r['last_name'] . '<div class="text-xs text-gray-400">' . $r['email'] . '</div>',
                'preferred_date' => ($r['confirmed_date'] ? format_datetime($r['confirmed_date']) : format_date($r['preferred_date']))
                    . '<div class="text-[11px] mt-0.5 ' . (($r['booking_type'] ?? 'special') === 'regular' ? 'text-emerald-600' : 'text-amber-600') . '">' . (($r['booking_type'] ?? 'special') === 'regular' ? 'Regular / Parish Schedule' : 'Special Booking') . '</div>',
                'status'       => '<span class="px-2.5 py-1 rounded-full text-xs font-medium ' . status_badge_class($r['status']) . '">' . status_label($r['status']) . '</span>',
                'created_at'   => format_date($r['created_at']),
                'actions'      => '<div class="flex items-center justify-center gap-1.5 whitespace-nowrap">'
                    . dt_icon_link('ph-eye', 'Review booking', site_url('staff/booking/view/' . $r['id']))
                    . '</div>',
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
        $data['allowed_statuses'] = $this->Booking_model->allowed_status_transitions($booking);

        $this->render_app('admin/booking_view', $data, 'layouts/app_admin');
    }

    public function update_status()
    {
        $id = (int) $this->input->post('id');
        $status = $this->input->post('status');
        $remarks = $this->input->post('remarks', true);

        $booking = $this->Booking_model->get($id);
        if (!$booking) return $this->json(['success' => false, 'message' => 'Not found.']);

        if (!$this->Booking_model->change_status($id, $status, $this->current_user['id'], $remarks)) {
            return $this->json([
                'success' => false,
                'message' => $this->Booking_model->transition_error() ?: 'That status change is not allowed.'
            ]);
        }
        if ($status === 'missing_requirements' && $remarks) {
            $this->Booking_model->update($id, ['rejection_reason' => $remarks]);
        }

        $this->Notification_model->push(
            $booking['user_id'], 'Application Update',
            'Your ' . $booking['service_name'] . ' application (' . $booking['booking_code'] . ') is now ' . status_label($status) . '.',
            site_url('my/bookings/' . $id)
        );

        $this->log_activity('Updated booking status to ' . $status, 'booking', $booking['booking_code']);
        $this->json(['success' => true, 'message' => 'Status updated.']);
    }

    public function assign()
    {
        $id = (int) $this->input->post('id');
        $booking = $this->Booking_model->get($id);
        if (!$booking) return $this->json(['success'=>false,'message'=>'Not found.']);

        $result = $this->Booking_model->assign_priest_and_schedule(
            $id,
            $this->input->post('priest_id') ?: null,
            $this->input->post('confirmed_date'),
            $this->current_user['id']
        );

        if (!$result) {
            return $this->json([
                'success'=>false,
                'message'=>$this->Booking_model->transition_error() ?: 'The assignment could not be saved.'
            ]);
        }

        $this->log_activity('Assigned priest / confirmed schedule', 'booking', $booking['booking_code']);
        $this->json(['success'=>true,'message'=>$result['message']]);
    }

    public function verify_document()
    {
        $id = (int) $this->input->post('document_id');
        $this->db->where('id', $id)->update('booking_documents', ['verified' => 1]);
        $this->json(['success' => true]);
    }
}
