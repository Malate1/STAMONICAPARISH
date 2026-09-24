<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('generate_code')) {
    /**
     * Generate a human-friendly unique code e.g. SMC-BAP-2026-00042
     */
    function generate_code($prefix, $number)
    {
        return sprintf('SMC-%s-%s-%05d', strtoupper($prefix), date('Y'), $number);
    }
}

if (!function_exists('peso')) {
    function peso($amount)
    {
        return '₱' . number_format((float) $amount, 2);
    }
}

if (!function_exists('format_date')) {
    function format_date($date, $format = 'M d, Y')
    {
        if (empty($date) || $date === '0000-00-00') return '—';
        return date($format, strtotime($date));
    }
}

if (!function_exists('format_datetime')) {
    function format_datetime($datetime, $format = 'M d, Y g:i A')
    {
        if (empty($datetime) || $datetime === '0000-00-00 00:00:00') return '—';
        return date($format, strtotime($datetime));
    }
}

if (!function_exists('status_badge_class')) {
    /**
     * Maps a workflow status string to a Tailwind badge color class set.
     */
    function status_badge_class($status)
    {
        $map = [
            'draft'                  => 'bg-gray-100 text-gray-700',
            'submitted'               => 'bg-blue-100 text-blue-700',
            'under_review'            => 'bg-amber-100 text-amber-700',
            'missing_requirements'    => 'bg-red-100 text-red-700',
            'requirements_complete'   => 'bg-teal-100 text-teal-700',
            'interview_processing'    => 'bg-amber-100 text-amber-700',
            'priest_review'           => 'bg-indigo-100 text-indigo-700',
            'awaiting_payment'        => 'bg-orange-100 text-orange-700',
            'payment_verification'    => 'bg-orange-100 text-orange-700',
            'approved'                => 'bg-emerald-100 text-emerald-700',
            'ready_for_reading'       => 'bg-blue-100 text-blue-700',
            'listed'                  => 'bg-blue-100 text-blue-700',
            'scheduled'               => 'bg-sky-100 text-sky-700',
            'completed'               => 'bg-green-100 text-green-700',
            'cancelled'               => 'bg-gray-200 text-gray-600',
            'returned'                => 'bg-red-100 text-red-700',
            'payment_verified'        => 'bg-emerald-100 text-emerald-700',
            'rejected'                => 'bg-red-100 text-red-700',
            'ready_for_release'       => 'bg-emerald-100 text-emerald-700',
            'released'                => 'bg-green-100 text-green-700',
        ];
        return $map[$status] ?? 'bg-gray-100 text-gray-700';
    }
}

if (!function_exists('status_label')) {
    function status_label($status)
    {
        return ucwords(str_replace('_', ' ', $status));
    }
}

if (!function_exists('role_label')) {
    function role_label($role_id)
    {
        $map = [
            ROLE_ADMIN       => 'Administrator',
            ROLE_SECRETARY   => 'Parish Secretary',
            ROLE_PRIEST      => 'Priest',
            ROLE_PARISHIONER => 'Parishioner',
        ];
        return $map[$role_id] ?? 'User';
    }
}

if (!function_exists('role_home_url')) {
    function role_home_url($role_id)
    {
        switch ((int) $role_id) {
            case ROLE_ADMIN:       return site_url('admin');
            case ROLE_SECRETARY:   return site_url('staff');
            case ROLE_PRIEST:      return site_url('priest');
            default:               return site_url('my/dashboard');
        }
    }
}

if (!function_exists('day_name')) {
    function day_name($dow)
    {
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        return $days[(int) $dow] ?? '';
    }
}

if (!function_exists('dt_action_classes')) {
    function dt_action_classes($tone = 'primary')
    {
        $map = [
            'primary' => 'border-parish-100 bg-parish-50 text-parish-700 hover:bg-parish-100 hover:border-parish-200',
            'danger'  => 'border-red-100 bg-red-50 text-red-600 hover:bg-red-100 hover:border-red-200',
            'warning' => 'border-amber-100 bg-amber-50 text-amber-700 hover:bg-amber-100 hover:border-amber-200',
            'neutral' => 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50 hover:border-gray-300',
            'blue'    => 'border-blue-100 bg-blue-50 text-blue-700 hover:bg-blue-100 hover:border-blue-200',
        ];

        return $map[$tone] ?? $map['primary'];
    }
}

if (!function_exists('dt_icon_button')) {
    function dt_icon_button($icon, $label, $onclick, $tone = 'primary')
    {
        $icon = htmlspecialchars($icon, ENT_QUOTES, 'UTF-8');
        $label = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        $onclick = htmlspecialchars($onclick, ENT_QUOTES, 'UTF-8');

        return '<button type="button" onclick="' . $onclick . '" title="' . $label . '" aria-label="' . $label . '" class="inline-flex w-9 h-9 items-center justify-center rounded-lg border transition ' . dt_action_classes($tone) . '"><i class="ph ' . $icon . ' text-base"></i><span class="sr-only">' . $label . '</span></button>';
    }
}

if (!function_exists('dt_icon_link')) {
    function dt_icon_link($icon, $label, $url, $tone = 'primary')
    {
        $icon = htmlspecialchars($icon, ENT_QUOTES, 'UTF-8');
        $label = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        $url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

        return '<a href="' . $url . '" title="' . $label . '" aria-label="' . $label . '" class="inline-flex w-9 h-9 items-center justify-center rounded-lg border transition ' . dt_action_classes($tone) . '"><i class="ph ' . $icon . ' text-base"></i><span class="sr-only">' . $label . '</span></a>';
    }
}

if (!function_exists('initials')) {
    function initials($name)
    {
        $parts = preg_split('/\s+/', trim($name));
        $out = '';
        foreach (array_slice($parts, 0, 2) as $p) {
            $out .= mb_strtoupper(mb_substr($p, 0, 1));
        }
        return $out ?: 'U';
    }
}
