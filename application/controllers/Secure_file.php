<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Secure_file extends Auth_Controller
{
    public function document($id)
    {
        $id = (int) $id;

        $row = $this->db->select('booking_documents.*, service_bookings.user_id, service_bookings.assigned_priest_id')
            ->from('booking_documents')
            ->join('service_bookings', 'service_bookings.id = booking_documents.booking_id')
            ->where('booking_documents.id', $id)
            ->get()->row_array();

        if (!$row || !$this->can_access_booking_file($row)) {
            show_404();
        }

        $this->log_activity('Viewed protected booking document', 'secure_file', 'Booking #' . (int)$row['booking_id'] . ' · Document #' . $id);

        $this->serve_private_path(
            $row['file_path'],
            $row['mime_type'] ?: null,
            $row['original_name'] ?: $row['file_name'],
            UPLOAD_DOCUMENTS
        );
    }

    public function archive($id)
    {
        $id = (int) $id;
        if (!defined('UPLOAD_ARCHIVE')) show_404();

        $role = (int) $this->current_user['role_id'];
        if (!in_array($role, [ROLE_ADMIN, ROLE_SECRETARY], true)) {
            show_404();
        }

        $page = $this->db->get_where('archive_pages', ['id' => $id])->row_array();
        if (!$page) show_404();

        $this->log_activity('Viewed legacy register scan', 'archive', 'Archive page #' . $id . ' · Batch #' . (int)$page['batch_id']);

        $this->serve_private_path(
            $page['file_path'],
            $page['mime_type'] ?: null,
            $page['original_name'] ?: $page['file_name'],
            UPLOAD_ARCHIVE
        );
    }

    public function payment($id)
    {
        $id = (int) $id;

        $row = $this->db->get_where('payments', ['id' => $id])->row_array();
        if (!$row || empty($row['proof_of_payment'])) {
            show_404();
        }

        $role = (int) $this->current_user['role_id'];
        $allowed = in_array($role, [ROLE_ADMIN, ROLE_SECRETARY], true)
            || ($role === ROLE_PARISHIONER && (int) $row['user_id'] === (int) $this->current_user['id']);

        if (!$allowed) {
            show_404();
        }

        $this->log_activity('Viewed protected payment proof', 'payment', $row['payment_code'] ?? ('Payment #' . $id));

        $this->serve_private_path(
            $row['proof_of_payment'],
            null,
            basename($row['proof_of_payment']),
            UPLOAD_PAYMENTS
        );
    }

    private function can_access_booking_file(array $row)
    {
        $role = (int) $this->current_user['role_id'];

        if (in_array($role, [ROLE_ADMIN, ROLE_SECRETARY], true)) {
            return true;
        }

        if ($role === ROLE_PARISHIONER) {
            return (int) $row['user_id'] === (int) $this->current_user['id'];
        }

        if ($role === ROLE_PRIEST) {
            return !empty($row['assigned_priest_id'])
                && (int) $row['assigned_priest_id'] === (int) $this->current_user['id'];
        }

        return false;
    }

    private function serve_private_path($stored_path, $declared_mime, $download_name, $allowed_root)
    {
        $stored_path = ltrim(str_replace('\\', '/', (string) $stored_path), '/');
        $allowed_root = trim(str_replace('\\', '/', (string) $allowed_root), '/') . '/';

        if (strpos($stored_path, $allowed_root) !== 0) {
            show_404();
        }

        $root = realpath(FCPATH . $allowed_root);
        $file = realpath(FCPATH . $stored_path);

        if (!$root || !$file || !is_file($file)
            || strpos(str_replace('\\', '/', $file), rtrim(str_replace('\\', '/', $root), '/') . '/') !== 0) {
            show_404();
        }

        $mime = $declared_mime;
        if (!$mime && class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file);
        }
        if (!$mime) {
            $mime = 'application/octet-stream';
        }

        $safe_name = preg_replace('/[^A-Za-z0-9._() -]/', '_', basename((string) $download_name));
        if ($safe_name === '') $safe_name = basename($file);

        $this->output
            ->set_header('Cache-Control: private, no-store, max-age=0')
            ->set_header('Pragma: no-cache')
            ->set_header('Content-Type: ' . $mime)
            ->set_header('Content-Length: ' . filesize($file))
            ->set_header('Content-Disposition: inline; filename="' . str_replace('"', '', $safe_name) . '"');

        $this->output->_display();
        readfile($file);
        exit;
    }
}
