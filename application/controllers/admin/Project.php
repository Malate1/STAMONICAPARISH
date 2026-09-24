<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Project extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_ADMIN, ROLE_SECRETARY]);
        $this->load->model('Project_model');
    }

    public function index()
    {
        $data['schema_ready'] = $this->Project_model->schema_ready();
        $this->render_app('admin/project_list', $data, 'layouts/app_admin');
    }

    public function datatable()
    {
        $request = $this->input->post();
        $total = $this->Project_model->datatable_query($request, true);
        $rows = $this->Project_model->datatable_query($request, false);

        $data = [];
        foreach ($rows as $r) {
            $goal = (float) ($r['goal_amount'] ?? 0);
            $raised = (float) ($r['raised_amount'] ?? 0);
            $progress = (float) ($r['progress_percent'] ?? 0);

            $progress_html = '<div class="min-w-[180px]">'
                . '<div class="flex items-center justify-between gap-3 text-xs"><span class="font-semibold text-gray-700">' . peso($raised) . '</span><span class="text-gray-400">' . ($goal > 0 ? peso($goal) : 'No target') . '</span></div>'
                . '<div class="mt-2 h-2 rounded-full bg-gray-100 overflow-hidden"><div class="h-full rounded-full bg-parish-600" style="width:' . min(100, $progress) . '%"></div></div>'
                . '<div class="mt-1 text-[10px] text-gray-400">' . ($goal > 0 ? number_format($progress, 1) . '% funded' : (int) $r['donor_count'] . ' verified gift(s)') . '</div>'
                . '</div>';

            $data[] = [
                'title' => '<div class="font-medium text-gray-800">' . html_escape($r['title']) . '</div>'
                    . (!empty($r['is_featured']) ? '<div class="text-[10px] text-gold-700 mt-1"><i class="ph ph-star mr-1"></i>Featured project</div>' : ''),
                'category' => html_escape($r['category'] ?? '—'),
                'goal' => $progress_html,
                'target' => !empty($r['target_date']) ? format_date($r['target_date']) : 'Open-ended',
                'status' => '<span class="px-2.5 py-1 rounded-full text-xs font-medium ' . ($r['status'] === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600') . '">' . ucfirst($r['status']) . '</span>',
                'actions' => '<div class="flex items-center justify-center gap-1.5 whitespace-nowrap">'
                    . dt_icon_button('ph-pencil-simple', 'Edit project', 'editProject(' . (int) $r['id'] . ')')
                    . dt_icon_link('ph-arrow-square-out', 'View public project', site_url('projects/' . $r['slug']), 'neutral')
                    . dt_icon_button('ph-trash', 'Delete project', 'deleteProject(' . (int) $r['id'] . ')', 'danger')
                    . '</div>',
            ];
        }

        $this->json([
            'draw' => (int) ($request['draw'] ?? 1),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data,
        ]);
    }

    public function get($id)
    {
        $item = $this->Project_model->get((int) $id);
        if (!$item) return $this->json(['success' => false, 'message' => 'Project not found.'], 404);

        $item['cover_url'] = !empty($item['cover_image']) ? base_url($item['cover_image']) : null;
        $this->json(['success' => true, 'data' => $item]);
    }

    public function store()
    {
        if (!$this->Project_model->schema_ready()) {
            return $this->json([
                'success' => false,
                'message' => 'Run database/migrations/20260924_projects_donations.sql in phpMyAdmin first.'
            ]);
        }

        $this->form_validation->set_rules('title', 'Project Title', 'required|max_length[200]');
        $this->form_validation->set_rules('goal_amount', 'Goal Amount', 'numeric');

        if ($this->form_validation->run() === false) {
            return $this->json(['success' => false, 'message' => strip_tags(validation_errors())]);
        }

        $id = (int) $this->input->post('id');
        $existing = $id ? $this->Project_model->get($id) : null;
        if ($id && !$existing) {
            return $this->json(['success' => false, 'message' => 'Project not found.'], 404);
        }

        $start_date = $this->input->post('start_date', true) ?: null;
        $target_date = $this->input->post('target_date', true) ?: null;
        if ($start_date && $target_date && strtotime($target_date) < strtotime($start_date)) {
            return $this->json(['success' => false, 'message' => 'Target date cannot be earlier than the project start date.']);
        }

        $title = trim((string) $this->input->post('title', true));
        $payload = [
            'title' => $title,
            'short_description' => trim((string) $this->input->post('short_description', true)) ?: null,
            'description' => $this->input->post('description'),
            'category' => trim((string) $this->input->post('category', true)) ?: null,
            'location' => trim((string) $this->input->post('location', true)) ?: null,
            'start_date' => $start_date,
            'target_date' => $target_date,
            'goal_amount' => $this->input->post('goal_amount') !== '' ? max(0, (float) $this->input->post('goal_amount')) : null,
            'is_featured' => $this->input->post('is_featured') ? 1 : 0,
            'display_order' => max(0, (int) $this->input->post('display_order')),
            'status' => in_array($this->input->post('status', true), ['active','closed'], true) ? $this->input->post('status', true) : 'active',
        ];

        $upload = $this->handle_cover_upload();
        if (!$upload['success']) {
            return $this->json(['success' => false, 'message' => $upload['message']]);
        }
        if (!empty($upload['path'])) {
            $payload['cover_image'] = $upload['path'];
        }

        if ($id) {
            if (!$this->Project_model->update($id, $payload)) {
                return $this->json(['success' => false, 'message' => 'Could not update the project.']);
            }
            if (!empty($upload['path']) && !empty($existing['cover_image']) && $existing['cover_image'] !== $upload['path']) {
                $this->delete_local_cover($existing['cover_image']);
            }
            $message = 'Project updated.';
        } else {
            $slug = url_title($title, '-', true);
            if ($this->db->where('slug', $slug)->count_all_results('donation_campaigns') > 0) {
                $slug .= '-' . substr(bin2hex(random_bytes(3)), 0, 6);
            }
            $payload['slug'] = $slug;
            $payload['created_by'] = $this->current_user['id'];
            $id = $this->Project_model->create($payload);
            if (!$id) return $this->json(['success' => false, 'message' => 'Could not create the project.']);
            $message = 'Project created.';
        }

        $this->json(['success' => true, 'message' => $message, 'id' => (int) $id]);
    }

    public function delete($id)
    {
        $project = $this->Project_model->get((int) $id);
        if (!$project) return $this->json(['success' => false, 'message' => 'Project not found.'], 404);

        $has_donations = $this->db->where('campaign_id', (int) $id)->count_all_results('donations') > 0;
        if ($has_donations) {
            return $this->json([
                'success' => false,
                'message' => 'This project already has donation records. Close it instead of deleting it so the parish keeps an accurate giving history.'
            ]);
        }

        if ($this->Project_model->delete((int) $id)) {
            if (!empty($project['cover_image'])) $this->delete_local_cover($project['cover_image']);
            return $this->json(['success' => true, 'message' => 'Project deleted.']);
        }

        $this->json(['success' => false, 'message' => 'Project could not be deleted.']);
    }

    private function handle_cover_upload()
    {
        if (empty($_FILES['cover_image']['name'])) {
            return ['success' => true, 'path' => null];
        }

        if ($_FILES['cover_image']['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'The project image upload did not complete successfully.'];
        }

        if ((int) $_FILES['cover_image']['size'] > 5 * 1024 * 1024) {
            return ['success' => false, 'message' => 'Project photo must be 5 MB or smaller.'];
        }

        $ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','webp'], true)) {
            return ['success' => false, 'message' => 'Use a JPG, PNG, or WebP image.'];
        }

        if (!@getimagesize($_FILES['cover_image']['tmp_name'])) {
            return ['success' => false, 'message' => 'The uploaded file is not a valid image.'];
        }

        $target_dir = FCPATH . 'uploads/projects/';
        if (!is_dir($target_dir) && !@mkdir($target_dir, 0755, true)) {
            return ['success' => false, 'message' => 'The server could not create the project image directory.'];
        }

        $filename = 'project_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
        if (!move_uploaded_file($_FILES['cover_image']['tmp_name'], $target_dir . $filename)) {
            return ['success' => false, 'message' => 'The project image could not be saved.'];
        }

        return ['success' => true, 'path' => 'uploads/projects/' . $filename];
    }

    private function delete_local_cover($path)
    {
        if (strpos($path, 'uploads/projects/') !== 0) return;
        $full = FCPATH . $path;
        if (is_file($full)) @unlink($full);
    }
}
