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
        $this->db->where('id', $id)->update('service_bookings', ['staff_notes' => 'Acknowledged by presider']);
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
        $this->Booking_model->change_status($id, 'completed', $this->current_user['id'], 'Service completed');
        $this->json(['success' => true, 'message' => 'Marked as completed.']);
    }

    /** AJAX: add a note to a booking */
    public function add_note()
    {
        $id = (int) $this->input->post('id');
        $note = $this->input->post('note', true);
        $this->db->where('id', $id)->update('service_bookings', ['staff_notes' => $note]);
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

        $this->db->insert('priest_unavailability', [
            'priest_id' => $this->current_user['id'],
            'date_from' => $this->input->post('date_from'),
            'date_to'   => $this->input->post('date_to'),
            'reason'    => $this->input->post('reason', true),
        ]);

        $this->json(['success' => true, 'message' => 'Dates blocked.']);
    }

    public function unblock_date($id)
    {
        $this->db->where('id', $id)->where('priest_id', $this->current_user['id'])->delete('priest_unavailability');
        $this->json(['success' => true]);
    }
}
