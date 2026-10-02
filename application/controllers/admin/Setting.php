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
        $fields = ['parish_name', 'parish_address', 'parish_contact', 'facebook_url', 'gcash_account_name', 'gcash_account_number', 'priest_booking_capacity', 'mass_intention_cutoff_minutes'];
        foreach ($fields as $f) {
            $value = $this->input->post($f, true);
            if ($f === 'facebook_url') {
                $value = trim((string) $value);
                if ($value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
                    return $this->json(['success' => false, 'message' => 'Please enter a valid Facebook page URL including https://']);
                }
            } elseif ($f === 'priest_booking_capacity') {
                $value = (string) max(1, min(10, (int) $value));
            } elseif ($f === 'mass_intention_cutoff_minutes') {
                $value = (string) max(0, min(1440, (int) $value));
            }
            $existing = $this->db->get_where('system_settings', ['setting_key' => $f])->row_array();
            if ($existing) {
                $this->db->where('setting_key', $f)->update('system_settings', ['setting_value' => $value]);
            } else {
                $this->db->insert('system_settings', ['setting_key' => $f, 'setting_value' => $value]);
            }
        }

        if (!empty($_FILES['gcash_qr_image']['name'])) {
            $this->load->library('secure_upload');

            $stored = $this->secure_upload->store(
                $_FILES['gcash_qr_image'],
                FCPATH . 'uploads/settings/',
                'gcash_qr',
                5 * 1024 * 1024,
                [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                ]
            );

            if (!$stored['success']) {
                return $this->json(['success' => false, 'message' => $stored['message']]);
            }

            $path = 'uploads/settings/' . $stored['filename'];
            $existing_qr = $this->db->get_where('system_settings', ['setting_key' => 'gcash_qr_image'])->row_array();

            if ($existing_qr) {
                $this->db->where('setting_key', 'gcash_qr_image')->update('system_settings', ['setting_value' => $path]);
            } else {
                $this->db->insert('system_settings', ['setting_key' => 'gcash_qr_image', 'setting_value' => $path]);
            }

            if (!empty($existing_qr['setting_value'])) {
                $old_path = ltrim(str_replace('\\', '/', $existing_qr['setting_value']), '/');
                if (strpos($old_path, 'uploads/settings/') === 0) {
                    $old_file = FCPATH . $old_path;
                    if (is_file($old_file)) @unlink($old_file);
                }
            }
        }

        $this->log_activity('Updated system settings', 'settings');
        $this->json(['success' => true, 'message' => 'Settings saved.']);
    }
}
