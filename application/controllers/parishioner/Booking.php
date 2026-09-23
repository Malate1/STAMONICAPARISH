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
                'preferred_date' => format_date($r['preferred_date']),
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

        $data['service']      = $service;
        $data['requirements'] = $this->ServiceType_model->requirements($service['id']);
        $this->render_app('parishioner/booking_form', $data, 'layouts/app_parishioner');
    }

    /** POST /my/bookings/store - submit new application (AJAX) */
    public function store()
    {
        $service_id = (int) $this->input->post('service_type_id');
        $service = $this->ServiceType_model->get($service_id);
        if (!$service) {
            return $this->json(['success' => false, 'message' => 'Invalid service selected.']);
        }

        $this->form_validation->set_rules('preferred_date', 'Preferred Date', 'required');

        if ($this->form_validation->run() === FALSE) {
            return $this->json(['success' => false, 'message' => strip_tags(validation_errors())]);
        }

        // Collect all non-system POST fields into a flexible "details" JSON blob
        $skip = ['service_type_id', 'preferred_date', 'alternative_date', '<csrf>'];
        $details = [];
        foreach ($this->input->post() as $k => $v) {
            if (!in_array($k, $skip, true) && strpos($k, $this->security->get_csrf_token_name()) === FALSE) {
                $details[$k] = is_array($v) ? $v : trim((string) $v);
            }
        }

        $booking_id = $this->Booking_model->create([
            'booking_code'      => $this->Booking_model->generate_code($service['service_key']),
            'service_type_id'   => $service_id,
            'user_id'           => $this->current_user['id'],
            'preferred_date'    => $this->input->post('preferred_date'),
            'alternative_date'  => $this->input->post('alternative_date') ?: null,
            'status'            => $service['requires_approval_workflow'] ? 'under_review' : 'submitted',
            'fee_amount'        => $service['base_fee'],
            'details'           => json_encode($details),
        ]);

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
