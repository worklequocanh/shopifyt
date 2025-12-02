<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Account.php';

/**
 * Account Controller
 * Handles account management
 */
class AccountController extends BaseController
{
    private $accountModel;

    public function __construct()
    {
        parent::__construct();
        $this->accountModel = new Account();
    }

    /**
     * Account info page
     */
    public function info()
    {
        $this->requireRole('customer');

        $page_title = 'Thông tin tài khoản';
        $accountId = getCurrentUserId();
        
        $account = $this->accountModel->find($accountId);

        $this->view('account/info', [
            'page_title' => $page_title,
            'account' => $account,
            'current_section' => 'profile'
        ]);
    }

    /**
     * Password change page
     */
    public function password()
    {
        $this->requireRole('customer');

        $page_title = 'Đổi mật khẩu';
        $accountId = getCurrentUserId();
        
        $account = $this->accountModel->find($accountId);

        $this->view('account/info', [
            'page_title' => $page_title,
            'account' => $account,
            'current_section' => 'password'
        ]);
    }

    /**
     * Orders history page
     */
    public function orders()
    {
        $this->requireRole('customer');

        $page_title = 'Lịch sử đơn hàng';
        $accountId = getCurrentUserId();
        
        $account = $this->accountModel->find($accountId);
        
        // Get orders
        require_once __DIR__ . '/../Models/Order.php';
        $orderModel = new Order();
        $orders = $orderModel->getByUser($accountId);

        $this->view('account/info', [
            'page_title' => $page_title,
            'account' => $account,
            'orders' => $orders,
            'current_section' => 'orders'
        ]);
    }

    /**
     * Update profile (AJAX)
     */
    public function updateProfile()
    {
        $this->requireRole('customer');

        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Phương thức không được hỗ trợ.'], 405);
        }

        $accountId = getCurrentUserId();
        $name = $this->post('name');
        $phone = $this->post('phone');
        $address = $this->post('address');

        // Validation
        if (empty($name) || empty($phone) || empty($address)) {
            $this->json(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin.'], 400);
        }

        // Validate phone number
        if (!preg_match("/^[0-9]{10,11}$/", $phone)) {
            $this->json(['success' => false, 'message' => 'Số điện thoại không hợp lệ.'], 400);
        }

        $success = $this->accountModel->updateProfile($accountId, [
            'name' => $name,
            'phone' => $phone,
            'address' => $address
        ]);

        if ($success) {
            // Update session
            $_SESSION['name'] = $name;
            $this->json(['success' => true, 'message' => 'Cập nhật thông tin cá nhân thành công.']);
        } else {
            $this->json(['success' => false, 'message' => 'Lỗi máy chủ. Vui lòng thử lại sau.'], 500);
        }
    }

    /**
     * Change password (AJAX)
     */
    public function changePassword()
    {
        $this->requireRole('customer');

        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Phương thức không được hỗ trợ.'], 405);
        }

        $accountId = getCurrentUserId();
        $oldPassword = $this->post('old_password');
        $newPassword = $this->post('new_password');
        $confirmPassword = $this->post('confirm_password');

        // Validation
        if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
            $this->json(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin.'], 400);
        }

        if ($newPassword !== $confirmPassword) {
            $this->json(['success' => false, 'message' => 'Mật khẩu mới không khớp.'], 400);
        }

        if (strlen($newPassword) < 6) {
            $this->json(['success' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự.'], 400);
        }

        $result = $this->accountModel->changePassword($accountId, $oldPassword, $newPassword);
        $this->json($result, $result['success'] ? 200 : 400);
    }
}
