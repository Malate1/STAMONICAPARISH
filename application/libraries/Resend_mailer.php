<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * The Resend/password-reset email workflow was intentionally removed.
 * This compatibility stub prevents accidental use by stale code while the
 * project continues to use the existing administrator-managed password reset.
 */
class Resend_mailer
{
    public function configured()
    {
        return false;
    }

    public function send($to, $subject, $html, $text = null)
    {
        return [
            'success' => false,
            'message' => 'Resend email delivery is not enabled in this project.',
        ];
    }
}
