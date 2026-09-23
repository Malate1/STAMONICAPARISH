<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Base_Controller
 * Every controller in the app should extend one of the controllers below
 * rather than CI_Controller directly.
 */
class Base_Controller extends CI_Controller
{
    /** @var array|null Logged in user row (users JOIN roles), or null */
    public $current_user = null;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
        $this->_load_current_user();
    }

    private function _load_current_user()
    {
        $user_id = $this->session->userdata('user_id');
        if ($user_id) {
            $this->current_user = $this->User_model->get_with_role($user_id);
        }
    }

    protected function is_logged_in()
    {
        return !empty($this->current_user) && $this->current_user['status'] === 'active';
    }

    protected function flash_success($msg)
    {
        $this->session->set_flashdata('toastr_success', $msg);
    }

    protected function flash_error($msg)
    {
        $this->session->set_flashdata('toastr_error', $msg);
    }

    protected function flash_info($msg)
    {
        $this->session->set_flashdata('toastr_info', $msg);
    }

    /**
     * Render a view inside the public site layout.
     */
    protected function render_public($view, $data = [])
    {
        $data['current_user'] = $this->current_user;
        $data['body_view'] = $view;
        $data['body_data'] = $data;
        $this->load->view('layouts/public', $data);
    }

    /**
     * Render a view inside an authenticated app layout (parishioner/admin/staff/priest).
     */
    protected function render_app($view, $data = [], $layout = 'layouts/app')
    {
        $data['current_user'] = $this->current_user;
        $data['body_view'] = $view;
        $this->load->view($layout, $data);
    }

    /**
     * JSON response shortcut.
     */
    protected function json($payload, $status_code = 200)
    {
        $this->output
            ->set_status_header($status_code)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($payload));
    }

    protected function log_activity($action, $module = null, $description = null)
    {
        $this->load->model('Audit_model');
        $this->Audit_model->log(
            $this->current_user['id'] ?? null,
            $action,
            $module,
            $description,
            $this->input->ip_address()
        );
    }
}

/**
 * Public_Controller
 * For public-facing pages. No login required. Injects $current_user (may be null)
 * so the layout can show Login/Register vs. My Account.
 */
class Public_Controller extends Base_Controller
{
    public function __construct()
    {
        parent::__construct();
    }
}

/**
 * Auth_Controller
 * Requires the visitor to be logged in, regardless of role.
 */
class Auth_Controller extends Base_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->is_logged_in()) {
            $this->session->set_userdata('redirect_after_login', current_url());
            $this->flash_info('Please log in to continue.');
            redirect('login');
        }
    }
}

/**
 * Role_Controller
 * Abstract-ish controller: subclasses set $this->allowed_roles in their own
 * constructor BEFORE calling parent::__construct(), OR pass roles to guard().
 */
class Role_Controller extends Auth_Controller
{
    protected $allowed_roles = [];

    protected function guard(array $roles)
    {
        $this->allowed_roles = $roles;
        if (!in_array((int) $this->current_user['role_id'], $roles, true)) {
            show_error('You do not have permission to access this page.', 403, 'Access Denied');
        }
    }
}
