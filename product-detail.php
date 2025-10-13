<?php
require_once 'includes/config.php';
session_start();

// Lấy ID sản phẩm từ URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header('Location: products.php');
    exit;
}

// Lấy thông tin sản phẩm
try {
    $stmt = $pdo->prepare("SELECT p.*, pi.image_url FROM products p 
                          LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1 
                          WHERE p.id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        header('Location: products.php');
        exit;
    }

    // Thiết lập page title và breadcrumbs
    $page_title = $product['name'];
    $breadcrumbs = [
        ['text' => 'Trang chủ', 'url' => 'index.php', 'icon' => 'fas fa-home'],
        ['text' => 'Sản phẩm', 'url' => 'products.php'],
        ['text' => $product['name']]
    ];
} catch (Exception $e) {
    $error_message = "Lỗi tải sản phẩm: " . $e->getMessage();
    header('Location: products.php');
    exit;
}

// Lấy hình ảnh sản phẩm
try {
    $images_stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_main DESC");
    $images_stmt->execute([$product_id]);
    $product_images = $images_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $product_images = [];
}

// Lấy sản phẩm liên quan
try {
    $related_stmt = $pdo->prepare("SELECT p.*, pi.image_url FROM products p 
                                  LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1 
                                  WHERE p.category = ? AND p.id != ? 
                                  ORDER BY p.created_at DESC LIMIT 4");
    $related_stmt->execute([$product['category'], $product_id]);
    $related_products = $related_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $related_products = [];
}

// Include header
include 'includes/layouts/header.php';
?>

<!-- Main Content -->
<main class="section">
    <div class="container">
        <div class="product-detail-layout">
            <!-- Product Images -->
            <div class="product-images">
                <div class="main-image">
                    <img id="mainImage" src="<?php echo $product_images[0]['image_url'] ?? 'assets/img/product/product-1.jpg'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>

                <?php if (count($product_images) > 1): ?>
                    <div class="thumbnail-images">
                        <?php foreach ($product_images as $index => $image): ?>
                            <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" onclick="changeMainImage('<?php echo $image['image_url']; ?>', this)">
                                <img src="<?php echo $image['image_url']; ?>" alt="Thumbnail <?php echo $index + 1; ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Product Info -->
            <div class="product-info">
                <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>

                <div class="product-rating">
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <span class="rating-text">(4.5) - 128 đánh giá</span>
                </div>

                <div class="product-price">
                    <span class="current-price"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</span>
                    <span class="original-price"><?php echo number_format($product['price'] * 1.2, 0, ',', '.'); ?>đ</span>
                    <span class="discount-badge">-20%</span>
                </div>

                <div class="product-description">
                    <h3>Mô tả sản phẩm</h3>
                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>

                <div class="product-options">
                    <div class="quantity-selector">
                        <label>Số lượng:</label>
                        <div class="quantity-controls">
                            <button type="button" class="quantity-btn minus" onclick="decreaseQuantity()">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" id="quantity" value="1" min="1" max="99" class="quantity-input">
                            <button type="button" class="quantity-btn plus" onclick="increaseQuantity()">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="product-actions">
                    <button class="btn btn-primary btn-lg add-to-cart-btn" onclick="addToCart(<?php echo $product['id']; ?>)">
                        <i class="fas fa-cart-plus"></i>
                        Thêm vào giỏ hàng
                    </button>
                    <button class="btn btn-outline-primary btn-lg wishlist-btn" onclick="toggleWishlist(<?php echo $product['id']; ?>)">
                        <i class="far fa-heart"></i>
                        Yêu thích
                    </button>
                </div>

                <div class="product-features">
                    <div class="feature-item">
                        <i class="fas fa-shipping-fast"></i>
                        <div>
                            <h4>Giao hàng nhanh</h4>
                            <p>Miễn phí vận chuyển đơn từ 500k</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-shield-alt"></i>
                        <div>
                            <h4>Bảo hành chính hãng</h4>
                            <p>Bảo hành 12 tháng từ nhà sản xuất</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-undo"></i>
                        <div>
                            <h4>Đổi trả dễ dàng</h4>
                            <p>Đổi trả trong 30 ngày</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Tabs -->
        <div class="product-tabs">
            <div class="tab-nav">
                <button class="tab-btn active" onclick="showTab('description')">Mô tả chi tiết</button>
                <button class="tab-btn" onclick="showTab('reviews')">Đánh giá</button>
                <button class="tab-btn" onclick="showTab('shipping')">Vận chuyển</button>
            </div>

            <div class="tab-content">
                <div id="description" class="tab-panel active">
                    <h3>Thông tin chi tiết</h3>
                    <div class="product-specs">
                        <div class="spec-row">
                            <span class="spec-label">Thương hiệu:</span>
                            <span class="spec-value">ShopifyT</span>
                        </div>
                        <div class="spec-row">
                            <span class="spec-label">Danh mục:</span>
                            <span class="spec-value"><?php echo htmlspecialchars($product['category']); ?></span>
                        </div>
                        <div class="spec-row">
                            <span class="spec-label">Tình trạng:</span>
                            <span class="spec-value">Còn hàng</span>
                        </div>
                        <div class="spec-row">
                            <span class="spec-label">Số lượng:</span>
                            <span class="spec-value"><?php echo $product['stock']; ?> sản phẩm</span>
                        </div>
                    </div>
                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>

                <div id="reviews" class="tab-panel">
                    <h3>Đánh giá sản phẩm</h3>
                    <div class="reviews-summary">
                        <div class="rating-overview">
                            <div class="rating-score">4.5</div>
                            <div class="rating-stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <p>Dựa trên 128 đánh giá</p>
                        </div>
                    </div>

                    <div class="reviews-list">
                        <div class="review-item">
                            <div class="review-header">
                                <div class="reviewer-info">
                                    <img src="assets/img/about/team-1.jpg" alt="Người đánh giá" class="reviewer-avatar">
                                    <div>
                                        <h4>Nguyễn Văn A</h4>
                                        <div class="review-rating">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                        </div>
                                    </div>
                                </div>
                                <span class="review-date">2 ngày trước</span>
                            </div>
                            <p class="review-text">Sản phẩm chất lượng tốt, giao hàng nhanh. Rất hài lòng!</p>
                        </div>

                        <div class="review-item">
                            <div class="review-header">
                                <div class="reviewer-info">
                                    <img src="assets/img/about/team-2.jpg" alt="Người đánh giá" class="reviewer-avatar">
                                    <div>
                                        <h4>Trần Thị B</h4>
                                        <div class="review-rating">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="far fa-star"></i>
                                        </div>
                                    </div>
                                </div>
                                <span class="review-date">1 tuần trước</span>
                            </div>
                            <p class="review-text">Sản phẩm đúng như mô tả, giá cả hợp lý.</p>
                        </div>
                    </div>
                </div>

                <div id="shipping" class="tab-panel">
                    <h3>Thông tin vận chuyển</h3>
                    <div class="shipping-info">
                        <div class="shipping-item">
                            <i class="fas fa-truck"></i>
                            <div>
                                <h4>Giao hàng nhanh</h4>
                                <p>Giao hàng trong 1-3 ngày làm việc</p>
                            </div>
                        </div>
                        <div class="shipping-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <h4>Toàn quốc</h4>
                                <p>Giao hàng đến tất cả tỉnh thành</p>
                            </div>
                        </div>
                        <div class="shipping-item">
                            <i class="fas fa-gift"></i>
                            <div>
                                <h4>Miễn phí vận chuyển</h4>
                                <p>Đơn hàng từ 500.000đ</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (!empty($related_products)): ?>
            <div class="related-products">
                <h2>Sản phẩm liên quan</h2>
                <div class="products-grid">
                    <?php foreach ($related_products as $related):
                        $image_url = $related['image_url'] ?: 'assets/img/product/product-1.jpg';
                        $price = number_format($related['price'], 0, ',', '.');
                    ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($related['name']); ?>">
                                <div class="product-actions">
                                    <button class="product-action-btn" onclick="toggleWishlist(<?php echo $related['id']; ?>)">
                                        <i class="far fa-heart"></i>
                                    </button>
                                    <button class="product-action-btn" onclick="window.location.href='product-detail.php?id=<?php echo $related['id']; ?>'">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="product-info">
                                <h3 class="product-name"><?php echo htmlspecialchars($related['name']); ?></h3>
                                <div class="product-price">
                                    <span class="current-price"><?php echo $price; ?>đ</span>
                                </div>
                                <button class="add-to-cart-btn" onclick="addToCart(<?php echo $related['id']; ?>)">
                                    <i class="fas fa-cart-plus"></i>
                                    Thêm vào giỏ
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php
// Page-specific JavaScript
$page_scripts = '
    function changeMainImage(imageUrl, thumbnail) {
        document.getElementById("mainImage").src = imageUrl;
        
        // Update active thumbnail
        document.querySelectorAll(".thumbnail").forEach(t => t.classList.remove("active"));
        thumbnail.classList.add("active");
    }
    
    function increaseQuantity() {
        const quantityInput = document.getElementById("quantity");
        const currentValue = parseInt(quantityInput.value);
        if (currentValue < 99) {
            quantityInput.value = currentValue + 1;
        }
    }
    
    function decreaseQuantity() {
        const quantityInput = document.getElementById("quantity");
        const currentValue = parseInt(quantityInput.value);
        if (currentValue > 1) {
            quantityInput.value = currentValue - 1;
        }
    }
    
    function showTab(tabName) {
        // Hide all tab panels
        document.querySelectorAll(".tab-panel").forEach(panel => {
            panel.classList.remove("active");
        });
        
        // Remove active class from all tab buttons
        document.querySelectorAll(".tab-btn").forEach(btn => {
            btn.classList.remove("active");
        });
        
        // Show selected tab panel
        document.getElementById(tabName).classList.add("active");
        
        // Add active class to clicked button
        event.target.classList.add("active");
    }
';

// Include footer
include 'includes/layouts/footer.php';
?>