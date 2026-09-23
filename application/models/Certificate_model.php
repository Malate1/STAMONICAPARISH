<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Certificate_model extends CI_Model
{
    protected $table = 'certificate_requests';

    public function generate_code()
    {
        $count = $this->db->count_all_results($this->table) + 1;
        return generate_code('cert', $count);
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

    public function get($id)
    {
        return $this->db->select('certificate_requests.*, users.first_name, users.last_name, users.email,
                sacramental_records.full_name as record_name, sacramental_records.sacrament_date, sacramental_records.registry_book, sacramental_records.registry_page')
            ->from($this->table)
            ->join('users', 'users.id = certificate_requests.user_id')
            ->join('sacramental_records', 'sacramental_records.id = certificate_requests.record_id', 'left')
            ->where('certificate_requests.id', $id)
            ->get()->row_array();
    }

    public function get_by_token($token)
    {
        return $this->db->get_where($this->table, ['qr_code_token' => $token])->row_array();
    }

    public function for_user($user_id)
    {
        return $this->db->where('user_id', $user_id)->order_by('created_at', 'desc')->get($this->table)->result_array();
    }

    public function search_records($type, $keyword)
    {
        return $this->db->where('record_type', $type)
            ->group_start()
                ->like('full_name', $keyword)
                ->or_like('father_name', $keyword)
                ->or_like('mother_name', $keyword)
            ->group_end()
            ->limit(20)
            ->get('sacramental_records')->result_array();
    }

    public function datatable_query($request, $scope = [], $count_only = false)
    {
        $this->db->select('certificate_requests.*, users.first_name, users.last_name, users.email')
            ->from($this->table)
            ->join('users', 'users.id = certificate_requests.user_id');

        if (!empty($scope['user_id'])) {
            $this->db->where('certificate_requests.user_id', $scope['user_id']);
        }
        if (!empty($request['status'])) {
            $this->db->where('certificate_requests.status', $request['status']);
        }
        if (!empty($request['search']['value'])) {
            $kw = $request['search']['value'];
            $this->db->group_start()
                ->like('certificate_requests.request_code', $kw)
                ->or_like('users.first_name', $kw)
                ->or_like('users.last_name', $kw)
                ->group_end();
        }

        if ($count_only) return $this->db->count_all_results();

        $this->db->order_by('certificate_requests.id', 'desc');
        if (isset($request['start'], $request['length']) && (int) $request['length'] !== -1) {
            $this->db->limit((int) $request['length'], (int) $request['start']);
        }
        return $this->db->get()->result_array();
    }
}
