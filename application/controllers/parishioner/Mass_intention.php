<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mass_intention extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_PARISHIONER]);
        $this->load->model('MassSchedule_model');
    }

    public function index()
    {
        $data['intentions'] = $this->db->where('user_id', $this->current_user['id'])
            ->order_by('created_at', 'desc')->get('mass_intentions')->result_array();
        $data['masses'] = $this->MassSchedule_model->all_active();
        $this->render_app('parishioner/mass_intention', $data, 'layouts/app_parishioner');
    }

    public function store()
    {
        $this->form_validation->set_rules('intention_type', 'Intention Type', 'required');
        $this->form_validation->set_rules('offered_for', 'Offered For', 'required');
        $this->form_validation->set_rules('mass_schedule_id', 'Mass Schedule', 'required');

        if ($this->form_validation->run() === FALSE) {
            return $this->json(['success' => false, 'message' => strip_tags(validation_errors())]);
        }

        $this->db->insert('mass_intentions', [
            'user_id'            => $this->current_user['id'],
            'mass_schedule_id'   => $this->input->post('mass_schedule_id'),
            'intention_type'     => $this->input->post('intention_type'),
            'offered_for'        => $this->input->post('offered_for', true),
            'requestor_name'     => trim($this->current_user['first_name'] . ' ' . $this->current_user['last_name']),
            'requestor_contact'  => $this->current_user['mobile_number'],
            'fee_amount'         => 100.00,
            'created_at'         => date('Y-m-d H:i:s'),
        ]);

        $this->json(['success' => true, 'message' => 'Mass intention submitted for approval.']);
    }
}
