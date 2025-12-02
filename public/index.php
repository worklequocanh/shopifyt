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

// Account
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

// Dispatch the request
$router->dispatch();
