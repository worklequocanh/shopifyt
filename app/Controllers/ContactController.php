<?php

require_once __DIR__ . '/BaseController.php';

/**
 * Contact Controller (extending Home for now)
 */
class ContactController extends BaseController
{
    public function index()
    {
        $page_title = 'Liên hệ';
        
        $this->view('contact/index', [
            'page_title' => $page_title
        ]);
    }
}
