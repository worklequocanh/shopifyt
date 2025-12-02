<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Product.php';

/**
 * Home Controller
 * Handles homepage and about page
 */
class HomeController extends BaseController
{
    /**
     * Homepage - display featured products
     */
    public function index()
    {
        $page_title = 'Trang chủ';
        
        $productModel = new Product();
        $featured_products =  $productModel->getFeatured(4);

        $this->view('home/index', [
            'page_title' => $page_title,
            'featured_products' => $featured_products
        ]);
    }

    /**
     * About page
     */
    public function about()
    {
        $page_title = 'Về chúng tôi';
        
        $this->view('about/index', [
            'page_title' => $page_title
        ]);
    }
}
