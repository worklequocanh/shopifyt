<?php

namespace Admin;

use BaseController;
use Category;
use Permission;
use Product;

require_once __DIR__ . '/../BaseController.php';
require_once __DIR__ . '/../../Models/Category.php';
require_once __DIR__ . '/../../Models/Product.php';

class AdminCategoryController extends BaseController
{
    private $categoryModel;
    private $productModel;

    public function __construct()
    {
        parent::__construct();
        
        // Require admin or employee role
        $this->requireAnyRole(['admin', 'employee']);
        $this->requirePermission(Permission::VIEW_CATEGORIES);
        
        $this->categoryModel = new Category();
        $this->productModel = new Product();
    }

    public function index()
    {
        $search = $this->get('search');
        $categories = $this->categoryModel->getAll($search);

        // Add product count to each category
        foreach ($categories as &$category) {
            $category['product_count'] = $this->productModel->countByCategory($category['id']);
        }

        $data = [
            'page_title' => 'Quản lý danh mục',
            'categories' => $categories,
            'search' => $search,
            'current_role' => $this->getRole()
        ];

        $this->view('admin/categories/index', $data, 'admin');
    }

    public function store()
    {
        $this->requirePermission(Permission::MANAGE_CATEGORIES);

        if ($this->isPost()) {
            $name = trim($this->post('name'));

            if (empty($name)) {
                setFlashMessage('error', 'Tên danh mục không được để trống');
            } else {
                // Check if exists
                if ($this->categoryModel->exists($name)) {
                    setFlashMessage('error', 'Danh mục đã tồn tại');
                } else {
                    if ($this->categoryModel->create(['name' => $name])) {
                        setFlashMessage('success', 'Thêm danh mục thành công');
                    } else {
                        setFlashMessage('error', 'Lỗi khi thêm danh mục');
                    }
                }
            }
        }
        $this->redirect('/admin/categories');
    }

    public function update()
    {
        $this->requirePermission(Permission::MANAGE_CATEGORIES);

        if ($this->isPost()) {
            $id = $this->post('id');
            $name = trim($this->post('name'));

            if (empty($name)) {
                setFlashMessage('error', 'Tên danh mục không được để trống');
            } else {
                // Check if exists (excluding current)
                if ($this->categoryModel->exists($name, $id)) {
                    setFlashMessage('error', 'Tên danh mục đã tồn tại');
                } else {
                    if ($this->categoryModel->update($id, ['name' => $name])) {
                        setFlashMessage('success', 'Cập nhật danh mục thành công');
                    } else {
                        setFlashMessage('error', 'Lỗi khi cập nhật danh mục');
                    }
                }
            }
        }
        $this->redirect('/admin/categories');
    }

    public function delete($id)
    {
        $this->requirePermission(Permission::MANAGE_CATEGORIES);

        // Check product count
        $count = $this->productModel->countByCategory($id);
        if ($count > 0) {
            setFlashMessage('error', "Không thể xóa! Còn $count sản phẩm trong danh mục này.");
        } else {
            if ($this->categoryModel->delete($id)) {
                setFlashMessage('success', 'Xóa danh mục thành công');
            } else {
                setFlashMessage('error', 'Lỗi khi xóa danh mục');
            }
        }
        $this->redirect('/admin/categories');
    }
}
