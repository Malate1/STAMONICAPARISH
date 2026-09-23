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
}
