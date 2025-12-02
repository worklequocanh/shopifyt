<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Cart.php';

/**
 * Cart Controller
 * Handles shopping cart operations
 */
class CartController extends BaseController
{
    private $cartModel;

    public function __construct()
    {
        parent::__construct();
        $this->cartModel = new Cart();
    }

    /**
     * Shopping cart page
     */
    public function index()
    {
        $this->requireAuth();
        
        $page_title = 'Giỏ hàng';
        $accountId = getCurrentUserId();
        
        // Get cart data
        $cartData = $this->cartModel->getCartWithDetails($accountId);
        
        // Validate stock for each item
        foreach ($cartData['items'] as &$item) {
            $item['is_out_of_stock'] = $item['quantity'] > $item['stock'];
            $item['selected'] = true; // Default: all items selected
        }
        
        $this->view('cart/index', [
            'page_title' => $page_title,
            'cart_items' => $cartData['items'],
            'total_amount' => $cartData['total_amount']
        ]);
    }

    /**
     * Add item to cart (AJAX)
     */
    public function add()
    {
        $this->requireRole('customer');

        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Phương thức không được hỗ trợ.'], 405);
        }

        $accountId = getCurrentUserId();
        $productId = (int)$this->post('product_id', 0);
        $quantity = (int)$this->post('quantity', 1);

        if (!$productId || !$quantity || $quantity <= 0) {
            $this->json(['success' => false, 'message' => 'Dữ liệu sản phẩm không hợp lệ.'], 400);
        }

        $result = $this->cartModel->addItem($accountId, $productId, $quantity);
        $this->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Update cart item quantity (AJAX)
     * Update cart item quantity
     */
    public function update()
    {
        $this->requireAuth();
        
        $productId = (int)$this->post('product_id');
        $quantity = (int)$this->post('quantity');
        $accountId = getCurrentUserId();
        
        if ($quantity < 1) {
            $this->json(['success' => false, 'message' => 'Số lượng phải lớn hơn 0'], 400);
            return;
        }
        
        // Check current stock
        require_once __DIR__ . '/../Models/Product.php';
        $productModel = new Product();
        $product = $productModel->find($productId);
        
        if (!$product) {
            $this->json(['success' => false, 'message' => 'Sản phẩm không tồn tại'], 404);
            return;
        }
        
        if ($quantity > $product['stock']) {
            $this->json([
                'success' => false, 
                'message' => "Kho hàng chỉ còn {$product['stock']} sản phẩm. Không thể cập nhật số lượng."
            ], 400);
            return;
        }
        
        $result = $this->cartModel->updateItem($accountId, $productId, $quantity);
        $this->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Remove item from cart (AJAX)
     */
    public function remove()
    {
        $this->requireRole('customer');

        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Phương thức không được hỗ trợ.'], 405);
        }

        $accountId = getCurrentUserId();
        $productId = (int)$this->post('product_id', 0);

        if (!$productId) {
            $this->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ.'], 400);
        }

        $result = $this->cartModel->removeItem($accountId, $productId);
        $this->json($result, $result['success'] ? 200 : 400);
    }
}
