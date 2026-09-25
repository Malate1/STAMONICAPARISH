<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Booking_model extends CI_Model
{
    protected $table = 'service_bookings';

    public function generate_code($service_key)
    {
        $count = $this->db->count_all_results($this->table) + 1;
        return generate_code($service_key, $count);
    }

    public function create(array $data)
    {
        // Optional metadata used by staff-created/walk-in bookings. These keys
        // are deliberately removed before the booking row is inserted.
        $history_changed_by = array_key_exists('_history_changed_by', $data)
            ? $data['_history_changed_by']
            : ($data['user_id'] ?? null);
        $history_remarks = $data['_history_remarks'] ?? 'Application submitted';
        unset($data['_history_changed_by'], $data['_history_remarks']);

        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        $id = $this->db->insert_id();

        if ($id) {
            $this->add_status_history(
                $id,
                null,
                $data['status'] ?? 'submitted',
                $history_remarks,
                $history_changed_by
            );
        }

        return $id;
    }

    public function update($id, array $data)
    {
        return $this->db->where('id', $id)->update($this->table, $data);
    }

    public function get($id)
    {
        return $this->db->select('service_bookings.*, service_types.name as service_name, service_types.service_key, service_types.icon,
                service_types.duration_minutes, service_types.booking_buffer_before_minutes, service_types.booking_buffer_minutes,
                service_types.uses_main_church, service_types.requires_priest,
                users.first_name, users.last_name, users.email, users.mobile_number,
                priest.first_name as priest_first_name, priest.last_name as priest_last_name')
            ->from($this->table)
            ->join('service_types', 'service_types.id = service_bookings.service_type_id')
            ->join('users', 'users.id = service_bookings.user_id')
            ->join('users as priest', 'priest.id = service_bookings.assigned_priest_id', 'left')
            ->where('service_bookings.id', $id)
            ->get()->row_array();
    }

    public function documents($booking_id)
    {
        return $this->db->select('booking_documents.*, service_requirements.label')
            ->from('booking_documents')
            ->join('service_requirements', 'service_requirements.id = booking_documents.requirement_id', 'left')
            ->where('booking_id', $booking_id)
            ->get()->result_array();
    }

    public function add_document(array $data)
    {
        $this->db->insert('booking_documents', $data);
        return $this->db->insert_id();
    }

    public function status_history($booking_id)
    {
        return $this->db->select('booking_status_history.*, users.first_name, users.last_name')
            ->from('booking_status_history')
            ->join('users', 'users.id = booking_status_history.changed_by', 'left')
            ->where('booking_id', $booking_id)
            ->order_by('changed_at', 'asc')
            ->get()->result_array();
    }

    public function add_status_history($booking_id, $from, $to, $remarks = null, $changed_by = null)
    {
        $this->db->insert('booking_status_history', [
            'booking_id'  => $booking_id,
            'from_status' => $from,
            'to_status'   => $to,
            'remarks'     => $remarks,
            'changed_by'  => $changed_by,
            'changed_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    public function change_status($id, $new_status, $changed_by = null, $remarks = null)
    {
        $booking = $this->get($id);
        if (!$booking) return false;
        $this->update($id, ['status' => $new_status]);
        $this->add_status_history($id, $booking['status'], $new_status, $remarks, $changed_by);
        return true;
    }

    public function for_user($user_id)
    {
        return $this->db->select('service_bookings.*, service_types.name as service_name, service_types.icon')
            ->from($this->table)
            ->join('service_types', 'service_types.id = service_bookings.service_type_id')
            ->where('service_bookings.user_id', $user_id)
            ->order_by('service_bookings.created_at', 'desc')
            ->get()->result_array();
    }

    public function for_priest($priest_id)
    {
        return $this->db->select('service_bookings.*, service_types.name as service_name, users.first_name, users.last_name')
            ->from($this->table)
            ->join('service_types', 'service_types.id = service_bookings.service_type_id')
            ->join('users', 'users.id = service_bookings.user_id')
            ->where('service_bookings.assigned_priest_id', $priest_id)
            ->where_in('service_bookings.status', ['approved', 'scheduled'])
            ->order_by('service_bookings.confirmed_date', 'asc')
            ->get()->result_array();
    }

    public function counts_by_status()
    {
        $rows = $this->db->select('status, COUNT(*) as total')
            ->group_by('status')->get($this->table)->result_array();
        $out = [];
        foreach ($rows as $r) $out[$r['status']] = (int) $r['total'];
        return $out;
    }

    public function today_summary()
    {
        return $this->db->select('service_types.name as service_name, COUNT(*) as total')
            ->from($this->table)
            ->join('service_types', 'service_types.id = service_bookings.service_type_id')
            ->where('DATE(service_bookings.confirmed_date)', date('Y-m-d'))
            ->group_by('service_types.name')
            ->get()->result_array();
    }

    public function priests_list()
    {
        return $this->db->where('role_id', ROLE_PRIEST)->where('status', 'active')->order_by('last_name')->get('users')->result_array();
    }

    public function active_priest_count()
    {
        $active = (int) $this->db->where('role_id', ROLE_PRIEST)->where('status', 'active')->count_all_results('users');
        $row = $this->db->get_where('system_settings', ['setting_key' => 'priest_booking_capacity'])->row_array();
        $capacity = $row ? max(1, (int) $row['setting_value']) : 2;
        return min($active, $capacity);
    }

    /**
     * Return upcoming recurring/regular slots that still have availability.
     */
    public function upcoming_regular_slots($service_type_id, $limit = 8)
    {
        $service = $this->db->get_where('service_types', ['id' => $service_type_id, 'is_active' => 1])->row_array();
        if (!$service) return [];

        $rules = $this->db->where('service_type_id', $service_type_id)
            ->where('is_active', 1)
            ->order_by('display_order', 'asc')
            ->get('service_schedule_rules')->result_array();

        if (empty($rules)) return [];

        $min_days = max(0, (int) ($service['min_advance_days'] ?? 1));
        $max_days = max($min_days, (int) ($service['max_advance_days'] ?? 365));
        $cursor = new DateTimeImmutable(date('Y-m-d'));
        $cursor = $cursor->modify('+' . $min_days . ' day');
        $end = (new DateTimeImmutable(date('Y-m-d')))->modify('+' . $max_days . ' day');
        $slots = [];

        while ($cursor <= $end && count($slots) < $limit) {
            $date = $cursor->format('Y-m-d');

            foreach ($rules as $rule) {
                // A recurring date can be configured before its official time is known.
                // Do not publish it until staff sets a time.
                if (empty($rule['start_time']) || !$this->rule_matches_date($rule, $date)) continue;

                $start = $date . ' ' . $rule['start_time'];
                if (strtotime($start) <= time()) continue;
                $availability = $this->check_slot_availability($service, $start, $rule, null);
                if (!$availability['available']) continue;

                $slots[] = [
                    'booking_type' => 'regular',
                    'schedule_rule_id' => (int) $rule['id'],
                    'datetime' => date('Y-m-d H:i:s', strtotime($start)),
                    'date' => $date,
                    'time' => date('g:i A', strtotime($start)),
                    'day_label' => date('D', strtotime($date)),
                    'date_label' => date('M j, Y', strtotime($date)),
                    'rule_name' => $rule['rule_name'],
                    'fee' => (float) $rule['fee_amount'],
                    'capacity' => (int) $rule['capacity'],
                    'remaining' => $availability['remaining'],
                    'reserved_from' => $availability['reserved_from'],
                    'reserved_until' => $availability['reserved_until'],
                    'nearby_bookings' => $availability['nearby_bookings'],
                    'priest_total' => $availability['priest_total'],
                    'priests_available_for_slot' => $availability['priests_available_for_slot'],
                    'priests_remaining_after_booking' => $availability['priests_remaining_after_booking'],
                ];

                if (count($slots) >= $limit) break;
            }

            $cursor = $cursor->modify('+1 day');
        }

        usort($slots, function ($a, $b) {
            return strcmp($a['datetime'], $b['datetime']);
        });

        return array_slice($slots, 0, $limit);
    }

    /**
     * Return the next dates that actually have at least one special-booking slot.
     * This keeps the parishioner from choosing arbitrary dates that are already
     * occupied by another sacrament or otherwise unavailable.
     */
    public function upcoming_special_dates($service_type_id, $limit = 12)
    {
        $service = $this->db->get_where('service_types', ['id' => $service_type_id, 'is_active' => 1])->row_array();
        if (!$service || empty($service['allow_special_booking'])) return [];
        if (empty($service['special_start_time']) || empty($service['special_end_time'])) return [];

        $min_days = max(0, (int) ($service['min_advance_days'] ?? 1));
        $max_days = max($min_days, (int) ($service['max_advance_days'] ?? 365));
        $cursor = (new DateTimeImmutable(date('Y-m-d')))->modify('+' . $min_days . ' day');
        $end = (new DateTimeImmutable(date('Y-m-d')))->modify('+' . $max_days . ' day');
        $dates = [];

        while ($cursor <= $end && count($dates) < $limit) {
            $date = $cursor->format('Y-m-d');

            // Dates assigned to a regular parish schedule stay under the
            // Regular booking option instead of being sold as special dates.
            if (!$this->date_matches_any_regular_rule($service_type_id, $date)) {
                $result = $this->special_slots_for_date($service_type_id, $date);
                if (!empty($result['success']) && !empty($result['slots'])) {
                    $dates[] = [
                        'date' => $date,
                        'day_label' => $cursor->format('D'),
                        'date_label' => $cursor->format('M j, Y'),
                        'month_label' => $cursor->format('M'),
                        'day_number' => $cursor->format('j'),
                        'available_count' => count($result['slots']),
                        'fee' => (float) $service['special_fee'],
                    ];
                }
            }

            $cursor = $cursor->modify('+1 day');
        }

        return $dates;
    }

    /**
     * Return the next actual date+time choices for Special Booking.
     * Casual users should not have to pick a date first and then discover
     * whether a time is available.
     */
    public function upcoming_special_slots($service_type_id, $limit = 12)
    {
        $service = $this->db->get_where('service_types', ['id' => $service_type_id, 'is_active' => 1])->row_array();
        if (!$service || empty($service['allow_special_booking'])) return [];
        if (empty($service['special_start_time']) || empty($service['special_end_time'])) return [];

        $min_days = max(0, (int) ($service['min_advance_days'] ?? 1));
        $max_days = max($min_days, (int) ($service['max_advance_days'] ?? 365));
        $cursor = (new DateTimeImmutable(date('Y-m-d')))->modify('+' . $min_days . ' day');
        $end = (new DateTimeImmutable(date('Y-m-d')))->modify('+' . $max_days . ' day');
        $slots = [];

        while ($cursor <= $end && count($slots) < $limit) {
            $date = $cursor->format('Y-m-d');

            if (!$this->date_matches_any_regular_rule($service_type_id, $date)) {
                $result = $this->special_slots_for_date($service_type_id, $date);
                if (!empty($result['success']) && !empty($result['slots'])) {
                    foreach ($result['slots'] as $slot) {
                        $slots[] = $slot;
                        if (count($slots) >= $limit) break;
                    }
                }
            }

            $cursor = $cursor->modify('+1 day');
        }

        usort($slots, function ($a, $b) {
            return strcmp($a['datetime'], $b['datetime']);
        });

        return array_slice($slots, 0, $limit);
    }

    /**
     * Generate special-booking time slots for a date selected by the parishioner.
     */
    public function special_slots_for_date($service_type_id, $date)
    {
        $service = $this->db->get_where('service_types', ['id' => $service_type_id, 'is_active' => 1])->row_array();
        if (!$service) return ['success' => false, 'message' => 'Service not found.', 'slots' => []];
        if (empty($service['allow_special_booking'])) {
            return ['success' => false, 'message' => 'Special booking is not enabled for this service.', 'slots' => []];
        }

        $date_obj = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        if (!$date_obj || $date_obj->format('Y-m-d') !== $date) {
            return ['success' => false, 'message' => 'Please select a valid date.', 'slots' => []];
        }

        $range_error = $this->validate_advance_window($service, $date);
        if ($range_error) {
            return ['success' => false, 'message' => $range_error, 'slots' => []];
        }

        if ($this->date_matches_any_regular_rule($service_type_id, $date)) {
            return [
                'success' => false,
                'regular_date' => true,
                'message' => 'This date is part of the parish regular schedule. Please choose an available Regular / Free slot instead.',
                'slots' => [],
            ];
        }

        if (empty($service['special_start_time']) || empty($service['special_end_time'])) {
            return [
                'success' => false,
                'message' => 'Special booking hours are not yet configured by the parish office.',
                'slots' => [],
            ];
        }

        $interval = max(15, (int) ($service['slot_interval_minutes'] ?? 60));
        $before = max(0, (int) ($service['booking_buffer_before_minutes'] ?? 0));
        $duration = max(15, (int) ($service['duration_minutes'] ?? 60));
        $after = max(0, (int) ($service['booking_buffer_minutes'] ?? 0));
        $window_start = new DateTimeImmutable($date . ' ' . $service['special_start_time']);
        $window_end = new DateTimeImmutable($date . ' ' . $service['special_end_time']);
        $first_ceremony = $window_start->modify('+' . $before . ' minutes');
        $slots = [];

        for ($cursor = $first_ceremony; $cursor < $window_end; $cursor = $cursor->modify('+' . $interval . ' minutes')) {
            if ($cursor->getTimestamp() <= time()) continue;
            $protected_end = $cursor->modify('+' . ($duration + $after) . ' minutes');
            if ($protected_end > $window_end) break;

            $availability = $this->check_slot_availability($service, $cursor->format('Y-m-d H:i:s'), null, null);
            if (!$availability['available']) continue;

            $slots[] = [
                'booking_type' => 'special',
                'schedule_rule_id' => null,
                'datetime' => $cursor->format('Y-m-d H:i:s'),
                'date' => $date,
                'time' => $cursor->format('g:i A'),
                'day_label' => $cursor->format('D'),
                'date_label' => $cursor->format('M j, Y'),
                'rule_name' => 'Special Booking',
                'fee' => (float) $service['special_fee'],
                'capacity' => max(1, (int) ($service['special_capacity'] ?? 1)),
                'remaining' => $availability['remaining'],
                'reserved_from' => $availability['reserved_from'],
                'reserved_until' => $availability['reserved_until'],
                'nearby_bookings' => $availability['nearby_bookings'],
                'priest_total' => $availability['priest_total'],
                'priests_available_for_slot' => $availability['priests_available_for_slot'],
                'priests_remaining_after_booking' => $availability['priests_remaining_after_booking'],
            ];
        }

        return ['success' => true, 'message' => '', 'slots' => $slots];
    }

    /**
     * Validate a client-selected slot server-side and derive the fee from parish settings.
     */
    public function resolve_slot($service_type_id, $booking_type, $schedule_start, $schedule_rule_id = null, $exclude_booking_id = null)
    {
        $service = $this->db->get_where('service_types', ['id' => $service_type_id, 'is_active' => 1])->row_array();
        if (!$service) return ['valid' => false, 'message' => 'Invalid service selected.'];

        $timestamp = strtotime($schedule_start);
        if (!$timestamp) return ['valid' => false, 'message' => 'Please select an available schedule.'];
        if ($timestamp <= time()) return ['valid' => false, 'message' => 'Please choose a future schedule.'];

        $normalized = date('Y-m-d H:i:s', $timestamp);
        $date = date('Y-m-d', $timestamp);
        $range_error = $this->validate_advance_window($service, $date);
        if ($range_error) return ['valid' => false, 'message' => $range_error];

        if ($booking_type === 'regular') {
            $rule = $this->db->get_where('service_schedule_rules', [
                'id' => (int) $schedule_rule_id,
                'service_type_id' => $service_type_id,
                'is_active' => 1,
            ])->row_array();

            if (!$rule || empty($rule['start_time']) || !$this->rule_matches_date($rule, $date)) {
                return ['valid' => false, 'message' => 'That regular schedule is no longer available. Please choose another slot.'];
            }

            if (date('H:i:s', $timestamp) !== $rule['start_time']) {
                return ['valid' => false, 'message' => 'The selected time does not match the parish regular schedule.'];
            }

            $availability = $this->check_slot_availability($service, $normalized, $rule, $exclude_booking_id);
            if (!$availability['available']) {
                return ['valid' => false, 'message' => $availability['message']];
            }

            return [
                'valid' => true,
                'datetime' => $normalized,
                'date' => $date,
                'fee' => (float) $rule['fee_amount'],
                'booking_type' => 'regular',
                'schedule_rule_id' => (int) $rule['id'],
                'rule_name' => $rule['rule_name'],
            ];
        }

        if ($booking_type !== 'special' || empty($service['allow_special_booking'])) {
            return ['valid' => false, 'message' => 'Please choose a valid booking type.'];
        }

        if ($this->date_matches_any_regular_rule($service_type_id, $date)) {
            return ['valid' => false, 'message' => 'That date belongs to the parish regular schedule. Please choose the Regular / Free option.'];
        }

        if (empty($service['special_start_time']) || empty($service['special_end_time'])) {
            return ['valid' => false, 'message' => 'Special booking hours are not yet configured by the parish office.'];
        }

        $slot_time = date('H:i:s', $timestamp);
        $open = strtotime($date . ' ' . $service['special_start_time']);
        $close = strtotime($date . ' ' . $service['special_end_time']);
        $before = max(0, (int) ($service['booking_buffer_before_minutes'] ?? 0));
        $duration = max(15, (int) ($service['duration_minutes'] ?? 60));
        $after = max(0, (int) ($service['booking_buffer_minutes'] ?? 0));
        $protected_start = $timestamp - ($before * 60);
        $protected_end = $timestamp + (($duration + $after) * 60);
        if ($protected_start < $open || $protected_end > $close) {
            return ['valid' => false, 'message' => 'That time does not leave enough protected preparation/clearance time inside the parish special-booking hours.'];
        }

        $interval = max(15, (int) ($service['slot_interval_minutes'] ?? 60));
        $first_ceremony = $open + ($before * 60);
        $offset_minutes = (int) (($timestamp - $first_ceremony) / 60);
        if ($offset_minutes % $interval !== 0) {
            return ['valid' => false, 'message' => 'Please select one of the available time slots shown.'];
        }

        $availability = $this->check_slot_availability($service, $normalized, null, $exclude_booking_id);
        if (!$availability['available']) {
            return ['valid' => false, 'message' => $availability['message']];
        }

        return [
            'valid' => true,
            'datetime' => $normalized,
            'date' => $date,
            'fee' => (float) $service['special_fee'],
            'booking_type' => 'special',
            'schedule_rule_id' => null,
            'rule_name' => 'Special Booking',
        ];
    }

    public function priest_has_conflict($priest_id, $schedule_start, $service_type_id, $exclude_booking_id = null)
    {
        if (!$priest_id || !$schedule_start) return false;

        $service = $this->db->get_where('service_types', ['id' => $service_type_id])->row_array();
        if (!$service || empty($service['requires_priest'])) return false;

        $start_ts = strtotime($schedule_start);
        if (!$start_ts) return false;

        $candidate_before = max(0, (int) ($service['booking_buffer_before_minutes'] ?? 0));
        $candidate_duration = max(15, (int) ($service['duration_minutes'] ?? 60));
        $candidate_after = max(0, (int) ($service['booking_buffer_minutes'] ?? 0));
        $candidate_window_start = $start_ts - ($candidate_before * 60);
        $candidate_window_end = $start_ts + (($candidate_duration + $candidate_after) * 60);
        $date = date('Y-m-d', $start_ts);

        $candidate_rule_id = null;
        $candidate_booking_type = null;
        if ($exclude_booking_id) {
            $candidate_booking = $this->db->select('booking_type, schedule_rule_id')
                ->get_where('service_bookings', ['id' => (int) $exclude_booking_id])->row_array();
            $candidate_rule_id = $candidate_booking['schedule_rule_id'] ?? null;
            $candidate_booking_type = $candidate_booking['booking_type'] ?? null;
        }

        $this->db->select('service_bookings.id, service_bookings.service_type_id, service_bookings.booking_type, service_bookings.schedule_rule_id,
                service_bookings.confirmed_date, service_types.name AS service_name,
                service_types.duration_minutes, service_types.booking_buffer_before_minutes,
                service_types.booking_buffer_minutes')
            ->from('service_bookings')
            ->join('service_types', 'service_types.id = service_bookings.service_type_id')
            ->where('service_bookings.assigned_priest_id', (int) $priest_id)
            ->where('service_bookings.confirmed_date IS NOT NULL', null, false)
            ->where('DATE(service_bookings.confirmed_date)', $date)
            ->where_not_in('service_bookings.status', ['cancelled', 'returned']);

        if ($exclude_booking_id) {
            $this->db->where('service_bookings.id !=', (int) $exclude_booking_id);
        }

        foreach ($this->db->get()->result_array() as $row) {
            $existing_start = strtotime($row['confirmed_date']);

            $same_regular_group = $candidate_rule_id
                && (int) $row['service_type_id'] === (int) $service_type_id
                && (int) $row['schedule_rule_id'] === (int) $candidate_rule_id
                && $existing_start === $start_ts;

            $same_special_group = $candidate_booking_type === 'special'
                && ($row['booking_type'] ?? '') === 'special'
                && (int) $row['service_type_id'] === (int) $service_type_id
                && empty($row['schedule_rule_id'])
                && max(1, (int) ($service['special_capacity'] ?? 1)) > 1
                && $existing_start === $start_ts;

            if ($same_regular_group || $same_special_group) continue;

            $existing_before = max(0, (int) ($row['booking_buffer_before_minutes'] ?? 0));
            $existing_duration = max(15, (int) ($row['duration_minutes'] ?? 60));
            $existing_after = max(0, (int) ($row['booking_buffer_minutes'] ?? 0));
            $existing_window_start = $existing_start - ($existing_before * 60);
            $existing_window_end = $existing_start + (($existing_duration + $existing_after) * 60);

            if ($candidate_window_start < $existing_window_end && $candidate_window_end > $existing_window_start) {
                return $row;
            }
        }

        return false;
    }

    public function date_matches_any_regular_rule($service_type_id, $date)
    {
        $rules = $this->db->where('service_type_id', $service_type_id)
            ->where('is_active', 1)
            ->get('service_schedule_rules')->result_array();

        foreach ($rules as $rule) {
            if ($this->rule_matches_date($rule, $date)) return true;
        }
        return false;
    }

    private function rule_matches_date(array $rule, $date)
    {
        $ts = strtotime($date);
        if (!$ts) return false;

        if ((int) date('w', $ts) !== (int) $rule['day_of_week']) return false;
        if (!empty($rule['valid_from']) && $date < $rule['valid_from']) return false;
        if (!empty($rule['valid_until']) && $date > $rule['valid_until']) return false;

        $occurrence = (int) ceil(((int) date('j', $ts)) / 7);
        $weeks = array_filter(array_map('intval', explode(',', (string) $rule['week_numbers'])));
        return in_array($occurrence, $weeks, true);
    }

    private function validate_advance_window(array $service, $date)
    {
        $today = new DateTimeImmutable(date('Y-m-d'));
        $target = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        if (!$target || $target->format('Y-m-d') !== $date) return 'Please select a valid date.';

        $days = (int) $today->diff($target)->format('%r%a');
        $min_days = max(0, (int) ($service['min_advance_days'] ?? 1));
        $max_days = max($min_days, (int) ($service['max_advance_days'] ?? 365));

        if ($days < $min_days) return 'This service must be booked at least ' . $min_days . ' day(s) in advance.';
        if ($days > $max_days) return 'This date is beyond the parish booking window.';
        return null;
    }

    /**
     * Cross-service availability check.
     *
     * Main Church is one shared resource. Every booking can protect time before
     * the ceremony, the ceremony itself, and clearance time afterward.
     *
     * Services that require a priest also consume one of the currently active
     * parish priests. Group regular schedules share one priest/event.
     */
    private function check_slot_availability(array $service, $schedule_start, $rule = null, $exclude_booking_id = null)
    {
        $start_ts = strtotime($schedule_start);
        if (!$start_ts) {
            return [
                'available' => false,
                'remaining' => 0,
                'message' => 'Please choose a valid schedule.',
                'reserved_from' => null,
                'reserved_until' => null,
                'nearby_bookings' => [],
                'priest_total' => 0,
                'priests_available_for_slot' => 0,
                'priests_remaining_after_booking' => 0,
            ];
        }

        $before = max(0, (int) ($service['booking_buffer_before_minutes'] ?? 0));
        $duration = max(15, (int) ($service['duration_minutes'] ?? 60));
        $after = max(0, (int) ($service['booking_buffer_minutes'] ?? 0));

        $window_start = $start_ts - ($before * 60);
        $window_end = $start_ts + (($duration + $after) * 60);
        $date = date('Y-m-d', $start_ts);

        $this->db->select('service_bookings.id, service_bookings.service_type_id, service_bookings.booking_type, service_bookings.schedule_rule_id,
                service_bookings.confirmed_date, service_bookings.status,
                service_types.name AS service_name, service_types.duration_minutes,
                service_types.booking_buffer_before_minutes, service_types.booking_buffer_minutes,
                service_types.uses_main_church, service_types.requires_priest, service_types.special_capacity')
            ->from('service_bookings')
            ->join('service_types', 'service_types.id = service_bookings.service_type_id')
            ->where('service_bookings.confirmed_date IS NOT NULL', null, false)
            ->where('DATE(service_bookings.confirmed_date)', $date)
            ->where_not_in('service_bookings.status', ['cancelled', 'returned']);

        if ($exclude_booking_id) {
            $this->db->where('service_bookings.id !=', (int) $exclude_booking_id);
        }

        $rows = $this->db->get()->result_array();
        $same_slot_count = 0;
        $candidate_booking_type = $rule ? 'regular' : 'special';
        $capacity = $rule
            ? max(1, (int) $rule['capacity'])
            : max(1, (int) ($service['special_capacity'] ?? 1));
        $priest_event_keys = [];
        $nearby_before = null;
        $nearby_after = null;

        foreach ($rows as $existing) {
            $existing_start = strtotime($existing['confirmed_date']);
            $existing_before = max(0, (int) ($existing['booking_buffer_before_minutes'] ?? 0));
            $existing_duration = max(15, (int) ($existing['duration_minutes'] ?? 60));
            $existing_after = max(0, (int) ($existing['booking_buffer_minutes'] ?? 0));
            $existing_window_start = $existing_start - ($existing_before * 60);
            $existing_window_end = $existing_start + (($existing_duration + $existing_after) * 60);

            $same_regular_slot = $rule
                && (int) $existing['service_type_id'] === (int) $service['id']
                && (int) $existing['schedule_rule_id'] === (int) $rule['id']
                && $existing_start === $start_ts;

            $same_special_slot = !$rule
                && (int) $existing['service_type_id'] === (int) $service['id']
                && ($existing['booking_type'] ?? '') === $candidate_booking_type
                && empty($existing['schedule_rule_id'])
                && $existing_start === $start_ts;

            $same_capacity_slot = $same_regular_slot || $same_special_slot;

            $overlaps = ($window_start < $existing_window_end && $window_end > $existing_window_start);

            if ($same_capacity_slot) {
                $same_slot_count++;
            }

            if ($overlaps
                && !$same_capacity_slot
                && !empty($service['uses_main_church'])
                && !empty($existing['uses_main_church'])) {
                return [
                    'available' => false,
                    'remaining' => 0,
                    'message' => 'This schedule is unavailable because the protected church time overlaps with ' . $existing['service_name'] . '. Please choose another date or time.',
                    'reserved_from' => date('g:i A', $window_start),
                    'reserved_until' => date('g:i A', $window_end),
                    'nearby_bookings' => [],
                    'priest_total' => $this->active_priest_count(),
                    'priests_available_for_slot' => 0,
                    'priests_remaining_after_booking' => 0,
                ];
            }

            if (!empty($service['requires_priest'])
                && !empty($existing['requires_priest'])
                && $overlaps
                && !$same_capacity_slot) {
                if (!empty($existing['schedule_rule_id'])) {
                    $event_key = 'rule:' . $existing['schedule_rule_id'] . ':' . $existing['confirmed_date'];
                } elseif (($existing['booking_type'] ?? '') === 'special'
                    && max(1, (int) ($existing['special_capacity'] ?? 1)) > 1) {
                    // Multiple families in one Special Baptism/group session
                    // still consume one priest event, not one priest per baby.
                    $event_key = 'special-group:' . $existing['service_type_id'] . ':' . $existing['confirmed_date'];
                } else {
                    $event_key = 'booking:' . $existing['id'];
                }
                $priest_event_keys[$event_key] = true;
            }

            if (!empty($service['uses_main_church'])
                && !empty($existing['uses_main_church'])
                && !$overlaps
                && !$same_capacity_slot) {
                if ($existing_window_end <= $window_start) {
                    $gap = (int) floor(($window_start - $existing_window_end) / 60);
                    if ($gap <= 180 && ($nearby_before === null || $gap < $nearby_before['gap_minutes'])) {
                        $nearby_before = [
                            'direction' => 'before',
                            'service_name' => $existing['service_name'],
                            'ceremony_time' => date('g:i A', $existing_start),
                            'reserved_until' => date('g:i A', $existing_window_end),
                            'gap_minutes' => $gap,
                        ];
                    }
                } elseif ($existing_window_start >= $window_end) {
                    $gap = (int) floor(($existing_window_start - $window_end) / 60);
                    if ($gap <= 180 && ($nearby_after === null || $gap < $nearby_after['gap_minutes'])) {
                        $nearby_after = [
                            'direction' => 'after',
                            'service_name' => $existing['service_name'],
                            'ceremony_time' => date('g:i A', $existing_start),
                            'reserved_from' => date('g:i A', $existing_window_start),
                            'gap_minutes' => $gap,
                        ];
                    }
                }
            }
        }

        if ($same_slot_count >= $capacity) {
            return [
                'available' => false,
                'remaining' => 0,
                'message' => $candidate_booking_type === 'regular'
                    ? 'This regular schedule is already fully booked.'
                    : 'This special session is already fully booked.',
                'reserved_from' => date('g:i A', $window_start),
                'reserved_until' => date('g:i A', $window_end),
                'nearby_bookings' => [],
                'priest_total' => $this->active_priest_count(),
                'priests_available_for_slot' => 0,
                'priests_remaining_after_booking' => 0,
            ];
        }

        $priest_total = $this->active_priest_count();
        $requires_priest = !empty($service['requires_priest']);
        $busy_priest_events = count($priest_event_keys);
        $priests_available_for_slot = $requires_priest ? max(0, $priest_total - $busy_priest_events) : $priest_total;

        if ($requires_priest && $priest_total < 1) {
            return [
                'available' => false,
                'remaining' => 0,
                'message' => 'No active parish priest is currently configured for this service. Please contact the parish office.',
                'reserved_from' => date('g:i A', $window_start),
                'reserved_until' => date('g:i A', $window_end),
                'nearby_bookings' => [],
                'priest_total' => 0,
                'priests_available_for_slot' => 0,
                'priests_remaining_after_booking' => 0,
            ];
        }

        if ($requires_priest && $priests_available_for_slot < 1) {
            return [
                'available' => false,
                'remaining' => 0,
                'message' => 'This time is unavailable because all parish priests are already committed to other services.',
                'reserved_from' => date('g:i A', $window_start),
                'reserved_until' => date('g:i A', $window_end),
                'nearby_bookings' => [],
                'priest_total' => $priest_total,
                'priests_available_for_slot' => 0,
                'priests_remaining_after_booking' => 0,
            ];
        }

        $nearby = [];
        if ($nearby_before !== null) $nearby[] = $nearby_before;
        if ($nearby_after !== null) $nearby[] = $nearby_after;

        return [
            'available' => true,
            'remaining' => max(0, $capacity - $same_slot_count),
            'message' => '',
            'reserved_from' => date('g:i A', $window_start),
            'reserved_until' => date('g:i A', $window_end),
            'nearby_bookings' => $nearby,
            'priest_total' => $priest_total,
            'priests_available_for_slot' => $priests_available_for_slot,
            'priests_remaining_after_booking' => $requires_priest
                ? max(0, $priests_available_for_slot - 1)
                : $priests_available_for_slot,
        ];
    }

    /**
     * Server-side DataTables query, scoped by role.
     * $scope: ['role_id' => .., 'user_id' => .., 'priest_id' => ..]
     */
    public function datatable_query($request, $scope = [], $count_only = false)
    {
        $this->db->select('service_bookings.*, service_types.name as service_name,
                users.first_name, users.last_name, users.email')
            ->from($this->table)
            ->join('service_types', 'service_types.id = service_bookings.service_type_id')
            ->join('users', 'users.id = service_bookings.user_id');

        if (!empty($scope['user_id'])) {
            $this->db->where('service_bookings.user_id', $scope['user_id']);
        }
        if (!empty($scope['priest_id'])) {
            $this->db->where('service_bookings.assigned_priest_id', $scope['priest_id']);
        }
        if (!empty($request['status'])) {
            $this->db->where('service_bookings.status', $request['status']);
        }
        if (!empty($request['service_type_id'])) {
            $this->db->where('service_bookings.service_type_id', $request['service_type_id']);
        }
        if (!empty($request['search']['value'])) {
            $kw = $request['search']['value'];
            $this->db->group_start()
                ->like('service_bookings.booking_code', $kw)
                ->or_like('users.first_name', $kw)
                ->or_like('users.last_name', $kw)
                ->or_like('service_types.name', $kw)
                ->group_end();
        }

        if ($count_only) {
            return $this->db->count_all_results();
        }

        $columns = ['service_bookings.id', 'service_bookings.booking_code', 'service_types.name', 'users.first_name', 'service_bookings.preferred_date', 'service_bookings.status', 'service_bookings.created_at'];
        if (isset($request['order'][0])) {
            $col = $columns[(int) $request['order'][0]['column']] ?? 'service_bookings.id';
            $this->db->order_by($col, $request['order'][0]['dir']);
        } else {
            $this->db->order_by('service_bookings.id', 'desc');
        }

        if (isset($request['start'], $request['length']) && (int) $request['length'] !== -1) {
            $this->db->limit((int) $request['length'], (int) $request['start']);
        }

        return $this->db->get()->result_array();
    }
}
