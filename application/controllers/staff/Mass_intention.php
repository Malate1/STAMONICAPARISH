<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mass_intention extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_SECRETARY]);
        $this->load->model('MassIntention_model');
        $this->load->model('MassSchedule_model');
    }

    public function index()
    {
        $data['intentions'] = $this->MassIntention_model->staff_list();
        $data['reader_occurrences'] = $this->MassIntention_model->ready_occurrences();
        $cutoff = $this->get_cutoff_minutes();
        $data['available_occurrences'] = $this->MassSchedule_model->upcoming_occurrences(60, $cutoff, 120);
        $this->render_app('staff/mass_intention_list', $data, 'layouts/app_admin');
    }

    public function update_status()
    {
        $id = (int) $this->input->post('id');
        $status = $this->input->post('status', true);
        $intention = $this->MassIntention_model->get($id);

        if (!$intention) {
            return $this->json(['success' => false, 'message' => 'Mass intention not found.'], 404);
        }

        $allowed = [
            'pending' => ['approved', 'cancelled'],
            'approved' => ['ready_for_reading', 'cancelled'],
            'ready_for_reading' => ['completed', 'approved'],
            'completed' => [],
            'cancelled' => [],
        ];

        $current = $intention['status'];
        if ($current === 'listed') $current = 'ready_for_reading';

        if (!isset($allowed[$current]) || !in_array($status, $allowed[$current], true)) {
            return $this->json([
                'success' => false,
                'message' => 'That status change is not allowed from ' . status_label($current) . '.'
            ]);
        }

        if (in_array($status, ['approved', 'ready_for_reading'], true) && empty($intention['mass_date'])) {
            return $this->json([
                'success' => false,
                'message' => 'This older intention has no actual Mass date. Ask the parishioner to resubmit or assign it to a dated Mass before preparing the reader sheet.'
            ]);
        }

        if ($status === 'completed') {
            if (empty($intention['mass_date']) || empty($intention['mass_time'])) {
                return $this->json(['success' => false, 'message' => 'This intention has no dated Mass occurrence.']);
            }

            $mass_ts = strtotime($intention['mass_date'] . ' ' . $intention['mass_time']);
            if ($mass_ts > time()) {
                return $this->json([
                    'success' => false,
                    'message' => 'This Mass has not started yet. Mark it completed only after the Mass.'
                ]);
            }
        }

        if (!$this->MassIntention_model->update_status($id, $status, $this->current_user['id'])) {
            return $this->json(['success' => false, 'message' => 'Could not update the Mass intention.']);
        }

        $messages = [
            'approved' => 'Intention approved. Review it once more before marking it Ready for Reading.',
            'ready_for_reading' => 'Intention is now on the commentator/reader sheet.',
            'completed' => 'Intention marked completed after Mass.',
            'cancelled' => 'Intention cancelled.',
        ];

        $this->json(['success' => true, 'message' => $messages[$status] ?? 'Mass intention updated.']);
    }

    public function assign_mass()
    {
        $id = (int) $this->input->post('id');
        $schedule_id = (int) $this->input->post('mass_schedule_id');
        $mass_date = $this->input->post('mass_date', true);

        $intention = $this->MassIntention_model->get($id);
        if (!$intention) {
            return $this->json(['success' => false, 'message' => 'Mass intention not found.'], 404);
        }

        $occurrence = $this->MassSchedule_model->occurrence_for_submission($schedule_id, $mass_date, $this->get_cutoff_minutes());
        if (!$occurrence) {
            return $this->json(['success' => false, 'message' => 'That Mass is no longer available. Please choose another occurrence.']);
        }

        $this->db->where('id', $id)->update('mass_intentions', [
            'mass_schedule_id' => $schedule_id,
            'mass_date' => $mass_date,
            'processed_by' => $this->current_user['id'],
        ]);

        $this->json(['success' => true, 'message' => 'Mass occurrence assigned. You can now continue the normal review workflow.']);
    }

    public function complete_mass()
    {
        $mass_date = $this->input->post('mass_date', true);
        $schedule_id = (int) $this->input->post('schedule_id');

        $schedule = $this->MassSchedule_model->get($schedule_id);
        if (!$schedule || !$mass_date) {
            return $this->json(['success' => false, 'message' => 'Mass schedule not found.']);
        }

        $mass_ts = strtotime($mass_date . ' ' . $schedule['mass_time']);
        if (!$mass_ts || $mass_ts > time()) {
            return $this->json([
                'success' => false,
                'message' => 'This Mass has not started yet. Mark the intentions completed only after the Mass.'
            ]);
        }

        $this->MassIntention_model->complete_occurrence($mass_date, $schedule_id, $this->current_user['id']);
        $this->json(['success' => true, 'message' => 'All Ready for Reading intentions for this Mass were marked completed.']);
    }

    public function reader()
    {
        $mass_date = $this->input->get('date', true);
        $schedule_id = (int) $this->input->get('schedule_id');

        if (!$mass_date || !$schedule_id) {
            $occurrences = $this->MassIntention_model->ready_occurrences();
            if (!empty($occurrences)) {
                $mass_date = $occurrences[0]['mass_date'];
                $schedule_id = (int) $occurrences[0]['mass_schedule_id'];
            }
        }

        $data['occurrences'] = $this->MassIntention_model->ready_occurrences();
        $data['mass_date'] = $mass_date;
        $data['schedule_id'] = $schedule_id;
        $data['intentions'] = ($mass_date && $schedule_id)
            ? $this->MassIntention_model->reader_sheet($mass_date, $schedule_id)
            : [];
        $data['can_manage'] = true;
        $data['back_url'] = site_url('staff/mass_intention');

        $this->load->view('staff/mass_intention_reader', $data);
    }

    private function get_cutoff_minutes()
    {
        $row = $this->db->get_where('system_settings', ['setting_key' => 'mass_intention_cutoff_minutes'])->row_array();
        return $row ? max(0, (int) $row['setting_value']) : 30;
    }
}
