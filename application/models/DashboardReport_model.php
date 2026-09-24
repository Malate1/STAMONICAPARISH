<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class DashboardReport_model extends CI_Model
{
    private function month_buckets($months)
    {
        $months = max(1, min(24, (int) $months));
        $start = new DateTimeImmutable(date('Y-m-01'));
        $start = $start->modify('-' . ($months - 1) . ' months');
        $buckets = [];

        for ($i = 0; $i < $months; $i++) {
            $d = $start->modify('+' . $i . ' months');
            $buckets[$d->format('Y-m')] = [
                'key' => $d->format('Y-m'),
                'label' => $d->format('M Y'),
            ];
        }

        return $buckets;
    }

    private function day_buckets($days)
    {
        $days = max(1, min(90, (int) $days));
        $start = new DateTimeImmutable(date('Y-m-d'));
        $start = $start->modify('-' . ($days - 1) . ' days');
        $buckets = [];

        for ($i = 0; $i < $days; $i++) {
            $d = $start->modify('+' . $i . ' days');
            $buckets[$d->format('Y-m-d')] = [
                'key' => $d->format('Y-m-d'),
                'label' => $d->format('M j'),
            ];
        }

        return $buckets;
    }

    private function apply_booking_scope(array $scope)
    {
        if (!empty($scope['user_id'])) {
            $this->db->where('service_bookings.user_id', (int) $scope['user_id']);
        }
        if (!empty($scope['assigned_priest_id'])) {
            $this->db->where('service_bookings.assigned_priest_id', (int) $scope['assigned_priest_id']);
        }
    }

    public function monthly_bookings($months = 6, array $scope = [])
    {
        $buckets = $this->month_buckets($months);
        $start = array_key_first($buckets) . '-01';

        $this->db->select("DATE_FORMAT(service_bookings.created_at, '%Y-%m') AS period, COUNT(*) AS total", false)
            ->from('service_bookings')
            ->where('service_bookings.created_at >=', $start)
            ->group_by("DATE_FORMAT(service_bookings.created_at, '%Y-%m')", false)
            ->order_by('period', 'asc');
        $this->apply_booking_scope($scope);

        $rows = $this->db->get()->result_array();
        $map = [];
        foreach ($rows as $row) $map[$row['period']] = (int) $row['total'];

        $out = [];
        foreach ($buckets as $key => $bucket) {
            $out[] = ['label' => $bucket['label'], 'total' => $map[$key] ?? 0];
        }
        return $out;
    }

    public function monthly_verified_collections($months = 6)
    {
        $buckets = $this->month_buckets($months);
        $start = array_key_first($buckets) . '-01';

        $rows = $this->db
            ->select("DATE_FORMAT(COALESCE(verified_at, created_at), '%Y-%m') AS period, SUM(amount) AS total", false)
            ->from('payments')
            ->where('status', 'payment_verified')
            ->where('COALESCE(verified_at, created_at) >= ' . $this->db->escape($start), null, false)
            ->group_by("DATE_FORMAT(COALESCE(verified_at, created_at), '%Y-%m')", false)
            ->order_by('period', 'asc')
            ->get()->result_array();

        $map = [];
        foreach ($rows as $row) $map[$row['period']] = (float) $row['total'];

        $out = [];
        foreach ($buckets as $key => $bucket) {
            $out[] = ['label' => $bucket['label'], 'total' => $map[$key] ?? 0];
        }
        return $out;
    }

    public function service_mix($days = 180, array $scope = [], $limit = 7)
    {
        $from = date('Y-m-d 00:00:00', strtotime('-' . max(1, (int) $days) . ' days'));

        $this->db->select('service_types.name, COUNT(*) AS total')
            ->from('service_bookings')
            ->join('service_types', 'service_types.id = service_bookings.service_type_id')
            ->where('service_bookings.created_at >=', $from);
        $this->apply_booking_scope($scope);

        return $this->db
            ->group_by(['service_types.id', 'service_types.name'])
            ->order_by('total', 'desc')
            ->limit(max(1, (int) $limit))
            ->get()->result_array();
    }

    public function booking_status_mix(array $scope = [])
    {
        $this->db->select('service_bookings.status, COUNT(*) AS total')
            ->from('service_bookings');
        $this->apply_booking_scope($scope);

        return $this->db
            ->group_by('service_bookings.status')
            ->order_by('total', 'desc')
            ->get()->result_array();
    }

    public function daily_operational_intake($days = 14)
    {
        $buckets = $this->day_buckets($days);
        $start = array_key_first($buckets) . ' 00:00:00';

        $queries = [
            'bookings' => ['service_bookings', 'created_at'],
            'certificates' => ['certificate_requests', 'created_at'],
            'payments' => ['payments', 'created_at'],
        ];

        $maps = [];
        foreach ($queries as $key => $meta) {
            $rows = $this->db
                ->select("DATE({$meta[1]}) AS period, COUNT(*) AS total", false)
                ->from($meta[0])
                ->where($meta[1] . ' >=', $start)
                ->group_by("DATE({$meta[1]})", false)
                ->get()->result_array();

            $maps[$key] = [];
            foreach ($rows as $row) $maps[$key][$row['period']] = (int) $row['total'];
        }

        $out = [];
        foreach ($buckets as $key => $bucket) {
            $out[] = [
                'label' => $bucket['label'],
                'bookings' => $maps['bookings'][$key] ?? 0,
                'certificates' => $maps['certificates'][$key] ?? 0,
                'payments' => $maps['payments'][$key] ?? 0,
            ];
        }
        return $out;
    }

    public function monthly_user_growth($months = 6)
    {
        $buckets = $this->month_buckets($months);
        $start = array_key_first($buckets) . '-01';

        $rows = $this->db
            ->select("DATE_FORMAT(users.created_at, '%Y-%m') AS period, roles.role_key, COUNT(*) AS total", false)
            ->from('users')
            ->join('roles', 'roles.id = users.role_id')
            ->where('users.created_at >=', $start)
            ->group_by(["DATE_FORMAT(users.created_at, '%Y-%m')", 'roles.role_key'], false)
            ->get()->result_array();

        $map = [];
        foreach ($rows as $row) {
            $map[$row['period']][$row['role_key']] = (int) $row['total'];
        }

        $out = [];
        foreach ($buckets as $key => $bucket) {
            $out[] = [
                'label' => $bucket['label'],
                'parishioners' => $map[$key]['parishioner'] ?? 0,
                'staff' => ($map[$key]['secretary'] ?? 0) + ($map[$key]['admin'] ?? 0),
                'priests' => $map[$key]['priest'] ?? 0,
            ];
        }
        return $out;
    }

    public function office_report()
    {
        $month_start = date('Y-m-01 00:00:00');
        $month_end = date('Y-m-t 23:59:59');
        $intentions_ready = 0;

        if ($this->db->field_exists('mass_date', 'mass_intentions')) {
            $intentions_ready = (int) $this->db
                ->where('status', 'ready_for_reading')
                ->where('mass_date >=', date('Y-m-d'))
                ->count_all_results('mass_intentions');
        }

        return [
            'bookings_received' => (int) $this->db->where('created_at >=', $month_start)->where('created_at <=', $month_end)->count_all_results('service_bookings'),
            'bookings_completed' => (int) $this->db->where('status', 'completed')->where('updated_at >=', $month_start)->where('updated_at <=', $month_end)->count_all_results('service_bookings'),
            'certificates_received' => (int) $this->db->where('created_at >=', $month_start)->where('created_at <=', $month_end)->count_all_results('certificate_requests'),
            'certificates_released' => (int) $this->db->where('status', 'released')->where('released_at >=', $month_start)->where('released_at <=', $month_end)->count_all_results('certificate_requests'),
            'payments_verified' => (int) $this->db->where('status', 'payment_verified')->where('verified_at >=', $month_start)->where('verified_at <=', $month_end)->count_all_results('payments'),
            'mass_intentions_ready' => $intentions_ready,
        ];
    }

    public function priest_weekly_assignments($priest_id, $weeks = 8)
    {
        $weeks = max(1, min(16, (int) $weeks));
        $start = new DateTimeImmutable('monday this week');
        $end = $start->modify('+' . $weeks . ' weeks');
        $rows = $this->db
            ->select('confirmed_date')
            ->from('service_bookings')
            ->where('assigned_priest_id', (int) $priest_id)
            ->where('confirmed_date >=', $start->format('Y-m-d 00:00:00'))
            ->where('confirmed_date <', $end->format('Y-m-d 00:00:00'))
            ->where_not_in('status', ['cancelled', 'returned'])
            ->get()->result_array();

        $map = [];
        foreach ($rows as $row) {
            $key = date('o-W', strtotime($row['confirmed_date']));
            $map[$key] = ($map[$key] ?? 0) + 1;
        }

        $out = [];
        for ($i = 0; $i < $weeks; $i++) {
            $d = $start->modify('+' . $i . ' weeks');
            $out[] = [
                'label' => $d->format('M j'),
                'total' => $map[$d->format('o-W')] ?? 0,
            ];
        }
        return $out;
    }

    public function priest_report($priest_id)
    {
        $now = date('Y-m-d H:i:s');
        $week_end = date('Y-m-d H:i:s', strtotime('+7 days'));
        $month_end = date('Y-m-d H:i:s', strtotime('+30 days'));
        $month_start = date('Y-m-01 00:00:00');
        $reader_intentions = 0;

        if ($this->db->field_exists('mass_date', 'mass_intentions')) {
            $reader_intentions = (int) $this->db
                ->where('status', 'ready_for_reading')
                ->where('mass_date >=', date('Y-m-d'))
                ->count_all_results('mass_intentions');
        }

        return [
            'next_7_days' => (int) $this->db->where('assigned_priest_id', (int) $priest_id)->where('confirmed_date >=', $now)->where('confirmed_date <=', $week_end)->where_not_in('status', ['cancelled','returned'])->count_all_results('service_bookings'),
            'next_30_days' => (int) $this->db->where('assigned_priest_id', (int) $priest_id)->where('confirmed_date >=', $now)->where('confirmed_date <=', $month_end)->where_not_in('status', ['cancelled','returned'])->count_all_results('service_bookings'),
            'completed_this_month' => (int) $this->db->where('assigned_priest_id', (int) $priest_id)->where('status', 'completed')->where('updated_at >=', $month_start)->count_all_results('service_bookings'),
            'reader_intentions' => $reader_intentions,
        ];
    }

    public function parishioner_monthly_activity($user_id, $months = 6)
    {
        $buckets = $this->month_buckets($months);
        $start = array_key_first($buckets) . '-01';

        $booking_rows = $this->db
            ->select("DATE_FORMAT(created_at, '%Y-%m') AS period, COUNT(*) AS total", false)
            ->from('service_bookings')
            ->where('user_id', (int) $user_id)
            ->where('created_at >=', $start)
            ->group_by("DATE_FORMAT(created_at, '%Y-%m')", false)
            ->get()->result_array();

        $cert_rows = $this->db
            ->select("DATE_FORMAT(created_at, '%Y-%m') AS period, COUNT(*) AS total", false)
            ->from('certificate_requests')
            ->where('user_id', (int) $user_id)
            ->where('created_at >=', $start)
            ->group_by("DATE_FORMAT(created_at, '%Y-%m')", false)
            ->get()->result_array();

        $intent_rows = $this->db
            ->select("DATE_FORMAT(created_at, '%Y-%m') AS period, COUNT(*) AS total", false)
            ->from('mass_intentions')
            ->where('user_id', (int) $user_id)
            ->where('created_at >=', $start)
            ->group_by("DATE_FORMAT(created_at, '%Y-%m')", false)
            ->get()->result_array();

        $maps = ['bookings'=>[], 'certificates'=>[], 'intentions'=>[]];
        foreach ($booking_rows as $r) $maps['bookings'][$r['period']] = (int) $r['total'];
        foreach ($cert_rows as $r) $maps['certificates'][$r['period']] = (int) $r['total'];
        foreach ($intent_rows as $r) $maps['intentions'][$r['period']] = (int) $r['total'];

        $out = [];
        foreach ($buckets as $key => $bucket) {
            $out[] = [
                'label' => $bucket['label'],
                'bookings' => $maps['bookings'][$key] ?? 0,
                'certificates' => $maps['certificates'][$key] ?? 0,
                'intentions' => $maps['intentions'][$key] ?? 0,
            ];
        }
        return $out;
    }

    public function parishioner_report($user_id)
    {
        $now = date('Y-m-d H:i:s');
        $ready_intentions = 0;

        if ($this->db->field_exists('mass_date', 'mass_intentions')) {
            $ready_intentions = (int) $this->db
                ->where('user_id', (int) $user_id)
                ->where('status', 'ready_for_reading')
                ->where('mass_date >=', date('Y-m-d'))
                ->count_all_results('mass_intentions');
        }

        $payment_row = $this->db->select_sum('amount')
            ->where('user_id', (int) $user_id)
            ->where('status', 'payment_verified')
            ->get('payments')->row();

        return [
            'upcoming_services' => (int) $this->db->where('user_id', (int) $user_id)->where('confirmed_date >=', $now)->where_not_in('status', ['cancelled','returned','completed'])->count_all_results('service_bookings'),
            'open_certificates' => (int) $this->db->where('user_id', (int) $user_id)->where_not_in('status', ['released','cancelled'])->count_all_results('certificate_requests'),
            'unread_notifications' => (int) $this->db->where('user_id', (int) $user_id)->where('is_read', 0)->count_all_results('notifications'),
            'verified_payments' => (float) ($payment_row->amount ?? 0),
            'mass_intentions_ready' => $ready_intentions,
        ];
    }
}
