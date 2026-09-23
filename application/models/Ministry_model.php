<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ministry_model extends CI_Model
{
    protected $table = 'ministries';

    public function active()
    {
        return $this->db->where('is_active', 1)->order_by('display_order', 'asc')->get($this->table)->result_array();
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

    public function add_interest(array $data)
    {
        $this->db->insert('ministry_interests', $data);
        return $this->db->insert_id();
    }
}
