<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Walkin extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_SECRETARY]);
        $this->load->model(['ServiceType_model', 'Booking_model']);
    }

    public function index()
    {
        $services = $this->ServiceType_model->all_active();
        $catalog = [];

        foreach ($services as $service) {
            $service['requirements'] = $this->ServiceType_model->requirements($service['id']);
            $service['schedule_rules'] = $this->ServiceType_model->schedule_rules($service['id'], true);
            $service['has_regular'] = !empty($service['schedule_rules']);
            $catalog[(int) $service['id']] = $service;
        }

        $data['service_types'] = $services;
        $data['service_catalog'] = $catalog;
        $data['active_priest_count'] = $this->Booking_model->active_priest_count();

        $this->render_app('staff/walkin', $data, 'layouts/app_admin');
    }

    /**
     * AJAX: check whether an email already belongs to a parishioner account.
     */
    public function lookup()
    {
        $email = trim((string) $this->input->post('email', true));

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json([
                'success' => false,
                'message' => 'Enter a valid email address first.',
            ]);
        }

        $user = $this->User_model->find_by_email($email);

        if (!$user) {
            return $this->json([
                'success' => true,
                'exists' => false,
                'message' => 'No account found. A Parishioner account will be created when the walk-in booking is submitted.',
            ]);
        }

        if ((int) $user['role_id'] !== (int) ROLE_PARISHIONER) {
            return $this->json([
                'success' => false,
                'exists' => true,
                'role_conflict' => true,
                'message' => 'This email belongs to a ' . role_label($user['role_id']) . ' account. Use the parishioner’s own email address instead.',
            ]);
        }

        if (($user['status'] ?? 'active') !== 'active') {
            return $this->json([
                'success' => false,
                'exists' => true,
                'message' => 'This Parishioner account is currently ' . status_label($user['status']) . '. Reactivate the account before recording a new booking under it.',
            ]);
        }

        return $this->json([
            'success' => true,
            'exists' => true,
            'message' => 'Existing Parishioner account found. This walk-in booking will be linked to it.',
            'data' => [
                'id' => (int) $user['id'],
                'full_name' => trim($user['first_name'] . ' ' . ($user['middle_name'] ? $user['middle_name'] . ' ' : '') . $user['last_name']),
                'mobile_number' => $user['mobile_number'],
                'email' => $user['email'],
            ],
        ]);
    }

    /**
     * AJAX: use exactly the same availability engine as online parishioner bookings.
     */
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
                    ? 'No regular slots are currently available. The parish schedule may still be under configuration or all published slots are full.'
                    : '',
            ]);
        }

        if ($booking_type === 'special') {
            $slots = $this->Booking_model->upcoming_special_slots($service_id, 12);

            return $this->json([
                'success' => true,
                'slots' => $slots,
                'message' => empty($slots)
                    ? 'No special-booking schedules are currently available within the parish booking window.'
                    : '',
            ]);
        }

        return $this->json([
            'success' => false,
            'message' => 'Choose Regular / Parish Schedule or Special Booking.',
        ]);
    }

    /**
     * Store a walk-in booking through the same slot validation/capacity rules
     * used by the online booking flow.
     */
    public function store()
    {
        $this->form_validation->set_rules('full_name', 'Full Name', 'required|max_length[250]');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|max_length[150]');
        $this->form_validation->set_rules('service_type_id', 'Service', 'required|integer');
        $this->form_validation->set_rules('booking_type', 'Booking Type', 'required|in_list[regular,special]');
        $this->form_validation->set_rules('schedule_start', 'Available Schedule', 'required');

        if ($this->form_validation->run() === false) {
            return $this->json([
                'success' => false,
                'message' => strip_tags(validation_errors()),
            ]);
        }

        $service_id = (int) $this->input->post('service_type_id');
        $service = $this->ServiceType_model->get($service_id);

        if (!$service || empty($service['is_active'])) {
            return $this->json([
                'success' => false,
                'message' => 'The selected parish service is no longer active.',
            ]);
        }

        $email = trim((string) $this->input->post('email', true));
        $full_name = trim((string) $this->input->post('full_name', true));
        $mobile_number = trim((string) $this->input->post('mobile_number', true));
        $existing_user = $this->User_model->find_by_email($email);

        if ($existing_user && (int) $existing_user['role_id'] !== (int) ROLE_PARISHIONER) {
            return $this->json([
                'success' => false,
                'message' => 'The email entered belongs to a ' . role_label($existing_user['role_id']) . ' account. A walk-in booking must be linked to a Parishioner account.',
            ]);
        }

        if ($existing_user && ($existing_user['status'] ?? 'active') !== 'active') {
            return $this->json([
                'success' => false,
                'message' => 'The existing Parishioner account is currently ' . status_label($existing_user['status']) . '. Reactivate it before recording this booking.',
            ]);
        }

        $booking_type = $this->input->post('booking_type', true);
        $schedule_start = $this->input->post('schedule_start', true);
        $schedule_rule_id = $this->input->post('schedule_rule_id') ?: null;

        // First check gives staff immediate feedback; the same slot is checked
        // again under a database lock before the booking is actually created.
        $slot = $this->Booking_model->resolve_slot(
            $service_id,
            $booking_type,
            $schedule_start,
            $schedule_rule_id
        );

        if (empty($slot['valid'])) {
            return $this->json([
                'success' => false,
                'message' => $slot['message'] ?? 'That schedule is no longer available.',
            ]);
        }

        $skip = [
            'full_name',
            'email',
            'mobile_number',
            'service_type_id',
            'booking_type',
            'schedule_start',
            'schedule_rule_id',
            'requirement_id',
            'preferred_date',
            'alternative_date',
        ];

        $details = [];
        foreach ($this->input->post() as $key => $value) {
            if (in_array($key, $skip, true)) continue;
            if (strpos($key, $this->security->get_csrf_token_name()) !== false) continue;

            $details[$key] = is_array($value)
                ? $value
                : trim((string) $value);
        }

        $details['schedule_label'] = $slot['rule_name'];
        $details['intake_channel'] = 'walk_in';
        $details['recorded_by'] = trim($this->current_user['first_name'] . ' ' . $this->current_user['last_name']);

        $new_account = false;
        $user_id = $existing_user ? (int) $existing_user['id'] : null;

        $this->db->trans_begin();

        // Serialize booking creation for this service, then recheck capacity.
        $this->db->query(
            'SELECT id FROM service_types WHERE id = ? FOR UPDATE',
            [$service_id]
        );

        $slot = $this->Booking_model->resolve_slot(
            $service_id,
            $booking_type,
            $schedule_start,
            $schedule_rule_id
        );

        if (empty($slot['valid'])) {
            $this->db->trans_rollback();

            return $this->json([
                'success' => false,
                'message' => $slot['message'] ?? 'That schedule was just taken. Please choose another slot.',
            ]);
        }

        if (!$user_id) {
            $name = $this->split_name($full_name);

            $user_id = $this->User_model->create([
                'role_id' => ROLE_PARISHIONER,
                'first_name' => $name['first_name'],
                'middle_name' => $name['middle_name'],
                'last_name' => $name['last_name'],
                'email' => $email,
                'mobile_number' => $mobile_number ?: null,
                'password_hash' => password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT),
                'status' => 'active',
            ]);

            if (!$user_id) {
                $this->db->trans_rollback();

                return $this->json([
                    'success' => false,
                    'message' => 'The Parishioner profile could not be created. Please try again.',
                ]);
            }

            $new_account = true;
        } elseif ($mobile_number && empty($existing_user['mobile_number'])) {
            // Preserve existing profile data, but fill a missing contact number
            // supplied directly by the parishioner during the office visit.
            $this->User_model->update($user_id, ['mobile_number' => $mobile_number]);
        }

        $status = !empty($service['requires_approval_workflow'])
            ? 'under_review'
            : 'submitted';

        $staff_name = trim($this->current_user['first_name'] . ' ' . $this->current_user['last_name']);
        $booking_id = $this->Booking_model->create([
            'booking_code' => $this->Booking_model->generate_code($service['service_key']),
            'service_type_id' => $service_id,
            'booking_type' => $slot['booking_type'],
            'schedule_rule_id' => $slot['schedule_rule_id'],
            'user_id' => $user_id,
            'preferred_date' => $slot['date'],
            'alternative_date' => null,
            'confirmed_date' => $slot['datetime'],
            'status' => $status,
            'fee_amount' => $slot['fee'],
            'details' => json_encode($details),
            'staff_notes' => 'Recorded as a walk-in booking by ' . $staff_name . '.',
            'processed_by' => $this->current_user['id'],
            '_history_changed_by' => $this->current_user['id'],
            '_history_remarks' => 'Walk-in application recorded at the parish office by ' . $staff_name,
        ]);

        if (!$booking_id || $this->db->trans_status() === false) {
            $this->db->trans_rollback();

            return $this->json([
                'success' => false,
                'message' => 'The walk-in booking could not be saved. Please try again.',
            ]);
        }

        $this->db->trans_commit();

        $upload_result = $this->handle_uploads($booking_id);

        $this->log_activity(
            'Recorded walk-in booking',
            'walkin',
            $service['name'] . ' for ' . $email . ' — ' . $slot['datetime']
        );

        $message = 'Walk-in booking recorded using the same availability and review workflow as an online booking.';
        if ($new_account) {
            $message .= ' A new Parishioner profile was also created for this email. If the parishioner wants portal access later, use the existing password-reset process for that email.';
        }
        if (!empty($upload_result['warnings'])) {
            $message .= ' Some document uploads were skipped; review the booking requirements.';
        }

        return $this->json([
            'success' => true,
            'message' => $message,
            'booking_id' => (int) $booking_id,
            'new_account' => $new_account,
            'upload_warnings' => $upload_result['warnings'],
            'redirect' => site_url('staff/booking/view/' . $booking_id),
        ]);
    }

    private function split_name($full_name)
    {
        $parts = preg_split('/\s+/', trim($full_name), -1, PREG_SPLIT_NO_EMPTY);

        if (count($parts) <= 1) {
            return [
                'first_name' => $parts[0] ?? 'Parishioner',
                'middle_name' => null,
                'last_name' => '',
            ];
        }

        $first = array_shift($parts);
        $last = array_pop($parts);

        return [
            'first_name' => $first,
            'middle_name' => $parts ? implode(' ', $parts) : null,
            'last_name' => $last,
        ];
    }

    private function handle_uploads($booking_id)
    {
        $result = ['uploaded' => 0, 'warnings' => []];

        if (empty($_FILES['documents']['name']) || !is_array($_FILES['documents']['name'])) {
            return $result;
        }

        $requirement_ids = $this->input->post('requirement_id') ?: [];
        $count = count($_FILES['documents']['name']);
        $target_dir = FCPATH . UPLOAD_DOCUMENTS;

        if (!is_dir($target_dir) && !@mkdir($target_dir, 0755, true)) {
            $result['warnings'][] = 'The booking was saved but the document upload directory is unavailable.';
            return $result;
        }

        $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];

        for ($i = 0; $i < $count; $i++) {
            if (empty($_FILES['documents']['name'][$i])) continue;

            if ($_FILES['documents']['error'][$i] !== UPLOAD_ERR_OK) {
                $result['warnings'][] = $_FILES['documents']['name'][$i] . ' could not be uploaded.';
                continue;
            }

            if ((int) $_FILES['documents']['size'][$i] > 5 * 1024 * 1024) {
                $result['warnings'][] = $_FILES['documents']['name'][$i] . ' is larger than 5 MB.';
                continue;
            }

            $extension = strtolower(pathinfo($_FILES['documents']['name'][$i], PATHINFO_EXTENSION));
            if (!in_array($extension, $allowed_extensions, true)) {
                $result['warnings'][] = $_FILES['documents']['name'][$i] . ' is not an accepted JPG, PNG or PDF file.';
                continue;
            }

            $safe_name = 'doc_' . $booking_id . '_' . bin2hex(random_bytes(6)) . '.' . ($extension === 'jpeg' ? 'jpg' : $extension);
            $destination = $target_dir . $safe_name;

            if (!move_uploaded_file($_FILES['documents']['tmp_name'][$i], $destination)) {
                $result['warnings'][] = $_FILES['documents']['name'][$i] . ' could not be saved.';
                continue;
            }

            $this->Booking_model->add_document([
                'booking_id' => $booking_id,
                'requirement_id' => isset($requirement_ids[$i]) && $requirement_ids[$i] !== ''
                    ? (int) $requirement_ids[$i]
                    : null,
                'file_name' => $safe_name,
                'original_name' => $_FILES['documents']['name'][$i],
                'file_path' => UPLOAD_DOCUMENTS . $safe_name,
                'mime_type' => $_FILES['documents']['type'][$i] ?: null,
                'uploaded_at' => date('Y-m-d H:i:s'),
            ]);

            $result['uploaded']++;
        }

        return $result;
    }
}
