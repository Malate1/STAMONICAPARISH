<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Audit extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_ADMIN]);
        $this->load->model('Audit_model');
    }

    public function index()
    {
        $data['modules'] = $this->Audit_model->modules();
        $data['users'] = $this->db->select('id, first_name, last_name, email')
            ->where_in('role_id', [ROLE_ADMIN, ROLE_SECRETARY, ROLE_PRIEST])
            ->order_by('last_name', 'asc')
            ->order_by('first_name', 'asc')
            ->get('users')->result_array();

        $this->render_app('admin/audit_list', $data, 'layouts/app_admin');
    }

    public function datatable()
    {
        $request = $this->input->post();
        $filtered = $this->Audit_model->datatable_query($request, true);
        $rows = $this->Audit_model->datatable_query($request, false);
        $total = (int)$this->db->count_all('audit_logs');

        $data = [];
        foreach ($rows as $row) {
            $name = trim((string)($row['first_name'] ?? '') . ' ' . (string)($row['last_name'] ?? ''));
            if ($name === '') $name = 'System / Deleted Account';

            $description = trim((string)($row['description'] ?? ''));
            $data[] = [
                'when' => '<div class="whitespace-nowrap text-gray-700">' . html_escape(format_datetime($row['created_at'])) . '</div>',
                'user' => '<div class="font-medium text-gray-800">' . html_escape($name) . '</div>'
                    . (!empty($row['role_name']) ? '<div class="text-[11px] text-gray-400">' . html_escape($row['role_name']) . '</div>' : ''),
                'module' => '<span class="inline-flex px-2.5 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-medium">'
                    . html_escape($row['module'] ?: 'general') . '</span>',
                'action' => '<div class="font-medium text-gray-800">' . html_escape($row['action']) . '</div>'
                    . ($description !== '' ? '<div class="text-xs text-gray-400 mt-1 max-w-xl break-words">' . html_escape($description) . '</div>' : ''),
                'ip' => '<span class="font-mono text-xs text-gray-500">' . html_escape($row['ip_address'] ?: '—') . '</span>',
            ];
        }

        $this->json([
            'draw' => (int)($request['draw'] ?? 1),
            'recordsTotal' => $total,
            'recordsFiltered' => (int)$filtered,
            'data' => $data,
        ]);
    }
}
