<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notifications extends Auth_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Notification_model');
    }

    public function list()
    {
        $this->json(['success' => true, 'data' => $this->Notification_model->for_user($this->current_user['id'], 10)]);
    }

    public function unread_count()
    {
        $this->json(['count' => $this->Notification_model->unread_count($this->current_user['id'])]);
    }

    public function mark_all_read()
    {
        $this->Notification_model->mark_all_read($this->current_user['id']);
        $this->json(['success' => true]);
    }
}
