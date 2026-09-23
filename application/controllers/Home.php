<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends Public_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['MassSchedule_model', 'Announcement_model', 'Event_model', 'Ministry_model', 'ServiceType_model', 'Certificate_model']);
    }

    public function index()
    {
        $data['today_masses']    = $this->MassSchedule_model->today();
        $data['next_mass']       = $this->MassSchedule_model->next_mass();
        $data['announcements']   = $this->Announcement_model->published(4);
        $data['events']          = $this->Event_model->upcoming(4);
        $data['service_types']   = $this->ServiceType_model->all_active();
        $data['priests']         = $this->User_model->priests(3);
        $this->render_public('public/home', $data);
    }

    public function mass_schedule()
    {
        $data['grid']            = $this->MassSchedule_model->weekly_grid();
        $data['special']         = $this->MassSchedule_model->upcoming_special(10);
        $this->render_public('public/mass_schedule', $data);
    }

    public function sacraments()
    {
        $data['service_types'] = $this->ServiceType_model->all_active();
        $this->render_public('public/sacraments', $data);
    }

    public function announcements()
    {
        $data['announcements'] = $this->Announcement_model->published();
        $this->render_public('public/announcements', $data);
    }

    public function announcement_detail($slug)
    {
        $data['item'] = $this->Announcement_model->find_by_slug($slug);
        if (!$data['item']) show_404();
        $this->render_public('public/announcement_detail', $data);
    }

    public function events()
    {
        $data['events'] = $this->Event_model->upcoming();
        $this->render_public('public/events', $data);
    }

    public function event_detail($slug)
    {
        $data['item'] = $this->Event_model->find_by_slug($slug);
        if (!$data['item']) show_404();
        $data['registration_count'] = $this->Event_model->registration_count($data['item']['id']);
        $this->render_public('public/event_detail', $data);
    }

    public function ministries()
    {
        $data['ministries'] = $this->Ministry_model->active();
        $this->render_public('public/ministries', $data);
    }

    public function ministry_detail($slug)
    {
        $data['item'] = $this->Ministry_model->find_by_slug($slug);
        if (!$data['item']) show_404();
        $this->render_public('public/ministry_detail', $data);
    }

    public function priests()
    {
        $data['priests'] = $this->User_model->priests();
        $this->render_public('public/priests', $data);
    }

    public function about()
    {
        $this->render_public('public/about');
    }

    public function contact()
    {
        $this->render_public('public/contact');
    }

    public function donate()
    {
        $this->render_public('public/donate');
    }

    public function verify_certificate($token)
    {
        $data['cert'] = $this->Certificate_model->get_by_token($token);
        $this->render_public('public/verify_certificate', $data);
    }

    /** AJAX: register for a parish event */
    public function ajax_register_event()
    {
        $event_id = (int) $this->input->post('event_id');
        $this->form_validation->set_rules('full_name', 'Full Name', 'required');
        $this->form_validation->set_rules('contact_number', 'Contact Number', 'required');

        if ($this->form_validation->run() === FALSE) {
            return $this->json(['success' => false, 'message' => strip_tags(validation_errors())]);
        }

        $this->Event_model->register($event_id, [
            'user_id'        => $this->current_user['id'] ?? null,
            'full_name'      => $this->input->post('full_name', true),
            'contact_number' => $this->input->post('contact_number', true),
        ]);

        $this->json(['success' => true, 'message' => 'You are registered for this event. See you there!']);
    }

    /** AJAX: express interest to join a ministry */
    public function ajax_ministry_interest()
    {
        $ministry_id = (int) $this->input->post('ministry_id');
        $this->form_validation->set_rules('full_name', 'Full Name', 'required');
        $this->form_validation->set_rules('contact_number', 'Contact Number', 'required');

        if ($this->form_validation->run() === FALSE) {
            return $this->json(['success' => false, 'message' => strip_tags(validation_errors())]);
        }

        $this->Ministry_model->add_interest([
            'ministry_id'     => $ministry_id,
            'user_id'         => $this->current_user['id'] ?? null,
            'full_name'       => $this->input->post('full_name', true),
            'contact_number'  => $this->input->post('contact_number', true),
            'message'         => $this->input->post('message', true),
            'created_at'      => date('Y-m-d H:i:s'),
        ]);

        $this->json(['success' => true, 'message' => 'Thank you for your interest! A ministry coordinator will reach out to you.']);
    }

    /** AJAX: submit a private prayer request */
    public function ajax_prayer_request()
    {
        $this->form_validation->set_rules('request_text', 'Prayer Request', 'required|max_length[2000]');
        if ($this->form_validation->run() === FALSE) {
            return $this->json(['success' => false, 'message' => strip_tags(validation_errors())]);
        }

        $this->db->insert('prayer_requests', [
            'user_id'         => $this->current_user['id'] ?? null,
            'requestor_name'  => $this->input->post('requestor_name', true) ?: null,
            'is_confidential' => $this->input->post('is_confidential') ? 1 : 0,
            'request_text'    => $this->input->post('request_text', true),
            'created_at'      => date('Y-m-d H:i:s'),
        ]);

        $this->json(['success' => true, 'message' => 'Your prayer request has been received. The parish will keep you in prayer.']);
    }

    /** AJAX: general contact / inquiry form */
    public function ajax_contact()
    {
        $this->form_validation->set_rules('name', 'Name', 'required');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('message', 'Message', 'required');

        if ($this->form_validation->run() === FALSE) {
            return $this->json(['success' => false, 'message' => strip_tags(validation_errors())]);
        }

        // In production: send email/notification to parish office staff here.
        $this->json(['success' => true, 'message' => 'Thank you for reaching out. The parish office will respond shortly.']);
    }
}
