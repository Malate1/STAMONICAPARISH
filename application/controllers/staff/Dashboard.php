<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_SECRETARY]);
        $this->load->model(['Booking_model', 'Certificate_model', 'Payment_model', 'DashboardReport_model']);
    }

    public function index()
    {
        $status_counts = $this->Booking_model->counts_by_status();
        $data['today_summary'] = $this->Booking_model->today_summary();
        $data['needs_attention'] = [
            'awaiting_verification'   => ($status_counts['submitted'] ?? 0) + ($status_counts['under_review'] ?? 0),
            'incomplete_requirements' => $status_counts['missing_requirements'] ?? 0,
            'payments_awaiting'       => $this->db->where('status', 'submitted')->count_all_results('payments'),
            'certificates_ready'      => $this->db->where('status', 'ready_for_release')->count_all_results('certificate_requests'),
        ];
        $data['status_counts'] = $status_counts;
        $data['intake_trend'] = $this->DashboardReport_model->daily_operational_intake(14);
        $data['service_mix'] = $this->DashboardReport_model->service_mix(90, [], 7);
        $data['status_mix'] = $this->DashboardReport_model->booking_status_mix();
        $data['office_report'] = $this->DashboardReport_model->office_report();

        $this->render_app('staff/dashboard', $data, 'layouts/app_admin');
    }
}
