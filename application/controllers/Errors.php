<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Errors extends Public_Controller
{
    public function page_missing()
    {
        $this->output->set_status_header(404);
        $this->output->set_header('Cache-Control: no-store, max-age=0');

        $this->render_public('errors/page_missing', [
            'page_title' => 'Page Not Found',
        ]);
    }
}
