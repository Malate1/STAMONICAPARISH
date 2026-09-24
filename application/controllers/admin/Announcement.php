<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Announcement extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_ADMIN, ROLE_SECRETARY]);
        $this->load->model('Announcement_model');
    }

    public function index()
    {
        $layout = $this->current_user['role_id'] == ROLE_ADMIN ? 'layouts/app_admin' : 'layouts/app_admin';
        $this->render_app('admin/announcement_list', [], $layout);
    }

    public function datatable()
    {
        $request = $this->input->post();
        $total = $this->Announcement_model->datatable_query($request, true);
        $rows  = $this->Announcement_model->datatable_query($request, false);

        $data = [];
        foreach ($rows as $r) {
            $data[] = [
                'title'      => ($r['is_pinned'] ? '<i class="ph-fill ph-push-pin text-amber-500 mr-1"></i>' : '') . htmlspecialchars($r['title']),
                'category'   => ucfirst(str_replace('_', ' ', $r['category'])),
                'publish_date' => format_datetime($r['publish_date']),
                'expiration_date' => $r['expiration_date'] ? format_datetime($r['expiration_date']) : 'No expiry',
                'status'     => '<span class="px-2.5 py-1 rounded-full text-xs font-medium ' . ($r['status'] === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600') . '">' . ucfirst($r['status']) . '</span>',
                'actions'    => '<div class="flex items-center justify-center gap-1.5 whitespace-nowrap">'
                    . dt_icon_button('ph-pencil-simple', 'Edit announcement', 'editAnnouncement(' . (int) $r['id'] . ')')
                    . dt_icon_button('ph-trash', 'Delete announcement', 'deleteAnnouncement(' . (int) $r['id'] . ')', 'danger')
                    . '</div>',
            ];
        }

        $this->json(['draw' => (int) ($request['draw'] ?? 1), 'recordsTotal' => $total, 'recordsFiltered' => $total, 'data' => $data]);
    }

    public function get($id)
    {
        $this->json(['success' => true, 'data' => $this->Announcement_model->get($id)]);
    }

    public function store()
    {
        $this->form_validation->set_rules('title', 'Title', 'required');
        $this->form_validation->set_rules('body', 'Content', 'required');
        $this->form_validation->set_rules('publish_date', 'Publish Date', 'required');

        if ($this->form_validation->run() === FALSE) {
            return $this->json(['success' => false, 'message' => strip_tags(validation_errors())]);
        }

        $title = $this->input->post('title', true);
        $payload = [
            'title'            => $title,
            'slug'             => url_title($title . '-' . uniqid(), '-', true),
            'body'             => $this->input->post('body'),
            'category'         => $this->input->post('category') ?: 'notice',
            'publish_date'     => $this->input->post('publish_date'),
            'expiration_date'  => $this->input->post('expiration_date') ?: null,
            'is_pinned'        => $this->input->post('is_pinned') ? 1 : 0,
            'status'           => $this->input->post('status') ?: 'published',
            'created_by'       => $this->current_user['id'],
        ];

        $id = $this->input->post('id');
        if ($id) {
            unset($payload['slug']); // keep original slug on edit
            $this->Announcement_model->update($id, $payload);
            $msg = 'Announcement updated.';
        } else {
            $this->Announcement_model->create($payload);
            $msg = 'Announcement published.';
        }
        $this->log_activity('Saved announcement', 'announcement', $title);
        $this->json(['success' => true, 'message' => $msg]);
    }

    public function delete($id)
    {
        $this->Announcement_model->delete($id);
        $this->log_activity('Deleted announcement', 'announcement', "ID {$id}");
        $this->json(['success' => true, 'message' => 'Announcement deleted.']);
    }
}
