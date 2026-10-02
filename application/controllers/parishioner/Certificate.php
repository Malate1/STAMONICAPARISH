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

    public function printable($id)
    {
        $cert = $this->Certificate_model->get((int)$id);
        if (!$cert
            || (int)$cert['user_id'] !== (int)$this->current_user['id']
            || ($cert['status'] ?? '') !== 'released') {
            show_404();
        }

        $record = $this->Certificate_model->printable_record((int)$id);
        if (($cert['certificate_type'] ?? '') !== 'no_record') {
            if (!$record || ($this->db->field_exists('record_status','sacramental_records')
                && ($record['record_status'] ?? 'verified') !== 'verified')) {
                show_404();
            }
        }

        $settings = [];
        foreach ($this->db->where_in('setting_key', ['parish_name','parish_address','parish_contact'])
            ->get('system_settings')->result_array() as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        $processor = 'Parish Office';
        if (!empty($cert['processed_by'])) {
            $staff = $this->User_model->get((int)$cert['processed_by']);
            if ($staff) $processor = trim($staff['first_name'] . ' ' . $staff['last_name']);
        }

        $this->log_activity('Opened released certificate copy', 'certificate', $cert['request_code']);
        $this->load->view('certificates/printable', [
            'cert'=>$cert,
            'record'=>$record,
            'settings'=>$settings,
            'verification_url'=>site_url('verify/' . $cert['qr_code_token']),
            'processor'=>$processor,
        ]);
    }
}
