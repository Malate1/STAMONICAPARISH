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

    public function activity_schema_ready()
    {
        return $this->db->table_exists('event_activities');
    }

    public function activities($event_id)
    {
        if (!$this->activity_schema_ready()) return [];

        return $this->db->where('event_id', (int) $event_id)
            ->order_by('activity_date', 'asc')
            ->order_by('CASE WHEN start_time IS NULL THEN 1 ELSE 0 END', '', false)
            ->order_by('start_time', 'asc')
            ->order_by('sort_order', 'asc')
            ->order_by('id', 'asc')
            ->get('event_activities')->result_array();
    }

    public function get_activity($id)
    {
        if (!$this->activity_schema_ready()) return null;
        return $this->db->get_where('event_activities', ['id' => (int) $id])->row_array();
    }

    public function save_activity($id, array $data)
    {
        if (!$this->activity_schema_ready()) return false;

        if ($id) {
            return $this->db->where('id', (int) $id)->update('event_activities', $data);
        }

        $this->db->insert('event_activities', $data);
        return $this->db->insert_id();
    }

    public function delete_activity($id)
    {
        if (!$this->activity_schema_ready()) return false;
        return $this->db->where('id', (int) $id)->delete('event_activities');
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

    /**
     * Editable starter program items for each seasonal event. These are copied
     * into event_activities only when staff prepares that year's event. The
     * public site reads the database rows, so staff can freely add, edit,
     * reorder or remove activities without changing code.
     */
    public function seasonal_activity_templates($key, $year)
    {
        $year = max(2000, min(2100, (int) $year));
        $easter = $this->gregorian_easter($year);
        $ash = $easter->modify('-46 days');
        $palm = $easter->modify('-7 days');
        $holy_thursday = $easter->modify('-3 days');
        $good_friday = $easter->modify('-2 days');
        $holy_saturday = $easter->modify('-1 day');

        $templates = [
            'lent' => [
                ['type'=>'mass','title'=>'Ash Wednesday Masses','date'=>$ash->format('Y-m-d'),'description'=>'Add the parish Mass times and distribution of ashes for this year.','featured'=>1],
                ['type'=>'devotion','title'=>'Stations of the Cross','date'=>$ash->modify('+2 days')->format('Y-m-d'),'end'=>$good_friday->format('Y-m-d'),'description'=>'Set the parish Friday schedule, chapel assignments or procession details for the Stations of the Cross.'],
                ['type'=>'confession','title'=>'Lenten Confession / Kumpisal','date'=>$palm->modify('-7 days')->format('Y-m-d'),'description'=>'Add the actual parish penitential service or confession schedule.'],
                ['type'=>'mass','title'=>'Palm Sunday Masses','date'=>$palm->format('Y-m-d'),'description'=>'Add blessing of palms, procession and Mass schedules.','featured'=>1],
                ['type'=>'liturgy','title'=>'Mass of the Lord’s Supper','date'=>$holy_thursday->format('Y-m-d'),'description'=>'Add the Holy Thursday Mass, washing of feet and Altar of Repose schedule.','featured'=>1],
                ['type'=>'liturgy','title'=>'Good Friday Liturgy','date'=>$good_friday->format('Y-m-d'),'description'=>'Add the Passion service, veneration of the Cross, procession and related parish activities.','featured'=>1],
                ['type'=>'mass','title'=>'Easter Vigil','date'=>$holy_saturday->format('Y-m-d'),'description'=>'Add the parish Easter Vigil time and preparation details.','featured'=>1],
                ['type'=>'mass','title'=>'Easter Sunday Masses','date'=>$easter->format('Y-m-d'),'description'=>'Add all Easter Sunday Mass schedules.','featured'=>1],
            ],
            'fiesta' => [
                ['type'=>'novena','title'=>'Novena Masses to Sta. Monica','date'=>sprintf('%04d-08-18',$year),'end'=>sprintf('%04d-08-26',$year),'description'=>'Nine-day novena preparation. Add the daily Mass time, sponsoring chapel/ministry and celebrant details.','featured'=>1],
                ['type'=>'mass','title'=>'Parish Fiesta Mass','date'=>sprintf('%04d-08-27',$year),'description'=>'Add the principal fiesta Mass time, main celebrant, concelebrants and liturgical notes.','featured'=>1],
                ['type'=>'procession','title'=>'Procession in Honor of Sta. Monica','date'=>sprintf('%04d-08-27',$year),'description'=>'Add the procession route, assembly time and participating chapels or ministries.'],
                ['type'=>'fellowship','title'=>'Community Dinner by Chapel Groups','date'=>sprintf('%04d-08-27',$year),'description'=>'Parish fellowship meal where families gather with their respective chapel/community members. Add the venue, meal time and chapel assignments.','featured'=>1],
                ['type'=>'program','title'=>'Parish Fiesta Program & Live Band','date'=>sprintf('%04d-08-27',$year),'description'=>'Add the community program, presentations, live band or cultural entertainment, venue and start time.','featured'=>1],
            ],
            'rosary_month' => [
                ['type'=>'devotion','title'=>'Opening Rosary for October','date'=>sprintf('%04d-10-01',$year),'description'=>'Add the opening Marian prayer or community rosary schedule.','featured'=>1],
                ['type'=>'devotion','title'=>'Daily / Chapel Rosary Schedule','date'=>sprintf('%04d-10-01',$year),'end'=>sprintf('%04d-10-31',$year),'description'=>'Add chapel rotations, family rosary assignments or parish rosary schedules for the month.'],
                ['type'=>'procession','title'=>'Living Rosary / Marian Procession','date'=>sprintf('%04d-10-31',$year),'description'=>'Add the parish closing Rosary Month activity, procession or Living Rosary details.','featured'=>1],
            ],
            'undas' => [
                ['type'=>'mass','title'=>'All Saints’ Day Mass','date'=>sprintf('%04d-11-01',$year),'description'=>'Add the parish All Saints’ Day Mass schedule.','featured'=>1],
                ['type'=>'prayer','title'=>'Cemetery Prayer & Blessing','date'=>sprintf('%04d-11-01',$year),'description'=>'Add cemetery prayer, grave blessing, assembly point and priest schedule if offered.'],
                ['type'=>'mass','title'=>'All Souls’ Day Masses','date'=>sprintf('%04d-11-02',$year),'description'=>'Add Mass schedules offered for the faithful departed and instructions for intentions.','featured'=>1],
                ['type'=>'prayer','title'=>'Memorial Prayer for the Faithful Departed','date'=>sprintf('%04d-11-02',$year),'description'=>'Add any parish memorial service, candle-lighting or remembrance activity.'],
            ],
            'christmas' => [
                ['type'=>'mass','title'=>'Simbang Gabi — 9-Day Novena Masses','date'=>sprintf('%04d-12-16',$year),'end'=>sprintf('%04d-12-24',$year),'description'=>'Nine-day Simbang Gabi/Misa de Gallo. Add the daily Mass time, sponsoring chapel/ministry, choir assignments and celebrants.','featured'=>1],
                ['type'=>'fellowship','title'=>'Post-Simbang Gabi Fellowship','date'=>sprintf('%04d-12-16',$year),'end'=>sprintf('%04d-12-24',$year),'description'=>'Optional parish or chapel fellowship after selected Simbang Gabi Masses. Edit or remove if not part of this year’s program.'],
                ['type'=>'program','title'=>'Parish Christmas Program','date'=>sprintf('%04d-12-23',$year),'description'=>'Add Christmas presentations, choir program, children’s activities or community fellowship if scheduled.'],
                ['type'=>'mass','title'=>'Christmas Eve Mass','date'=>sprintf('%04d-12-24',$year),'description'=>'Add the Christmas Eve / Midnight Mass schedule and any pre-Mass program.','featured'=>1],
                ['type'=>'mass','title'=>'Christmas Day Masses','date'=>sprintf('%04d-12-25',$year),'description'=>'Add all Christmas Day Mass schedules.','featured'=>1],
            ],
            'new_year' => [
                ['type'=>'mass','title'=>'Year-End Thanksgiving Mass','date'=>sprintf('%04d-12-31',$year),'description'=>'Add the parish year-end thanksgiving Mass schedule.','featured'=>1],
                ['type'=>'prayer','title'=>'New Year Prayer / Vigil','date'=>sprintf('%04d-12-31',$year),'description'=>'Add any prayer vigil, adoration or community thanksgiving activity before the New Year.'],
                ['type'=>'mass','title'=>'Solemnity of Mary / New Year’s Day Masses','date'=>sprintf('%04d-01-01',$year + 1),'description'=>'Add January 1 Mass schedules for the Solemnity of Mary, Mother of God.','featured'=>1],
            ],
        ];

        return $templates[$key] ?? [];
    }

    public function seed_seasonal_activities($event_id, $key, $year)
    {
        if (!$this->activity_schema_ready()) return 0;
        if ($this->db->where('event_id', (int) $event_id)->count_all_results('event_activities') > 0) return 0;

        $rows = $this->seasonal_activity_templates($key, $year);
        $inserted = 0;
        foreach ($rows as $index => $row) {
            $this->db->insert('event_activities', [
                'event_id' => (int) $event_id,
                'activity_type' => $row['type'] ?? 'activity',
                'title' => $row['title'],
                'description' => $row['description'] ?? null,
                'activity_date' => $row['date'],
                'activity_end_date' => $row['end'] ?? null,
                'start_time' => $row['start_time'] ?? null,
                'end_time' => $row['end_time'] ?? null,
                'location' => $row['location'] ?? 'Sta. Monica Parish Church',
                'is_featured' => !empty($row['featured']) ? 1 : 0,
                'sort_order' => ($index + 1) * 10,
            ]);
            if ($this->db->affected_rows() > 0) $inserted++;
        }
        return $inserted;
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
            $seeded = $this->seed_seasonal_activities((int) $existing['id'], $key, $year);
            return ['id' => (int) $existing['id'], 'created' => false, 'seeded_activities' => $seeded];
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

        if (!$id) return false;

        $seeded = $this->seed_seasonal_activities((int) $id, $key, $year);
        return ['id' => (int) $id, 'created' => true, 'seeded_activities' => $seeded];
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
