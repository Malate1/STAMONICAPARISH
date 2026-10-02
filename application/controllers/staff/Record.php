<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Record extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_SECRETARY]);
    }

    public function index()
    {
        $data['record_workflow_ready'] = $this->db->field_exists('record_status', 'sacramental_records')
            && $this->db->field_exists('source_type', 'sacramental_records')
            && $this->db->field_exists('updated_by', 'sacramental_records')
            && $this->db->field_exists('verified_by', 'sacramental_records')
            && $this->db->field_exists('verified_at', 'sacramental_records');
        $this->render_app('admin/record_list', $data, 'layouts/app_admin');
    }

    public function datatable()
    {
        $request = $this->input->post();

        $records_total = (int) $this->db->count_all('sacramental_records');

        $this->apply_datatable_filters($request);
        $records_filtered = (int) $this->db->count_all_results('sacramental_records');

        $this->apply_datatable_filters($request);
        $this->db->order_by('id', 'desc');
        if (isset($request['start'], $request['length']) && (int) $request['length'] !== -1) {
            $this->db->limit((int) $request['length'], (int) $request['start']);
        }
        $rows = $this->db->get('sacramental_records')->result_array();

        $workflow_ready = $this->db->field_exists('record_status', 'sacramental_records');
        $data = [];
        foreach ($rows as $r) {
            $type_html = html_escape(ucfirst($r['record_type']));
            if ($workflow_ready) {
                $is_verified = ($r['record_status'] ?? 'verified') === 'verified';
                $type_html .= '<div class="mt-1"><span class="px-2 py-0.5 rounded-full text-[10px] font-semibold '
                    . ($is_verified ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700')
                    . '">' . ($is_verified ? 'Verified' : 'Draft · Needs Review') . '</span></div>';
            }

            $actions = dt_icon_button('ph-pencil-simple', 'Edit sacramental record', 'editRecord(' . (int) $r['id'] . ')');
            if ($workflow_ready && ($r['record_status'] ?? 'verified') === 'draft') {
                $actions .= dt_icon_button('ph-seal-check', 'Verify registry record', 'verifyRecord(' . (int) $r['id'] . ')', 'blue');
            }

            $data[] = [
                'type'      => $type_html,
                'full_name' => html_escape($r['full_name']),
                'sacrament_date' => !empty($r['sacrament_date']) ? format_date($r['sacrament_date']) : '—',
                'parents'   => html_escape(trim(($r['father_name'] ?: '') . ' / ' . ($r['mother_name'] ?: ''), ' /') ?: '—'),
                'registry'  => 'Bk. ' . html_escape($r['registry_book'] ?: '—') . ' Pg. ' . html_escape($r['registry_page'] ?: '—'),
                'actions'   => '<div class="flex items-center justify-center gap-1.5 whitespace-nowrap">' . $actions . '</div>',
            ];
        }

        $this->json([
            'draw' => (int) ($request['draw'] ?? 1),
            'recordsTotal' => $records_total,
            'recordsFiltered' => $records_filtered,
            'data' => $data
        ]);
    }

    private function apply_datatable_filters(array $request)
    {
        $allowed_types = ['baptism', 'confirmation', 'communion', 'marriage', 'funeral'];

        if (!empty($request['record_type']) && in_array($request['record_type'], $allowed_types, true)) {
            $this->db->where('record_type', $request['record_type']);
        }

        if (!empty($request['search']['value'])) {
            $kw = trim((string) $request['search']['value']);
            if ($kw !== '') {
                $this->db->group_start()
                    ->like('full_name', $kw)
                    ->or_like('father_name', $kw)
                    ->or_like('mother_name', $kw)
                    ->or_like('spouse_name', $kw)
                    ->or_like('minister_name', $kw)
                    ->or_like('registry_book', $kw)
                    ->or_like('registry_page', $kw)
                    ->or_like('registry_entry_no', $kw)
                    ->group_end();
            }
        }
    }

    public function get($id)
    {
        $this->json(['success' => true, 'data' => $this->db->get_where('sacramental_records', ['id' => $id])->row_array()]);
    }

    public function store()
    {
        $this->form_validation->set_rules('record_type', 'Record Type', 'required');
        $this->form_validation->set_rules('full_name', 'Full Name', 'required');
        if ($this->form_validation->run() === FALSE) {
            return $this->json(['success' => false, 'message' => strip_tags(validation_errors())]);
        }

        $payload = [
            'record_type'       => $this->input->post('record_type'),
            'full_name'         => $this->input->post('full_name', true),
            'birth_date'        => $this->input->post('birth_date') ?: null,
            'sacrament_date'    => $this->input->post('sacrament_date') ?: null,
            'father_name'       => $this->input->post('father_name', true),
            'mother_name'       => $this->input->post('mother_name', true),
            'spouse_name'       => $this->input->post('spouse_name', true),
            'minister_name'     => $this->input->post('minister_name', true),
            'registry_book'     => $this->input->post('registry_book', true),
            'registry_page'     => $this->input->post('registry_page', true),
            'registry_entry_no' => $this->input->post('registry_entry_no', true),
            'remarks'           => $this->input->post('remarks', true),
        ];

        $workflow_ready = $this->db->field_exists('record_status', 'sacramental_records')
            && $this->db->field_exists('source_type', 'sacramental_records')
            && $this->db->field_exists('updated_by', 'sacramental_records')
            && $this->db->field_exists('verified_by', 'sacramental_records')
            && $this->db->field_exists('verified_at', 'sacramental_records');

        if ($workflow_ready) {
            $payload['record_status'] = 'draft';
            $payload['updated_by'] = $this->current_user['id'];
            $payload['verified_by'] = null;
            $payload['verified_at'] = null;
        }

        $id = $this->input->post('id');
        if ($id) {
            $this->db->where('id', $id)->update('sacramental_records', $payload);
            $msg = 'Record updated.';
        } else {
            $payload['created_by'] = $this->current_user['id'];
            if ($workflow_ready) $payload['source_type'] = 'manual';
            $this->db->insert('sacramental_records', $payload);
            $msg = $workflow_ready
                ? 'Record saved as Draft. Another authorized staff member should verify it against the parish register.'
                : 'Record added to registry.';
        }

        if ($id && $workflow_ready) {
            $msg = 'Record updated and returned to Draft for verification.';
        }

        $this->log_activity('Saved sacramental record', 'records', $payload['full_name']);
        $this->json(['success' => true, 'message' => $msg]);
    }

    public function verify($id)
    {
        if (!$this->db->field_exists('record_status', 'sacramental_records')) {
            return $this->json([
                'success' => false,
                'message' => 'Run database/migrations/20260925_sacramental_record_workflow.sql first.'
            ]);
        }

        $record = $this->db->get_where('sacramental_records', ['id' => (int) $id])->row_array();
        if (!$record) return $this->json(['success' => false, 'message' => 'Record not found.'], 404);

        if (($record['record_status'] ?? 'verified') === 'verified') {
            return $this->json(['success' => true, 'message' => 'This registry record is already verified.']);
        }

        $last_encoder = (int) ($record['updated_by'] ?: $record['created_by']);
        if ($last_encoder === (int) $this->current_user['id']) {
            return $this->json([
                'success' => false,
                'message' => 'For registry integrity, the staff member who last encoded or edited this record cannot verify the same record. Ask another authorized staff member to compare it with the source register.'
            ]);
        }

        $this->db->where('id', (int) $id)->update('sacramental_records', [
            'record_status' => 'verified',
            'verified_by' => $this->current_user['id'],
            'verified_at' => date('Y-m-d H:i:s'),
        ]);

        $this->log_activity('Verified sacramental record', 'records', $record['full_name']);
        $this->json(['success' => true, 'message' => 'Registry record verified against the parish source record.']);
    }
}
