<?php

namespace Admin;

use BaseController;
use Voucher;
use Permission;

require_once __DIR__ . '/../BaseController.php';
require_once __DIR__ . '/../../Models/Voucher.php';

class AdminVoucherController extends BaseController
{
    private $voucherModel;

    public function __construct()
    {
        parent::__construct();
        
        // Require admin or employee role
        $this->requireAnyRole(['admin', 'employee']);
        $this->requirePermission(Permission::MANAGE_VOUCHERS);
        
        $this->voucherModel = new Voucher();
    }

    public function index()
    {
        $vouchers = $this->voucherModel->getAll();

        $data = [
            'page_title' => 'Quản lý mã giảm giá',
            'vouchers' => $vouchers,
            'current_role' => $this->getRole()
        ];

        $this->view('admin/vouchers/index', $data, 'admin');
    }

    public function store()
    {
        if ($this->isPost()) {
            $code = trim($this->post('code'));
            
            if ($this->voucherModel->exists($code)) {
                setFlashMessage('error', 'Mã giảm giá đã tồn tại');
            } else {
                $data = [
                    'code' => strtoupper($code),
                    'name' => trim($this->post('name')),
                    'description' => trim($this->post('description')),
                    'discount_type' => $this->post('discount_type'),
                    'discount_value' => floatval($this->post('discount_value')),
                    'min_order_value' => floatval($this->post('min_order_value')),
                    'max_discount' => $this->post('max_discount') ? floatval($this->post('max_discount')) : null,
                    'usage_limit' => $this->post('usage_limit') ? intval($this->post('usage_limit')) : null,
                    'start_date' => $this->post('start_date'),
                    'end_date' => $this->post('end_date'),
                    'is_active' => 1,
                    'created_by' => $_SESSION['id'] ?? null
                ];

                if ($this->voucherModel->create($data)) {
                    setFlashMessage('success', 'Thêm mã giảm giá thành công');
                } else {
                    setFlashMessage('error', 'Lỗi khi thêm mã giảm giá');
                }
            }
        }
        $this->redirect('/admin/vouchers');
    }

    public function update()
    {
        if ($this->isPost()) {
            $id = $this->post('id');
            $code = trim($this->post('code'));
            
            if ($this->voucherModel->exists($code, $id)) {
                setFlashMessage('error', 'Mã giảm giá đã tồn tại');
            } else {
                $data = [
                    'code' => strtoupper($code),
                    'name' => trim($this->post('name')),
                    'description' => trim($this->post('description')),
                    'discount_type' => $this->post('discount_type'),
                    'discount_value' => floatval($this->post('discount_value')),
                    'min_order_value' => floatval($this->post('min_order_value')),
                    'max_discount' => $this->post('max_discount') ? floatval($this->post('max_discount')) : null,
                    'usage_limit' => $this->post('usage_limit') ? intval($this->post('usage_limit')) : null,
                    'start_date' => $this->post('start_date'),
                    'end_date' => $this->post('end_date'),
                    'is_active' => $this->post('is_active') ? 1 : 0
                ];

                if ($this->voucherModel->update($id, $data)) {
                    setFlashMessage('success', 'Cập nhật mã giảm giá thành công');
                } else {
                    setFlashMessage('error', 'Lỗi khi cập nhật mã giảm giá');
                }
            }
        }
        $this->redirect('/admin/vouchers');
    }

    public function delete($id)
    {
        if ($this->voucherModel->delete($id)) {
            setFlashMessage('success', 'Xóa mã giảm giá thành công');
        } else {
            setFlashMessage('error', 'Lỗi khi xóa mã giảm giá');
        }
        $this->redirect('/admin/vouchers');
    }
}
