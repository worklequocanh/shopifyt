<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Account.php';

/**
 * Auth Controller
 * Handles authentication (login, register, logout)
 */
class AuthController extends BaseController
{
    private $accountModel;

    public function __construct()
    {
        parent::__construct();
        $this->accountModel = new Account();
    }

    /**
     * Login page and handler
     */
    public function login()
    {
        // If already logged in, redirect
        if (isLoggedIn()) {
            $role = getCurrentUserRole();
            if ($role === 'admin' || $role === 'employee') {
                $this->redirect('/admin/dashboard');
            } else {
                $this->redirect('/');
            }
        }

        if ($this->isPost()) {
            $email = $this->post('email');
            $password = $this->post('password');

            if (empty($email) || empty($password)) {
                setFlashMessage('error', 'Vui lòng nhập đầy đủ thông tin.');
            } else {
                $result = $this->accountModel->login($email, $password);
                
                if ($result['success']) {
                    $this->redirect($result['redirect']);
                } else {
                    setFlashMessage('error', $result['message']);
                }
            }
        }

        $page_title = 'Đăng nhập';
        $this->view('auth/login', ['page_title' => $page_title]);
    }

    /**
     * Register page and handler
     */
    public function register()
    {
        // If already logged in, redirect
        if (isLoggedIn()) {
            $role = getCurrentUserRole();
            if ($role === 'admin' || $role === 'employee') {
                $this->redirect('/admin/dashboard');
            } else {
                $this->redirect('/');
            }
        }

        if ($this->isPost()) {
            $name = $this->post('name');
            $email = $this->post('email');
            $password = $this->post('password');
            $confirm_password = $this->post('confirm_password');
            $phone = $this->post('phone');
            $address = $this->post('address');

            // Validation
            if (empty($name) || empty($email) || empty($password)) {
                setFlashMessage('error', 'Vui lòng nhập đầy đủ thông tin bắt buộc.');
            } elseif ($password !== $confirm_password) {
                setFlashMessage('error', 'Mật khẩu xác nhận không khớp.');
            } elseif (strlen($password) < 6) {
                setFlashMessage('error', 'Mật khẩu phải có ít nhất 6 ký tự.');
            } else {
                $result = $this->accountModel->register([
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'phone' => $phone,
                    'address' => $address
                ]);

                if ($result['success']) {
                    setFlashMessage('success', $result['message']);
                    $this->redirect('/auth/login');
                } else {
                    setFlashMessage('error', $result['message']);
                }
            }
        }

        $page_title = 'Đăng ký';
        $this->view('auth/register', ['page_title' => $page_title]);
    }

    /**
     * Logout handler
     */
    public function logout()
    {
        $role = getCurrentUserRole();
        
        session_unset();
        session_destroy();

        if ($role === 'admin' || $role === 'employee') {
            $this->redirect('/auth/login');
        } else {
            $this->redirect('/');
        }
    }
}
