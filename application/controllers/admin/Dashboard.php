<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_ADMIN]);
        $this->load->model(['Booking_model', 'Certificate_model', 'Payment_model', 'Audit_model', 'User_model']);
    }

    public function index()
    {
        $status_counts = $this->Booking_model->counts_by_status();

        $data['today_summary'] = $this->Booking_model->today_summary();
        $data['needs_attention'] = [
            'awaiting_verification' => ($status_counts['submitted'] ?? 0) + ($status_counts['under_review'] ?? 0),
            'incomplete_requirements' => $status_counts['missing_requirements'] ?? 0,
            'payments_awaiting'     => $this->db->where('status', 'submitted')->count_all_results('payments'),
            'certificates_ready'    => $this->db->where('status', 'ready_for_release')->count_all_results('certificate_requests'),
        ];
        $data['status_counts'] = $status_counts;
        $data['recent_activity'] = $this->Audit_model->recent(10);
        $data['user_counts'] = [
            'parishioners' => count($this->User_model->list_by_role(ROLE_PARISHIONER)),
            'priests'      => count($this->User_model->list_by_role(ROLE_PRIEST)),
            'secretaries'  => count($this->User_model->list_by_role(ROLE_SECRETARY)),
        ];
        $data['revenue_this_month'] = $this->Payment_model->total_verified_between(date('Y-m-01'), date('Y-m-t 23:59:59'));

        $this->render_app('admin/dashboard', $data, 'layouts/app_admin');
    }
}
