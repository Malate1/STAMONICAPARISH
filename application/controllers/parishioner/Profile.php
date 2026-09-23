<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profile extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_PARISHIONER]);
    }

    public function index()
    {
        $this->render_app('parishioner/profile', [], 'layouts/app_parishioner');
    }

    public function update()
    {
        $this->form_validation->set_rules('first_name', 'First Name', 'required');
        $this->form_validation->set_rules('last_name', 'Last Name', 'required');
        $this->form_validation->set_rules('mobile_number', 'Mobile Number', 'required');

        if ($this->form_validation->run() === FALSE) {
            return $this->json(['success' => false, 'message' => strip_tags(validation_errors())]);
        }

        $this->User_model->update($this->current_user['id'], [
            'first_name'    => $this->input->post('first_name', true),
            'last_name'     => $this->input->post('last_name', true),
            'mobile_number' => $this->input->post('mobile_number', true),
            'address'       => $this->input->post('address', true),
        ]);

        $this->json(['success' => true, 'message' => 'Profile updated.']);
    }

    public function change_password()
    {
        $this->form_validation->set_rules('current_password', 'Current Password', 'required');
        $this->form_validation->set_rules('new_password', 'New Password', 'required|min_length[8]');
        $this->form_validation->set_rules('new_password_confirm', 'Confirm Password', 'required|matches[new_password]');

        if ($this->form_validation->run() === FALSE) {
            return $this->json(['success' => false, 'message' => strip_tags(validation_errors())]);
        }

        $user = $this->User_model->get($this->current_user['id']);
        if (!password_verify($this->input->post('current_password'), $user['password_hash'])) {
            return $this->json(['success' => false, 'message' => 'Current password is incorrect.']);
        }

        $this->User_model->update($user['id'], [
            'password_hash' => password_hash($this->input->post('new_password'), PASSWORD_BCRYPT),
        ]);

        $this->json(['success' => true, 'message' => 'Password changed successfully.']);
    }
}
