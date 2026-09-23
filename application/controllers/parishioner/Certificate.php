<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Certificate extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_PARISHIONER]);
        $this->load->model(['Certificate_model', 'Payment_model']);
    }

    public function index()
    {
        $data['certificates'] = $this->Certificate_model->for_user($this->current_user['id']);
        $this->render_app('parishioner/certificate_list', $data, 'layouts/app_parishioner');
    }

    public function create()
    {
        $this->render_app('parishioner/certificate_form', [], 'layouts/app_parishioner');
    }

    public function store()
    {
        $this->form_validation->set_rules('certificate_type', 'Certificate Type', 'required');
        $this->form_validation->set_rules('purpose', 'Purpose', 'required');
        $this->form_validation->set_rules('number_of_copies', 'Number of Copies', 'required|integer|greater_than[0]');

        if ($this->form_validation->run() === FALSE) {
            return $this->json(['success' => false, 'message' => strip_tags(validation_errors())]);
        }

        $copies = (int) $this->input->post('number_of_copies');
        $fee = 100 * $copies; // simple flat certificate fee per copy

        $id = $this->Certificate_model->create([
            'request_code'      => $this->Certificate_model->generate_code(),
            'user_id'           => $this->current_user['id'],
            'certificate_type'  => $this->input->post('certificate_type'),
            'purpose'           => $this->input->post('purpose', true),
            'number_of_copies'  => $copies,
            'fee_amount'        => $fee,
            'release_method'    => $this->input->post('release_method') ?: 'pickup',
            'status'            => 'submitted',
            'created_at'        => date('Y-m-d H:i:s'),
        ]);

        $this->flash_success('Certificate request submitted. The parish will search the record and notify you.');
        $this->json(['success' => true, 'redirect' => site_url('my/certificates/' . $id)]);
    }

    public function view($id)
    {
        $cert = $this->Certificate_model->get($id);
        if (!$cert || (int) $cert['user_id'] !== (int) $this->current_user['id']) show_404();

        $data['cert']    = $cert;
        $data['payment'] = $this->Payment_model->for_payable('certificate_request', $id);
        $this->render_app('parishioner/certificate_view', $data, 'layouts/app_parishioner');
    }
}
