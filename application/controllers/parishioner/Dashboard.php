<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_PARISHIONER]);
        $this->load->model(['Booking_model', 'Certificate_model', 'Payment_model', 'Notification_model', 'Announcement_model', 'DashboardReport_model']);
    }

    public function index()
    {
        $uid = $this->current_user['id'];
        $data['bookings']       = array_slice($this->Booking_model->for_user($uid), 0, 5);
        $data['certificates']   = array_slice($this->Certificate_model->for_user($uid), 0, 5);
        $data['notifications']  = $this->Notification_model->for_user($uid, 5);
        $data['announcements']  = $this->Announcement_model->published(3);

        $all_bookings = $this->Booking_model->for_user($uid);
        $data['stats'] = [
            'total_bookings' => count($all_bookings),
            'pending'        => count(array_filter($all_bookings, fn($b) => !in_array($b['status'], ['completed', 'cancelled']))),
            'completed'      => count(array_filter($all_bookings, fn($b) => $b['status'] === 'completed')),
            'certificates'   => count($this->Certificate_model->for_user($uid)),
        ];
        $data['activity_trend'] = $this->DashboardReport_model->parishioner_monthly_activity($uid, 6);
        $data['status_mix'] = $this->DashboardReport_model->booking_status_mix(['user_id' => $uid]);
        $data['personal_report'] = $this->DashboardReport_model->parishioner_report($uid);

        $this->render_app('parishioner/dashboard', $data, 'layouts/app_parishioner');
    }
}
