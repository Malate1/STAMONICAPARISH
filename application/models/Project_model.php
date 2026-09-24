<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Project_model extends CI_Model
{
    protected $table = 'donation_campaigns';

    public function schema_ready()
    {
        foreach (['short_description','category','location','start_date','target_date','is_featured','display_order'] as $column) {
            if (!$this->db->field_exists($column, $this->table)) return false;
        }
        return true;
    }

    public function get($id)
    {
        $row = $this->db->get_where($this->table, ['id' => (int) $id])->row_array();
        return $row ? $this->with_progress($row) : null;
    }

    public function find_by_slug($slug)
    {
        $row = $this->db->get_where($this->table, ['slug' => $slug])->row_array();
        return $row ? $this->with_progress($row) : null;
    }

    public function active($limit = null)
    {
        $this->db->where('status', 'active');

        if ($this->schema_ready()) {
            $this->db->order_by('is_featured', 'desc')
                ->order_by('display_order', 'asc')
                ->order_by('created_at', 'desc');
        } else {
            $this->db->order_by('created_at', 'desc');
        }

        if ($limit) $this->db->limit((int) $limit);
        $rows = $this->db->get($this->table)->result_array();
        return $this->with_progress_many($rows);
    }

    public function public_projects()
    {
        if ($this->schema_ready()) {
            $this->db->order_by("CASE WHEN status = 'active' THEN 0 ELSE 1 END", '', false)
                ->order_by('is_featured', 'desc')
                ->order_by('display_order', 'asc')
                ->order_by('created_at', 'desc');
        } else {
            $this->db->order_by("CASE WHEN status = 'active' THEN 0 ELSE 1 END", '', false)
                ->order_by('created_at', 'desc');
        }

        return $this->with_progress_many($this->db->get($this->table)->result_array());
    }

    public function featured()
    {
        if (!$this->schema_ready()) {
            $rows = $this->active(1);
            return $rows ? $rows[0] : null;
        }

        $row = $this->db->where('status', 'active')
            ->where('is_featured', 1)
            ->order_by('display_order', 'asc')
            ->order_by('created_at', 'desc')
            ->limit(1)
            ->get($this->table)->row_array();

        if (!$row) {
            $rows = $this->active(1);
            return $rows ? $rows[0] : null;
        }

        return $this->with_progress($row);
    }

    public function create(array $data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, array $data)
    {
        return $this->db->where('id', (int) $id)->update($this->table, $data);
    }

    public function delete($id)
    {
        return $this->db->where('id', (int) $id)->delete($this->table);
    }

    public function verified_raised($campaign_id)
    {
        $row = $this->db
            ->select_sum('payments.amount', 'raised')
            ->from('payments')
            ->join('donations', 'donations.id = payments.payable_id', 'inner')
            ->where('payments.payable_type', 'donation')
            ->where('payments.status', 'payment_verified')
            ->where('donations.campaign_id', (int) $campaign_id)
            ->get()->row_array();

        return (float) ($row['raised'] ?? 0);
    }

    public function general_verified_raised()
    {
        $row = $this->db
            ->select_sum('payments.amount', 'raised')
            ->from('payments')
            ->join('donations', 'donations.id = payments.payable_id', 'inner')
            ->where('payments.payable_type', 'donation')
            ->where('payments.status', 'payment_verified')
            ->where('donations.campaign_id IS NULL', null, false)
            ->get()->row_array();

        return (float) ($row['raised'] ?? 0);
    }

    public function verified_donation_count($campaign_id = null)
    {
        $this->db->from('payments')
            ->join('donations', 'donations.id = payments.payable_id', 'inner')
            ->where('payments.payable_type', 'donation')
            ->where('payments.status', 'payment_verified');

        if ($campaign_id === null) {
            $this->db->where('donations.campaign_id IS NULL', null, false);
        } else {
            $this->db->where('donations.campaign_id', (int) $campaign_id);
        }

        return (int) $this->db->count_all_results();
    }

    public function datatable_query($request, $count_only = false)
    {
        $this->db->from($this->table);

        if (!empty($request['status'])) {
            $this->db->where('status', $request['status']);
        }

        if (!empty($request['search']['value'])) {
            $kw = $request['search']['value'];
            $this->db->group_start()
                ->like('title', $kw);
            if ($this->schema_ready()) {
                $this->db->or_like('category', $kw)
                    ->or_like('location', $kw);
            }
            $this->db->group_end();
        }

        if ($count_only) return $this->db->count_all_results();

        if ($this->schema_ready()) {
            $this->db->order_by('is_featured', 'desc')
                ->order_by('display_order', 'asc')
                ->order_by('created_at', 'desc');
        } else {
            $this->db->order_by('created_at', 'desc');
        }

        if (isset($request['start'], $request['length']) && (int) $request['length'] !== -1) {
            $this->db->limit((int) $request['length'], (int) $request['start']);
        }

        return $this->with_progress_many($this->db->get()->result_array());
    }

    private function with_progress_many(array $rows)
    {
        foreach ($rows as &$row) {
            $row = $this->with_progress($row);
        }
        unset($row);
        return $rows;
    }

    private function with_progress(array $row)
    {
        $defaults = [
            'short_description' => null,
            'category' => null,
            'location' => null,
            'start_date' => null,
            'target_date' => null,
            'is_featured' => 0,
            'display_order' => 0,
        ];
        $row = array_merge($defaults, $row);

        $raised = $this->verified_raised($row['id']);
        $goal = (float) ($row['goal_amount'] ?? 0);

        $row['raised_amount'] = $raised;
        $row['donor_count'] = $this->verified_donation_count($row['id']);
        $row['progress_percent'] = $goal > 0 ? min(100, round(($raised / $goal) * 100, 1)) : 0;
        $row['remaining_amount'] = $goal > 0 ? max(0, $goal - $raised) : 0;

        return $row;
    }
}
