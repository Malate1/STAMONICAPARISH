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
        $this->render_app('admin/record_list', [], 'layouts/app_admin');
    }

    public function datatable()
    {
        $request = $this->input->post();
        $this->db->from('sacramental_records');
        if (!empty($request['record_type'])) $this->db->where('record_type', $request['record_type']);
        if (!empty($request['search']['value'])) {
            $kw = $request['search']['value'];
            $this->db->group_start()->like('full_name', $kw)->or_like('father_name', $kw)->or_like('mother_name', $kw)->group_end();
        }
        $total = $this->db->count_all_results('', false);
        $this->db->order_by('id', 'desc');
        if (isset($request['start'], $request['length']) && (int) $request['length'] !== -1) {
            $this->db->limit((int) $request['length'], (int) $request['start']);
        }
        $rows = $this->db->get('sacramental_records')->result_array();

        $data = [];
        foreach ($rows as $r) {
            $data[] = [
                'type'      => ucfirst($r['record_type']),
                'full_name' => $r['full_name'],
                'sacrament_date' => format_date($r['sacrament_date']),
                'parents'   => trim(($r['father_name'] ?: '') . ' / ' . ($r['mother_name'] ?: ''), ' /'),
                'registry'  => 'Bk. ' . ($r['registry_book'] ?: '—') . ' Pg. ' . ($r['registry_page'] ?: '—'),
                'actions'   => '<button onclick="editRecord(' . $r['id'] . ')" class="text-emerald-700 hover:underline font-medium">Edit</button>',
            ];
        }

        $this->json(['draw' => (int) ($request['draw'] ?? 1), 'recordsTotal' => $total, 'recordsFiltered' => $total, 'data' => $data]);
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
