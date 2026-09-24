<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Booking extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_PARISHIONER]);
        $this->load->model(['Booking_model', 'ServiceType_model', 'Payment_model']);
    }

    /** GET /my/bookings - list + service catalog to start a new one */
    public function index()
    {
        $data['service_types'] = $this->ServiceType_model->all_active();
        $this->render_app('parishioner/booking_list', $data, 'layouts/app_parishioner');
    }

    /** AJAX DataTables source, scoped to the logged-in parishioner */
    public function datatable()
    {
        $request = $this->input->post();
        $scope = ['user_id' => $this->current_user['id']];

        $total = $this->Booking_model->datatable_query($request, $scope, true);
        $rows  = $this->Booking_model->datatable_query($request, $scope, false);

        $data = [];
        foreach ($rows as $r) {
            $data[] = [
                'booking_code' => $r['booking_code'],
                'service_name' => $r['service_name'],
                'preferred_date' => ($r['confirmed_date'] ? format_datetime($r['confirmed_date']) : format_date($r['preferred_date']))
                    . '<div class="text-[11px] mt-0.5 ' . (($r['booking_type'] ?? 'special') === 'regular' ? 'text-emerald-600' : 'text-amber-600') . '">' . (($r['booking_type'] ?? 'special') === 'regular' ? 'Regular / Parish Schedule' : 'Special Booking') . '</div>',
                'status' => '<span class="px-2.5 py-1 rounded-full text-xs font-medium ' . status_badge_class($r['status']) . '">' . status_label($r['status']) . '</span>',
                'created_at' => format_date($r['created_at']),
                'actions' => '<a href="' . site_url('my/bookings/' . $r['id']) . '" class="text-emerald-700 hover:underline font-medium">View</a>',
            ];
        }

        $this->json([
            'draw' => (int) ($request['draw'] ?? 1),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data,
        ]);
    }

    /** GET /my/bookings/new/{service_key} - booking form */
    public function create($service_key = null)
    {
        $service = $this->ServiceType_model->find_by_key($service_key);
        if (!$service) show_404();

        $data['service']             = $service;
        $data['requirements']        = $this->ServiceType_model->requirements($service['id']);
        $data['schedule_rules']      = $this->ServiceType_model->schedule_rules($service['id'], true);
        $data['active_priest_count'] = $this->Booking_model->active_priest_count();
        $this->render_app('parishioner/booking_form', $data, 'layouts/app_parishioner');
    }

    /** AJAX: availability-based schedule lookup. */
    public function availability()
    {
        $service_id = (int) $this->input->post('service_type_id');
        $booking_type = $this->input->post('booking_type', true);
        $service = $this->ServiceType_model->get($service_id);
        if (!$service || empty($service['is_active'])) {
            return $this->json(['success' => false, 'message' => 'Service is not available.']);
        }

        if ($booking_type === 'regular') {
            $slots = $this->Booking_model->upcoming_regular_slots($service_id, 10);
            return $this->json([
                'success' => true,
                'slots' => $slots,
                'message' => empty($slots)
                    ? 'No regular slots are currently available. The parish may still be configuring the schedule, or the published slots are already full.'
                    : '',
            ]);
        }

        if ($booking_type === 'special') {
            $date = $this->input->post('date', true);

            // Default casual-user flow: show actual date + time choices in one
            // step instead of asking for a date and then making the user check
            // whether that date still has an open time.
            if (!$date) {
                $slots = $this->Booking_model->upcoming_special_slots($service_id, 12);
                return $this->json([
                    'success' => true,
                    'slots' => $slots,
                    'message' => empty($slots)
                        ? 'No special-booking schedules are currently available within the parish booking window.'
                        : '',
                ]);
            }

            // Retained for compatibility with any older UI/client that still
            // requests the times for one specific date.
            $result = $this->Booking_model->special_slots_for_date($service_id, $date);
            return $this->json($result);
        }

        $this->json(['success' => false, 'message' => 'Please choose Regular / Free or Special booking.']);
    }

    /** POST /my/bookings/store - submit new application (AJAX) */
    public function store()
    {
        $service_id = (int) $this->input->post('service_type_id');
        $service = $this->ServiceType_model->get($service_id);
        if (!$service) {
            return $this->json(['success' => false, 'message' => 'Invalid service selected.']);
        }

        $this->form_validation->set_rules('booking_type', 'Booking Type', 'required|in_list[regular,special]');
        $this->form_validation->set_rules('schedule_start', 'Available Schedule', 'required');

        if ($this->form_validation->run() === FALSE) {
            return $this->json(['success' => false, 'message' => strip_tags(validation_errors())]);
        }

        $booking_type = $this->input->post('booking_type', true);
        $schedule_start = $this->input->post('schedule_start', true);
        $schedule_rule_id = $this->input->post('schedule_rule_id') ?: null;

        // Never trust a date/time or fee coming from the browser. Re-resolve the
        // selected slot from current parish rules and current bookings.
        $slot = $this->Booking_model->resolve_slot(
            $service_id,
            $booking_type,
            $schedule_start,
            $schedule_rule_id
        );
        if (empty($slot['valid'])) {
            return $this->json(['success' => false, 'message' => $slot['message'] ?? 'That schedule is no longer available.']);
        }

        // Collect all non-system POST fields into a flexible details JSON blob.
        $skip = ['service_type_id', 'booking_type', 'schedule_start', 'schedule_rule_id', 'preferred_date', 'alternative_date', '<csrf>'];
        $details = [];
        foreach ($this->input->post() as $k => $v) {
            if (!in_array($k, $skip, true) && strpos($k, $this->security->get_csrf_token_name()) === FALSE) {
                $details[$k] = is_array($v) ? $v : trim((string) $v);
            }
        }
        $details['schedule_label'] = $slot['rule_name'];

        $this->db->trans_start();

        // Serialize submissions for this service while we re-check capacity.
        // This prevents two families from taking the final Baptism place at
        // the same instant and exceeding the configured session capacity.
        $this->db->query('SELECT id FROM service_types WHERE id = ? FOR UPDATE', [$service_id]);

        // Recheck inside the transaction after acquiring the service lock.
        $slot = $this->Booking_model->resolve_slot(
            $service_id,
            $booking_type,
            $schedule_start,
            $schedule_rule_id
        );
        if (empty($slot['valid'])) {
            $this->db->trans_complete();
            return $this->json(['success' => false, 'message' => $slot['message'] ?? 'That schedule was just taken. Please choose another slot.']);
        }

        $booking_id = $this->Booking_model->create([
            'booking_code'      => $this->Booking_model->generate_code($service['service_key']),
            'service_type_id'   => $service_id,
            'booking_type'      => $slot['booking_type'],
            'schedule_rule_id'  => $slot['schedule_rule_id'],
            'user_id'           => $this->current_user['id'],
            'preferred_date'    => $slot['date'],
            'alternative_date'  => null,
            'confirmed_date'    => $slot['datetime'],
            'status'            => $service['requires_approval_workflow'] ? 'under_review' : 'submitted',
            'fee_amount'        => $slot['fee'],
            'details'           => json_encode($details),
        ]);

        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            return $this->json(['success' => false, 'message' => 'The booking could not be saved. Please try again.']);
        }

        // Handle multi-file requirement uploads
        if (!empty($_FILES['documents']['name'][0])) {
            $this->_handle_uploads($booking_id);
        }

        $this->flash_success('Your application (' . $this->db->get_where('service_bookings', ['id' => $booking_id])->row()->booking_code . ') has been submitted.');
        $this->json(['success' => true, 'redirect' => site_url('my/bookings/' . $booking_id)]);
    }

    private function _handle_uploads($booking_id)
    {
        $req_ids = $this->input->post('requirement_id') ?: [];
        $count = count($_FILES['documents']['name']);
        $target_dir = FCPATH . UPLOAD_DOCUMENTS;
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

        for ($i = 0; $i < $count; $i++) {
            if ($_FILES['documents']['error'][$i] !== UPLOAD_ERR_OK) continue;

            $ext = pathinfo($_FILES['documents']['name'][$i], PATHINFO_EXTENSION);
            $safe_name = 'doc_' . $booking_id . '_' . uniqid() . '.' . $ext;
            $dest = $target_dir . $safe_name;

            if (move_uploaded_file($_FILES['documents']['tmp_name'][$i], $dest)) {
                $this->Booking_model->add_document([
                    'booking_id'     => $booking_id,
                    'requirement_id' => $req_ids[$i] ?? null,
                    'file_name'      => $safe_name,
                    'original_name'  => $_FILES['documents']['name'][$i],
                    'file_path'      => UPLOAD_DOCUMENTS . $safe_name,
                    'mime_type'      => $_FILES['documents']['type'][$i],
                    'uploaded_at'    => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    /** GET /my/bookings/{id} - view single application & status timeline */
    public function view($id)
    {
        $booking = $this->Booking_model->get($id);
        if (!$booking || (int) $booking['user_id'] !== (int) $this->current_user['id']) show_404();

        $data['booking']  = $booking;
        $data['documents'] = $this->Booking_model->documents($id);
        $data['history']  = $this->Booking_model->status_history($id);
        $data['payment']  = $this->Payment_model->for_payable('service_booking', $id);

        $this->render_app('parishioner/booking_view', $data, 'layouts/app_parishioner');
    }

    /** AJAX: cancel a pending application */
    public function cancel($id)
    {
        $booking = $this->Booking_model->get($id);
        if (!$booking || (int) $booking['user_id'] !== (int) $this->current_user['id']) {
            return $this->json(['success' => false, 'message' => 'Not found.']);
        }
        if (in_array($booking['status'], ['completed', 'cancelled'], true)) {
            return $this->json(['success' => false, 'message' => 'This application can no longer be cancelled.']);
        }

        $this->Booking_model->change_status($id, 'cancelled', $this->current_user['id'], 'Cancelled by parishioner');
        $this->json(['success' => true, 'message' => 'Application cancelled.']);
    }
}
