<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Service_type extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_ADMIN]);
        $this->load->model('ServiceType_model');
    }

    public function index()
    {
        $data['service_types'] = $this->ServiceType_model->all();
        $this->render_app('admin/service_type_list', $data, 'layouts/app_admin');
    }

    public function get($id)
    {
        $service = $this->ServiceType_model->get($id);
        $service['requirements'] = $this->ServiceType_model->requirements($id);
        $this->json(['success' => true, 'data' => $service]);
    }

    public function store()
    {
        $this->form_validation->set_rules('name', 'Name', 'required');
        $this->form_validation->set_rules('base_fee', 'Base Fee', 'required|numeric');

        if ($this->form_validation->run() === FALSE) {
            return $this->json(['success' => false, 'message' => strip_tags(validation_errors())]);
        }

        $this->ServiceType_model->update($this->input->post('id'), [
            'name'                       => $this->input->post('name', true),
            'description'                => $this->input->post('description', true),
            'base_fee'                   => $this->input->post('base_fee'),
            'duration_minutes'           => $this->input->post('duration_minutes') ?: 60,
            'requires_approval_workflow' => $this->input->post('requires_approval_workflow') ? 1 : 0,
            'is_active'                  => $this->input->post('is_active') ? 1 : 0,
        ]);

        $this->log_activity('Updated service type', 'service_type', $this->input->post('name'));
        $this->json(['success' => true, 'message' => 'Service updated.']);
    }

    public function add_requirement()
    {
        $this->db->insert('service_requirements', [
            'service_type_id' => $this->input->post('service_type_id'),
            'label'            => $this->input->post('label', true),
            'is_required'      => $this->input->post('is_required') ? 1 : 0,
            'display_order'    => 0,
        ]);
        $this->json(['success' => true, 'message' => 'Requirement added.']);
    }

    public function delete_requirement($id)
    {
        $this->db->where('id', $id)->delete('service_requirements');
        $this->json(['success' => true]);
    }
}
