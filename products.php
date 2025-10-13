<?php
require_once 'includes/config.php';
session_start();

// Thiết lập page title và breadcrumbs
$page_title = 'Sản phẩm';
$breadcrumbs = [
    ['text' => 'Trang chủ', 'url' => 'index.php', 'icon' => 'fas fa-home'],
    ['text' => 'Sản phẩm']
];

// Xử lý tham số URL
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

$category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : '';
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : '';

// Xây dựng query
$where_conditions = [];
$params = [];

if (!empty($category)) {
    $where_conditions[] = "p.category = ?";
    $params[] = $category;
}

if (!empty($search)) {
    $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($min_price)) {
    $where_conditions[] = "p.price >= ?";
    $params[] = $min_price;
}

if (!empty($max_price)) {
    $where_conditions[] = "p.price <= ?";
    $params[] = $max_price;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Xử lý sắp xếp
$order_clause = '';
switch ($sort) {
    case 'price_low':
        $order_clause = 'ORDER BY p.price ASC';
        break;
    case 'price_high':
        $order_clause = 'ORDER BY p.price DESC';
        break;
    case 'name':
        $order_clause = 'ORDER BY p.name ASC';
        break;
    default:
        $order_clause = 'ORDER BY p.created_at DESC';
        break;
}

// Đếm tổng số sản phẩm
try {
    $count_sql = "SELECT COUNT(*) FROM products p $where_clause";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_products = $count_stmt->fetchColumn();
    $total_pages = ceil($total_products / $limit);
} catch (Exception $e) {
    $total_products = 0;
    $total_pages = 0;
}

// Lấy sản phẩm
try {
    $sql = "SELECT p.*, pi.image_url FROM products p 
            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1 
            $where_clause 
            $order_clause 
            LIMIT $limit OFFSET $offset";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $products = [];
    $error_message = "Lỗi tải sản phẩm: " . $e->getMessage();
}

// Lấy danh sách categories
try {
    $categories_stmt = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL ORDER BY category");
    $categories = $categories_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $categories = [];
}

// Include header
include 'includes/layouts/header.php';
?>

<!-- Main Content -->
<main class="section">
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <?php if (!empty($category)): ?>
                    <?php echo htmlspecialchars($category); ?>
                <?php elseif (!empty($search)): ?>
                    Kết quả tìm kiếm: "<?php echo htmlspecialchars($search); ?>"
                <?php else: ?>
                    Tất cả sản phẩm
                <?php endif; ?>
            </h1>
            <p class="page-subtitle">
                <?php if (!empty($search)): ?>
                    Tìm thấy <?php echo $total_products; ?> sản phẩm
                <?php else: ?>
                    Khám phá hàng ngàn sản phẩm chất lượng cao
                <?php endif; ?>
            </p>
        </div>

        <div class="products-layout">
            <!-- Sidebar Filters -->
            <aside class="products-sidebar">
                <div class="filter-section">
                    <h3>Bộ lọc</h3>

                    <!-- Category Filter -->
                    <div class="filter-group">
                        <h4>Danh mục</h4>
                        <div class="filter-options">
                            <label class="filter-option">
                                <input type="radio" name="category" value="" <?php echo empty($category) ? 'checked' : ''; ?>>
                                <span>Tất cả</span>
                            </label>
                            <?php foreach ($categories as $cat): ?>
                                <label class="filter-option">
                                    <input type="radio" name="category" value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'checked' : ''; ?>>
                                    <span><?php echo htmlspecialchars($cat); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Price Filter -->
                    <div class="filter-group">
                        <h4>Giá</h4>
                        <div class="price-range">
                            <input type="number" name="min_price" placeholder="Từ" value="<?php echo $min_price; ?>" class="price-input">
                            <span>-</span>
                            <input type="number" name="max_price" placeholder="Đến" value="<?php echo $max_price; ?>" class="price-input">
                        </div>
                    </div>

                    <!-- Sort Filter -->
                    <div class="filter-group">
                        <h4>Sắp xếp</h4>
                        <select name="sort" class="sort-select">
                            <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                            <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Giá thấp đến cao</option>
                            <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Giá cao đến thấp</option>
                            <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Tên A-Z</option>
                        </select>
                    </div>

                    <button class="btn btn-primary filter-btn" onclick="applyFilters()">
                        <i class="fas fa-filter"></i>
                        Áp dụng bộ lọc
                    </button>
                </div>
            </aside>

            <!-- Products Grid -->
            <div class="products-content">
                <?php if (empty($products)): ?>
                    <div class="no-products">
                        <div class="no-products-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>Không tìm thấy sản phẩm</h3>
                        <p>Hãy thử điều chỉnh bộ lọc hoặc tìm kiếm với từ khóa khác.</p>
                        <a href="products.php" class="btn btn-primary">
                            <i class="fas fa-refresh"></i>
                            Xem tất cả sản phẩm
                        </a>
                    </div>
                <?php else: ?>
                    <div class="products-grid">
                        <?php foreach ($products as $product):
                            $image_url = $product['image_url'] ?: 'assets/img/product/product-1.jpg';
                            $price = number_format($product['price'], 0, ',', '.');
                            $original_price = number_format($product['price'] * 1.2, 0, ',', '.');
                        ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <img src="<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <div class="product-badges">
                                        <span class="discount-badge">-20%</span>
                                    </div>
                                    <div class="product-actions">
                                        <button class="product-action-btn" onclick="toggleWishlist(<?php echo $product['id']; ?>)">
                                            <i class="far fa-heart"></i>
                                        </button>
                                        <button class="product-action-btn" onclick="quickView(<?php echo $product['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="product-info">
                                    <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                    <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>

                                    <div class="product-price">
                                        <span class="current-price"><?php echo $price; ?>đ</span>
                                        <span class="original-price"><?php echo $original_price; ?>đ</span>
                                    </div>

                                    <div class="product-rating">
                                        <div class="stars">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                        </div>
                                        <span class="rating-text">(4.5)</span>
                                    </div>

                                    <button class="add-to-cart-btn" onclick="addToCart(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-cart-plus"></i>
                                        Thêm vào giỏ
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="pagination-btn">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>

                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"
                                    class="pagination-btn <?php echo $i === $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="pagination-btn">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php
// Page-specific JavaScript
$page_scripts = '
    function applyFilters() {
        const category = document.querySelector("input[name=\"category\"]:checked").value;
        const minPrice = document.querySelector("input[name=\"min_price\"]").value;
        const maxPrice = document.querySelector("input[name=\"max_price\"]").value;
        const sort = document.querySelector("select[name=\"sort\"]").value;
        
        const params = new URLSearchParams();
        if (category) params.append("category", category);
        if (minPrice) params.append("min_price", minPrice);
        if (maxPrice) params.append("max_price", maxPrice);
        if (sort) params.append("sort", sort);
        
        window.location.href = "products.php?" + params.toString();
    }
    
    function quickView(productId) {
        window.location.href = "product-detail.php?id=" + productId;
    }
';

// Include footer
include 'includes/layouts/footer.php';
?>