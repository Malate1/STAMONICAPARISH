<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ServiceType_model extends CI_Model
{
    protected $table = 'service_types';

    public function all_active()
    {
        return $this->db->where('is_active', 1)->order_by('display_order', 'asc')->get($this->table)->result_array();
    }

    public function get($id)
    {
        return $this->db->get_where($this->table, ['id' => $id])->row_array();
    }

    public function find_by_key($key)
    {
        return $this->db->get_where($this->table, ['service_key' => $key])->row_array();
    }

    public function requirements($service_type_id)
    {
        return $this->db->where('service_type_id', $service_type_id)
            ->order_by('display_order', 'asc')
            ->get('service_requirements')->result_array();
    }

    public function all()
    {
        return $this->db->order_by('display_order', 'asc')->get($this->table)->result_array();
    }

    public function update($id, array $data)
    {
        return $this->db->where('id', $id)->update($this->table, $data);
    }
}
