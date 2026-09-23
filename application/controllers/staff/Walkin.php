<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Walkin extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_SECRETARY]);
        $this->load->model(['ServiceType_model', 'Booking_model']);
    }

    public function index()
    {
        $data['service_types'] = $this->ServiceType_model->all_active();
        $this->render_app('staff/walkin', $data, 'layouts/app_admin');
    }

    public function store()
    {
        $this->form_validation->set_rules('full_name', 'Full Name', 'required');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('service_type_id', 'Service', 'required');
        $this->form_validation->set_rules('preferred_date', 'Date', 'required');

        if ($this->form_validation->run() === FALSE) {
            return $this->json(['success' => false, 'message' => strip_tags(validation_errors())]);
        }

        $email = $this->input->post('email', true);
        $user = $this->User_model->find_by_email($email);

        if (!$user) {
            $names = explode(' ', trim($this->input->post('full_name', true)), 2);
            $user_id = $this->User_model->create([
                'role_id'       => ROLE_PARISHIONER,
                'first_name'    => $names[0],
                'last_name'     => $names[1] ?? '',
                'email'         => $email,
                'mobile_number' => $this->input->post('mobile_number', true),
                'password_hash' => password_hash(bin2hex(random_bytes(6)), PASSWORD_BCRYPT),
                'status'        => 'active',
            ]);
        } else {
            $user_id = $user['id'];
        }

        $service = $this->ServiceType_model->get($this->input->post('service_type_id'));

        $booking_id = $this->Booking_model->create([
            'booking_code'     => $this->Booking_model->generate_code($service['service_key']),
            'service_type_id'  => $service['id'],
            'user_id'          => $user_id,
            'preferred_date'   => $this->input->post('preferred_date'),
            'status'           => 'under_review',
            'fee_amount'       => $service['base_fee'],
            'staff_notes'      => 'Recorded as walk-in transaction by ' . $this->current_user['first_name'] . ' ' . $this->current_user['last_name'],
        ]);

        $this->log_activity('Recorded walk-in transaction', 'walkin', $service['name'] . ' for ' . $email);
        $this->json(['success' => true, 'message' => 'Walk-in transaction recorded.', 'redirect' => site_url('staff/booking/view/' . $booking_id)]);
    }
}
