<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Certificate extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_ADMIN, ROLE_SECRETARY]);
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
                'request_code' => '<a href="' . site_url('admin/certificate/view/' . $r['id']) . '" class="font-medium text-emerald-700 hover:underline">' . $r['request_code'] . '</a>',
                'type'         => ucfirst($r['certificate_type']),
                'requestor'    => $r['first_name'] . ' ' . $r['last_name'],
                'copies'       => $r['number_of_copies'],
                'status'       => '<span class="px-2.5 py-1 rounded-full text-xs font-medium ' . status_badge_class($r['status']) . '">' . status_label($r['status']) . '</span>',
                'created_at'   => format_date($r['created_at']),
                'actions'      => '<div class="flex items-center justify-center gap-1.5 whitespace-nowrap">'
                    . dt_icon_link('ph-eye', 'Process certificate request', site_url('admin/certificate/view/' . $r['id']))
                    . '</div>',
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

    /** AJAX: search parish sacramental registry */
    public function search_records()
    {
        $id = (int) $this->input->post('id');
        if ($id) {
            $cert = $this->Certificate_model->get($id);
            if (!$cert) return $this->json(['success'=>false,'message'=>'Certificate request not found.'],404);

            if ($cert['status'] === 'submitted'
                && !$this->Certificate_model->change_status($id, 'searching_record', $this->current_user['id'])) {
                return $this->json(['success'=>false,'message'=>$this->Certificate_model->transition_error()]);
            }
        }

        $type = $this->input->post('type', true);
        $kw = trim((string)$this->input->post('keyword', true));
        if ($kw === '') {
            return $this->json(['success'=>false,'message'=>'Enter a name or registry search term.']);
        }

        $results = $this->Certificate_model->search_records($type, $kw);
        $this->json(['success' => true, 'data' => $results]);
    }

    /** AJAX: link a found registry record to this request */
    public function link_record()
    {
        $id = (int) $this->input->post('id');
        $record_id = (int) $this->input->post('record_id');

        $cert = $this->Certificate_model->get($id);
        if (!$cert || !in_array($cert['status'], ['submitted', 'searching_record', 'record_found'], true)) {
            return $this->json(['success' => false, 'message' => 'This certificate request is not in record-search processing.']);
        }

        $next_status = $this->Certificate_model->link_verified_record_and_advance(
            $id,
            $record_id,
            $this->current_user['id']
        );

        if (!$next_status) {
            return $this->json([
                'success' => false,
                'message' => $this->Certificate_model->transition_error() ?: 'The record could not be linked.'
            ]);
        }

        $this->log_activity('Linked verified sacramental record', 'certificate', $cert['request_code'] . ' → record #' . $record_id);
        $this->json([
            'success' => true,
            'message' => $next_status === 'awaiting_payment'
                ? 'Verified record linked. The request is now awaiting payment.'
                : 'Verified record linked. The request is ready for certificate preparation.'
        ]);
    }

    /** AJAX: no matching record found */
    public function no_record()
    {
        $id = (int) $this->input->post('id');
        $cert = $this->Certificate_model->get($id);
        if (!$cert || !in_array($cert['status'], ['submitted', 'searching_record'], true)) {
            return $this->json(['success' => false, 'message' => 'This request is no longer in record-search processing.']);
        }

        if ($cert['status'] === 'submitted') {
            if (!$this->Certificate_model->change_status($id, 'searching_record', $this->current_user['id'])) {
                return $this->json(['success'=>false,'message'=>$this->Certificate_model->transition_error()]);
            }
        }

        if (!$this->Certificate_model->change_status($id, 'no_record_found', $this->current_user['id'])) {
            return $this->json(['success'=>false,'message'=>$this->Certificate_model->transition_error()]);
        }

        $message = 'Marked as no record found.';
        if (($cert['certificate_type'] ?? '') === 'no_record') {
            $next_status = (float)$cert['fee_amount'] > 0 ? 'awaiting_payment' : 'preparing';
            if (!$this->Certificate_model->change_status($id, $next_status, $this->current_user['id'])) {
                return $this->json(['success'=>false,'message'=>$this->Certificate_model->transition_error()]);
            }
            $message = $next_status === 'awaiting_payment'
                ? 'No matching record was found. This Certificate of No Record request is now awaiting payment.'
                : 'No matching record was found. This Certificate of No Record request is ready for preparation.';
        }

        $this->log_activity('Marked certificate search as no record found', 'certificate', $cert['request_code']);
        $this->json(['success' => true, 'message' => $message]);
    }

    /** AJAX: generate certificate (mark prepared) + QR token, move to ready for release */
    public function prepare()
    {
        $id = (int) $this->input->post('id');
        $cert = $this->Certificate_model->get($id);
        if (!$cert) return $this->json(['success' => false, 'message' => 'Not found.']);

        if ($cert['status'] !== 'preparing') {
            return $this->json(['success' => false, 'message' => 'This certificate request is not ready for preparation.']);
        }

        $is_no_record_certificate = ($cert['certificate_type'] ?? '') === 'no_record';

        if (!$is_no_record_certificate && empty($cert['record_id'])) {
            return $this->json(['success' => false, 'message' => 'Link a verified sacramental record before preparing the certificate.']);
        }

        if (!$is_no_record_certificate && $this->db->field_exists('record_status', 'sacramental_records')) {
            $record = $this->db->get_where('sacramental_records', ['id' => (int) $cert['record_id']])->row_array();
            if (!$record || ($record['record_status'] ?? 'verified') !== 'verified') {
                return $this->json(['success' => false, 'message' => 'The linked sacramental record must be Verified before certificate preparation.']);
            }
        }

        if ((float) $cert['fee_amount'] > 0) {
            $payment = $this->Payment_model->for_payable('certificate_request', $id);
            if (!$payment || $payment['status'] !== 'payment_verified') {
                return $this->json(['success' => false, 'message' => 'Verify the certificate payment before preparing the certificate.']);
            }
        }

        $token = bin2hex(random_bytes(16));
        $this->Certificate_model->update($id, [
            'qr_code_token' => $token,
            'processed_by'  => $this->current_user['id'],
        ]);

        if (!$this->Certificate_model->change_status($id, 'ready_for_release', $this->current_user['id'])) {
            $this->Certificate_model->update($id, ['qr_code_token' => null]);
            return $this->json([
                'success'=>false,
                'message'=>$this->Certificate_model->transition_error() ?: 'The certificate is not ready for release.'
            ]);
        }

        $this->Notification_model->push($cert['user_id'], 'Certificate Ready', 'Your certificate (' . $cert['request_code'] . ') is ready for release.', site_url('my/certificates/' . $id));
        $this->log_activity('Prepared certificate', 'certificate', $cert['request_code']);
        $this->json(['success' => true, 'message' => 'Certificate marked ready for release.', 'verify_url' => site_url('verify/' . $token)]);
    }

    public function printable($id)
    {
        $cert = $this->Certificate_model->get((int)$id);
        if (!$cert) show_404();
        if (!in_array($cert['status'], ['ready_for_release','released'], true)) {
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

        $this->log_activity('Opened print-ready certificate', 'certificate', $cert['request_code']);
        $this->load->view('certificates/printable', [
            'cert' => $cert,
            'record' => $record,
            'settings' => $settings,
            'verification_url' => site_url('verify/' . $cert['qr_code_token']),
            'processor' => trim($this->current_user['first_name'] . ' ' . $this->current_user['last_name']),
        ]);
    }

    /** AJAX: mark released to the parishioner */
    public function release()
    {
        $id = (int) $this->input->post('id');
        $cert = $this->Certificate_model->get($id);
        if (!$cert) return $this->json(['success' => false, 'message' => 'Not found.']);
        if ($cert['status'] !== 'ready_for_release') {
            return $this->json(['success' => false, 'message' => 'Only a certificate that is Ready for Release can be marked Released.']);
        }

        if (!$this->Certificate_model->change_status($id, 'released', $this->current_user['id'])) {
            return $this->json([
                'success'=>false,
                'message'=>$this->Certificate_model->transition_error() ?: 'The certificate could not be released.'
            ]);
        }

        $this->log_activity('Released certificate', 'certificate', $cert['request_code']);
        $this->json(['success' => true, 'message' => 'Certificate marked as released.']);
    }

    public function update_status()
    {
        $id = (int) $this->input->post('id');
        $status = $this->input->post('status', true);
        $cert = $this->Certificate_model->get($id);
        if (!$cert) return $this->json(['success' => false, 'message' => 'Not found.']);

        if (!$this->Certificate_model->change_status($id, $status, $this->current_user['id'])) {
            return $this->json([
                'success'=>false,
                'message'=>$this->Certificate_model->transition_error() ?: 'That certificate status change is not allowed.'
            ]);
        }
        $this->log_activity('Updated certificate status', 'certificate', $cert['request_code'] . ' → ' . $status);
        $this->json(['success' => true, 'message' => 'Status updated.']);
    }
}
