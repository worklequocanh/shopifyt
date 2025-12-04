<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Account.php';

/**
 * Password Reset Controller
 * Handles forgot password and reset password functionality
 */
class PasswordResetController extends BaseController
{
    private Account $accountModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->accountModel = new Account();
    }
    
    /**
     * Show forgot password form
     */
    public function requestReset()
    {
        if ($this->isPost()) {
            $email = $this->post('email');
            
            if (empty($email)) {
                return $this->json(['success' => false, 'message' => 'Vui lòng nhập email.']);
            }
            
            $result = $this->accountModel->requestPasswordReset($email);
            return $this->json($result);
        }
        
        $page_title = 'Quên mật khẩu';
        $this->view('auth/forgot_password', ['page_title' => $page_title]);
    }
    
    /**
     * Show reset password form
     */
    public function showResetForm($token = null)
    {
        // Get token from route parameter or GET
        if (!$token) {
            $token = $_GET['token'] ?? '';
        }
        
        if (empty($token)) {
            setFlashMessage('error', 'Token không hợp lệ.');
            $this->redirect('/auth/login');
            return;
        }
        
        $page_title = 'Đặt lại mật khẩu';
        $this->view('auth/reset_password', [
            'page_title' => $page_title,
            'token' => $token
        ]);
    }
    
    /**
     * Process password reset
     */
    public function resetPassword()
    {
        if (!$this->isPost()) {
            $this->redirect('/auth/login');
            return;
        }
        
        $token = $this->post('token');
        $password = $this->post('password');
        $confirmPassword = $this->post('confirm_password');
        
        if (empty($token) || empty($password)) {
            return $this->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
        }
        
        if ($password !== $confirmPassword) {
            return $this->json(['success' => false, 'message' => 'Mật khẩu xác nhận không khớp.']);
        }
        
        if (strlen($password) < 6) {
            return $this->json(['success' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự.']);
        }
        
        $result = $this->accountModel->resetPassword($token, $password);
        return $this->json($result);
    }
}
