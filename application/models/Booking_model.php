<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Booking_model extends CI_Model
{
    protected $table = 'service_bookings';

    public function generate_code($service_key)
    {
        $count = $this->db->count_all_results($this->table) + 1;
        return generate_code($service_key, $count);
    }

    public function create(array $data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        $id = $this->db->insert_id();
        $this->add_status_history($id, null, $data['status'] ?? 'submitted', 'Application submitted', $data['user_id'] ?? null);
        return $id;
    }

    public function update($id, array $data)
    {
        return $this->db->where('id', $id)->update($this->table, $data);
    }

    public function get($id)
    {
        return $this->db->select('service_bookings.*, service_types.name as service_name, service_types.service_key, service_types.icon,
                users.first_name, users.last_name, users.email, users.mobile_number,
                priest.first_name as priest_first_name, priest.last_name as priest_last_name')
            ->from($this->table)
            ->join('service_types', 'service_types.id = service_bookings.service_type_id')
            ->join('users', 'users.id = service_bookings.user_id')
            ->join('users as priest', 'priest.id = service_bookings.assigned_priest_id', 'left')
            ->where('service_bookings.id', $id)
            ->get()->row_array();
    }

    public function documents($booking_id)
    {
        return $this->db->select('booking_documents.*, service_requirements.label')
            ->from('booking_documents')
            ->join('service_requirements', 'service_requirements.id = booking_documents.requirement_id', 'left')
            ->where('booking_id', $booking_id)
            ->get()->result_array();
    }

    public function add_document(array $data)
    {
        $this->db->insert('booking_documents', $data);
        return $this->db->insert_id();
    }

    public function status_history($booking_id)
    {
        return $this->db->select('booking_status_history.*, users.first_name, users.last_name')
            ->from('booking_status_history')
            ->join('users', 'users.id = booking_status_history.changed_by', 'left')
            ->where('booking_id', $booking_id)
            ->order_by('changed_at', 'asc')
            ->get()->result_array();
    }

    public function add_status_history($booking_id, $from, $to, $remarks = null, $changed_by = null)
    {
        $this->db->insert('booking_status_history', [
            'booking_id'  => $booking_id,
            'from_status' => $from,
            'to_status'   => $to,
            'remarks'     => $remarks,
            'changed_by'  => $changed_by,
            'changed_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    public function change_status($id, $new_status, $changed_by = null, $remarks = null)
    {
        $booking = $this->get($id);
        if (!$booking) return false;
        $this->update($id, ['status' => $new_status]);
        $this->add_status_history($id, $booking['status'], $new_status, $remarks, $changed_by);
        return true;
    }

    public function for_user($user_id)
    {
        return $this->db->select('service_bookings.*, service_types.name as service_name, service_types.icon')
            ->from($this->table)
            ->join('service_types', 'service_types.id = service_bookings.service_type_id')
            ->where('service_bookings.user_id', $user_id)
            ->order_by('service_bookings.created_at', 'desc')
            ->get()->result_array();
    }

    public function for_priest($priest_id)
    {
        return $this->db->select('service_bookings.*, service_types.name as service_name, users.first_name, users.last_name')
            ->from($this->table)
            ->join('service_types', 'service_types.id = service_bookings.service_type_id')
            ->join('users', 'users.id = service_bookings.user_id')
            ->where('service_bookings.assigned_priest_id', $priest_id)
            ->where_in('service_bookings.status', ['approved', 'scheduled'])
            ->order_by('service_bookings.confirmed_date', 'asc')
            ->get()->result_array();
    }

    public function counts_by_status()
    {
        $rows = $this->db->select('status, COUNT(*) as total')
            ->group_by('status')->get($this->table)->result_array();
        $out = [];
        foreach ($rows as $r) $out[$r['status']] = (int) $r['total'];
        return $out;
    }

    public function today_summary()
    {
        return $this->db->select('service_types.name as service_name, COUNT(*) as total')
            ->from($this->table)
            ->join('service_types', 'service_types.id = service_bookings.service_type_id')
            ->where('DATE(service_bookings.confirmed_date)', date('Y-m-d'))
            ->group_by('service_types.name')
            ->get()->result_array();
    }

    public function priests_list()
    {
        return $this->db->where('role_id', ROLE_PRIEST)->where('status', 'active')->get('users')->result_array();
    }

    /**
     * Server-side DataTables query, scoped by role.
     * $scope: ['role_id' => .., 'user_id' => .., 'priest_id' => ..]
     */
    public function datatable_query($request, $scope = [], $count_only = false)
    {
        $this->db->select('service_bookings.*, service_types.name as service_name,
                users.first_name, users.last_name, users.email')
            ->from($this->table)
            ->join('service_types', 'service_types.id = service_bookings.service_type_id')
            ->join('users', 'users.id = service_bookings.user_id');

        if (!empty($scope['user_id'])) {
            $this->db->where('service_bookings.user_id', $scope['user_id']);
        }
        if (!empty($scope['priest_id'])) {
            $this->db->where('service_bookings.assigned_priest_id', $scope['priest_id']);
        }
        if (!empty($request['status'])) {
            $this->db->where('service_bookings.status', $request['status']);
        }
        if (!empty($request['service_type_id'])) {
            $this->db->where('service_bookings.service_type_id', $request['service_type_id']);
        }
        if (!empty($request['search']['value'])) {
            $kw = $request['search']['value'];
            $this->db->group_start()
                ->like('service_bookings.booking_code', $kw)
                ->or_like('users.first_name', $kw)
                ->or_like('users.last_name', $kw)
                ->or_like('service_types.name', $kw)
                ->group_end();
        }

        if ($count_only) {
            return $this->db->count_all_results();
        }

        $columns = ['service_bookings.id', 'service_bookings.booking_code', 'service_types.name', 'users.first_name', 'service_bookings.preferred_date', 'service_bookings.status', 'service_bookings.created_at'];
        if (isset($request['order'][0])) {
            $col = $columns[(int) $request['order'][0]['column']] ?? 'service_bookings.id';
            $this->db->order_by($col, $request['order'][0]['dir']);
        } else {
            $this->db->order_by('service_bookings.id', 'desc');
        }

        if (isset($request['start'], $request['length']) && (int) $request['length'] !== -1) {
            $this->db->limit((int) $request['length'], (int) $request['start']);
        }

        return $this->db->get()->result_array();
    }
}
