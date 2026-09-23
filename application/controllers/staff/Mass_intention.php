<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mass_intention extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_SECRETARY]);
    }

    public function index()
    {
        $data['intentions'] = $this->db->select('mass_intentions.*, mass_schedules.title as mass_title, mass_schedules.mass_time, mass_schedules.day_of_week')
            ->from('mass_intentions')
            ->join('mass_schedules', 'mass_schedules.id = mass_intentions.mass_schedule_id', 'left')
            ->order_by('mass_intentions.created_at', 'desc')
            ->get()->result_array();
        $this->render_app('staff/mass_intention_list', $data, 'layouts/app_admin');
    }

    public function update_status()
    {
        $id = (int) $this->input->post('id');
        $status = $this->input->post('status');
        $this->db->where('id', $id)->update('mass_intentions', ['status' => $status]);
        $this->json(['success' => true, 'message' => 'Updated.']);
    }
}
