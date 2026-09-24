<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mass_intention extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_PRIEST]);
        $this->load->model('MassIntention_model');
    }

    public function index()
    {
        return $this->reader();
    }

    public function reader()
    {
        $mass_date = $this->input->get('date', true);
        $schedule_id = (int) $this->input->get('schedule_id');

        $occurrences = $this->MassIntention_model->ready_occurrences();
        if ((!$mass_date || !$schedule_id) && !empty($occurrences)) {
            $mass_date = $occurrences[0]['mass_date'];
            $schedule_id = (int) $occurrences[0]['mass_schedule_id'];
        }

        $data['occurrences'] = $occurrences;
        $data['mass_date'] = $mass_date;
        $data['schedule_id'] = $schedule_id;
        $data['intentions'] = ($mass_date && $schedule_id)
            ? $this->MassIntention_model->reader_sheet($mass_date, $schedule_id)
            : [];
        $data['can_manage'] = false;
        $data['back_url'] = site_url('priest/dashboard');

        $this->load->view('staff/mass_intention_reader', $data);
    }
}
