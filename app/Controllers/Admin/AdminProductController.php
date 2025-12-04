<?php

namespace Admin;

require_once __DIR__ . '/../BaseController.php';

use BaseController;
use Database;
use Permission;
use Product;
use Category;
use PDO;

/**
 * Admin Products Controller
 * Manage products - CRUD operations
 */
class AdminProductController extends BaseController
{
    private $productModel;
    private $categoryModel;

    public function __construct()
    {
        parent::__construct();
        
        // Require permission to manage products
        $this->requireAnyRole(['admin', 'employee']);
        $this->requirePermission(Permission::MANAGE_PRODUCTS);
        
        // Load models
        require_once __DIR__ . '/../../Models/Product.php';
        require_once __DIR__ . '/../../Models/Category.php';
        
        $this->productModel = new Product();
        $this->categoryModel = new Category();
    }

    /**
     * Product list
     */
    /**
     * Product list
     */
    public function index()
    {
        $page = max(1, (int)$this->get('page', 1));
        $categoryId = $this->get('category');
        $categoryId = ($categoryId !== '' && $categoryId !== null) ? (int)$categoryId : null;
        $search = $this->get('search', '');
        
        // Status: 1=Active, 0=Hidden, null=All
        $status = $this->get('status');
        if ($status === 'all') {
            $status = null;
        } else {
            $status = (int)($status ?? 1);
        }

        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        // Get products
        if (!empty($search)) {
            $products = $this->productModel->search($search, $perPage, $offset, $status);
            $totalProducts = $this->productModel->countSearch($search, $status);
        } else {
            $products = $this->productModel->getAll($perPage, $offset, $categoryId, $status);
            $totalProducts = $this->productModel->countAll($categoryId, $status);
        }

        $totalPages = ceil($totalProducts / $perPage);
        $categories = $this->categoryModel->getAll();

        $this->view('admin/products/index', [
            'page_title' => 'Quản lý sản phẩm',
            'products' => $products,
            'categories' => $categories,
            'totalPages' => $totalPages,
            'current_page' => $page,
            'category_id' => $categoryId,
            'search' => $search,
            'status' => $status,
            'current_role' => $this->getRole()
        ], 'admin');
    }

    /**
     * Create product form
     */
    public function create()
    {
        if ($this->isPost()) {
            return $this->processCreate();
        }

        $categories = $this->categoryModel->getAll();
        
        $this->view('admin/products/create', [
            'page_title' => 'Thêm sản phẩm mới',
            'categories' => $categories,
            'current_role' => $this->getRole()
        ], 'admin');
    }

    /**
     * Process create product
     */
    private function processCreate()
    {
        $data = [
            'name' => $this->post('name'),
            'description' => $this->post('description'),
            'price' => (float)$this->post('price'),
            'stock' => (int)$this->post('stock'),
            'category_id' => (int)$this->post('category_id'),
            'is_active' => $this->post('is_active', 1) ? 1 : 0,
            'is_featured' => $this->post('is_featured', 0) ? 1 : 0,
        ];

        // Validate
        if (empty($data['name']) || empty($data['price']) || empty($data['category_id'])) {
            setFlashMessage('error', 'Vui lòng điền đầy đủ thông tin bắt buộc.');
            $this->redirect('/admin/products/create');
            return;
        }

        // Create product
        $stmt = $this->pdo->prepare("
            INSERT INTO products (name, description, price, stock, category_id, is_active, is_featured, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        if ($stmt->execute([
            $data['name'],
            $data['description'],
            $data['price'],
            $data['stock'],
            $data['category_id'],
            $data['is_active'],
            $data['is_featured']
        ])) {
            $productId = $this->pdo->lastInsertId();
            
            // Handle image upload
            $this->handleImageUpload($productId);
            
            setFlashMessage('success', 'Thêm sản phẩm thành công!');
            $this->redirect('/admin/products');
        } else {
            setFlashMessage('error', 'Có lỗi xảy ra khi thêm sản phẩm.');
            $this->redirect('/admin/products/create');
        }
    }

    /**
     * Edit product
     */
    public function edit($id)
    {
        $product = $this->productModel->find($id);
        
        if (!$product) {
            setFlashMessage('error', 'Sản phẩm không tồn tại.');
            $this->redirect('/admin/products');
            return;
        }

        if ($this->isPost()) {
            return $this->processEdit($id);
        }

        $categories = $this->categoryModel->getAll();
        $images = $this->productModel->getProductImages($id);
        
        $this->view('admin/products/edit', [
            'page_title' => 'Chỉnh sửa sản phẩm',
            'product' => $product,
            'categories' => $categories,
            'images' => $images,
            'current_role' => $this->getRole()
        ], 'admin');
    }

    /**
     * Process edit product
     */
    private function processEdit($id)
    {
        $data = [
            'name' => $this->post('name'),
            'description' => $this->post('description'),
            'price' => (float)$this->post('price'),
            'stock' => (int)$this->post('stock'),
            'category_id' => (int)$this->post('category_id'),
            'is_active' => $this->post('is_active', 0) ? 1 : 0,
            'is_featured' => $this->post('is_featured', 0) ? 1 : 0,
        ];

        // Validate
        if (empty($data['name']) || empty($data['price'])) {
            setFlashMessage('error', 'Vui lòng điền đầy đủ thông tin bắt buộc.');
            $this->redirect("/admin/products/edit/$id");
            return;
        }

        // Update product
        $stmt = $this->pdo->prepare("
            UPDATE products 
            SET name = ?, description = ?, price = ?, stock = ?, 
                category_id = ?, is_active = ?, is_featured = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        if ($stmt->execute([
            $data['name'],
            $data['description'],
            $data['price'],
            $data['stock'],
            $data['category_id'],
            $data['is_active'],
            $data['is_featured'],
            $id
        ])) {
            // Handle image upload
            $this->handleImageUpload($id);
            
            setFlashMessage('success', 'Cập nhật sản phẩm thành công!');
            $this->redirect('/admin/products');
        } else {
            setFlashMessage('error', 'Có lỗi xảy ra khi cập nhật sản phẩm.');
            $this->redirect("/admin/products/edit/$id");
        }
    }

    /**
     * Delete product (admin only)
     */
    public function delete($id)
    {
        // Only admin can delete
        $this->requireRole('admin');
        $this->requirePermission(Permission::DELETE_PRODUCTS);
        
        $product = $this->productModel->find($id);
        
        if (!$product) {
            $this->json(['success' => false, 'message' => 'Sản phẩm không tồn tại'], 404);
            return;
        }

        // Soft delete - just mark as inactive
        // $stmt = $this->pdo->prepare("UPDATE products SET is_active = 0 WHERE id = ?");
        $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = ?");
        
        if ($stmt->execute([$id])) {
            $this->json(['success' => true, 'message' => 'Xóa sản phẩm thành công']);
        } else {
            $this->json(['success' => false, 'message' => 'Có lỗi xảy ra'], 500);
        }
    }

    /**
     * Toggle product status
     */
    public function toggleStatus($id)
    {
        $product = $this->productModel->find($id);
        
        if (!$product) {
            $this->json(['success' => false, 'message' => 'Sản phẩm không tồn tại'], 404);
            return;
        }

        $newStatus = $product['is_active'] ? 0 : 1;
        $stmt = $this->pdo->prepare("UPDATE products SET is_active = ? WHERE id = ?");
        
        if ($stmt->execute([$newStatus, $id])) {
            $this->json(['success' => true, 'message' => 'Cập nhật trạng thái thành công', 'new_status' => $newStatus]);
        } else {
            $this->json(['success' => false, 'message' => 'Có lỗi xảy ra'], 500);
        }
    }

    /**
     * Handle image upload
     */
    /**
     * Handle image upload
     */
    private function handleImageUpload($productId)
    {
        if (!isset($_FILES['images']) || empty($_FILES['images']['name'][0])) {
            return;
        }

        $uploadDir = __DIR__ . '/../../../public/assets/images/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $files = $_FILES['images'];
        $isMain = $this->post('main_image', 0);

        // Check if product has main image
        $hasMain = false;
        $existingImages = $this->productModel->getProductImages($productId);
        foreach ($existingImages as $img) {
            if ($img['is_main']) {
                $hasMain = true;
                break;
            }
        }

        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $fileName = time() . '_' . uniqid() . '_' . basename($files['name'][$i]);
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($files['tmp_name'][$i], $targetPath)) {
                    $imageUrl = '/assets/images/products/' . $fileName;
                    
                    // Determine if this should be main image
                    $setMain = false;
                    if (!$hasMain && $i == 0) {
                        $setMain = true;
                        $hasMain = true;
                    } elseif ($i == $isMain && count($files['name']) > 1) {
                        // Logic for selecting main from upload batch not fully implemented in UI yet,
                        // but keeping basic logic here.
                        // For now, if no main image exists, first one becomes main.
                    }
                    
                    // Insert image
                    $stmt = $this->pdo->prepare("
                        INSERT INTO product_images (product_id, image_url, is_main, created_at)
                        VALUES (?, ?, ?, NOW())
                    ");
                    $stmt->execute([$productId, $imageUrl, $setMain ? 1 : 0]);
                }
            }
        }
    }

    /**
     * Delete product image
     */
    public function deleteImage($imageId)
    {
        $this->requirePermission(Permission::MANAGE_PRODUCTS);

        // Get image info
        $stmt = $this->pdo->prepare("SELECT * FROM product_images WHERE id = ?");
        $stmt->execute([$imageId]);
        $image = $stmt->fetch();

        if (!$image) {
            $this->json(['success' => false, 'message' => 'Hình ảnh không tồn tại']);
            return;
        }

        // Delete file
        $filePath = __DIR__ . '/../../../public' . $image['image_url'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Delete from DB
        $stmt = $this->pdo->prepare("DELETE FROM product_images WHERE id = ?");
        if ($stmt->execute([$imageId])) {
            $newMainId = null;
            // If deleted image was main, set another as main
            if ($image['is_main']) {
                $stmt = $this->pdo->prepare("SELECT id FROM product_images WHERE product_id = ? ORDER BY created_at ASC LIMIT 1");
                $stmt->execute([$image['product_id']]);
                $nextImage = $stmt->fetch();
                if ($nextImage) {
                    $this->pdo->prepare("UPDATE product_images SET is_main = 1 WHERE id = ?")
                              ->execute([$nextImage['id']]);
                    $newMainId = $nextImage['id'];
                }
            }
            $this->json(['success' => true, 'new_main_id' => $newMainId]);
        } else {
            $this->json(['success' => false, 'message' => 'Lỗi database']);
        }
    }

    /**
     * Set main image
     */
    public function setMainImage($imageId)
    {
        $this->requirePermission(Permission::MANAGE_PRODUCTS);

        // Get image info to find product_id
        $stmt = $this->pdo->prepare("SELECT product_id FROM product_images WHERE id = ?");
        $stmt->execute([$imageId]);
        $image = $stmt->fetch();

        if (!$image) {
            $this->json(['success' => false, 'message' => 'Hình ảnh không tồn tại']);
            return;
        }

        $productId = $image['product_id'];

        try {
            $this->pdo->beginTransaction();

            // Reset all to 0
            $this->pdo->prepare("UPDATE product_images SET is_main = 0 WHERE product_id = ?")
                      ->execute([$productId]);

            // Set selected to 1
            $this->pdo->prepare("UPDATE product_images SET is_main = 1 WHERE id = ?")
                      ->execute([$imageId]);

            $this->pdo->commit();
            $this->json(['success' => true]);
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
