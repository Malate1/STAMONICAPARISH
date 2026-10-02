<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Secure_upload
{
    private $allowed_mimes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'application/pdf' => 'pdf',
    ];

    public function store(array $file, $target_dir, $prefix = 'file', $max_bytes = 5242880, array $allowed_mimes = null)
    {
        if (empty($file) || !isset($file['error'])) {
            return ['success' => false, 'message' => 'No file was received.'];
        }

        if ((int) $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => $this->upload_error_message((int) $file['error'])];
        }

        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['success' => false, 'message' => 'The uploaded file could not be verified.'];
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size < 1 || $size > $max_bytes) {
            $limit_mb = max(1, (int) ceil($max_bytes / 1048576));
            return ['success' => false, 'message' => 'File size must be ' . $limit_mb . ' MB or less.'];
        }

        $allowed = $allowed_mimes ?: $this->allowed_mimes;
        $mime = $this->detect_mime($file['tmp_name']);
        if (!$mime || !isset($allowed[$mime])) {
            $labels = array_values(array_unique(array_map('strtoupper', array_values($allowed))));
            return [
                'success' => false,
                'message' => 'Only ' . implode(', ', $labels) . ' files are accepted.'
            ];
        }

        if (!is_dir($target_dir) && !@mkdir($target_dir, 0755, true)) {
            return ['success' => false, 'message' => 'The upload directory is unavailable.'];
        }

        $extension = $allowed[$mime];
        $filename = preg_replace('/[^a-z0-9_-]/i', '_', (string) $prefix)
            . '_' . bin2hex(random_bytes(16)) . '.' . $extension;
        $destination = rtrim($target_dir, '/\\') . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => false, 'message' => 'The uploaded file could not be saved.'];
        }

        @chmod($destination, 0640);

        return [
            'success' => true,
            'filename' => $filename,
            'mime_type' => $mime,
            'size' => $size,
            'original_name' => basename((string) ($file['name'] ?? $filename)),
        ];
    }

    private function detect_mime($path)
    {
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($path);
            if ($mime) return strtolower(trim($mime));
        }

        if (function_exists('mime_content_type')) {
            $mime = mime_content_type($path);
            if ($mime) return strtolower(trim($mime));
        }

        return null;
    }

    private function upload_error_message($code)
    {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'The uploaded file is too large.';
            case UPLOAD_ERR_PARTIAL:
                return 'The file upload was interrupted. Please try again.';
            case UPLOAD_ERR_NO_FILE:
                return 'Please choose a file to upload.';
            default:
                return 'The file could not be uploaded.';
        }
    }
}
