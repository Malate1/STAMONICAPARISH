<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Record extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_ADMIN, ROLE_SECRETARY]);
    }

    public function index()
    {
        $this->render_app('admin/record_list', [], 'layouts/app_admin');
    }

    public function datatable()
    {
        $request = $this->input->post();

        // DataTables needs an unfiltered total and a filtered total. Do not keep
        // the Query Builder state after count_all_results(); doing so and then
        // calling get('sacramental_records') adds the same table a second time
        // on CI3/MySQL and can produce a 500 "Not unique table/alias" error.
        $records_total = (int) $this->db->count_all('sacramental_records');

        $this->apply_datatable_filters($request);
        $records_filtered = (int) $this->db->count_all_results('sacramental_records');

        $this->apply_datatable_filters($request);
        $this->db->order_by('id', 'desc');
        if (isset($request['start'], $request['length']) && (int) $request['length'] !== -1) {
            $this->db->limit((int) $request['length'], (int) $request['start']);
        }
        $rows = $this->db->get('sacramental_records')->result_array();

        $data = [];
        foreach ($rows as $r) {
            $data[] = [
                'type'      => html_escape(ucfirst($r['record_type'])),
                'full_name' => html_escape($r['full_name']),
                'sacrament_date' => !empty($r['sacrament_date']) ? format_date($r['sacrament_date']) : '—',
                'parents'   => html_escape(trim(($r['father_name'] ?: '') . ' / ' . ($r['mother_name'] ?: ''), ' /') ?: '—'),
                'registry'  => 'Bk. ' . html_escape($r['registry_book'] ?: '—') . ' Pg. ' . html_escape($r['registry_page'] ?: '—'),
                'actions'   => '<div class="flex items-center justify-center gap-1.5 whitespace-nowrap">'
                    . dt_icon_button('ph-pencil-simple', 'Edit sacramental record', 'editRecord(' . (int) $r['id'] . ')')
                    . '</div>',
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
            'created_by'        => $this->current_user['id'],
        ];

        $id = $this->input->post('id');
        if ($id) {
            $this->db->where('id', $id)->update('sacramental_records', $payload);
            $msg = 'Record updated.';
        } else {
            $this->db->insert('sacramental_records', $payload);
            $msg = 'Record added to registry.';
        }

        $this->log_activity('Saved sacramental record', 'records', $payload['full_name']);
        $this->json(['success' => true, 'message' => $msg]);
    }
}
