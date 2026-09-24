<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Event_model extends CI_Model
{
    protected $table = 'events';

    public function upcoming($limit = null)
    {
        $this->db->where('status', 'published')
            ->where('COALESCE(end_date, event_date) >= ' . $this->db->escape(date('Y-m-d')), null, false)
            ->order_by('event_date', 'asc');
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
        return $this->db->where('event_id', $event_id)
            ->where('status', 'registered')
            ->count_all_results('event_registrations');
    }

    public function seasonal_schema_ready()
    {
        foreach (['season_key', 'season_year', 'is_seasonal', 'highlight_on_home', 'highlight_start', 'highlight_end'] as $column) {
            if (!$this->db->field_exists($column, $this->table)) return false;
        }
        return true;
    }

    /**
     * Prebuilt yearly parish seasons. These are starter records only:
     * staff may change dates, photo, description, schedule details and public
     * highlight duration for the selected year.
     */
    public function seasonal_templates($year)
    {
        $year = max(2000, min(2100, (int) $year));
        $easter = $this->gregorian_easter($year);
        $ash_wednesday = $easter->modify('-46 days');

        return [
            'lent' => [
                'key' => 'lent',
                'label' => 'Lent, Holy Week & Easter',
                'short_label' => 'Lenten Season',
                'category' => 'Liturgical Season',
                'theme' => 'lent',
                'event_date' => $ash_wednesday->format('Y-m-d'),
                'end_date' => $easter->format('Y-m-d'),
                'highlight_start' => $ash_wednesday->modify('-7 days')->format('Y-m-d'),
                'highlight_end' => $easter->format('Y-m-d'),
                'location' => 'Sta. Monica Parish Church',
                'description' => "Journey with Sta. Monica Parish through Lent, Holy Week and Easter. Use this page for this year's Ash Wednesday Masses, Stations of the Cross, confession schedules, Holy Week liturgies, Easter celebrations and parish reminders. Update the exact schedules and details before publishing.",
                'icon' => 'ph-cross',
            ],
            'fiesta' => [
                'key' => 'fiesta',
                'label' => 'Sta. Monica Parish Fiesta',
                'short_label' => 'Parish Fiesta',
                'category' => 'Patronal Fiesta',
                'theme' => 'fiesta',
                'event_date' => sprintf('%04d-08-18', $year),
                'end_date' => sprintf('%04d-08-27', $year),
                'highlight_start' => sprintf('%04d-08-01', $year),
                'highlight_end' => sprintf('%04d-08-27', $year),
                'location' => 'Sta. Monica Parish Church',
                'description' => "Celebrate the patronal fiesta of Sta. Monica Parish. This starter event covers the traditional novena-to-feast period ending on August 27. Add this year's novena schedule, fiesta Mass, procession, parish activities, guest celebrants and community announcements before publishing.",
                'icon' => 'ph-church',
            ],
            'rosary_month' => [
                'key' => 'rosary_month',
                'label' => 'Month of the Holy Rosary',
                'short_label' => 'Rosary Month',
                'category' => 'Devotion',
                'theme' => 'rosary',
                'event_date' => sprintf('%04d-10-01', $year),
                'end_date' => sprintf('%04d-10-31', $year),
                'highlight_start' => sprintf('%04d-10-01', $year),
                'highlight_end' => sprintf('%04d-10-31', $year),
                'location' => 'Sta. Monica Parish Church',
                'description' => "October is observed in the parish as a special time for the Holy Rosary. Add this year's community rosary, family rosary, Marian activities, processions or prayer schedules, together with any ministry-specific details.",
                'icon' => 'ph-hands-praying',
            ],
            'undas' => [
                'key' => 'undas',
                'label' => 'All Saints & All Souls (UNDAS)',
                'short_label' => 'UNDAS',
                'category' => 'Holy Days',
                'theme' => 'undas',
                'event_date' => sprintf('%04d-10-31', $year),
                'end_date' => sprintf('%04d-11-02', $year),
                'highlight_start' => sprintf('%04d-10-15', $year),
                'highlight_end' => sprintf('%04d-11-02', $year),
                'location' => 'Sta. Monica Parish Church',
                'description' => "Prepare parishioners for All Saints' Day and All Souls' Day. Add this year's Mass schedules, cemetery or memorial prayers, blessing schedules, remembrance activities and instructions for Mass intentions for the faithful departed.",
                'icon' => 'ph-flame',
            ],
            'christmas' => [
                'key' => 'christmas',
                'label' => 'Simbang Gabi & Christmas',
                'short_label' => 'Christmas',
                'category' => 'Christmas Season',
                'theme' => 'christmas',
                'event_date' => sprintf('%04d-12-16', $year),
                'end_date' => sprintf('%04d-12-25', $year),
                'highlight_start' => sprintf('%04d-12-01', $year),
                'highlight_end' => sprintf('%04d-12-25', $year),
                'location' => 'Sta. Monica Parish Church',
                'description' => "Celebrate the Christmas season with Sta. Monica Parish. Add this year's Simbang Gabi schedule, Christmas Eve and Christmas Day Masses, parish activities, choir schedules and visitor information before publishing.",
                'icon' => 'ph-star',
            ],
            'new_year' => [
                'key' => 'new_year',
                'label' => 'New Year & Solemnity of Mary',
                'short_label' => 'New Year',
                'category' => 'Holy Day',
                'theme' => 'new_year',
                'event_date' => sprintf('%04d-12-31', $year),
                'end_date' => sprintf('%04d-01-01', $year + 1),
                'highlight_start' => sprintf('%04d-12-26', $year),
                'highlight_end' => sprintf('%04d-01-01', $year + 1),
                'location' => 'Sta. Monica Parish Church',
                'description' => "Welcome the New Year in prayer and thanksgiving. Add the parish's year-end thanksgiving Mass, New Year's Eve schedule, January 1 Masses for the Solemnity of Mary, and any pastoral reminders for the coming year.",
                'icon' => 'ph-sparkle',
            ],
        ];
    }

    public function seasonal_instances($year)
    {
        if (!$this->seasonal_schema_ready()) return [];

        $rows = $this->db->where('is_seasonal', 1)
            ->where('season_year', (int) $year)
            ->get($this->table)->result_array();

        $indexed = [];
        foreach ($rows as $row) {
            if (!empty($row['season_key'])) $indexed[$row['season_key']] = $row;
        }
        return $indexed;
    }

    public function seasonal_cards($year)
    {
        $templates = $this->seasonal_templates($year);
        $instances = $this->seasonal_instances($year);

        $cards = [];
        foreach ($templates as $key => $template) {
            $cards[] = array_merge($template, [
                'year' => (int) $year,
                'event' => $instances[$key] ?? null,
            ]);
        }
        return $cards;
    }

    public function prepare_seasonal($key, $year, $created_by)
    {
        if (!$this->seasonal_schema_ready()) return false;

        $templates = $this->seasonal_templates($year);
        if (empty($templates[$key])) return false;

        $existing = $this->db->get_where($this->table, [
            'season_key' => $key,
            'season_year' => (int) $year,
        ])->row_array();

        if ($existing) {
            return ['id' => (int) $existing['id'], 'created' => false];
        }

        $t = $templates[$key];
        $slug = url_title($key . '-' . $year, '-', true);
        if ($this->db->where('slug', $slug)->count_all_results($this->table) > 0) {
            $slug .= '-' . substr(bin2hex(random_bytes(3)), 0, 6);
        }

        $id = $this->create([
            'title' => $t['label'] . ' ' . $year,
            'slug' => $slug,
            'description' => $t['description'],
            'category' => $t['category'],
            'season_key' => $key,
            'season_year' => (int) $year,
            'is_seasonal' => 1,
            'highlight_on_home' => 1,
            'highlight_start' => $t['highlight_start'],
            'highlight_end' => $t['highlight_end'],
            'event_date' => $t['event_date'],
            'end_date' => $t['end_date'],
            'location' => $t['location'],
            'allow_registration' => 0,
            'status' => 'draft',
            'created_by' => (int) $created_by,
        ]);

        return $id ? ['id' => (int) $id, 'created' => true] : false;
    }

    public function active_seasonal_highlight()
    {
        if (!$this->seasonal_schema_ready()) return null;

        $today = date('Y-m-d');
        return $this->db->where('is_seasonal', 1)
            ->where('highlight_on_home', 1)
            ->where('status', 'published')
            ->where('(COALESCE(highlight_start, event_date) <= ' . $this->db->escape($today) . ')', null, false)
            ->where('(COALESCE(highlight_end, end_date, event_date) >= ' . $this->db->escape($today) . ')', null, false)
            ->order_by('event_date', 'desc')
            ->limit(1)
            ->get($this->table)->row_array();
    }

    public function event_duration_days(array $event)
    {
        if (empty($event['event_date'])) return 0;
        $start = new DateTimeImmutable($event['event_date']);
        $end = new DateTimeImmutable(!empty($event['end_date']) ? $event['end_date'] : $event['event_date']);
        return max(1, (int) $start->diff($end)->days + 1);
    }

    public function datatable_query($request, $count_only = false)
    {
        $this->db->from($this->table);
        if (!empty($request['search']['value'])) {
            $this->db->group_start()
                ->like('title', $request['search']['value'])
                ->or_like('category', $request['search']['value'])
                ->group_end();
        }
        if ($count_only) return $this->db->count_all_results();

        $this->db->order_by('event_date', 'desc');
        if (isset($request['start'], $request['length']) && (int) $request['length'] !== -1) {
            $this->db->limit((int) $request['length'], (int) $request['start']);
        }
        return $this->db->get()->result_array();
    }

    private function gregorian_easter($year)
    {
        // Meeus/Jones/Butcher Gregorian computus.
        $a = $year % 19;
        $b = intdiv($year, 100);
        $c = $year % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $month = intdiv($h + $l - 7 * $m + 114, 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;

        return new DateTimeImmutable(sprintf('%04d-%02d-%02d', $year, $month, $day));
    }
}
