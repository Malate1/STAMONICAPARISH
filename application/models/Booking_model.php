<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Booking_model extends CI_Model
{
    protected $table = 'service_bookings';

    public function generate_code($service_key)
    {
        $prefix = strtoupper(substr(preg_replace('/[^A-Z0-9]/i', '', (string) $service_key), 0, 6));
        if ($prefix === '') $prefix = 'BOOK';

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $code = sprintf(
                'SMC-%s-%s-%s',
                $prefix,
                date('Y'),
                strtoupper(bin2hex(random_bytes(4)))
            );

            if (!$this->db->where('booking_code', $code)->count_all_results($this->table)) {
                return $code;
            }
        }

        throw new RuntimeException('Unable to generate a unique booking code.');
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
                service_types.requires_approval_workflow, service_types.requires_schedule,
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

    private $last_transition_error = '';

    /**
     * Central booking workflow. Controllers and UI should use this map rather
     * than accepting arbitrary status jumps.
     */
    public function allowed_status_transitions(array $booking)
    {
        $status = $booking['status'] ?? 'submitted';
        $fee = (float) ($booking['fee_amount'] ?? 0);
        $approval = !empty($booking['requires_approval_workflow']);
        $service_key = $booking['service_key'] ?? '';

        switch ($status) {
            case 'draft':
                return ['submitted', 'cancelled'];

            case 'submitted':
                return ['under_review', 'missing_requirements', 'requirements_complete', 'cancelled', 'returned'];

            case 'under_review':
                return ['missing_requirements', 'requirements_complete', 'cancelled', 'returned'];

            case 'missing_requirements':
                return ['under_review', 'requirements_complete', 'cancelled', 'returned'];

            case 'requirements_complete':
                if ($approval) {
                    if ($service_key === 'wedding') {
                        return ['interview_processing', 'missing_requirements', 'cancelled', 'returned'];
                    }
                    return ['priest_review', 'missing_requirements', 'cancelled', 'returned'];
                }
                return array_values(array_filter([
                    $fee > 0 ? 'awaiting_payment' : 'approved',
                    'missing_requirements',
                    'cancelled',
                    'returned',
                ]));

            case 'interview_processing':
                return ['priest_review', 'missing_requirements', 'cancelled', 'returned'];

            case 'priest_review':
                return [
                    $fee > 0 ? 'awaiting_payment' : 'approved',
                    'missing_requirements',
                    'cancelled',
                    'returned',
                ];

            case 'awaiting_payment':
                return ['payment_verification', 'cancelled', 'returned'];

            case 'payment_verification':
                return ['approved', 'awaiting_payment', 'cancelled'];

            case 'approved':
                return ['scheduled', 'cancelled'];

            case 'scheduled':
                return ['completed', 'cancelled'];

            case 'returned':
                return ['under_review', 'submitted', 'cancelled'];

            case 'completed':
            case 'cancelled':
            default:
                return [];
        }
    }

    public function can_transition(array $booking, $new_status)
    {
        $new_status = (string) $new_status;
        if ($new_status === ($booking['status'] ?? null)) return true;
        return in_array($new_status, $this->allowed_status_transitions($booking), true);
    }

    public function transition_error()
    {
        return $this->last_transition_error;
    }

    public function change_status($id, $new_status, $changed_by = null, $remarks = null)
    {
        $this->last_transition_error = '';
        $booking = $this->get($id);

        if (!$booking) {
            $this->last_transition_error = 'Booking not found.';
            return false;
        }

        if ($new_status === $booking['status']) {
            return true;
        }

        if (!$this->can_transition($booking, $new_status)) {
            $allowed = $this->allowed_status_transitions($booking);
            $this->last_transition_error = empty($allowed)
                ? 'This booking is already in a final state and cannot be moved to another status.'
                : 'The booking cannot move from ' . status_label($booking['status'])
                    . ' directly to ' . status_label($new_status) . '.';
            return false;
        }

        if ($new_status === 'scheduled') {
            if (empty($booking['confirmed_date'])) {
                $this->last_transition_error = 'Set a confirmed date and time before marking this booking Scheduled.';
                return false;
            }
            if (!empty($booking['requires_priest']) && empty($booking['assigned_priest_id'])) {
                $this->last_transition_error = 'Assign a priest before marking this booking Scheduled.';
                return false;
            }
        }

        if ($new_status === 'completed' && !empty($booking['confirmed_date'])
            && strtotime($booking['confirmed_date']) > time()) {
            $this->last_transition_error = 'This service has not started yet. Mark it Completed only after the scheduled service.';
            return false;
        }

        $this->db->trans_start();
        $this->update($id, ['status' => $new_status]);
        $this->add_status_history($id, $booking['status'], $new_status, $remarks, $changed_by);
        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->last_transition_error = 'The booking status could not be saved.';
            return false;
        }

        if ($new_status === 'completed') {
            $this->sync_sacramental_record_from_booking($id, $changed_by);
        }

        return true;
    }

    /**
     * When a sacramental service is completed, create a draft parish registry
     * entry from the booking instead of asking staff to encode the same people
     * and dates again. Registry book/page/entry still require office review.
     */
    public function sync_sacramental_record_from_booking($booking_id, $changed_by = null)
    {
        if (!$this->db->table_exists('sacramental_records')
            || !$this->db->field_exists('record_status', 'sacramental_records')
            || !$this->db->field_exists('source_type', 'sacramental_records')) {
            log_message('error', 'Sacramental record workflow migration is missing; booking ' . (int) $booking_id . ' could not create a draft registry record.');
            return false;
        }

        $booking = $this->get((int) $booking_id);
        if (!$booking) return false;

        $type_map = [
            'baptism' => 'baptism',
            'confirmation' => 'confirmation',
            'wedding' => 'marriage',
            'funeral' => 'funeral',
        ];

        $record_type = $type_map[$booking['service_key']] ?? null;
        if (!$record_type) return true;

        $details = [];
        if (!empty($booking['details'])) {
            $decoded = json_decode($booking['details'], true);
            if (is_array($decoded)) $details = $decoded;
        }

        $full_name = null;
        $birth_date = null;
        $father_name = null;
        $mother_name = null;
        $spouse_name = null;
        $remarks = [];

        switch ($booking['service_key']) {
            case 'baptism':
                $full_name = trim((string) ($details['child_name'] ?? ''));
                $birth_date = $details['child_birth_date'] ?? null;
                $father_name = trim((string) ($details['father_name'] ?? '')) ?: null;
                $mother_name = trim((string) ($details['mother_name'] ?? '')) ?: null;
                if (!empty($details['sponsors'])) $remarks[] = 'Sponsors: ' . trim((string) $details['sponsors']);
                break;

            case 'confirmation':
                $full_name = trim((string) ($details['confirmand_name'] ?? ''));
                break;

            case 'wedding':
                $full_name = trim((string) ($details['bride_name'] ?? ''));
                $spouse_name = trim((string) ($details['groom_name'] ?? '')) ?: null;
                if (!empty($details['sponsor_information'])) $remarks[] = trim((string) $details['sponsor_information']);
                break;

            case 'funeral':
                $full_name = trim((string) ($details['deceased_name'] ?? ''));
                if (!empty($details['date_of_death'])) $remarks[] = 'Date of death: ' . $details['date_of_death'];
                if (!empty($details['funeral_home'])) $remarks[] = 'Funeral home: ' . trim((string) $details['funeral_home']);
                if (!empty($details['cemetery'])) $remarks[] = 'Cemetery: ' . trim((string) $details['cemetery']);
                break;
        }

        if ($full_name === '') {
            log_message('error', 'Completed booking ' . (int) $booking_id . ' has no registry subject name in details; draft sacramental record was not created.');
            return false;
        }

        $minister_name = null;
        if (!empty($booking['priest_first_name']) || !empty($booking['priest_last_name'])) {
            $minister_name = trim(($booking['priest_first_name'] ?? '') . ' ' . ($booking['priest_last_name'] ?? ''));
        }

        $payload = [
            'record_type' => $record_type,
            'booking_id' => (int) $booking_id,
            'full_name' => $full_name,
            'birth_date' => $birth_date ?: null,
            'sacrament_date' => !empty($booking['confirmed_date']) ? date('Y-m-d', strtotime($booking['confirmed_date'])) : null,
            'father_name' => $father_name,
            'mother_name' => $mother_name,
            'spouse_name' => $spouse_name,
            'minister_name' => $minister_name ?: null,
            'remarks' => $remarks ? implode("\n", $remarks) : null,
            'record_status' => 'draft',
            'source_type' => 'booking',
            'updated_by' => $changed_by ?: null,
            'verified_by' => null,
            'verified_at' => null,
        ];

        $existing = $this->db->get_where('sacramental_records', ['booking_id' => (int) $booking_id])->row_array();
        if ($existing) {
            if (($existing['record_status'] ?? '') === 'verified') {
                return true;
            }

            return $this->db->where('id', $existing['id'])->update('sacramental_records', $payload);
        }

        $payload['created_by'] = $changed_by ?: null;
        $payload['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert('sacramental_records', $payload);
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
     * Active priests who are not blocked for the requested calendar date.
     * Time-specific commitments are applied later by check_slot_availability().
     */
    private function active_priest_ids_for_date($date)
    {
        $active_ids = array_map('intval', array_column(
            $this->db->select('id')
                ->where('role_id', ROLE_PRIEST)
                ->where('status', 'active')
                ->get('users')->result_array(),
            'id'
        ));

        if (empty($active_ids)) return [];

        $blocked_ids = array_map('intval', array_column(
            $this->db->select('priest_id')
                ->where('date_from <=', $date)
                ->where('date_to >=', $date)
                ->where_in('priest_id', $active_ids)
                ->get('priest_unavailability')->result_array(),
            'priest_id'
        ));

        return array_values(array_diff($active_ids, $blocked_ids));
    }

    private function configured_priest_capacity()
    {
        $row = $this->db->get_where('system_settings', ['setting_key' => 'priest_booking_capacity'])->row_array();
        return $row ? max(1, (int) $row['setting_value']) : 2;
    }

    /**
     * Operational priest capacity for a date after whole-day unavailability.
     */
    private function active_priest_count_for_date($date)
    {
        return min(
            count($this->active_priest_ids_for_date($date)),
            $this->configured_priest_capacity()
        );
    }

    /**
     * Resolve real Mass occurrences for one date. Date-specific override rows
     * replace the recurring weekly schedule in the same way as the public Mass
     * calendar and Mass Intention workflow.
     */
    private function mass_occurrences_for_date($date)
    {
        if (!$this->db->table_exists('mass_schedules')) return [];

        $specific = $this->db->where('specific_date', $date)
            ->where('is_active', 1)
            ->get('mass_schedules')->result_array();

        $has_override = false;
        foreach ($specific as $row) {
            if (!empty($row['is_override'])) {
                $has_override = true;
                break;
            }
        }

        $regular = [];
        if (!$has_override) {
            $regular = $this->db->where('day_of_week', (int) date('w', strtotime($date)))
                ->where('specific_date IS NULL', null, false)
                ->where('is_active', 1)
                ->get('mass_schedules')->result_array();
        }

        return array_merge($regular, $specific);
    }

    public function priest_mass_assignment_on_date($priest_id, $date)
    {
        $priest_id = (int)$priest_id;
        if (!$priest_id) return null;

        foreach ($this->mass_occurrences_for_date($date) as $mass) {
            if (!empty($mass['presider_id']) && (int)$mass['presider_id'] === $priest_id) {
                return $mass;
            }
        }

        return null;
    }

    private function mass_uses_main_church(array $mass)
    {
        $location = strtolower(trim((string) ($mass['location'] ?? 'Main Church')));
        return $location === ''
            || strpos($location, 'main church') !== false
            || strpos($location, 'sta. monica parish church') !== false
            || strpos($location, 'st. monica parish church') !== false;
    }

    /**
     * Resolve the effective start time for one recurring regular-session date.
     *
     * If a date already has active bookings tied to this schedule rule, keep
     * that session's original booked time. This prevents a later edit to the
     * recurring rule (for example 8:30 AM -> 9:00 AM) from retroactively
     * moving an already-booked group session and causing it to conflict with
     * itself. Dates with no existing bookings use the rule's current time.
     */
    private function effective_regular_start(array $rule, $date)
    {
        if (empty($rule['id']) || empty($date)) return null;

        $existing = $this->db->select('confirmed_date')
            ->where('schedule_rule_id', (int) $rule['id'])
            ->where('booking_type', 'regular')
            ->where('confirmed_date IS NOT NULL', null, false)
            ->where('DATE(confirmed_date)', $date)
            ->where_not_in('status', ['cancelled', 'returned'])
            ->order_by('confirmed_date', 'asc')
            ->limit(1)
            ->get('service_bookings')->row_array();

        if ($existing && !empty($existing['confirmed_date'])) {
            $ts = strtotime($existing['confirmed_date']);
            if ($ts) return date('Y-m-d H:i:s', $ts);
        }

        if (empty($rule['start_time'])) return null;
        return $date . ' ' . $rule['start_time'];
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
                // Do not publish it until staff sets a time. If this particular
                // date already has bookings, preserve that session's original
                // booked time even if the recurring rule was edited afterward.
                if (empty($rule['start_time']) || !$this->rule_matches_date($rule, $date)) continue;

                $start = $this->effective_regular_start($rule, $date);
                if (!$start || strtotime($start) <= time()) continue;
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

            $effective_start = $this->effective_regular_start($rule, $date);
            if (!$effective_start || date('H:i:s', $timestamp) !== date('H:i:s', strtotime($effective_start))) {
                return ['valid' => false, 'message' => 'The selected time does not match the parish regular session for this date. Please refresh the available slots.'];
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

    public function assign_priest_and_schedule($id, $priest_id, $confirmed_date, $changed_by = null)
    {
        $this->last_transition_error = '';
        $id = (int)$id;
        $priest_id = $priest_id ? (int)$priest_id : null;
        $confirmed_date = trim((string)$confirmed_date);

        $this->db->trans_begin();
        $locked = $this->db->query(
            'SELECT id FROM service_bookings WHERE id = ? FOR UPDATE',
            [$id]
        )->row_array();

        if (!$locked) {
            $this->db->trans_rollback();
            $this->last_transition_error = 'Booking not found.';
            return false;
        }

        $booking = $this->get($id);
        if (!$booking) {
            $this->db->trans_rollback();
            $this->last_transition_error = 'Booking not found.';
            return false;
        }

        if (in_array($booking['status'], ['completed','cancelled'], true)) {
            $this->db->trans_rollback();
            $this->last_transition_error = 'Completed or cancelled bookings can no longer be reassigned.';
            return false;
        }

        if ($priest_id) {
            $valid_priest = $this->db->where('id', $priest_id)
                ->where('role_id', ROLE_PRIEST)
                ->where('status', 'active')
                ->count_all_results('users');
            if (!$valid_priest) {
                $this->db->trans_rollback();
                $this->last_transition_error = 'Please select an active priest account.';
                return false;
            }
        }

        if (!empty($booking['requires_priest']) && !$priest_id && $confirmed_date !== '') {
            $this->db->trans_rollback();
            $this->last_transition_error = 'Select an available priest before confirming this schedule.';
            return false;
        }

        if ($booking['status'] === 'scheduled' && $confirmed_date === '') {
            $this->db->trans_rollback();
            $this->last_transition_error = 'A Scheduled booking must keep a confirmed date and time.';
            return false;
        }

        if ($booking['status'] === 'scheduled' && !empty($booking['requires_priest']) && !$priest_id) {
            $this->db->trans_rollback();
            $this->last_transition_error = 'A Scheduled booking that requires a priest must keep an assigned priest.';
            return false;
        }

        $normalized = null;
        if ($confirmed_date !== '') {
            $timestamp = strtotime($confirmed_date);
            if (!$timestamp) {
                $this->db->trans_rollback();
                $this->last_transition_error = 'Enter a valid schedule date and time.';
                return false;
            }
            $normalized = date('Y-m-d H:i:s', $timestamp);

            $current = !empty($booking['confirmed_date'])
                ? date('Y-m-d H:i:s', strtotime($booking['confirmed_date']))
                : null;

            if ($normalized !== $current) {
                $slot = $this->resolve_slot(
                    $booking['service_type_id'],
                    $booking['booking_type'] ?? 'special',
                    $normalized,
                    $booking['schedule_rule_id'] ?? null,
                    $id
                );

                if (empty($slot['valid'])) {
                    $this->db->trans_rollback();
                    $this->last_transition_error = $slot['message'] ?? 'That schedule is not available.';
                    return false;
                }
            }

            if ($priest_id) {
                $conflict = $this->priest_has_conflict(
                    $priest_id,
                    $normalized,
                    $booking['service_type_id'],
                    $id
                );
                if ($conflict) {
                    $this->db->trans_rollback();
                    $this->last_transition_error = 'This priest already has '
                        . ($conflict['service_name'] ?? 'another commitment')
                        . ' during that time. Please choose another priest or schedule.';
                    return false;
                }
            }
        }

        $saved = $this->update($id, [
            'assigned_priest_id' => $priest_id,
            'confirmed_date' => $normalized,
        ]);

        if (!$saved) {
            $this->db->trans_rollback();
            $this->last_transition_error = 'The priest/schedule assignment could not be saved.';
            return false;
        }

        $scheduled_now = false;
        if ($normalized && $booking['status'] === 'approved') {
            if (!$this->change_status($id, 'scheduled', $changed_by, 'Schedule confirmed')) {
                $error = $this->transition_error();
                $this->db->trans_rollback();
                $this->last_transition_error = $error ?: 'The booking could not be marked Scheduled.';
                return false;
            }
            $scheduled_now = true;
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $this->last_transition_error = 'The assignment could not be saved.';
            return false;
        }

        $this->db->trans_commit();
        return [
            'scheduled_now' => $scheduled_now,
            'message' => $scheduled_now
                ? 'Assignment saved and booking marked Scheduled.'
                : ($normalized && !in_array($booking['status'], ['scheduled'], true)
                    ? 'Assignment saved. The reserved schedule remains protected while the booking continues through review.'
                    : 'Assignment saved.'),
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

        $blocked = $this->db->where('priest_id', (int) $priest_id)
            ->where('date_from <=', $date)
            ->where('date_to >=', $date)
            ->get('priest_unavailability')->row_array();
        if ($blocked) {
            return [
                'service_name' => 'a blocked/unavailable date',
                'conflict_type' => 'unavailability',
                'reason' => $blocked['reason'] ?? null,
            ];
        }

        foreach ($this->mass_occurrences_for_date($date) as $mass) {
            if (empty($mass['presider_id']) || (int) $mass['presider_id'] !== (int) $priest_id || empty($mass['mass_time'])) {
                continue;
            }

            $mass_start = strtotime($date . ' ' . $mass['mass_time']);
            $mass_end = $mass_start ? $mass_start + (60 * 60) : 0;
            if ($mass_start && $candidate_window_start < $mass_end && $candidate_window_end > $mass_start) {
                return [
                    'service_name' => trim((string) ($mass['title'] ?? 'Holy Mass')) ?: 'Holy Mass',
                    'conflict_type' => 'mass',
                ];
            }
        }

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
                service_types.uses_main_church, service_types.requires_priest, service_types.special_capacity,
                service_bookings.assigned_priest_id')
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
        // Each key is one distinct concurrent priest-consuming event. Assigned
        // priests are tracked separately so a priest blocked by Mass/service
        // duty cannot still be counted as available for another booking.
        $priest_event_keys = [];
        $unassigned_priest_event_keys = [];
        $busy_assigned_priest_ids = [];
        $nearby_before = null;
        $nearby_after = null;

        // Parish Masses are real consumers of the Main Church and priest
        // capacity. They must participate in the same conflict engine as
        // sacramental/service bookings.
        foreach ($this->mass_occurrences_for_date($date) as $mass) {
            if (empty($mass['mass_time'])) continue;

            $mass_start = strtotime($date . ' ' . $mass['mass_time']);
            if (!$mass_start) continue;

            $mass_end = $mass_start + (60 * 60);
            $mass_overlaps = ($window_start < $mass_end && $window_end > $mass_start);
            $mass_name = trim((string) ($mass['title'] ?? 'Holy Mass')) ?: 'Holy Mass';

            if ($mass_overlaps
                && !empty($service['uses_main_church'])
                && $this->mass_uses_main_church($mass)) {
                return [
                    'available' => false,
                    'remaining' => 0,
                    'message' => 'This schedule is unavailable because the protected church time overlaps with ' . $mass_name . '. Please choose another date or time.',
                    'reserved_from' => date('g:i A', $window_start),
                    'reserved_until' => date('g:i A', $window_end),
                    'nearby_bookings' => [],
                    'priest_total' => $this->active_priest_count_for_date($date),
                    'priests_available_for_slot' => 0,
                    'priests_remaining_after_booking' => 0,
                ];
            }

            if ($mass_overlaps && !empty($service['requires_priest'])) {
                $event_key = 'mass:' . (int) ($mass['id'] ?? 0) . ':' . $date . ':' . $mass['mass_time'];
                $priest_event_keys[$event_key] = true;

                if (!empty($mass['presider_id'])) {
                    $busy_assigned_priest_ids[(int) $mass['presider_id']] = true;
                } else {
                    // A Mass without an assigned presider still reserves one
                    // generic priest-capacity unit until staff assigns a priest.
                    $unassigned_priest_event_keys[$event_key] = true;
                }
            }

            if (!empty($service['uses_main_church'])
                && $this->mass_uses_main_church($mass)
                && !$mass_overlaps) {
                if ($mass_end <= $window_start) {
                    $gap = (int) floor(($window_start - $mass_end) / 60);
                    if ($gap <= 180 && ($nearby_before === null || $gap < $nearby_before['gap_minutes'])) {
                        $nearby_before = [
                            'direction' => 'before',
                            'service_name' => $mass_name,
                            'ceremony_time' => date('g:i A', $mass_start),
                            'reserved_until' => date('g:i A', $mass_end),
                            'gap_minutes' => $gap,
                        ];
                    }
                } elseif ($mass_start >= $window_end) {
                    $gap = (int) floor(($mass_start - $window_end) / 60);
                    if ($gap <= 180 && ($nearby_after === null || $gap < $nearby_after['gap_minutes'])) {
                        $nearby_after = [
                            'direction' => 'after',
                            'service_name' => $mass_name,
                            'ceremony_time' => date('g:i A', $mass_start),
                            'reserved_from' => date('g:i A', $mass_start),
                            'gap_minutes' => $gap,
                        ];
                    }
                }
            }
        }

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
                    'priest_total' => $this->active_priest_count_for_date($date),
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
                if (!empty($existing['assigned_priest_id'])) {
                    $busy_assigned_priest_ids[(int) $existing['assigned_priest_id']] = true;
                } else {
                    $unassigned_priest_event_keys[$event_key] = true;
                }
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
                'priest_total' => $this->active_priest_count_for_date($date),
                'priests_available_for_slot' => 0,
                'priests_remaining_after_booking' => 0,
            ];
        }

        $active_priest_ids = $this->active_priest_ids_for_date($date);
        $operational_capacity = $this->configured_priest_capacity();
        $priest_total = min(count($active_priest_ids), $operational_capacity);
        $requires_priest = !empty($service['requires_priest']);

        $free_priest_ids = array_values(array_diff(
            $active_priest_ids,
            array_map('intval', array_keys($busy_assigned_priest_ids))
        ));

        // Two independent constraints must both have room:
        // 1) actual people free at this time, after assigned Masses/services;
        // 2) parish-configured maximum simultaneous priest commitments.
        // Unassigned overlapping events reserve one free priest generically.
        $free_people_after_unassigned = max(
            0,
            count($free_priest_ids) - count($unassigned_priest_event_keys)
        );
        $operational_remaining = max(
            0,
            $operational_capacity - count($priest_event_keys)
        );

        $priests_available_for_slot = $requires_priest
            ? min($free_people_after_unassigned, $operational_remaining)
            : $priest_total;

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
