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

    /**
     * Build real upcoming Mass occurrences instead of exposing only recurring
     * schedule templates. This lets Mass Intentions attach to an actual date.
     */
    public function upcoming_occurrences($days = 60, $cutoff_minutes = 30, $limit = 120)
    {
        $days = max(1, min(365, (int) $days));
        $cutoff_minutes = max(0, (int) $cutoff_minutes);
        $limit = max(1, (int) $limit);

        $start = new DateTimeImmutable(date('Y-m-d'));
        $end = $start->modify('+' . $days . ' days');
        $cutoff_ts = time() + ($cutoff_minutes * 60);
        $occurrences = [];

        // Mass schedules are a small configuration table. Load active rows
        // once and expand the recurring templates in PHP instead of querying
        // the database twice for every calendar day.
        $active_rows = $this->db->where('is_active', 1)->get($this->table)->result_array();
        $regular_by_dow = array_fill(0, 7, []);
        $specific_by_date = [];

        foreach ($active_rows as $row) {
            if (!empty($row['specific_date'])) {
                $specific_by_date[$row['specific_date']][] = $row;
            } elseif ($row['day_of_week'] !== null) {
                $regular_by_dow[(int) $row['day_of_week']][] = $row;
            }
        }

        for ($date = $start; $date <= $end && count($occurrences) < $limit; $date = $date->modify('+1 day')) {
            $date_string = $date->format('Y-m-d');
            $dow = (int) $date->format('w');
            $specific = $specific_by_date[$date_string] ?? [];

            $has_override = false;
            foreach ($specific as $row) {
                if (!empty($row['is_override'])) {
                    $has_override = true;
                    break;
                }
            }

            $regular = $has_override ? [] : ($regular_by_dow[$dow] ?? []);
            $rows = array_merge($regular, $specific);
            usort($rows, function ($a, $b) {
                return strcmp($a['mass_time'], $b['mass_time']);
            });

            foreach ($rows as $row) {
                $mass_ts = strtotime($date_string . ' ' . $row['mass_time']);
                if ($mass_ts <= $cutoff_ts) continue;

                $row['mass_date'] = $date_string;
                $row['mass_datetime'] = date('Y-m-d H:i:s', $mass_ts);
                $row['date_label'] = date('D, M j, Y', $mass_ts);
                $row['time_label'] = date('g:i A', $mass_ts);
                $occurrences[] = $row;

                if (count($occurrences) >= $limit) break 2;
            }
        }

        return $occurrences;
    }

    /**
     * Validate that a selected schedule/date is a real upcoming Mass
     * occurrence and has not passed the Mass Intention cutoff.
     */
    public function occurrence_for_submission($schedule_id, $mass_date, $cutoff_minutes = 30)
    {
        $schedule = $this->get((int) $schedule_id);
        if (!$schedule || empty($schedule['is_active'])) return null;

        $date = DateTimeImmutable::createFromFormat('Y-m-d', (string) $mass_date);
        if (!$date || $date->format('Y-m-d') !== $mass_date) return null;

        if (!empty($schedule['specific_date'])) {
            if ($schedule['specific_date'] !== $mass_date) return null;
        } else {
            if ((int) $schedule['day_of_week'] !== (int) $date->format('w')) return null;

            $override = $this->db->where('specific_date', $mass_date)
                ->where('is_active', 1)
                ->where('is_override', 1)
                ->count_all_results($this->table);
            if ($override > 0) return null;
        }

        $mass_ts = strtotime($mass_date . ' ' . $schedule['mass_time']);
        if (!$mass_ts || $mass_ts <= time() + (max(0, (int) $cutoff_minutes) * 60)) {
            return null;
        }

        $schedule['mass_date'] = $mass_date;
        $schedule['mass_datetime'] = date('Y-m-d H:i:s', $mass_ts);
        return $schedule;
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
