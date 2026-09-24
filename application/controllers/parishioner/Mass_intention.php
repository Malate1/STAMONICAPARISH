<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mass_intention extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_PARISHIONER]);
        $this->load->model('MassSchedule_model');
        $this->load->model('MassIntention_model');
    }

    public function index()
    {
        $cutoff = $this->get_cutoff_minutes();
        $data['intentions'] = $this->MassIntention_model->for_user($this->current_user['id']);
        $data['masses'] = $this->MassSchedule_model->upcoming_occurrences(60, $cutoff, 120);
        $data['cutoff_minutes'] = $cutoff;
        $this->render_app('parishioner/mass_intention', $data, 'layouts/app_parishioner');
    }

    public function store()
    {
        $this->form_validation->set_rules('intention_type', 'Intention Type', 'required');
        $this->form_validation->set_rules('offered_for', 'Offered For', 'required');
        $this->form_validation->set_rules('mass_schedule_id', 'Mass Schedule', 'required');
        $this->form_validation->set_rules('mass_date', 'Mass Date', 'required');

        if ($this->form_validation->run() === FALSE) {
            return $this->json(['success' => false, 'message' => strip_tags(validation_errors())]);
        }

        $schedule_id = (int) $this->input->post('mass_schedule_id');
        $mass_date = $this->input->post('mass_date', true);
        $cutoff = $this->get_cutoff_minutes();
        $occurrence = $this->MassSchedule_model->occurrence_for_submission($schedule_id, $mass_date, $cutoff);

        if (!$occurrence) {
            return $this->json([
                'success' => false,
                'message' => 'That Mass is no longer available for online intentions. Please choose another upcoming Mass.'
            ]);
        }

        $this->db->insert('mass_intentions', [
            'user_id'            => $this->current_user['id'],
            'mass_schedule_id'   => $schedule_id,
            'mass_date'          => $mass_date,
            'intention_type'     => $this->input->post('intention_type'),
            'offered_for'        => trim((string) $this->input->post('offered_for', true)),
            'requestor_name'     => trim($this->current_user['first_name'] . ' ' . $this->current_user['last_name']),
            'requestor_contact'  => $this->current_user['mobile_number'],
            'fee_amount'         => 100.00,
            'status'             => 'pending',
            'created_at'         => date('Y-m-d H:i:s'),
        ]);

        $this->json([
            'success' => true,
            'message' => 'Mass intention submitted for parish review for ' . date('M j, Y', strtotime($mass_date)) . ' at ' . date('g:i A', strtotime($occurrence['mass_time'])) . '.'
        ]);
    }

    private function get_cutoff_minutes()
    {
        $row = $this->db->get_where('system_settings', ['setting_key' => 'mass_intention_cutoff_minutes'])->row_array();
        return $row ? max(0, (int) $row['setting_value']) : 30;
    }
}
