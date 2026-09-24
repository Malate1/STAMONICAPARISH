<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MassIntention_model extends CI_Model
{
    protected $table = 'mass_intentions';

    public function for_user($user_id)
    {
        return $this->db->select('mass_intentions.*, mass_schedules.title AS mass_title, mass_schedules.mass_time, mass_schedules.language, mass_schedules.location')
            ->from($this->table)
            ->join('mass_schedules', 'mass_schedules.id = mass_intentions.mass_schedule_id', 'left')
            ->where('mass_intentions.user_id', (int) $user_id)
            ->order_by('mass_intentions.mass_date', 'desc')
            ->order_by('mass_schedules.mass_time', 'desc')
            ->order_by('mass_intentions.created_at', 'desc')
            ->get()->result_array();
    }

    public function staff_list()
    {
        return $this->db->select('mass_intentions.*, mass_schedules.title AS mass_title, mass_schedules.mass_time, mass_schedules.language, mass_schedules.location, users.first_name, users.last_name')
            ->from($this->table)
            ->join('mass_schedules', 'mass_schedules.id = mass_intentions.mass_schedule_id', 'left')
            ->join('users', 'users.id = mass_intentions.user_id', 'left')
            ->order_by('CASE WHEN mass_intentions.mass_date IS NULL THEN 1 ELSE 0 END', '', false)
            ->order_by('mass_intentions.mass_date', 'asc')
            ->order_by('mass_schedules.mass_time', 'asc')
            ->order_by('mass_intentions.created_at', 'asc')
            ->get()->result_array();
    }

    public function get($id)
    {
        return $this->db->select('mass_intentions.*, mass_schedules.title AS mass_title, mass_schedules.mass_time, mass_schedules.language, mass_schedules.location')
            ->from($this->table)
            ->join('mass_schedules', 'mass_schedules.id = mass_intentions.mass_schedule_id', 'left')
            ->where('mass_intentions.id', (int) $id)
            ->get()->row_array();
    }

    public function reader_sheet($mass_date, $schedule_id)
    {
        return $this->db->select('mass_intentions.*, mass_schedules.title AS mass_title, mass_schedules.mass_time, mass_schedules.language, mass_schedules.location')
            ->from($this->table)
            ->join('mass_schedules', 'mass_schedules.id = mass_intentions.mass_schedule_id', 'left')
            ->where('mass_intentions.mass_date', $mass_date)
            ->where('mass_intentions.mass_schedule_id', (int) $schedule_id)
            ->where_in('mass_intentions.status', ['ready_for_reading', 'listed'])
            ->order_by("FIELD(mass_intentions.intention_type,'thanksgiving','birthday','anniversary','healing','safe_travel','special','souls_departed','other')", '', false)
            ->order_by('mass_intentions.offered_for', 'asc')
            ->get()->result_array();
    }

    public function ready_occurrences()
    {
        return $this->db->select('mass_intentions.mass_date, mass_intentions.mass_schedule_id, mass_schedules.title AS mass_title, mass_schedules.mass_time, mass_schedules.language, mass_schedules.location, COUNT(*) AS intention_count')
            ->from($this->table)
            ->join('mass_schedules', 'mass_schedules.id = mass_intentions.mass_schedule_id', 'left')
            ->where_in('mass_intentions.status', ['ready_for_reading', 'listed'])
            ->where('mass_intentions.mass_date IS NOT NULL', null, false)
            ->group_by(['mass_intentions.mass_date', 'mass_intentions.mass_schedule_id', 'mass_schedules.title', 'mass_schedules.mass_time', 'mass_schedules.language', 'mass_schedules.location'])
            ->order_by('CASE WHEN mass_intentions.mass_date >= CURDATE() THEN 0 ELSE 1 END', '', false)
            ->order_by('mass_intentions.mass_date', 'asc')
            ->order_by('mass_schedules.mass_time', 'asc')
            ->get()->result_array();
    }

    public function update_status($id, $status, $user_id)
    {
        $data = [
            'status' => $status,
            'processed_by' => (int) $user_id,
        ];

        if ($status === 'approved') {
            $data['approved_at'] = date('Y-m-d H:i:s');
            $data['ready_at'] = null;
        } elseif ($status === 'ready_for_reading') {
            $data['ready_at'] = date('Y-m-d H:i:s');
        } elseif ($status === 'completed') {
            $data['completed_at'] = date('Y-m-d H:i:s');
        }

        return $this->db->where('id', (int) $id)->update($this->table, $data);
    }

    public function complete_occurrence($mass_date, $schedule_id, $user_id)
    {
        return $this->db->where('mass_date', $mass_date)
            ->where('mass_schedule_id', (int) $schedule_id)
            ->where_in('status', ['ready_for_reading', 'listed'])
            ->update($this->table, [
                'status' => 'completed',
                'processed_by' => (int) $user_id,
                'completed_at' => date('Y-m-d H:i:s'),
            ]);
    }
}
