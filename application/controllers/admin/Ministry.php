<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ministry extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_ADMIN]);
        $this->load->model('Ministry_model');
    }

    public function index()
    {
        $data['ministries'] = $this->Ministry_model->active();
        $data['all'] = $this->db->order_by('display_order')->get('ministries')->result_array();
        $this->render_app('admin/ministry_list', $data, 'layouts/app_admin');
    }

    public function get($id)
    {
        $this->json(['success' => true, 'data' => $this->Ministry_model->get($id)]);
    }

    public function store()
    {
        $this->form_validation->set_rules('name', 'Name', 'required');
        if ($this->form_validation->run() === FALSE) {
            return $this->json(['success' => false, 'message' => strip_tags(validation_errors())]);
        }

        $name = $this->input->post('name', true);
        $payload = [
            'name'           => $name,
            'description'    => $this->input->post('description'),
            'contact_person' => $this->input->post('contact_person', true),
            'is_active'      => $this->input->post('is_active') ? 1 : 0,
        ];

        $id = $this->input->post('id');
        if ($id) {
            $this->Ministry_model->update($id, $payload);
            $msg = 'Ministry updated.';
        } else {
            $payload['slug'] = url_title($name . '-' . uniqid(), '-', true);
            $this->Ministry_model->create($payload);
            $msg = 'Ministry added.';
        }
        $this->json(['success' => true, 'message' => $msg]);
    }

    public function interests()
    {
        $data = $this->db->select('ministry_interests.*, ministries.name as ministry_name')
            ->from('ministry_interests')
            ->join('ministries', 'ministries.id = ministry_interests.ministry_id')
            ->order_by('ministry_interests.created_at', 'desc')
            ->get()->result_array();
        $this->json(['success' => true, 'data' => $data]);
    }
}
