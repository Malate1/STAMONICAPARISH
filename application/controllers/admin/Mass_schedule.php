<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mass_schedule extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_ADMIN]);
        $this->load->model(['MassSchedule_model', 'User_model']);
    }

    public function index()
    {
        $data['priests'] = $this->User_model->list_by_role(ROLE_PRIEST);
        $this->render_app('admin/mass_schedule_list', $data, 'layouts/app_admin');
    }

    public function datatable()
    {
        $request = $this->input->post();
        $total = $this->MassSchedule_model->datatable_query($request, true);
        $rows  = $this->MassSchedule_model->datatable_query($request, false);

        $data = [];
        foreach ($rows as $r) {
            $when = $r['specific_date']
                ? format_date($r['specific_date'])
                : day_name($r['day_of_week']) . 's';
            $priest = $r['first_name'] ? ($r['first_name'] . ' ' . $r['last_name']) : '—';
            $data[] = [
                'title'    => $r['title'] . '<div class="text-xs text-gray-400">' . ucfirst(str_replace('_', ' ', $r['schedule_type'])) . '</div>',
                'when'     => $when,
                'mass_time'=> date('g:i A', strtotime($r['mass_time'])),
                'location' => $r['location'],
                'priest'   => $priest,
                'status'   => $r['is_active']
                    ? '<span class="px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Active</span>'
                    : '<span class="px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Inactive</span>',
                'actions'  => '<div class="flex gap-3">
                        <button onclick="editSchedule(' . $r['id'] . ')" class="text-emerald-700 hover:underline font-medium">Edit</button>
                        <button onclick="deleteSchedule(' . $r['id'] . ')" class="text-red-600 hover:underline font-medium">Delete</button>
                    </div>',
            ];
        }

        $this->json(['draw' => (int) ($request['draw'] ?? 1), 'recordsTotal' => $total, 'recordsFiltered' => $total, 'data' => $data]);
    }

    public function get($id)
    {
        $this->json(['success' => true, 'data' => $this->MassSchedule_model->get($id)]);
    }

    public function store()
    {
        $this->form_validation->set_rules('title', 'Title', 'required');
        $this->form_validation->set_rules('mass_time', 'Mass Time', 'required');

        if ($this->form_validation->run() === FALSE) {
            return $this->json(['success' => false, 'message' => strip_tags(validation_errors())]);
        }

        $payload = [
            'title'         => $this->input->post('title', true),
            'day_of_week'   => $this->input->post('recurrence_type') === 'weekly' ? $this->input->post('day_of_week') : null,
            'specific_date' => $this->input->post('recurrence_type') === 'specific' ? $this->input->post('specific_date') : null,
            'mass_time'     => $this->input->post('mass_time'),
            'location'      => $this->input->post('location', true) ?: 'Main Church',
            'presider_id'   => $this->input->post('presider_id') ?: null,
            'schedule_type' => $this->input->post('schedule_type') ?: 'regular',
            'is_override'   => $this->input->post('is_override') ? 1 : 0,
            'is_active'     => 1,
            'notes'         => $this->input->post('notes', true),
            'created_by'    => $this->current_user['id'],
        ];

        $id = $this->input->post('id');
        if ($id) {
            $this->MassSchedule_model->update($id, $payload);
            $this->log_activity('Updated mass schedule', 'mass_schedule', $payload['title']);
            $msg = 'Mass schedule updated.';
        } else {
            $this->MassSchedule_model->create($payload);
            $this->log_activity('Created mass schedule', 'mass_schedule', $payload['title']);
            $msg = 'Mass schedule added.';
        }

        $this->json(['success' => true, 'message' => $msg]);
    }

    public function toggle($id)
    {
        $row = $this->MassSchedule_model->get($id);
        if (!$row) return $this->json(['success' => false, 'message' => 'Not found.']);
        $this->MassSchedule_model->update($id, ['is_active' => $row['is_active'] ? 0 : 1]);
        $this->json(['success' => true]);
    }

    public function delete($id)
    {
        $this->MassSchedule_model->delete($id);
        $this->log_activity('Deleted mass schedule', 'mass_schedule', "ID {$id}");
        $this->json(['success' => true, 'message' => 'Mass schedule removed.']);
    }
}
