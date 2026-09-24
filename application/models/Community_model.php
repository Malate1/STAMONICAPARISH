<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Community_model extends CI_Model
{
    public function schema_ready()
    {
        return $this->db->table_exists('chapels')
            && $this->db->table_exists('chapel_mass_schedules')
            && $this->db->table_exists('gsk_clusters')
            && $this->db->table_exists('church_officials');
    }

    public function chapels($active_only = true)
    {
        if ($active_only) $this->db->where('is_active', 1);
        return $this->db->order_by('display_order', 'asc')
            ->order_by('name', 'asc')
            ->get('chapels')->result_array();
    }

    public function chapel($id)
    {
        return $this->db->get_where('chapels', ['id' => (int)$id])->row_array();
    }

    public function chapel_by_slug($slug)
    {
        return $this->db->get_where('chapels', ['slug' => $slug])->row_array();
    }

    public function save_chapel($id, array $data)
    {
        if ($id) return $this->db->where('id', (int)$id)->update('chapels', $data);
        $this->db->insert('chapels', $data);
        return $this->db->insert_id();
    }

    public function delete_chapel($id)
    {
        return $this->db->where('id', (int)$id)->delete('chapels');
    }

    public function chapel_mass_schedules($chapel_id, $active_only = true)
    {
        if ($active_only) $this->db->where('is_active', 1);
        return $this->db->where('chapel_id', (int)$chapel_id)
            ->order_by('day_of_week', 'asc')
            ->order_by('mass_time', 'asc')
            ->order_by('display_order', 'asc')
            ->get('chapel_mass_schedules')->result_array();
    }

    public function mass_schedule($id)
    {
        return $this->db->get_where('chapel_mass_schedules', ['id' => (int)$id])->row_array();
    }

    public function save_mass_schedule($id, array $data)
    {
        if ($id) return $this->db->where('id', (int)$id)->update('chapel_mass_schedules', $data);
        $this->db->insert('chapel_mass_schedules', $data);
        return $this->db->insert_id();
    }

    public function delete_mass_schedule($id)
    {
        return $this->db->where('id', (int)$id)->delete('chapel_mass_schedules');
    }

    public function clusters($chapel_id = null, $active_only = true)
    {
        if ($chapel_id !== null) $this->db->where('chapel_id', (int)$chapel_id);
        if ($active_only) $this->db->where('is_active', 1);
        return $this->db->order_by('chapel_id', 'asc')
            ->order_by('display_order', 'asc')
            ->order_by('name', 'asc')
            ->get('gsk_clusters')->result_array();
    }

    public function cluster($id)
    {
        return $this->db->get_where('gsk_clusters', ['id' => (int)$id])->row_array();
    }

    public function save_cluster($id, array $data)
    {
        if ($id) return $this->db->where('id', (int)$id)->update('gsk_clusters', $data);
        $this->db->insert('gsk_clusters', $data);
        return $this->db->insert_id();
    }

    public function delete_cluster($id)
    {
        return $this->db->where('id', (int)$id)->delete('gsk_clusters');
    }

    public function officials($scope_type = null, $chapel_id = null, $cluster_id = null, $active_only = true)
    {
        if ($scope_type !== null) $this->db->where('scope_type', $scope_type);
        if ($chapel_id !== null) $this->db->where('chapel_id', (int)$chapel_id);
        if ($cluster_id !== null) $this->db->where('cluster_id', (int)$cluster_id);
        if ($active_only) $this->db->where('is_active', 1);

        return $this->db->order_by('display_order', 'asc')
            ->order_by('position_title', 'asc')
            ->order_by('full_name', 'asc')
            ->get('church_officials')->result_array();
    }

    public function official($id)
    {
        return $this->db->get_where('church_officials', ['id' => (int)$id])->row_array();
    }

    public function save_official($id, array $data)
    {
        if ($id) return $this->db->where('id', (int)$id)->update('church_officials', $data);
        $this->db->insert('church_officials', $data);
        return $this->db->insert_id();
    }

    public function delete_official($id)
    {
        return $this->db->where('id', (int)$id)->delete('church_officials');
    }

    public function public_structure()
    {
        $chapels = $this->chapels(true);
        foreach ($chapels as &$chapel) {
            $chapel['mass_schedules'] = $this->chapel_mass_schedules($chapel['id'], true);
            $chapel['officials'] = $this->officials('chapel', $chapel['id'], null, true);
            $chapel['clusters'] = $this->clusters($chapel['id'], true);
            foreach ($chapel['clusters'] as &$cluster) {
                $cluster['officials'] = $this->officials('cluster', $chapel['id'], $cluster['id'], true);
            }
            unset($cluster);
        }
        unset($chapel);

        return [
            'parish_officials' => $this->officials('parish', null, null, true),
            'chapels' => $chapels,
        ];
    }

    public function day_name($day)
    {
        $days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        return $days[(int)$day] ?? 'Day';
    }
}
