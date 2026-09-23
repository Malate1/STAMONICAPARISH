<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{
    protected $table = 'users';

    public function get_with_role($id)
    {
        return $this->db->select('users.*, roles.role_key, roles.role_name')
            ->from('users')
            ->join('roles', 'roles.id = users.role_id')
            ->where('users.id', $id)
            ->get()
            ->row_array();
    }

    public function find_by_email($email)
    {
        return $this->db->select('users.*, roles.role_key, roles.role_name')
            ->from('users')
            ->join('roles', 'roles.id = users.role_id')
            ->where('users.email', $email)
            ->get()
            ->row_array();
    }

    public function get($id)
    {
        return $this->db->get_where($this->table, ['id' => $id])->row_array();
    }

    public function create(array $data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, array $data)
    {
        return $this->db->where('id', $id)->update($this->table, $data);
    }

    public function register_failed_attempt($user_id)
    {
        $this->db->set('failed_login_attempts', 'failed_login_attempts + 1', false)
            ->where('id', $user_id)
            ->update($this->table);
    }

    public function reset_failed_attempts($user_id)
    {
        $this->update($user_id, ['failed_login_attempts' => 0, 'locked_until' => null, 'last_login_at' => date('Y-m-d H:i:s')]);
    }

    public function list_by_role($role_id)
    {
        return $this->db->where('role_id', $role_id)->order_by('first_name')->get($this->table)->result_array();
    }

    public function priests()
    {
        return $this->db->select('users.*, priest_profiles.title, priest_profiles.position, priest_profiles.bio, priest_profiles.is_public, priest_profiles.display_order')
            ->from('users')
            ->join('priest_profiles', 'priest_profiles.user_id = users.id', 'left')
            ->where('role_id', ROLE_PRIEST)
            ->where('status', 'active')
            ->order_by('priest_profiles.display_order', 'asc')
            ->get()
            ->result_array();
    }

    /**
     * Server-side DataTables query for admin user management.
     */
    public function datatable_query($request, $count_only = false)
    {
        $this->db->select('users.*, roles.role_name')
            ->from('users')
            ->join('roles', 'roles.id = users.role_id');

        if (!empty($request['search']['value'])) {
            $kw = $request['search']['value'];
            $this->db->group_start()
                ->like('users.first_name', $kw)
                ->or_like('users.last_name', $kw)
                ->or_like('users.email', $kw)
                ->group_end();
        }
        if (!empty($request['role_id'])) {
            $this->db->where('users.role_id', $request['role_id']);
        }

        if ($count_only) {
            return $this->db->count_all_results();
        }

        $columns = ['users.id', 'users.first_name', 'users.email', 'roles.role_name', 'users.status', 'users.created_at'];
        if (isset($request['order'][0])) {
            $col = $columns[(int) $request['order'][0]['column']] ?? 'users.id';
            $this->db->order_by($col, $request['order'][0]['dir']);
        } else {
            $this->db->order_by('users.id', 'desc');
        }

        if (isset($request['start'], $request['length']) && (int) $request['length'] !== -1) {
            $this->db->limit((int) $request['length'], (int) $request['start']);
        }

        return $this->db->get()->result_array();
    }
}
