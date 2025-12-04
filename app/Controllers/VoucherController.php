<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Voucher.php';

/**
 * Voucher Controller (API)
 * Handles voucher validation for checkout
 */
class VoucherController extends BaseController
{
    private $voucherModel;

    public function __construct()
    {
        parent::__construct();
        $this->voucherModel = new Voucher();
    }

    /**
     * Validate voucher code (AJAX)
     */
    public function validate()
    {
        header('Content-Type: application/json');
        
        if (!$this->isPost()) {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $code = strtoupper(trim($this->post('code', '')));
        $orderAmount = floatval($this->post('order_amount', 0));

        if (empty($code)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mã giảm giá']);
            return;
        }

        if ($orderAmount <= 0) {
            echo json_encode(['success' => false, 'message' => 'Giá trị đơn hàng không hợp lệ']);
            return;
        }

        $validation = $this->voucherModel->validate($code, $orderAmount);

        if (!$validation['valid']) {
            echo json_encode(['success' => false, 'message' => $validation['message']]);
            return;
        }

        $voucher = $validation['voucher'];
        $discountAmount = $this->voucherModel->calculateDiscount($voucher, $orderAmount);
        $finalAmount = $orderAmount - $discountAmount;

        echo json_encode([
            'success' => true,
            'message' => 'Áp dụng mã giảm giá thành công!',
            'voucher' => [
                'id' => $voucher['id'],
                'code' => $voucher['code'],
                'name' => $voucher['name'],
                'discount_type' => $voucher['discount_type'],
                'discount_value' => $voucher['discount_value'],
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount
            ]
        ]);
    }
}
