<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Product.php';
require_once __DIR__ . '/../Models/Category.php';

/**
 * Product Controller
 * Handles product listing, detail, and search
 */
class ProductController extends BaseController
{
    private $productModel;
    private $categoryModel;

    public function __construct()
    {
        parent::__construct();
        $this->productModel = new Product();
        $this->categoryModel = new Category();
    }

    /**
     * Product listing page
     */
    public function index()
    {
        $page_title = 'Sản phẩm';
        
        // Pagination
        $config = require __DIR__ . '/../../config/app.php';
        $itemsPerPage = $config['items_per_page'];
        $page = max(1, (int)$this->get('page', 1));
        $offset = ($page - 1) * $itemsPerPage;

        // Filter by category
        $categoryId = $this->get('category');
        
        // Get products
        $products = $this->productModel->getAll($itemsPerPage, $offset, $categoryId);
        $totalProducts = $this->productModel->countAll($categoryId);
        $totalPages = ceil($totalProducts / $itemsPerPage);

        // Get categories for filter
        $categories = $this->categoryModel->getAllWithProductCount();

        $this->view('products/index', [
            'page_title' => $page_title,
            'products' => $products,
            'categories' => $categories,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'category_id' => $categoryId
        ]);
    }

    /**
     * Product detail page
     */
    public function detail($id)
    {
        $productId = (int)$id;
        $product = $this->productModel->getById($productId);

        if (!$product) {
            setFlashMessage('error', 'Sản phẩm không tồn tại.');
            $this->redirect('/product');
        }

        $page_title = $product['name'];

        $this->view('products/detail', [
            'page_title' => $page_title,
            'product' => $product
        ]);
    }

    /**
     * Product search
     */
    public function search()
    {
        $page_title = 'Tìm kiếm sản phẩm';
        $keyword = $this->get('keyword', '');

        if (empty($keyword)) {
            $this->redirect('/product');
        }

        // Pagination
        $config = require __DIR__ . '/../../config/app.php';
        $itemsPerPage = $config['items_per_page'];
        $page = max(1, (int)$this->get('page', 1));
        $offset = ($page - 1) * $itemsPerPage;

        // Search
        $products = $this->productModel->search($keyword, $itemsPerPage, $offset);
        $totalProducts = $this->productModel->countSearch($keyword);
        $totalPages = ceil($totalProducts / $itemsPerPage);

        // Get categories for sidebar
        $categories = $this->categoryModel->getAllWithProductCount();

        $this->view('products/index', [
            'page_title' => $page_title,
            'products' => $products,
            'categories' => $categories,
            'current_page' => $page,
            'totalPages' => $totalPages,
            'totalProducts' => $totalProducts,
            'keyword' => $keyword,
            'category_id' => null
        ]);
    }
}
