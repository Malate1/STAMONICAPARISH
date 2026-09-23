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
        $this->render_app('admin/event_list', [], 'layouts/app_admin');
    }

    public function datatable()
    {
        $request = $this->input->post();
        $total = $this->Event_model->datatable_query($request, true);
        $rows  = $this->Event_model->datatable_query($request, false);

        $data = [];
        foreach ($rows as $r) {
            $reg = $this->Event_model->registration_count($r['id']);
            $data[] = [
                'title'      => $r['title'],
                'category'   => $r['category'] ?: '—',
                'event_date' => format_date($r['event_date']),
                'registrations' => $r['allow_registration'] ? ($reg . ($r['registration_limit'] ? '/' . $r['registration_limit'] : '')) : 'N/A',
                'status'     => '<span class="px-2.5 py-1 rounded-full text-xs font-medium ' . ($r['status'] === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600') . '">' . ucfirst($r['status']) . '</span>',
                'actions'    => '<div class="flex gap-3">
                        <button onclick="editEvent(' . $r['id'] . ')" class="text-emerald-700 hover:underline font-medium">Edit</button>
                        <button onclick="deleteEvent(' . $r['id'] . ')" class="text-red-600 hover:underline font-medium">Delete</button>
                    </div>',
            ];
        }

        $this->json(['draw' => (int) ($request['draw'] ?? 1), 'recordsTotal' => $total, 'recordsFiltered' => $total, 'data' => $data]);
    }

    public function get($id)
    {
        $this->json(['success' => true, 'data' => $this->Event_model->get($id)]);
    }

    public function store()
    {
        $this->form_validation->set_rules('title', 'Title', 'required');
        $this->form_validation->set_rules('event_date', 'Event Date', 'required');

        if ($this->form_validation->run() === FALSE) {
            return $this->json(['success' => false, 'message' => strip_tags(validation_errors())]);
        }

        $title = $this->input->post('title', true);
        $payload = [
            'title'               => $title,
            'description'         => $this->input->post('description'),
            'category'            => $this->input->post('category', true),
            'event_date'          => $this->input->post('event_date'),
            'end_date'            => $this->input->post('end_date') ?: null,
            'event_time'          => $this->input->post('event_time') ?: null,
            'location'            => $this->input->post('location', true),
            'allow_registration'  => $this->input->post('allow_registration') ? 1 : 0,
            'registration_limit'  => $this->input->post('registration_limit') ?: null,
            'status'              => $this->input->post('status') ?: 'published',
            'created_by'          => $this->current_user['id'],
        ];

        $id = $this->input->post('id');
        if ($id) {
            $this->Event_model->update($id, $payload);
            $msg = 'Event updated.';
        } else {
            $payload['slug'] = url_title($title . '-' . uniqid(), '-', true);
            $this->Event_model->create($payload);
            $msg = 'Event published.';
        }

        $this->json(['success' => true, 'message' => $msg]);
    }

    public function delete($id)
    {
        $this->Event_model->delete($id);
        $this->json(['success' => true, 'message' => 'Event deleted.']);
    }
}
