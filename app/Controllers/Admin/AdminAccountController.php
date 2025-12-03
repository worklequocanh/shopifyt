<?php

namespace Admin;

use BaseController;
use Account;
use Permission;

require_once __DIR__ . '/../BaseController.php';
require_once __DIR__ . '/../../Models/Account.php';

class AdminAccountController extends BaseController
{
    private $accountModel;

    public function __construct()
    {
        parent::__construct();
        
        // Require admin role for account management
        $this->requireRole('admin');
        $this->requirePermission(Permission::MANAGE_ACCOUNTS);
        
        $this->accountModel = new Account();
    }

    public function index()
    {
        $role = $this->get('role', 'all');
        $search = $this->get('search');
        
        $accounts = $this->accountModel->getAllAccounts($role, $search);

        $data = [
            'page_title' => 'Quản lý tài khoản',
            'accounts' => $accounts,
            'filter_role' => $role,
            'search' => $search,
            'current_role' => $this->getRole(),
            'current_user_id' => $_SESSION['id'] ?? 0
        ];

        $this->view('admin/accounts/index', $data, 'admin');
    }

    public function store()
    {
        if ($this->isPost()) {
            $email = trim($this->post('email'));
            $password = $this->post('password');
            
            if ($this->accountModel->emailExists($email)) {
                setFlashMessage('error', 'Email đã tồn tại');
            } else {
                $data = [
                    'name' => trim($this->post('name')),
                    'email' => $email,
                    'password' => $password,
                    'phone' => $this->post('phone'),
                    'address' => $this->post('address'),
                    'role' => $this->post('role'),
                    'position' => $this->post('position'),
                    'salary' => $this->post('salary') ? floatval($this->post('salary')) : null,
                    'is_active' => 1
                ];

                if ($this->accountModel->createAccount($data)) {
                    setFlashMessage('success', 'Thêm tài khoản thành công');
                } else {
                    setFlashMessage('error', 'Lỗi khi thêm tài khoản');
                }
            }
        }
        $this->redirect('/admin/accounts');
    }

    public function update()
    {
        if ($this->isPost()) {
            $id = $this->post('id');
            $email = trim($this->post('email'));
            $currentUserId = $_SESSION['id'] ?? 0;
            
            // Get current account data to check role
            $currentAccount = $this->accountModel->find($id);
            if (!$currentAccount) {
                setFlashMessage('error', 'Tài khoản không tồn tại');
                $this->redirect('/admin/accounts');
                return;
            }

            if ($this->accountModel->emailExists($email, $id)) {
                setFlashMessage('error', 'Email đã tồn tại');
            } else {
                $role = $this->post('role');
                
                // Prevent self-role update
                if ($id == $currentUserId) {
                    $role = $currentAccount['role']; // Force keep current role
                }

                // Validation for Employee role
                $position = $this->post('position');
                $salary = $this->post('salary');

                if ($role === 'employee') {
                    if (empty($position) || empty($salary)) {
                        setFlashMessage('error', 'Vui lòng nhập đầy đủ chức vụ và lương cho nhân viên');
                        $this->redirect('/admin/accounts');
                        return;
                    }
                } else {
                    // Clear position and salary if not employee
                    $position = null;
                    $salary = null;
                }

                $data = [
                    'name' => trim($this->post('name')),
                    'email' => $email,
                    'phone' => $this->post('phone'),
                    'address' => $this->post('address'),
                    'role' => $role,
                    'position' => $position,
                    'salary' => $salary ? floatval($salary) : null,
                    'is_active' => $this->post('is_active') ? 1 : 0
                ];

                // Prevent self-deactivation
                if ($id == $currentUserId) {
                    $data['is_active'] = 1;
                }

                if (!empty($this->post('new_password'))) {
                    $data['password'] = $this->post('new_password');
                }

                if ($this->accountModel->updateAccount($id, $data)) {
                    setFlashMessage('success', 'Cập nhật tài khoản thành công');
                } else {
                    setFlashMessage('error', 'Lỗi khi cập nhật tài khoản');
                }
            }
        }
        $this->redirect('/admin/accounts');
    }

    public function delete($id)
    {
        if ($id == $_SESSION['id']) {
            setFlashMessage('error', 'Không thể xóa tài khoản của chính mình');
        } else {
            if ($this->accountModel->delete($id)) {
                setFlashMessage('success', 'Xóa tài khoản thành công');
            } else {
                setFlashMessage('error', 'Lỗi khi xóa tài khoản');
            }
        }
        $this->redirect('/admin/accounts');
    }

    public function toggleActive($id)
    {
        if ($id == $_SESSION['id']) {
            setFlashMessage('error', 'Không thể khóa tài khoản của chính mình');
        } else {
            if ($this->accountModel->toggleActive($id)) {
                setFlashMessage('success', 'Thay đổi trạng thái thành công');
            } else {
                setFlashMessage('error', 'Lỗi khi thay đổi trạng thái');
            }
        }
        $this->redirect('/admin/accounts');
    }
}
