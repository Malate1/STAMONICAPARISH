<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Event extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_ADMIN, ROLE_SECRETARY]);
        $this->load->model('Event_model');
    }

    public function index()
    {
        $data['seasonal_schema_ready'] = $this->Event_model->seasonal_schema_ready();
        $data['default_season_year'] = (int) date('Y');
        $this->render_app('admin/event_list', $data, 'layouts/app_admin');
    }

    public function datatable()
    {
        $request = $this->input->post();
        $total = $this->Event_model->datatable_query($request, true);
        $rows  = $this->Event_model->datatable_query($request, false);

        $data = [];
        foreach ($rows as $r) {
            $reg = $this->Event_model->registration_count($r['id']);
            $duration = $this->Event_model->event_duration_days($r);
            $date_label = format_date($r['event_date']);
            if (!empty($r['end_date']) && $r['end_date'] !== $r['event_date']) {
                $date_label .= ' – ' . format_date($r['end_date']);
            }
            if ($duration > 1) {
                $date_label .= '<div class="text-[11px] text-gray-400 mt-1">' . $duration . ' days</div>';
            }

            $category = html_escape($r['category'] ?: '—');
            if (!empty($r['is_seasonal'])) {
                $category = '<div class="flex flex-col gap-1">'
                    . '<span class="text-gray-700">' . $category . '</span>'
                    . '<span class="inline-flex self-start px-2 py-0.5 rounded-full bg-gold-50 text-gold-700 text-[10px] font-semibold">Seasonal ' . (int) $r['season_year'] . '</span>'
                    . '</div>';
            }

            $data[] = [
                'title'      => '<div class="font-medium text-gray-800">' . html_escape($r['title']) . '</div>'
                    . (!empty($r['highlight_on_home']) ? '<div class="text-[10px] text-parish-600 mt-1"><i class="ph ph-star mr-1"></i>Homepage highlight</div>' : ''),
                'category'   => $category,
                'event_date' => $date_label,
                'registrations' => $r['allow_registration'] ? ($reg . ($r['registration_limit'] ? '/' . $r['registration_limit'] : '')) : 'N/A',
                'status'     => '<span class="px-2.5 py-1 rounded-full text-xs font-medium ' . ($r['status'] === 'published' ? 'bg-emerald-100 text-emerald-700' : ($r['status'] === 'draft' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600')) . '">' . ucfirst(str_replace('_', ' ', $r['status'])) . '</span>',
                'actions'    => '<div class="flex items-center justify-center gap-1.5 whitespace-nowrap">'
                    . dt_icon_button('ph-pencil-simple', 'Edit event', 'editEvent(' . (int) $r['id'] . ')')
                    . dt_icon_link('ph-arrow-square-out', 'View public event', site_url('events/' . $r['slug']), 'neutral')
                    . dt_icon_button('ph-trash', 'Delete event', 'deleteEvent(' . (int) $r['id'] . ')', 'danger')
                    . '</div>',
            ];
        }

        $this->json([
            'draw' => (int) ($request['draw'] ?? 1),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data
        ]);
    }

    public function get($id)
    {
        $item = $this->Event_model->get($id);
        if (!$item) return $this->json(['success' => false, 'message' => 'Event not found.'], 404);

        $item['duration_days'] = $this->Event_model->event_duration_days($item);
        $item['cover_url'] = !empty($item['cover_image']) ? base_url($item['cover_image']) : null;
        $this->json(['success' => true, 'data' => $item]);
    }

    public function seasonal_templates()
    {
        if (!$this->Event_model->seasonal_schema_ready()) {
            return $this->json([
                'success' => false,
                'message' => 'Seasonal Events database support is not installed yet. Run database/migrations/20260924_seasonal_events.sql in phpMyAdmin.'
            ]);
        }

        $year = max(2000, min(2100, (int) ($this->input->get('year') ?: date('Y'))));
        $this->json([
            'success' => true,
            'year' => $year,
            'templates' => $this->Event_model->seasonal_cards($year),
        ]);
    }

    public function prepare_seasonal()
    {
        if (!$this->Event_model->seasonal_schema_ready()) {
            return $this->json([
                'success' => false,
                'message' => 'Run database/migrations/20260924_seasonal_events.sql in phpMyAdmin before preparing seasonal events.'
            ]);
        }

        $key = $this->input->post('season_key', true);
        $year = max(2000, min(2100, (int) $this->input->post('season_year')));
        $result = $this->Event_model->prepare_seasonal($key, $year, $this->current_user['id']);

        if (!$result) {
            return $this->json(['success' => false, 'message' => 'That seasonal template could not be prepared.']);
        }

        $this->json([
            'success' => true,
            'id' => $result['id'],
            'created' => $result['created'],
            'message' => $result['created']
                ? 'Seasonal event prepared as a draft. Add this year’s photo and exact details, then publish it.'
                : 'This seasonal event already exists. Opening it for editing.'
        ]);
    }

    public function store()
    {
        $this->form_validation->set_rules('title', 'Title', 'required');
        $this->form_validation->set_rules('event_date', 'Event Date', 'required');

        if ($this->form_validation->run() === FALSE) {
            return $this->json(['success' => false, 'message' => strip_tags(validation_errors())]);
        }

        $event_date = $this->input->post('event_date', true);
        $end_date = $this->input->post('end_date', true) ?: null;
        $highlight_start = $this->input->post('highlight_start', true) ?: null;
        $highlight_end = $this->input->post('highlight_end', true) ?: null;

        if ($end_date && strtotime($end_date) < strtotime($event_date)) {
            return $this->json(['success' => false, 'message' => 'End Date cannot be earlier than the Event Start Date.']);
        }
        if ($highlight_start && $highlight_end && strtotime($highlight_end) < strtotime($highlight_start)) {
            return $this->json(['success' => false, 'message' => 'Homepage highlight end date cannot be earlier than its start date.']);
        }

        $id = (int) $this->input->post('id');
        $existing = $id ? $this->Event_model->get($id) : null;
        if ($id && !$existing) {
            return $this->json(['success' => false, 'message' => 'Event not found.'], 404);
        }

        $title = trim((string) $this->input->post('title', true));
        $payload = [
            'title'               => $title,
            'description'         => $this->input->post('description'),
            'category'            => $this->input->post('category', true),
            'event_date'          => $event_date,
            'end_date'            => $end_date,
            'event_time'          => $this->input->post('event_time', true) ?: null,
            'location'            => $this->input->post('location', true),
            'allow_registration'  => $this->input->post('allow_registration') ? 1 : 0,
            'registration_limit'  => $this->input->post('registration_limit') ?: null,
            'status'              => $this->input->post('status', true) ?: 'published',
        ];

        if ($this->Event_model->seasonal_schema_ready()) {
            $payload['highlight_on_home'] = $this->input->post('highlight_on_home') ? 1 : 0;
            $payload['highlight_start'] = $highlight_start;
            $payload['highlight_end'] = $highlight_end;

            if (!$id) {
                $payload['is_seasonal'] = 0;
            }
        }

        $upload = $this->handle_cover_upload();
        if (!$upload['success']) {
            return $this->json(['success' => false, 'message' => $upload['message']]);
        }
        if (!empty($upload['path'])) {
            $payload['cover_image'] = $upload['path'];
        }

        if ($id) {
            if (!$this->Event_model->update($id, $payload)) {
                return $this->json(['success' => false, 'message' => 'The event could not be updated.']);
            }
            if (!empty($upload['path']) && !empty($existing['cover_image']) && $existing['cover_image'] !== $upload['path']) {
                $this->delete_local_cover($existing['cover_image']);
            }
            $msg = 'Event updated.';
        } else {
            $payload['slug'] = url_title($title . '-' . uniqid(), '-', true);
            $payload['created_by'] = $this->current_user['id'];
            $id = $this->Event_model->create($payload);
            if (!$id) {
                return $this->json(['success' => false, 'message' => 'The event could not be created.']);
            }
            $msg = 'Event published.';
        }

        $saved = $this->Event_model->get($id);
        $this->json([
            'success' => true,
            'message' => $msg,
            'data' => [
                'id' => (int) $id,
                'duration_days' => $this->Event_model->event_duration_days($saved),
                'cover_url' => !empty($saved['cover_image']) ? base_url($saved['cover_image']) : null,
            ]
        ]);
    }

    public function delete($id)
    {
        $event = $this->Event_model->get($id);
        if (!$event) return $this->json(['success' => false, 'message' => 'Event not found.'], 404);

        if ($this->Event_model->delete($id)) {
            if (!empty($event['cover_image'])) $this->delete_local_cover($event['cover_image']);
            return $this->json(['success' => true, 'message' => 'Event deleted.']);
        }

        $this->json(['success' => false, 'message' => 'Event could not be deleted.']);
    }

    private function handle_cover_upload()
    {
        if (empty($_FILES['cover_image']['name'])) {
            return ['success' => true, 'path' => null];
        }

        if ($_FILES['cover_image']['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'The event photo upload did not complete successfully.'];
        }

        if ((int) $_FILES['cover_image']['size'] > 5 * 1024 * 1024) {
            return ['success' => false, 'message' => 'Event photo must be 5 MB or smaller.'];
        }

        $ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return ['success' => false, 'message' => 'Use a JPG, PNG, or WebP image for the event photo.'];
        }

        $image_info = @getimagesize($_FILES['cover_image']['tmp_name']);
        if (!$image_info) {
            return ['success' => false, 'message' => 'The uploaded file is not a valid image.'];
        }

        $target_dir = FCPATH . 'uploads/events/';
        if (!is_dir($target_dir) && !@mkdir($target_dir, 0755, true)) {
            return ['success' => false, 'message' => 'The server could not create the event image directory.'];
        }

        $filename = 'event_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
        $destination = $target_dir . $filename;

        if (!move_uploaded_file($_FILES['cover_image']['tmp_name'], $destination)) {
            return ['success' => false, 'message' => 'The event photo could not be saved on the server.'];
        }

        return ['success' => true, 'path' => 'uploads/events/' . $filename];
    }

    private function delete_local_cover($path)
    {
        if (strpos($path, 'uploads/events/') !== 0) return;
        $full = FCPATH . $path;
        if (is_file($full)) @unlink($full);
    }
}
