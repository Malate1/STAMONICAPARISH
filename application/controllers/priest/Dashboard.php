<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_PRIEST]);
        $this->load->model('Booking_model');
    }

    public function index()
    {
        $assignments = $this->Booking_model->for_priest($this->current_user['id']);
        $data['assignments'] = $assignments;
        $data['this_week'] = array_filter($assignments, function ($a) {
            return $a['confirmed_date'] && strtotime($a['confirmed_date']) <= strtotime('+7 days');
        });
        $this->render_app('priest/dashboard', $data, 'layouts/app_admin');
    }
}
