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
        // If already logged in, redirect based on role
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

            $result = $this->accountModel->login($email, $password);

            if ($result['success']) {
                // Check if email is verified (only for customers)
                if ($result['user']['role'] === 'customer' && !$result['user']['email_verified']) {
                    // Store email in session for resend functionality
                    $_SESSION['unverified_email'] = $email;
                    $_SESSION['unverified_user_id'] = $result['user']['id'];
                    
                    setFlashMessage('warning', 'Email chưa được xác nhận. Vui lòng kiểm tra email để xác nhận tài khoản.');
                    $this->redirect('/auth/verify-notice');
                    return;
                }
                
                // Login successful and verified
                $_SESSION['id'] = $result['user']['id'];
                $_SESSION['name'] = $result['user']['name'];
                $_SESSION['role'] = $result['user']['role'];
                $_SESSION['email'] = $result['user']['email'];

                // Redirect based on role
                if ($result['user']['role'] === 'admin' || $result['user']['role'] === 'employee') {
                    $this->redirect('/admin/dashboard');
                } else {
                    $this->redirect('/product');
                }
            } else {
                setFlashMessage('error', $result['message']);
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
     * Email verification handler
     */
    public function verify($token = null)
    {
        // Get token from route parameter or GET
        if (!$token) {
            $token = $_GET['token'] ?? '';
        }
        
        if (empty($token)) {
            setFlashMessage('error', 'Token xác nhận không hợp lệ.');
            $this->redirect('/auth/login');
            return;
        }
        
        // Verify email
        $result = $this->accountModel->verifyEmail($token);
        
        if ($result['success']) {
            $page_title = 'Xác nhận thành công';
            $this->view('auth/verify_success', [
                'page_title' => $page_title,
                'message' => $result['message']
            ]);
        } else {
            $page_title = 'Xác nhận thất bại';
            $this->view('auth/verify_failed', [
                'page_title' => $page_title,
                'message' => $result['message']
            ]);
        }
    }

    /**
     * Show verification notice for unverified accounts
     */
    public function verifyNotice()
    {
        $page_title = 'Email chưa xác nhận';
        $email = $_SESSION['unverified_email'] ?? null;
        
        $this->view('auth/verify_notice', [
            'page_title' => $page_title,
            'email' => $email
        ]);
    }
    
    /**
     * Resend verification email
     */
    public function resendVerification()
    {
        if (!$this->isPost()) {
            $this->redirect('/auth/login');
            return;
        }
        
        $userId = $_SESSION['unverified_user_id'] ?? null;
        $email = $_SESSION['unverified_email'] ?? $this->post('email');
        
        if (!$email) {
            setFlashMessage('error', 'Email không hợp lệ.');
            $this->redirect('/auth/login');
            return;
        }
        
        // Get user
        $user = $this->accountModel->findWhere('email', $email);
        
        if (!$user) {
            setFlashMessage('error', 'Tài khoản không tồn tại.');
            $this->redirect('/auth/login');
            return;
        }
        
        if ($user['email_verified']) {
            setFlashMessage('info', 'Email đã được xác nhận. Bạn có thể đăng nhập.');
            $this->redirect('/auth/login');
            return;
        }
        
        // Generate new verification token
        require_once __DIR__ . '/../Helpers/email_helpers.php';
        $token = generateToken(32);
        $hashedToken = hashToken($token);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Update user with new token
        $this->accountModel->update($user['id'], [
            'verification_token' => $hashedToken,
            'verification_expires' => $expiresAt
        ]);
        
        // Send verification email
        try {
            $emailService = getEmailService();
            $emailSent = $emailService->sendVerification($user, $token);
            
            if ($emailSent) {
                setFlashMessage('success', 'Email xác nhận đã được gửi lại. Vui lòng kiểm tra hộp thư.');
            } else {
                setFlashMessage('warning', 'Có lỗi khi gửi email. Vui lòng thử lại sau.');
            }
        } catch (Exception $e) {
            error_log("Resend verification failed: " . $e->getMessage());
            setFlashMessage('error', 'Không thể gửi email xác nhận.');
        }
        
        $this->redirect('/auth/verify-notice');
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
