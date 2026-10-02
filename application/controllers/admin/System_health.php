<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class System_health extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_ADMIN]);
    }

    public function index()
    {
        $checks = [];
        $add = function ($key, $label, $ok, $detail, $severity = 'required') use (&$checks) {
            $checks[] = [
                'key' => $key,
                'label' => $label,
                'ok' => (bool)$ok,
                'detail' => $detail,
                'severity' => $severity,
            ];
        };

        $php_ok = PHP_MAJOR_VERSION === 8
            && PHP_MINOR_VERSION === 1
            && version_compare(PHP_VERSION, '8.1.10', '>=');
        $add('php', 'PHP 8.1 runtime', $php_ok, 'Detected PHP ' . PHP_VERSION . '. Parish Connect targets PHP 8.1.10 or newer within the PHP 8.1 release line.');

        $encryption_key = (string)$this->config->item('encryption_key');
        $add('encryption', 'Application encryption key', strlen($encryption_key) >= 32, strlen($encryption_key) >= 32 ? 'A non-empty application key is configured.' : 'Configure STAMONICA_ENCRYPTION_KEY or application/config/secrets.php before production use.');
        $add('csrf', 'CSRF protection', (bool)$this->config->item('csrf_protection'), $this->config->item('csrf_protection') ? 'POST forms and AJAX requests are protected.' : 'CSRF protection is disabled.');
        $add('httponly', 'HttpOnly cookies', (bool)$this->config->item('cookie_httponly'), $this->config->item('cookie_httponly') ? 'Session cookies are not exposed to normal browser JavaScript.' : 'HttpOnly cookies are disabled.');

        $production = defined('ENVIRONMENT') && ENVIRONMENT === 'production';
        $secure_cookie_ok = !$production || (bool)$this->config->item('cookie_secure');
        $add('secure-cookie', 'HTTPS-only production cookies', $secure_cookie_ok, $production ? ($secure_cookie_ok ? 'Secure cookies are enabled for production.' : 'Production cookies are not restricted to HTTPS.') : 'Development environment detected; Secure cookies are required automatically in production.');

        foreach ([
            'application/logs/' => APPPATH . 'logs/',
            UPLOAD_DOCUMENTS => FCPATH . UPLOAD_DOCUMENTS,
            UPLOAD_PAYMENTS => FCPATH . UPLOAD_PAYMENTS,
            UPLOAD_ARCHIVE => FCPATH . UPLOAD_ARCHIVE,
        ] as $label => $path) {
            $ok = is_dir($path) && is_writable($path);
            $add('writable-' . md5($label), 'Writable: ' . $label, $ok, $ok ? 'Directory exists and PHP can write to it.' : 'Directory is missing or not writable by PHP.');
        }

        $fileinfo = class_exists('finfo') || function_exists('mime_content_type');
        $add('fileinfo', 'Server-side MIME inspection', $fileinfo, $fileinfo ? 'Secure uploads can verify actual file MIME types.' : 'Enable PHP fileinfo or mime_content_type support.');

        foreach ([UPLOAD_DOCUMENTS, UPLOAD_PAYMENTS, UPLOAD_ARCHIVE] as $relative) {
            $deny = is_file(FCPATH . $relative . '.htaccess');
            $add('deny-' . md5($relative), 'Private web access: ' . rtrim($relative, '/'), $deny, $deny ? 'A deny-all .htaccess file is present.' : 'Missing deny-all .htaccess. Private files could be directly reachable on Apache.', 'security');
        }

        // The experimental queued password-reset/Resend workflow was reverted
        // by project decision. Flag old database artifacts if that temporary
        // migration had been applied before the rollback.
        $reset_artifacts_absent = !$this->db->table_exists('password_reset_requests')
            && !$this->db->field_exists('must_change_password', 'users')
            && $this->db->where_in('setting_key', ['resend_from_name','resend_from_email'])
                ->count_all_results('system_settings') === 0;
        $add(
            'removed-reset-workflow',
            'Removed reset-email workflow cleanup',
            $reset_artifacts_absent,
            $reset_artifacts_absent
                ? 'No deprecated password-reset queue or Resend settings remain in the database.'
                : 'Old experimental reset/Resend database fields are still present. Run database/migrations/20260925_revert_password_reset_workflow.sql once.',
            'migration'
        );

        $migrations = [
            [
                'file' => 'database/migrations/20260925_payment_integrity.sql',
                'label' => 'Payment integrity',
                'ready' => $this->index_exists('payments', 'uq_payment_payable') && $this->index_exists('payments', 'uq_payment_receipt_no'),
                'detail' => 'One payment row per payable + unique official receipt numbers.',
            ],
            [
                'file' => 'database/migrations/20260925_sacramental_record_workflow.sql',
                'label' => 'Sacramental record workflow',
                'ready' => $this->db->field_exists('record_status', 'sacramental_records')
                    && $this->db->field_exists('source_type', 'sacramental_records')
                    && $this->db->field_exists('verified_by', 'sacramental_records'),
                'detail' => 'Draft/Verified workflow, source tracking and two-person verification.',
            ],
            [
                'file' => 'database/migrations/20260925_legacy_archive.sql',
                'label' => 'Legacy archive',
                'ready' => $this->db->table_exists('archive_batches')
                    && $this->db->table_exists('archive_pages')
                    && $this->db->field_exists('archive_batch_id', 'sacramental_records'),
                'detail' => 'Archive batches, private register scans and progressive historical digitization.',
            ],
        ];

        $data['checks'] = $checks;
        $data['migrations'] = $migrations;
        $data['passed'] = count(array_filter($checks, fn($check) => !empty($check['ok'])));
        $data['total'] = count($checks);
        $data['migration_ready'] = count(array_filter($migrations, fn($migration) => !empty($migration['ready'])));
        $data['environment'] = defined('ENVIRONMENT') ? ENVIRONMENT : 'unknown';
        $data['php_version'] = PHP_VERSION;

        $this->render_app('admin/system_health', $data, 'layouts/app_admin');
    }

    private function index_exists($table, $index)
    {
        $row = $this->db->query(
            'SELECT COUNT(*) AS total FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?',
            [$table, $index]
        )->row_array();
        return !empty($row['total']);
    }
}
