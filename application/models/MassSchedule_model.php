<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MassSchedule_model extends CI_Model
{
    protected $table = 'mass_schedules';

    public function all_active()
    {
        return $this->db->select('mass_schedules.*, users.first_name, users.last_name')
            ->from('mass_schedules')
            ->join('users', 'users.id = mass_schedules.presider_id', 'left')
            ->where('mass_schedules.is_active', 1)
            ->order_by('mass_schedules.day_of_week', 'asc')
            ->order_by('mass_schedules.mass_time', 'asc')
            ->get()
            ->result_array();
    }

    /**
     * Today's masses: regular weekly schedule for today's day-of-week,
     * plus any date-specific overrides/special masses for today.
     */
    public function today()
    {
        $dow = date('w');
        $today = date('Y-m-d');

        $overrides = $this->db->where('specific_date', $today)->where('is_active', 1)
            ->order_by('mass_time', 'asc')->get($this->table)->result_array();

        if (!empty($overrides)) {
            return $overrides;
        }

        return $this->db->where('day_of_week', $dow)->where('is_active', 1)
            ->where('specific_date IS NULL', null, false)
            ->order_by('mass_time', 'asc')->get($this->table)->result_array();
    }

    public function next_mass()
    {
        $today_schedules = $this->today();
        $now = date('H:i:s');
        foreach ($today_schedules as $m) {
            if ($m['mass_time'] >= $now) {
                return $m;
            }
        }
        return null;
    }

    public function weekly_grid()
    {
        $rows = $this->db->where('day_of_week IS NOT NULL', null, false)
            ->where('is_active', 1)
            ->order_by('day_of_week', 'asc')->order_by('mass_time', 'asc')
            ->get($this->table)->result_array();

        $grid = array_fill(0, 7, []);
        foreach ($rows as $r) {
            $grid[(int) $r['day_of_week']][] = $r;
        }
        return $grid;
    }

    public function upcoming_special($limit = 10)
    {
        return $this->db->where('specific_date >=', date('Y-m-d'))
            ->where('is_active', 1)
            ->order_by('specific_date', 'asc')
            ->limit($limit)
            ->get($this->table)->result_array();
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

    public function datatable_query($request, $count_only = false)
    {
        $this->db->select('mass_schedules.*, users.first_name, users.last_name')
            ->from('mass_schedules')
            ->join('users', 'users.id = mass_schedules.presider_id', 'left');

        if (!empty($request['search']['value'])) {
            $this->db->like('mass_schedules.title', $request['search']['value']);
        }

        if ($count_only) {
            return $this->db->count_all_results();
        }

        $this->db->order_by('mass_schedules.id', 'desc');
        if (isset($request['start'], $request['length']) && (int) $request['length'] !== -1) {
            $this->db->limit((int) $request['length'], (int) $request['start']);
        }
        return $this->db->get()->result_array();
    }
}
