<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_ADMIN]);
    }

    public function index()
    {
        $this->render_app('admin/user_list', [], 'layouts/app_admin');
    }

    public function datatable()
    {
        $request = $this->input->post();
        $total = $this->User_model->datatable_query($request, true);
        $rows  = $this->User_model->datatable_query($request, false);

        $data = [];
        foreach ($rows as $r) {
            $data[] = [
                'name'   => $r['first_name'] . ' ' . $r['last_name'],
                'email'  => $r['email'],
                'role'   => $r['role_name'],
                'status' => $r['status'] === 'active'
                    ? '<span class="px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Active</span>'
                    : '<span class="px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">' . ucfirst($r['status']) . '</span>',
                'created_at' => format_date($r['created_at']),
                'actions' => '<div class="flex gap-3">
                        <button onclick="editUser(' . $r['id'] . ')" class="text-emerald-700 hover:underline font-medium">Edit</button>
                        <button onclick="toggleUser(' . $r['id'] . ', \'' . $r['status'] . '\')" class="text-amber-600 hover:underline font-medium">' . ($r['status'] === 'active' ? 'Deactivate' : 'Activate') . '</button>
                    </div>',
            ];
        }

        $this->json(['draw' => (int) ($request['draw'] ?? 1), 'recordsTotal' => $total, 'recordsFiltered' => $total, 'data' => $data]);
    }

    public function get($id)
    {
        $user = $this->User_model->get_with_priest_profile($id);
        if (!$user) {
            return $this->json(['success' => false, 'message' => 'Account not found.'], 404);
        }

        $this->json(['success' => true, 'data' => $user]);
    }

    public function store()
    {
        $id = $this->input->post('id');

        $this->form_validation->set_rules('first_name', 'First Name', 'required');
        $this->form_validation->set_rules('last_name', 'Last Name', 'required');
        $this->form_validation->set_rules('role_id', 'Role', 'required');

        $email_rule = 'required|valid_email';
        if (!$id) $email_rule .= '|is_unique[users.email]';
        $this->form_validation->set_rules('email', 'Email', $email_rule);

        if ($this->form_validation->run() === FALSE) {
            return $this->json(['success' => false, 'message' => strip_tags(validation_errors())]);
        }

        $payload = [
            'first_name'    => $this->input->post('first_name', true),
            'last_name'     => $this->input->post('last_name', true),
            'email'         => $this->input->post('email', true),
            'mobile_number' => $this->input->post('mobile_number', true),
            'role_id'       => $this->input->post('role_id'),
        ];

        if ((int) $payload['role_id'] === ROLE_PRIEST) {
            $payload['avatar'] = trim((string) $this->input->post('avatar', true)) ?: null;
        }

        $profile_payload = [
            'title'         => trim((string) $this->input->post('priest_title', true)) ?: 'Rev. Fr.',
            'position'      => trim((string) $this->input->post('position', true)) ?: 'Assistant Priest',
            'bio'           => trim((string) $this->input->post('priest_bio', true)) ?: null,
            'is_public'     => $this->input->post('priest_is_public') ? 1 : 0,
            'display_order' => (int) $this->input->post('priest_display_order'),
        ];

        if ($id) {
            $this->db->trans_start();
            $this->User_model->update($id, $payload);

            if ((int) $payload['role_id'] === ROLE_PRIEST) {
                $existing_profile = $this->db->get_where('priest_profiles', ['user_id' => $id])->row_array();
                if ($existing_profile) {
                    $this->db->where('user_id', $id)->update('priest_profiles', $profile_payload);
                } else {
                    $profile_payload['user_id'] = $id;
                    $this->db->insert('priest_profiles', $profile_payload);
                }
            } else {
                $this->db->where('user_id', $id)->delete('priest_profiles');
            }

            $this->db->trans_complete();
            $msg = 'Account updated.';
        } else {
            $password = $this->input->post('password') ?: bin2hex(random_bytes(4));
            $payload['password_hash'] = password_hash($password, PASSWORD_BCRYPT);
            $payload['status'] = 'active';

            $this->db->trans_start();
            $new_id = $this->User_model->create($payload);

            if ((int) $payload['role_id'] === ROLE_PRIEST) {
                $profile_payload['user_id'] = $new_id;
                $this->db->insert('priest_profiles', $profile_payload);
            }
            $this->db->trans_complete();

            $msg = 'Account created. Temporary password: ' . $password;
        }

        $this->log_activity('Saved user account', 'users', $payload['email']);
        $this->json(['success' => true, 'message' => $msg]);
    }

    public function toggle($id)
    {
        $user = $this->User_model->get($id);
        if (!$user) return $this->json(['success' => false, 'message' => 'Not found.']);
        $this->User_model->update($id, ['status' => $user['status'] === 'active' ? 'inactive' : 'active']);
        $this->json(['success' => true]);
    }
}
