<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Certificate extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_SECRETARY]);
        $this->load->model(['Certificate_model', 'Payment_model', 'Notification_model']);
    }

    public function index()
    {
        $this->render_app('admin/certificate_list', [], 'layouts/app_admin');
    }

    public function datatable()
    {
        $request = $this->input->post();
        $total = $this->Certificate_model->datatable_query($request, [], true);
        $rows  = $this->Certificate_model->datatable_query($request, [], false);

        $data = [];
        foreach ($rows as $r) {
            $data[] = [
                'request_code' => '<a href="' . site_url('staff/certificate/view/' . $r['id']) . '" class="font-medium text-emerald-700 hover:underline">' . $r['request_code'] . '</a>',
                'type'         => ucfirst($r['certificate_type']),
                'requestor'    => $r['first_name'] . ' ' . $r['last_name'],
                'copies'       => $r['number_of_copies'],
                'status'       => '<span class="px-2.5 py-1 rounded-full text-xs font-medium ' . status_badge_class($r['status']) . '">' . status_label($r['status']) . '</span>',
                'created_at'   => format_date($r['created_at']),
                'actions'      => '<a href="' . site_url('staff/certificate/view/' . $r['id']) . '" class="text-emerald-700 hover:underline font-medium">Process</a>',
            ];
        }

        $this->json(['draw' => (int) ($request['draw'] ?? 1), 'recordsTotal' => $total, 'recordsFiltered' => $total, 'data' => $data]);
    }

    public function view($id)
    {
        $cert = $this->Certificate_model->get($id);
        if (!$cert) show_404();

        $data['cert']    = $cert;
        $data['payment'] = $this->Payment_model->for_payable('certificate_request', $id);
        $this->render_app('admin/certificate_view', $data, 'layouts/app_admin');
    }

    public function search_records()
    {
        $results = $this->Certificate_model->search_records($this->input->post('type'), $this->input->post('keyword'));
        $this->json(['success' => true, 'data' => $results]);
    }

    public function link_record()
    {
        $this->Certificate_model->update((int) $this->input->post('id'), ['record_id' => (int) $this->input->post('record_id'), 'status' => 'record_found']);
        $this->json(['success' => true, 'message' => 'Record linked. Awaiting payment.']);
    }

    public function no_record()
    {
        $this->Certificate_model->update((int) $this->input->post('id'), ['status' => 'no_record_found']);
        $this->json(['success' => true, 'message' => 'Marked as no record found.']);
    }

    public function prepare()
    {
        $id = (int) $this->input->post('id');
        $cert = $this->Certificate_model->get($id);
        if (!$cert) return $this->json(['success' => false, 'message' => 'Not found.']);

        $token = bin2hex(random_bytes(16));
        $this->Certificate_model->update($id, ['status' => 'ready_for_release', 'qr_code_token' => $token, 'processed_by' => $this->current_user['id']]);
        $this->Notification_model->push($cert['user_id'], 'Certificate Ready', 'Your certificate (' . $cert['request_code'] . ') is ready for release.', site_url('my/certificates/' . $id));
        $this->log_activity('Prepared certificate', 'certificate', $cert['request_code']);
        $this->json(['success' => true, 'message' => 'Certificate marked ready for release.', 'verify_url' => site_url('verify/' . $token)]);
    }

    public function release()
    {
        $id = (int) $this->input->post('id');
        $this->Certificate_model->update($id, ['status' => 'released', 'released_at' => date('Y-m-d H:i:s'), 'processed_by' => $this->current_user['id']]);
        $this->json(['success' => true, 'message' => 'Certificate marked as released.']);
    }
}
