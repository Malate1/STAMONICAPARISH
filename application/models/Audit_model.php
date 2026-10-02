<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Audit_model extends CI_Model
{
    public function log($user_id, $action, $module = null, $description = null, $ip = null)
    {
        $this->db->insert('audit_logs', [
            'user_id'     => $user_id,
            'action'      => $action,
            'module'      => $module,
            'description' => $description,
            'ip_address'  => $ip,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    public function recent($limit = 20)
    {
        return $this->db->select('audit_logs.*, users.first_name, users.last_name')
            ->from('audit_logs')
            ->join('users', 'users.id = audit_logs.user_id', 'left')
            ->order_by('audit_logs.id', 'desc')
            ->limit($limit)
            ->get()
            ->result_array();
    }

    public function modules()
    {
        return array_values(array_filter(array_column(
            $this->db->select('module')
                ->distinct()
                ->where('module IS NOT NULL', null, false)
                ->where('module !=', '')
                ->order_by('module', 'asc')
                ->get('audit_logs')->result_array(),
            'module'
        )));
    }

    public function datatable_query($request, $count_only = false)
    {
        $this->db->select('audit_logs.*, users.first_name, users.last_name, users.email, roles.name AS role_name')
            ->from('audit_logs')
            ->join('users', 'users.id = audit_logs.user_id', 'left')
            ->join('roles', 'roles.id = users.role_id', 'left');

        if (!empty($request['module'])) {
            $this->db->where('audit_logs.module', $request['module']);
        }
        if (!empty($request['user_id'])) {
            $this->db->where('audit_logs.user_id', (int)$request['user_id']);
        }
        if (!empty($request['date_from'])) {
            $this->db->where('audit_logs.created_at >=', $request['date_from'] . ' 00:00:00');
        }
        if (!empty($request['date_to'])) {
            $this->db->where('audit_logs.created_at <=', $request['date_to'] . ' 23:59:59');
        }
        if (!empty($request['search']['value'])) {
            $keyword = trim((string)$request['search']['value']);
            $this->db->group_start()
                ->like('audit_logs.action', $keyword)
                ->or_like('audit_logs.module', $keyword)
                ->or_like('audit_logs.description', $keyword)
                ->or_like('audit_logs.ip_address', $keyword)
                ->or_like('users.first_name', $keyword)
                ->or_like('users.last_name', $keyword)
                ->or_like('users.email', $keyword)
                ->group_end();
        }

        if ($count_only) {
            return $this->db->count_all_results();
        }

        $this->db->order_by('audit_logs.id', 'desc');
        if (isset($request['start'], $request['length']) && (int)$request['length'] !== -1) {
            $this->db->limit((int)$request['length'], (int)$request['start']);
        }

        return $this->db->get()->result_array();
    }
}
