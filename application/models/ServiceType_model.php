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

    public function schedule_rules($service_type_id, $active_only = false)
    {
        $this->db->where('service_type_id', $service_type_id);
        if ($active_only) {
            $this->db->where('is_active', 1);
        }

        return $this->db
            ->order_by('display_order', 'asc')
            ->order_by('day_of_week', 'asc')
            ->order_by('start_time', 'asc')
            ->get('service_schedule_rules')
            ->result_array();
    }

    public function get_schedule_rule($id)
    {
        return $this->db->get_where('service_schedule_rules', ['id' => $id])->row_array();
    }

    public function save_schedule_rule(array $data, $id = null)
    {
        if ($id) {
            return $this->db->where('id', $id)->update('service_schedule_rules', $data);
        }

        $this->db->insert('service_schedule_rules', $data);
        return $this->db->insert_id();
    }

    public function delete_schedule_rule($id)
    {
        return $this->db->where('id', $id)->delete('service_schedule_rules');
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
