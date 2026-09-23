<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Event_model extends CI_Model
{
    protected $table = 'events';

    public function upcoming($limit = null)
    {
        $this->db->where('status', 'published')->where('event_date >=', date('Y-m-d'))->order_by('event_date', 'asc');
        if ($limit) $this->db->limit($limit);
        return $this->db->get($this->table)->result_array();
    }

    public function find_by_slug($slug)
    {
        return $this->db->get_where($this->table, ['slug' => $slug])->row_array();
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

    public function delete($id)
    {
        return $this->db->where('id', $id)->delete($this->table);
    }

    public function register($event_id, array $data)
    {
        $this->db->insert('event_registrations', array_merge($data, ['event_id' => $event_id]));
        return $this->db->insert_id();
    }

    public function registration_count($event_id)
    {
        return $this->db->where('event_id', $event_id)->where('status', 'registered')->count_all_results('event_registrations');
    }

    public function datatable_query($request, $count_only = false)
    {
        $this->db->from($this->table);
        if (!empty($request['search']['value'])) {
            $this->db->like('title', $request['search']['value']);
        }
        if ($count_only) return $this->db->count_all_results();
        $this->db->order_by('event_date', 'desc');
        if (isset($request['start'], $request['length']) && (int) $request['length'] !== -1) {
            $this->db->limit((int) $request['length'], (int) $request['start']);
        }
        return $this->db->get()->result_array();
    }
}
