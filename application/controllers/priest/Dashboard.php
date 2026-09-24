<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_PRIEST]);
        $this->load->model(['Booking_model', 'DashboardReport_model']);
    }

    public function index()
    {
        $assignments = $this->Booking_model->for_priest($this->current_user['id']);
        $data['assignments'] = $assignments;
        $data['this_week'] = array_filter($assignments, function ($a) {
            return $a['confirmed_date'] && strtotime($a['confirmed_date']) >= time() && strtotime($a['confirmed_date']) <= strtotime('+7 days');
        });
        $data['weekly_assignments'] = $this->DashboardReport_model->priest_weekly_assignments($this->current_user['id'], 8);
        $data['service_mix'] = $this->DashboardReport_model->service_mix(180, ['assigned_priest_id' => $this->current_user['id']], 7);
        $data['priest_report'] = $this->DashboardReport_model->priest_report($this->current_user['id']);
        $this->render_app('priest/dashboard', $data, 'layouts/app_admin');
    }
}
