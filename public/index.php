<?php
/**
 * Front Controller
 * Entry point for all requests
 */

// Start session
session_start();

// Load configuration
$config = require __DIR__ . '/../config/app.php';

// Set timezone
date_default_timezone_set($config['timezone']);

// Error reporting based on environment
if ($config['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', $config['display_errors'] ? '1' : '0');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Load core classes
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Router.php';
require_once __DIR__ . '/../app/Core/Permission.php';
require_once __DIR__ . '/../app/Core/helpers.php';
require_once __DIR__ . '/../app/Controllers/BaseController.php';
require_once __DIR__ . '/../app/Models/BaseModel.php';

// Initialize router
$router = new Router();

// Define routes
// Home
$router->add('/', 'Home', 'index');
$router->add('/home', 'Home', 'index');
$router->add('/about', 'Home', 'about');
$router->add('/contact', 'Contact', 'index');

// Authentication
$router->add('/auth/login', 'Auth', 'login');
$router->add('/auth/register', 'Auth', 'register');
$router->add('/auth/logout', 'Auth', 'logout');

// Products
$router->add('/product', 'Product', 'index');
$router->add('/products', 'Product', 'index'); // Alternative route
$router->add('/product/detail/:id', 'Product', 'detail');
$router->add('/product/search', 'Product', 'search');

// Cart
$router->add('/cart', 'Cart', 'index');
$router->add('/cart/add', 'Cart', 'add');
$router->add('/cart/update', 'Cart', 'update');
$router->add('/cart/remove', 'Cart', 'remove');

// Account routes (customer)
$router->add('/account', 'Account', 'info');
$router->add('/account/info', 'Account', 'info');
$router->add('/account/password', 'Account', 'password');
$router->add('/account/orders', 'Account', 'orders');
$router->add('/account/update-profile', 'Account', 'updateProfile');
$router->add('/account/change-password', 'Account', 'changePassword');

// Checkout & Orders
$router->add('/checkout', 'Order', 'checkout');
$router->add('/checkout/success', 'Order', 'success');
$router->add('/order/detail/:id', 'Order', 'detail');

// Admin routes
$router->add('/admin', 'Admin\\Admin', 'index');
$router->add('/admin/dashboard', 'Admin\\Admin', 'index');

// Admin Products
$router->add('/admin/products', 'Admin\\AdminProduct', 'index');
$router->add('/admin/products/create', 'Admin\\AdminProduct', 'create');
$router->add('/admin/products/edit/:id', 'Admin\\AdminProduct', 'edit');
$router->add('/admin/products/delete/:id', 'Admin\\AdminProduct', 'delete');
$router->add('/admin/products/toggle/:id', 'Admin\\AdminProduct', 'toggleStatus');
$router->add('/admin/products/delete-image/:id', 'Admin\\AdminProduct', 'deleteImage');
$router->add('/admin/products/set-main-image/:id', 'Admin\\AdminProduct', 'setMainImage');

// Admin Order Routes
$router->add('/admin/orders', 'Admin\\AdminOrder', 'index');
$router->add('/admin/orders/detail/:id', 'Admin\\AdminOrder', 'detail');
$router->add('/admin/orders/update-status', 'Admin\\AdminOrder', 'updateStatus');

// Admin Category Routes
$router->add('/admin/categories', 'Admin\\AdminCategory', 'index');
$router->add('/admin/categories/store', 'Admin\\AdminCategory', 'store');
$router->add('/admin/categories/update', 'Admin\\AdminCategory', 'update');
$router->add('/admin/categories/delete/:id', 'Admin\\AdminCategory', 'delete');

// Admin Account Routes
$router->add('/admin/accounts', 'Admin\\AdminAccount', 'index');
$router->add('/admin/accounts/store', 'Admin\\AdminAccount', 'store');
$router->add('/admin/accounts/update', 'Admin\\AdminAccount', 'update');
$router->add('/admin/accounts/delete/:id', 'Admin\\AdminAccount', 'delete');
$router->add('/admin/accounts/toggle/:id', 'Admin\\AdminAccount', 'toggleActive');

// Admin Report Routes
$router->add('/admin/reports/revenue', 'Admin\\AdminReport', 'revenue');
$router->add('/admin/reports/stats', 'Admin\\AdminReport', 'stats');

// Admin Voucher Routes
$router->add('/admin/vouchers', 'Admin\\AdminVoucher', 'index');
$router->add('/admin/vouchers/store', 'Admin\\AdminVoucher', 'store');
$router->add('/admin/vouchers/update', 'Admin\\AdminVoucher', 'update');
$router->add('/admin/vouchers/delete/:id', 'Admin\\AdminVoucher', 'delete');

// Dispatch the request
$router->dispatch();
