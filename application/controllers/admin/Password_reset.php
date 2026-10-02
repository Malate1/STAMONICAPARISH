<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Password-reset queue workflow was intentionally removed.
 * Password resets continue to use Administrator -> Accounts -> Reset Password.
 */
class Password_reset extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_ADMIN]);
        show_404();
    }
}
