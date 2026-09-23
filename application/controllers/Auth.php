<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends Public_Controller
{
    public function login()
    {
        if ($this->is_logged_in()) {
            redirect(role_home_url($this->current_user['role_id']));
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
            $this->form_validation->set_rules('password', 'Password', 'required');

            if ($this->form_validation->run() === FALSE) {
                return $this->json(['success' => false, 'message' => validation_errors() ?: 'Please check your input.']);
            }

            $email = $this->input->post('email', true);
            $password = $this->input->post('password');

            $user = $this->User_model->find_by_email($email);

            if (!$user) {
                return $this->json(['success' => false, 'message' => 'Invalid email or password.']);
            }
            if ($user['status'] !== 'active') {
                return $this->json(['success' => false, 'message' => 'Your account is not active. Please contact the parish office.']);
            }
            if (!empty($user['locked_until']) && strtotime($user['locked_until']) > time()) {
                return $this->json(['success' => false, 'message' => 'Account temporarily locked due to multiple failed attempts. Try again later.']);
            }
            if (!password_verify($password, $user['password_hash'])) {
                $this->User_model->register_failed_attempt($user['id']);
                if (($user['failed_login_attempts'] + 1) >= 5) {
                    $this->User_model->update($user['id'], ['locked_until' => date('Y-m-d H:i:s', strtotime('+15 minutes'))]);
                }
                return $this->json(['success' => false, 'message' => 'Invalid email or password.']);
            }

            $this->User_model->reset_failed_attempts($user['id']);
            $this->session->set_userdata('user_id', $user['id']);
            $this->log_activity_static($user['id'], 'Logged in', 'auth');

            $redirect = $this->session->userdata('redirect_after_login') ?: role_home_url($user['role_id']);
            $this->session->unset_userdata('redirect_after_login');

            return $this->json(['success' => true, 'redirect' => $redirect]);
        }

        $this->render_public('auth/login');
    }

    public function register()
    {
        if ($this->is_logged_in()) {
            redirect(role_home_url($this->current_user['role_id']));
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('first_name', 'First Name', 'required|max_length[100]');
            $this->form_validation->set_rules('last_name', 'Last Name', 'required|max_length[100]');
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[users.email]');
            $this->form_validation->set_rules('mobile_number', 'Mobile Number', 'required|max_length[20]');
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]');
            $this->form_validation->set_rules('password_confirm', 'Confirm Password', 'required|matches[password]');

            if ($this->form_validation->run() === FALSE) {
                return $this->json(['success' => false, 'message' => strip_tags(validation_errors())]);
            }

            $user_id = $this->User_model->create([
                'role_id'       => ROLE_PARISHIONER,
                'first_name'    => $this->input->post('first_name', true),
                'last_name'     => $this->input->post('last_name', true),
                'email'         => $this->input->post('email', true),
                'mobile_number' => $this->input->post('mobile_number', true),
                'address'       => $this->input->post('address', true),
                'password_hash' => password_hash($this->input->post('password'), PASSWORD_BCRYPT),
                'status'        => 'active',
            ]);

            $this->session->set_userdata('user_id', $user_id);
            $this->log_activity_static($user_id, 'Registered a new account', 'auth');

            return $this->json(['success' => true, 'redirect' => site_url('my/dashboard')]);
        }

        $this->render_public('auth/register');
    }

    public function forgot_password()
    {
        $this->render_public('auth/forgot_password');
    }

    public function logout()
    {
        if ($this->current_user) {
            $this->log_activity_static($this->current_user['id'], 'Logged out', 'auth');
        }
        $this->session->sess_destroy();
        redirect('login');
    }

    /**
     * Small helper since we don't have current_user set for audit at the exact
     * moment of login in Base_Controller (it's loaded before session write).
     */
    private function log_activity_static($user_id, $action, $module = null)
    {
        $this->load->model('Audit_model');
        $this->Audit_model->log($user_id, $action, $module, null, $this->input->ip_address());
    }
}
