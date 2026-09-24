<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends Public_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['MassSchedule_model', 'Announcement_model', 'Event_model', 'Ministry_model', 'ServiceType_model', 'Certificate_model', 'Project_model', 'Community_model']);
    }

    public function index()
    {
        $data['today_masses']    = $this->MassSchedule_model->today();
        $data['next_mass']       = $this->MassSchedule_model->next_mass();
        $data['announcements']   = $this->Announcement_model->published(4);
        $data['events']          = $this->Event_model->upcoming(4);
        $data['seasonal_event']  = $this->Event_model->active_seasonal_highlight();
        $data['seasonal_event_duration'] = $data['seasonal_event'] ? $this->Event_model->event_duration_days($data['seasonal_event']) : 0;
        $data['service_types']   = $this->ServiceType_model->all_active();
        $data['priests']         = $this->User_model->priests(3);
        $data['projects']        = $this->Project_model->active(3);
        $data['chapels']         = $this->Community_model->schema_ready() ? array_slice($this->Community_model->chapels(true), 0, 3) : [];
        $this->render_public('public/home', $data);
    }

    public function mass_schedule()
    {
        $data['grid']            = $this->MassSchedule_model->weekly_grid();
        $data['special']         = $this->MassSchedule_model->upcoming_special(10);
        $data['chapel_schedules'] = [];

        if ($this->Community_model->schema_ready()) {
            $chapels = $this->Community_model->chapels(true);
            foreach ($chapels as $chapel) {
                $schedules = $this->Community_model->chapel_mass_schedules($chapel['id'], true);
                if ($schedules) {
                    $chapel['mass_schedules'] = $schedules;
                    $data['chapel_schedules'][] = $chapel;
                }
            }
        }

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
        $data['activities'] = $this->Event_model->activities($data['item']['id']);
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

    public function chapels()
    {
        $data['schema_ready'] = $this->Community_model->schema_ready();
        $data['chapels'] = $data['schema_ready'] ? $this->Community_model->chapels(true) : [];

        if ($data['schema_ready']) {
            foreach ($data['chapels'] as &$chapel) {
                $chapel['mass_schedules'] = $this->Community_model->chapel_mass_schedules($chapel['id'], true);
                $chapel['clusters'] = $this->Community_model->clusters($chapel['id'], true);
            }
            unset($chapel);
        }

        $this->render_public('public/chapels', $data);
    }

    public function chapel_detail($slug)
    {
        if (!$this->Community_model->schema_ready()) show_404();

        $data['item'] = $this->Community_model->chapel_by_slug($slug);
        if (!$data['item'] || !$data['item']['is_active']) show_404();

        $data['mass_schedules'] = $this->Community_model->chapel_mass_schedules($data['item']['id'], true);
        $data['officials'] = $this->Community_model->officials('chapel', $data['item']['id'], null, true);
        $data['clusters'] = $this->Community_model->clusters($data['item']['id'], true);
        foreach ($data['clusters'] as &$cluster) {
            $cluster['officials'] = $this->Community_model->officials('cluster', $data['item']['id'], $cluster['id'], true);
        }
        unset($cluster);

        $this->render_public('public/chapel_detail', $data);
    }

    public function parish_organization()
    {
        $data['schema_ready'] = $this->Community_model->schema_ready();
        $data['structure'] = $data['schema_ready']
            ? $this->Community_model->public_structure()
            : ['parish_officials'=>[], 'chapels'=>[]];
        $data['priests'] = $this->User_model->priests();
        $this->render_public('public/parish_organization', $data);
    }

    public function priests()
    {
        $data['priests'] = $this->User_model->priests();
        $this->render_public('public/priests', $data);
    }

    public function prayers()
    {
        $this->render_public('public/prayers');
    }

    public function st_monica()
    {
        $this->render_public('public/st_monica', ['page_title' => 'Life of St. Monica']);
    }

    public function about()
    {
        $this->render_public('public/about');
    }

    public function contact()
    {
        $this->render_public('public/contact');
    }

    public function projects()
    {
        $data['projects'] = $this->Project_model->public_projects();
        $data['featured_project'] = $this->Project_model->featured();
        $this->render_public('public/projects', $data);
    }

    public function project_detail($slug)
    {
        $data['item'] = $this->Project_model->find_by_slug($slug);
        if (!$data['item']) show_404();

        $this->render_public('public/project_detail', $data);
    }

    public function donate()
    {
        $data['projects'] = $this->Project_model->active();
        $data['featured_project'] = $this->Project_model->featured();

        $rows = $this->db->where_in('setting_key', ['gcash_qr_image','gcash_account_name','gcash_account_number'])
            ->get('system_settings')->result_array();
        $data['gcash'] = [];
        foreach ($rows as $row) $data['gcash'][$row['setting_key']] = $row['setting_value'];

        $data['general_raised'] = $this->Project_model->general_verified_raised();
        $this->render_public('public/donate', $data);
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
