<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Service_type extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_ADMIN, ROLE_SECRETARY]);
        $this->load->model('ServiceType_model');
    }

    public function index()
    {
        $data['service_types'] = $this->ServiceType_model->all();
        $this->render_app('admin/service_type_list', $data, 'layouts/app_admin');
    }

    public function get($id)
    {
        $service = $this->ServiceType_model->get($id);
        if (!$service) return $this->json(['success' => false, 'message' => 'Service not found.'], 404);
        $service['requirements'] = $this->ServiceType_model->requirements($id);
        $service['schedule_rules'] = $this->ServiceType_model->schedule_rules($id);
        $this->json(['success' => true, 'data' => $service]);
    }

    public function store()
    {
        $this->form_validation->set_rules('name', 'Name', 'required');
        $this->form_validation->set_rules('base_fee', 'Base Fee', 'required|numeric');

        if ($this->form_validation->run() === FALSE) {
            return $this->json(['success' => false, 'message' => strip_tags(validation_errors())]);
        }

        $allow_special = $this->input->post('allow_special_booking') ? 1 : 0;
        $special_start = $this->input->post('special_start_time') ?: null;
        $special_end = $this->input->post('special_end_time') ?: null;
        $min_days = max(0, (int) $this->input->post('min_advance_days'));
        $max_days = max(1, (int) $this->input->post('max_advance_days'));

        if ($max_days < $min_days) {
            return $this->json(['success' => false, 'message' => 'Maximum advance days cannot be lower than minimum advance days.']);
        }

        if ($allow_special) {
            if (!$special_start || !$special_end) {
                return $this->json(['success' => false, 'message' => 'Set both the start and end time for special-booking availability.']);
            }
            if (strtotime('1970-01-01 ' . $special_start) >= strtotime('1970-01-01 ' . $special_end)) {
                return $this->json(['success' => false, 'message' => 'Special-booking end time must be later than the start time.']);
            }
        }

        // These fields were added by the booking resource-protection upgrade.
        // Do not report a false "saved" state when the production database has
        // not yet been migrated.
        foreach (['booking_buffer_before_minutes', 'booking_buffer_minutes', 'special_capacity', 'requires_priest'] as $column) {
            if (!$this->db->field_exists($column, 'service_types')) {
                return $this->json([
                    'success' => false,
                    'message' => 'The booking-protection database upgrade is not installed yet. Run database/migrations/20260924_booking_resource_protection.sql in phpMyAdmin, then save again.'
                ]);
            }
        }

        $buffer_before = is_numeric($this->input->post('booking_buffer_before_minutes'))
            ? max(0, (int) $this->input->post('booking_buffer_before_minutes'))
            : 0;
        $buffer_after = is_numeric($this->input->post('booking_buffer_minutes'))
            ? max(0, (int) $this->input->post('booking_buffer_minutes'))
            : 0;
        $special_capacity = max(1, (int) ($this->input->post('special_capacity') ?: 1));

        $payload = [
            'name'                          => $this->input->post('name', true),
            'description'                   => $this->input->post('description', true),
            'base_fee'                      => $this->input->post('base_fee'),
            'duration_minutes'              => max(15, (int) ($this->input->post('duration_minutes') ?: 60)),
            'uses_main_church'              => $this->input->post('uses_main_church') ? 1 : 0,
            'allow_special_booking'         => $allow_special,
            'special_fee'                   => is_numeric($this->input->post('special_fee')) ? max(0, (float) $this->input->post('special_fee')) : 0,
            'special_capacity'              => $special_capacity,
            'special_start_time'            => $allow_special ? $special_start : null,
            'special_end_time'              => $allow_special ? $special_end : null,
            'slot_interval_minutes'         => max(15, (int) ($this->input->post('slot_interval_minutes') ?: 60)),
            'booking_buffer_before_minutes' => $buffer_before,
            'booking_buffer_minutes'        => $buffer_after,
            'requires_priest'               => $this->input->post('requires_priest') ? 1 : 0,
            'min_advance_days'              => $min_days,
            'max_advance_days'              => $max_days,
            'requires_approval_workflow'    => $this->input->post('requires_approval_workflow') ? 1 : 0,
            'is_active'                     => $this->input->post('is_active') ? 1 : 0,
        ];

        $service_id = (int) $this->input->post('id');
        $saved = $this->ServiceType_model->update($service_id, $payload);

        if (!$saved) {
            $db_error = $this->db->error();
            log_message('error', 'Service settings update failed: ' . ($db_error['message'] ?? 'unknown database error'));
            return $this->json([
                'success' => false,
                'message' => 'The service settings could not be saved. Please verify the database migration and try again.'
            ]);
        }

        $persisted = $this->ServiceType_model->get($service_id);

        if ((int) ($persisted['booking_buffer_before_minutes'] ?? -1) !== $buffer_before
            || (int) ($persisted['booking_buffer_minutes'] ?? -1) !== $buffer_after
            || (int) ($persisted['special_capacity'] ?? -1) !== $special_capacity) {
            log_message('error', 'Service buffer verification failed after update for service ID ' . $service_id);
            return $this->json([
                'success' => false,
                'message' => 'The booking protection/capacity values were not persisted by the database. Run database/migrations/20260924_booking_resource_protection.sql in phpMyAdmin, then try again.'
            ]);
        }

        $this->log_activity('Updated service type', 'service_type', $this->input->post('name'));
        $this->json([
            'success' => true,
            'message' => 'Service settings saved.',
            'data' => [
                'booking_buffer_before_minutes' => (int) ($persisted['booking_buffer_before_minutes'] ?? 0),
                'duration_minutes' => (int) ($persisted['duration_minutes'] ?? 0),
                'booking_buffer_minutes' => (int) ($persisted['booking_buffer_minutes'] ?? 0),
                'special_capacity' => (int) ($persisted['special_capacity'] ?? 1),
            ],
        ]);
    }

    public function add_requirement()
    {
        $this->db->insert('service_requirements', [
            'service_type_id' => $this->input->post('service_type_id'),
            'label'            => $this->input->post('label', true),
            'is_required'      => $this->input->post('is_required') ? 1 : 0,
            'display_order'    => 0,
        ]);
        $this->json(['success' => true, 'message' => 'Requirement added.']);
    }

    public function delete_requirement($id)
    {
        $this->db->where('id', $id)->delete('service_requirements');
        $this->json(['success' => true]);
    }

    public function save_schedule_rule()
    {
        $service_type_id = (int) $this->input->post('service_type_id');
        $id = (int) $this->input->post('id');
        $service = $this->ServiceType_model->get($service_type_id);
        if (!$service) return $this->json(['success' => false, 'message' => 'Service not found.']);

        $day = (int) $this->input->post('day_of_week');
        if ($day < 0 || $day > 6) {
            return $this->json(['success' => false, 'message' => 'Please select a valid weekday.']);
        }

        $weeks = preg_replace('/[^1-5,]/', '', (string) $this->input->post('week_numbers'));
        $week_list = array_values(array_unique(array_filter(array_map('intval', explode(',', $weeks)))));
        if (empty($week_list)) {
            return $this->json(['success' => false, 'message' => 'Enter at least one week occurrence, such as 2,4.']);
        }
        sort($week_list);

        $start_time = $this->input->post('start_time') ?: null;
        $capacity = max(1, (int) $this->input->post('capacity'));

        $payload = [
            'service_type_id' => $service_type_id,
            'rule_name'       => trim((string) $this->input->post('rule_name', true)) ?: 'Regular Schedule',
            'day_of_week'     => $day,
            'week_numbers'    => implode(',', $week_list),
            'start_time'      => $start_time,
            'fee_amount'      => is_numeric($this->input->post('fee_amount')) ? $this->input->post('fee_amount') : 0,
            'capacity'        => $capacity,
            'valid_from'      => $this->input->post('valid_from') ?: null,
            'valid_until'     => $this->input->post('valid_until') ?: null,
            'is_active'       => $this->input->post('is_active') ? 1 : 0,
            'display_order'   => (int) $this->input->post('display_order'),
        ];

        if ($id) {
            $existing = $this->ServiceType_model->get_schedule_rule($id);
            if (!$existing || (int) $existing['service_type_id'] !== $service_type_id) {
                return $this->json(['success' => false, 'message' => 'Schedule rule not found.']);
            }
        }

        $this->ServiceType_model->save_schedule_rule($payload, $id ?: null);
        $this->log_activity('Saved service schedule rule', 'service_type', $service['name']);
        $this->json(['success' => true, 'message' => 'Regular schedule saved.']);
    }

    public function delete_schedule_rule($id)
    {
        $rule = $this->ServiceType_model->get_schedule_rule($id);
        if (!$rule) return $this->json(['success' => false, 'message' => 'Schedule rule not found.']);

        $this->ServiceType_model->delete_schedule_rule($id);
        $this->json(['success' => true, 'message' => 'Schedule rule removed.']);
    }
}
