<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Setting extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_ADMIN]);
    }

    public function index()
    {
        $rows = $this->db->get('system_settings')->result_array();
        $data['settings'] = [];
        foreach ($rows as $r) $data['settings'][$r['setting_key']] = $r['setting_value'];
        $this->render_app('admin/settings', $data, 'layouts/app_admin');
    }

    public function store()
    {
        $fields = ['parish_name', 'parish_address', 'parish_contact', 'gcash_account_name', 'gcash_account_number', 'priest_booking_capacity'];
        foreach ($fields as $f) {
            $value = $this->input->post($f, true);
            if ($f === 'priest_booking_capacity') {
                $value = (string) max(1, min(10, (int) $value));
            }
            $this->db->where('setting_key', $f)->update('system_settings', ['setting_value' => $value]);
        }

        if (!empty($_FILES['gcash_qr_image']['name'])) {
            $target_dir = FCPATH . 'uploads/settings/';
            if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
            $ext = pathinfo($_FILES['gcash_qr_image']['name'], PATHINFO_EXTENSION);
            $name = 'gcash_qr_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['gcash_qr_image']['tmp_name'], $target_dir . $name)) {
                $this->db->where('setting_key', 'gcash_qr_image')->update('system_settings', ['setting_value' => 'uploads/settings/' . $name]);
            }
        }

        $this->log_activity('Updated system settings', 'settings');
        $this->json(['success' => true, 'message' => 'Settings saved.']);
    }
}
