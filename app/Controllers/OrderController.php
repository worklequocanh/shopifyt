<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Order.php';
require_once __DIR__ . '/../Models/Cart.php';

/**
 * Order Controller
 * Handles checkout and order operations
 */
class OrderController extends BaseController
{
    private $orderModel;
    private $cartModel;

    public function __construct()
    {
        parent::__construct();
        $this->orderModel = new Order();
        $this->cartModel = new Cart();
    }

    /**
     * Checkout page
     */
    public function checkout()
    {
        $this->requireRole('customer');

        $accountId = getCurrentUserId();
        
        // Handle selected items from cart
        if ($this->isPost() && $this->post('selected_items')) {
            $selectedIds = json_decode($this->post('selected_items'), true);
            $_SESSION['selected_cart_items'] = $selectedIds ?: [];
        }

        if ($this->isPost() && !$this->post('selected_items')) {
            // Processing order submission
            $shippingData = [
                'customer_name' => $this->post('name'),
                'shipping_phone' => $this->post('phone'),
                'shipping_address' => $this->post('address'),
                'note' => $this->post('note', '')
            ];

            // Validate
            if (empty($shippingData['customer_name']) || empty($shippingData['shipping_phone']) || empty($shippingData['shipping_address'])) {
                setFlashMessage('error', 'Vui lòng điền đầy đủ thông tin giao hàng.');
                $this->redirect('/checkout');
                return;
            }

            // Get selected items
            $selectedIds = $_SESSION['selected_cart_items'] ?? [];
            
            // Process checkout with selected items
            $result = $this->orderModel->createFromCart($accountId, $shippingData, $selectedIds);

            if ($result['success']) {
                // Clear selected items from session
                unset($_SESSION['selected_cart_items']);
                setFlashMessage('success', 'Đặt hàng thành công!');
                $this->redirect('/checkout/success?order_id=' . $result['order_id']);
            } else {
                setFlashMessage('error', $result['message']);
            }
        }

        $page_title = 'Thanh toán';
        
        // Get selected items for checkout
        $selectedIds = $_SESSION['selected_cart_items'] ?? [];
        
        // Get cart data
        $cartData = $this->cartModel->getCartWithDetails($accountId);
        
        // Filter only selected items
        if (!empty($selectedIds)) {
            $cartData['items'] = array_filter($cartData['items'], function($item) use ($selectedIds) {
                return in_array($item['id'], $selectedIds);
            });
            
            // Recalculate total for selected items only
            $cartData['total_amount'] = array_sum(array_column($cartData['items'], 'subtotal'));
        }

        // Check if cart is empty
        if (empty($cartData['items'])) {
            setFlashMessage('error', 'Vui lòng chọn sản phẩm để thanh toán.');
            $this->redirect('/cart');
            return;
        }

        // Get account info for shipping
        require_once __DIR__ . '/../Models/Account.php';
        $accountModel = new Account();
        $account = $accountModel->find($accountId);

        $this->view('checkout/index', [
            'page_title' => $page_title,
            'cart_items' => $cartData['items'],
            'total_amount' => $cartData['total_amount'],
            'account' => $account
        ]);
    }

    /**
     * Checkout success page
     */
    public function success()
    {
        $this->requireRole('customer');

        $orderId = (int)$this->get('order_id');
        $accountId = getCurrentUserId();

        if (!$orderId) {
            $this->redirect('/');
        }

        $orderSummary = $this->orderModel->getOrderSummary($orderId, $accountId);

        if (!$orderSummary) {
            setFlashMessage('error', 'Không tìm thấy đơn hàng.');
            $this->redirect('/');
        }

        $page_title = 'Đặt hàng thành công';

        $this->view('checkout/success', [
            'page_title' => $page_title,
            'order_summary' => $orderSummary
        ]);
    }

    /**
     * Order detail page
     */
    public function detail($id)
    {
        $this->requireRole('customer');

        $orderId = (int)$id;
        $accountId = getCurrentUserId();

        if (!$orderId) {
            setFlashMessage('error', 'ID đơn hàng không hợp lệ.');
            $this->redirect('/account/orders');
            return;
        }

        // Get order with items
        $order = $this->orderModel->getOrderById($orderId, $accountId);

        if (!$order) {
            setFlashMessage('error', 'Không tìm thấy đơn hàng hoặc bạn không có quyền xem đơn hàng này.');
            $this->redirect('/account/orders');
            return;
        }

        $page_title = 'Chi tiết đơn hàng #' . str_pad($order['id'], 6, '0', STR_PAD_LEFT);

        $this->view('order/detail', [
            'page_title' => $page_title,
            'order' => $order
        ]);
    }
}
