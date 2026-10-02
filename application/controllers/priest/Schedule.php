<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Schedule extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_PRIEST]);
        $this->load->model('Booking_model');
    }

    public function index()
    {
        $data['assignments'] = $this->Booking_model->for_priest($this->current_user['id']);
        $data['blocked_dates'] = $this->db->where('priest_id', $this->current_user['id'])
            ->order_by('date_from', 'desc')->get('priest_unavailability')->result_array();
        $this->render_app('priest/schedule', $data, 'layouts/app_admin');
    }

    public function view($id)
    {
        $booking = $this->Booking_model->get($id);
        if (!$booking || (int) $booking['assigned_priest_id'] !== (int) $this->current_user['id']) show_404();

        $data['booking']   = $booking;
        $data['documents'] = $this->Booking_model->documents($id);
        $this->render_app('priest/booking_view', $data, 'layouts/app_admin');
    }

    /** AJAX: acknowledge / accept an assignment */
    public function acknowledge()
    {
        $id = (int) $this->input->post('id');
        $booking = $this->Booking_model->get($id);

        if (!$booking || (int) $booking['assigned_priest_id'] !== (int) $this->current_user['id']) {
            return $this->json(['success' => false, 'message' => 'Assignment not found.'], 404);
        }

        $this->db->where('id', $id)->update('service_bookings', ['staff_notes' => 'Acknowledged by presider']);
        $this->log_activity('Acknowledged assigned service', 'booking', $booking['booking_code']);
        $this->json(['success' => true, 'message' => 'Assignment acknowledged.']);
    }

    /** AJAX: mark a service as completed */
    public function complete()
    {
        $id = (int) $this->input->post('id');
        $booking = $this->Booking_model->get($id);
        if (!$booking || (int) $booking['assigned_priest_id'] !== (int) $this->current_user['id']) {
            return $this->json(['success' => false, 'message' => 'Not found.']);
        }
        if (!$this->Booking_model->change_status($id, 'completed', $this->current_user['id'], 'Service completed')) {
            return $this->json([
                'success' => false,
                'message' => $this->Booking_model->transition_error() ?: 'This service cannot be marked completed yet.'
            ]);
        }

        $this->json(['success' => true, 'message' => 'Marked as completed. A draft sacramental registry record was created when applicable.']);
    }

    /** AJAX: add a note to a booking */
    public function add_note()
    {
        $id = (int) $this->input->post('id');
        $booking = $this->Booking_model->get($id);

        if (!$booking || (int) $booking['assigned_priest_id'] !== (int) $this->current_user['id']) {
            return $this->json(['success' => false, 'message' => 'Assignment not found.'], 404);
        }

        $note = trim((string) $this->input->post('note', true));
        if (mb_strlen($note) > 2000) {
            return $this->json(['success' => false, 'message' => 'Note is too long.']);
        }

        $this->db->where('id', $id)->update('service_bookings', ['staff_notes' => $note]);
        $this->log_activity('Updated priest note', 'booking', $booking['booking_code']);
        $this->json(['success' => true, 'message' => 'Note saved.']);
    }

    /** AJAX: block a date range as unavailable */
    public function block_date()
    {
        $this->form_validation->set_rules('date_from', 'From', 'required');
        $this->form_validation->set_rules('date_to', 'To', 'required');
        if ($this->form_validation->run() === FALSE) {
            return $this->json(['success' => false, 'message' => strip_tags(validation_errors())]);
        }

        $date_from = trim((string)$this->input->post('date_from', true));
        $date_to = trim((string)$this->input->post('date_to', true));
        $from = DateTimeImmutable::createFromFormat('!Y-m-d', $date_from);
        $to = DateTimeImmutable::createFromFormat('!Y-m-d', $date_to);

        if (!$from || !$to || $from->format('Y-m-d') !== $date_from || $to->format('Y-m-d') !== $date_to) {
            return $this->json(['success'=>false,'message'=>'Enter valid From and To dates.']);
        }
        if ($to < $from) {
            return $this->json(['success' => false, 'message' => 'The end date cannot be earlier than the start date.']);
        }
        if ((int)$from->diff($to)->days > 365) {
            return $this->json(['success'=>false,'message'=>'One unavailability block cannot exceed 365 days.']);
        }
        if ($to < new DateTimeImmutable(date('Y-m-d'))) {
            return $this->json(['success'=>false,'message'=>'Past dates no longer need to be blocked.']);
        }

        $overlapping_block = $this->db->where('priest_id', $this->current_user['id'])
            ->where('date_from <=', $date_to)
            ->where('date_to >=', $date_from)
            ->get('priest_unavailability')->row_array();
        if ($overlapping_block) {
            return $this->json([
                'success'=>false,
                'message'=>'This range overlaps an existing unavailability block ('
                    . format_date($overlapping_block['date_from']) . ' – '
                    . format_date($overlapping_block['date_to']) . ').'
            ]);
        }

        $assigned_service = $this->db->select('service_bookings.booking_code, service_bookings.confirmed_date, service_types.name AS service_name')
            ->from('service_bookings')
            ->join('service_types','service_types.id = service_bookings.service_type_id')
            ->where('service_bookings.assigned_priest_id', $this->current_user['id'])
            ->where('service_bookings.confirmed_date >=', $date_from . ' 00:00:00')
            ->where('service_bookings.confirmed_date <=', $date_to . ' 23:59:59')
            ->where_not_in('service_bookings.status', ['cancelled','returned','completed'])
            ->order_by('service_bookings.confirmed_date','asc')
            ->limit(1)
            ->get()->row_array();
        if ($assigned_service) {
            return $this->json([
                'success'=>false,
                'message'=>'You already have ' . $assigned_service['service_name'] . ' ('
                    . $assigned_service['booking_code'] . ') assigned on '
                    . format_datetime($assigned_service['confirmed_date'])
                    . '. Coordinate reassignment before blocking this range.'
            ]);
        }

        for ($cursor = $from; $cursor <= $to; $cursor = $cursor->modify('+1 day')) {
            $mass = $this->Booking_model->priest_mass_assignment_on_date(
                $this->current_user['id'],
                $cursor->format('Y-m-d')
            );
            if ($mass) {
                return $this->json([
                    'success'=>false,
                    'message'=>'You are assigned to ' . (trim((string)($mass['title'] ?? 'Holy Mass')) ?: 'Holy Mass')
                        . ' on ' . $cursor->format('M j, Y') . ' at '
                        . date('g:i A', strtotime($mass['mass_time']))
                        . '. Reassign that Mass before blocking this date.'
                ]);
            }
        }

        $reason = trim((string)$this->input->post('reason', true));
        if (mb_strlen($reason) > 255) {
            return $this->json(['success'=>false,'message'=>'Reason must be 255 characters or fewer.']);
        }

        $this->db->insert('priest_unavailability', [
            'priest_id' => $this->current_user['id'],
            'date_from' => $date_from,
            'date_to'   => $date_to,
            'reason'    => $reason ?: null,
        ]);

        $this->log_activity('Blocked priest availability', 'schedule', $date_from . ' to ' . $date_to);
        $this->json(['success' => true, 'message' => 'Dates blocked. Future booking availability will exclude this priest for the selected range.']);
    }

    public function unblock_date($id)
    {
        $this->db->where('id', $id)->where('priest_id', $this->current_user['id'])->delete('priest_unavailability');
        $this->json(['success' => true]);
    }
}
