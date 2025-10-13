<?php
// Lấy thông tin giỏ hàng
$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;

// Lấy danh sách categories cho dropdown
try {
    $categories_stmt = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL ORDER BY category");
    $categories = $categories_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $categories = [];
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>ShopifyT</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body>
    <!-- Header -->
    <header class="header">
        <!-- Top Bar -->
        <div class="header-top">
            <div class="container">
                <div class="d-flex justify-between align-center">
                    <div class="d-flex" style="gap: 2rem;">
                        <span><i class="fas fa-phone"></i> Hotline: 1900-xxxx</span>
                        <span><i class="fas fa-envelope"></i> support@shopifyt.com</span>
                    </div>
                    <div class="d-flex" style="gap: 1rem;">
                        <a href="#" class="text-white"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Header -->
        <div class="header-main">
            <div class="container">
                <div class="d-flex justify-between align-center">
                    <!-- Logo -->
                    <a href="index.php" class="logo">
                        <!-- <img src="assets/img/logo.png" alt="ShopifyT"> -->
                        <span>ShopifyT</span>
                    </a>

                    <!-- Search Bar -->
                    <div class="search-bar">
                        <form method="GET" action="products.php" style="display: flex; width: 100%;">
                            <input type="text" name="search" class="search-input" placeholder="Tìm kiếm sản phẩm..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>

                    <!-- User Actions -->
                    <div class="header-actions">
                        <a href="login.php" class="header-action">
                            <i class="fas fa-user"></i>
                            <span>Đăng nhập</span>
                        </a>
                        <a href="register.php" class="header-action">
                            <i class="fas fa-user-plus"></i>
                            <span>Đăng ký</span>
                        </a>
                        <a href="shopping-cart.php" class="header-action cart-badge <?php echo basename($_SERVER['PHP_SELF']) == 'shopping-cart.php' ? 'active' : ''; ?>">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Giỏ hàng</span>
                            <span class="cart-count"><?php echo $cart_count; ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="header-bottom">
            <div class="container">
                <nav class="nav-menu">
                    <div class="nav-links">
                        <a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i>
                            <span>Trang chủ</span>
                        </a>
                        <a href="products.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">
                            <i class="fas fa-th-large"></i>
                            <span>Sản phẩm</span>
                        </a>
                        <div class="nav-dropdown">
                            <a href="#" class="nav-link">
                                <i class="fas fa-list"></i>
                                <span>Danh mục</span>
                                <i class="fas fa-chevron-down"></i>
                            </a>
                            <div class="nav-dropdown-menu">
                                <?php foreach ($categories as $category): ?>
                                    <a href="products.php?category=<?php echo urlencode($category); ?>" class="nav-dropdown-link">
                                        <?php echo htmlspecialchars($category); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <a href="#" class="nav-link">
                            <i class="fas fa-percent"></i>
                            <span>Khuyến mãi</span>
                        </a>
                        <a href="#" class="nav-link">
                            <i class="fas fa-info-circle"></i>
                            <span>Liên hệ</span>
                        </a>
                    </div>
                    <div class="d-flex align-center" style="gap: 1rem;">
                        <span style="font-size: 0.875rem;">
                            <i class="fas fa-truck"></i>
                            Miễn phí vận chuyển đơn từ 500k
                        </span>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <!-- Breadcrumb (optional) -->
    <?php if (isset($breadcrumbs) && !empty($breadcrumbs)): ?>
        <div class="breadcrumb">
            <div class="container">
                <nav class="breadcrumb-nav">
                    <?php foreach ($breadcrumbs as $index => $breadcrumb): ?>
                        <?php if ($index > 0): ?>
                            <i class="fas fa-chevron-right breadcrumb-separator"></i>
                        <?php endif; ?>
                        <?php if (isset($breadcrumb['url'])): ?>
                            <a href="<?php echo $breadcrumb['url']; ?>">
                                <?php if (isset($breadcrumb['icon'])): ?>
                                    <i class="<?php echo $breadcrumb['icon']; ?>"></i>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($breadcrumb['text']); ?>
                            </a>
                        <?php else: ?>
                            <span><?php echo htmlspecialchars($breadcrumb['text']); ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </nav>
            </div>
        </div>
    <?php endif; ?>

    <!-- Success/Error Messages -->
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success message-slide">
            <div class="d-flex align-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span><?php echo $success_message; ?></span>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger message-slide">
            <div class="d-flex align-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span><?php echo $error_message; ?></span>
            </div>
        </div>
    <?php endif; ?>