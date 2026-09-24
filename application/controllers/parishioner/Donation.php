<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Donation extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_PARISHIONER]);
        $this->load->model(['Donation_model','Project_model']);
    }

    public function create($campaign_id = 0)
    {
        $campaign = $campaign_id ? $this->Project_model->get((int) $campaign_id) : null;
        if ($campaign_id && (!$campaign || $campaign['status'] !== 'active')) show_404();

        $data['campaign'] = $campaign;
        $data['donations'] = $this->Donation_model->for_user($this->current_user['id']);
        $this->render_app('parishioner/donation_form', $data, 'layouts/app_parishioner');
    }

    public function store()
    {
        $campaign_id = (int) $this->input->post('campaign_id');
        $campaign = $campaign_id ? $this->Project_model->get($campaign_id) : null;
        if ($campaign_id && (!$campaign || $campaign['status'] !== 'active')) {
            return $this->json(['success'=>false,'message'=>'That parish project is no longer accepting donations.']);
        }

        $this->form_validation->set_rules('amount','Donation Amount','required|numeric|greater_than[0]');
        if ($this->form_validation->run() === false) {
            return $this->json(['success'=>false,'message'=>strip_tags(validation_errors())]);
        }

        $amount = round((float) $this->input->post('amount'), 2);
        if ($amount < 1) return $this->json(['success'=>false,'message'=>'Donation amount must be at least ₱1.00.']);

        $id = $this->Donation_model->create([
            'campaign_id' => $campaign_id ?: null,
            'user_id' => $this->current_user['id'],
            'donor_name' => trim($this->current_user['first_name'].' '.$this->current_user['last_name']),
            'is_anonymous' => $this->input->post('is_anonymous') ? 1 : 0,
            'amount' => $amount,
            'message' => trim((string) $this->input->post('message', true)) ?: null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$id) return $this->json(['success'=>false,'message'=>'Could not create the donation record.']);

        $this->json([
            'success'=>true,
            'message'=>'Donation pledge created. Please submit your GCash payment proof.',
            'redirect'=>site_url('my/payments/pay/donation/'.$id)
        ]);
    }
}
