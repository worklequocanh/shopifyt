<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';;

// Thiết lập page title
$page_title = 'Thương mại điện tử hàng đầu';

// Lấy sản phẩm từ database
try {
    $stmt = $pdo->query("SELECT p.*, pi.image_url FROM products p 
                            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1 
                            ORDER BY p.created_at DESC LIMIT 8");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $products = [];
}

// Lấy danh sách categories
try {
    $categories_stmt = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL ORDER BY category");
    $categories = $categories_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $categories = [];
}

redirectIfLoggedIn();
restrictToRoles("customer", "/admin/index.php");

$logined = isLoggedIn();

// Include header
include __DIR__ .  'includes/layouts/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content animate-fadeInUp">
            <h1 class="hero-title">Khám phá thế giới mua sắm</h1>
            <p class="hero-subtitle">
                Hàng ngàn sản phẩm chất lượng cao với giá cả hợp lý.
                Giao hàng nhanh chóng và dịch vụ khách hàng tận tình.
            </p>
            <div class="hero-actions">
                <a href="products.php" class="btn btn-lg" style="background-color: var(--accent-color); color: var(--black);">
                    <i class="fas fa-shopping-bag"></i>
                    Mua sắm ngay
                </a>
                <a href="#" class="btn btn-lg btn-outline-primary" style="border-color: var(--white); color: var(--white);">
                    <i class="fas fa-play"></i>
                    Xem video
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="section features">
    <div class="container">
        <div class="grid grid-4">
            <div class="feature-item animate-fadeInUp">
                <div class="feature-icon" style="background-color: var(--primary-color);">
                    <i class="fas fa-shipping-fast"></i>
                </div>
                <h3 class="feature-title">Giao hàng nhanh</h3>
                <p class="feature-description">Giao hàng trong 24h tại TP.HCM</p>
            </div>
            <div class="feature-item animate-fadeInUp">
                <div class="feature-icon" style="background-color: var(--success-color);">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 class="feature-title">Bảo hành chính hãng</h3>
                <p class="feature-description">Cam kết chất lượng sản phẩm</p>
            </div>
            <div class="feature-item animate-fadeInUp">
                <div class="feature-icon" style="background-color: var(--info-color);">
                    <i class="fas fa-headset"></i>
                </div>
                <h3 class="feature-title">Hỗ trợ 24/7</h3>
                <p class="feature-description">Đội ngũ CSKH chuyên nghiệp</p>
            </div>
            <div class="feature-item animate-fadeInUp">
                <div class="feature-icon" style="background-color: var(--warning-color);">
                    <i class="fas fa-undo"></i>
                </div>
                <h3 class="feature-title">Đổi trả dễ dàng</h3>
                <p class="feature-description">30 ngày đổi trả miễn phí</p>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="section categories">
    <div class="container">
        <div class="section-title">
            <h2>Danh mục sản phẩm</h2>
            <p class="section-subtitle">Khám phá các danh mục sản phẩm đa dạng</p>
        </div>

        <div class="grid grid-6">
            <div class="category-item animate-fadeInUp">
                <div class="category-icon" style="background-color: #fee2e2;">
                    <i class="fas fa-tshirt" style="color: var(--danger-color);"></i>
                </div>
                <h3 class="category-title">Thời trang</h3>
                <p class="category-count">500+ sản phẩm</p>
            </div>

            <div class="category-item animate-fadeInUp">
                <div class="category-icon" style="background-color: #dbeafe;">
                    <i class="fas fa-shoe-prints" style="color: var(--primary-color);"></i>
                </div>
                <h3 class="category-title">Giày dép</h3>
                <p class="category-count">300+ sản phẩm</p>
            </div>

            <div class="category-item animate-fadeInUp">
                <div class="category-icon" style="background-color: #d1fae5;">
                    <i class="fas fa-laptop" style="color: var(--success-color);"></i>
                </div>
                <h3 class="category-title">Điện tử</h3>
                <p class="category-count">200+ sản phẩm</p>
            </div>

            <div class="category-item animate-fadeInUp">
                <div class="category-icon" style="background-color: #fef3c7;">
                    <i class="fas fa-home" style="color: var(--warning-color);"></i>
                </div>
                <h3 class="category-title">Gia dụng</h3>
                <p class="category-count">150+ sản phẩm</p>
            </div>

            <div class="category-item animate-fadeInUp">
                <div class="category-icon" style="background-color: #e9d5ff;">
                    <i class="fas fa-gamepad" style="color: #8b5cf6;"></i>
                </div>
                <h3 class="category-title">Thể thao</h3>
                <p class="category-count">100+ sản phẩm</p>
            </div>

            <div class="category-item animate-fadeInUp">
                <div class="category-icon" style="background-color: #fce7f3;">
                    <i class="fas fa-gem" style="color: #ec4899;"></i>
                </div>
                <h3 class="category-title">Phụ kiện</h3>
                <p class="category-count">80+ sản phẩm</p>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="section">
    <div class="container">
        <div class="section-title">
            <h2>Sản phẩm nổi bật</h2>
            <p class="section-subtitle">Những sản phẩm được yêu thích nhất</p>
        </div>

        <div class="grid grid-4">
            <?php foreach ($products as $product):
                $image_url = $product['image_url'] ?: 'assets/img/product/product-1.jpg';
                $price = number_format($product['price'], 0, ',', '.');
                $original_price = number_format($product['price'] * 1.2, 0, ',', '.');
            ?>
                <div class="product-card animate-fadeInUp">
                    <div class="product-image">
                        <img src="<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <div class="product-badge">-20%</div>
                        <div class="product-actions">
                            <button class="product-action-btn" onclick="toggleWishlist(<?php echo $product['id']; ?>)">
                                <i class="fas fa-heart"></i>
                            </button>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3 class="product-title">
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a>
                        </h3>
                        <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                        <div class="product-price">
                            <div>
                                <span class="price-current"><?php echo $price; ?>đ</span>
                                <span class="price-original"><?php echo $original_price; ?>đ</span>
                            </div>
                            <div class="product-rating">
                                <div class="rating-stars">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <span class="rating-count">(4.5)</span>
                            </div>
                        </div>
                        <button class="btn btn-primary btn-block" onclick="addToCart(<?php echo $product['id']; ?>)">
                            <i class="fas fa-cart-plus"></i>
                            Thêm vào giỏ
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-6">
            <a href="products.php" class="btn btn-lg btn-primary">
                <i class="fas fa-th-large"></i>
                Xem tất cả sản phẩm
            </a>
        </div>
    </div>
</section>

<!-- Hot Deals Section -->
<section class="section hot-deals">
    <div class="container">
        <div class="hot-deals-content">
            <div class="section-title">
                <h2 class="animate-bounce">🔥 DEAL HOT HÔM NAY</h2>
                <p class="section-subtitle">Giảm giá lên đến 70% - Chỉ trong ngày hôm nay!</p>
            </div>

            <div class="grid grid-3">
                <div class="deal-item animate-fadeInUp">
                    <div class="deal-icon">⏰</div>
                    <h3 class="deal-title">Flash Sale</h3>
                    <p class="deal-description">Giảm giá 50% trong 2 giờ tới</p>
                    <div class="countdown">
                        <div class="countdown-item">0</div>
                        <div class="countdown-item">1</div>
                        <span style="color: var(--white); font-size: 1.5rem;">:</span>
                        <div class="countdown-item">5</div>
                        <div class="countdown-item">9</div>
                    </div>
                </div>

                <div class="deal-item animate-fadeInUp">
                    <div class="deal-icon">🎁</div>
                    <h3 class="deal-title">Mua 2 Tặng 1</h3>
                    <p class="deal-description">Áp dụng cho tất cả sản phẩm thời trang</p>
                    <button class="btn" style="background-color: var(--white); color: var(--danger-color);">
                        Mua ngay
                    </button>
                </div>

                <div class="deal-item animate-fadeInUp">
                    <div class="deal-icon">🚚</div>
                    <h3 class="deal-title">Miễn phí ship</h3>
                    <p class="deal-description">Đơn hàng từ 200k được miễn phí vận chuyển</p>
                    <button class="btn" style="background-color: var(--white); color: var(--danger-color);">
                        Tìm hiểu thêm
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="section testimonials">
    <div class="container">
        <div class="section-title">
            <h2>Khách hàng nói gì về chúng tôi</h2>
            <p class="section-subtitle">Hơn 10,000 khách hàng tin tưởng</p>
        </div>

        <div class="grid grid-3">
            <div class="testimonial-item animate-fadeInUp">
                <div class="testimonial-header">
                    <img src="assets/img/about/testimonial-author.jpg" alt="Khách hàng" class="testimonial-avatar">
                    <div class="testimonial-info">
                        <h4>Nguyễn Văn A</h4>
                        <div class="testimonial-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <p class="testimonial-text">
                    "Sản phẩm chất lượng tốt, giao hàng nhanh. Tôi rất hài lòng với dịch vụ của ShopifyT!"
                </p>
            </div>

            <div class="testimonial-item animate-fadeInUp">
                <div class="testimonial-header">
                    <img src="assets/img/about/testimonial-pic.jpg" alt="Khách hàng" class="testimonial-avatar">
                    <div class="testimonial-info">
                        <h4>Trần Thị B</h4>
                        <div class="testimonial-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <p class="testimonial-text">
                    "Giá cả hợp lý, nhiều chương trình khuyến mãi hấp dẫn. Sẽ tiếp tục mua sắm tại đây!"
                </p>
            </div>

            <div class="testimonial-item animate-fadeInUp">
                <div class="testimonial-header">
                    <img src="assets/img/about/team-1.jpg" alt="Khách hàng" class="testimonial-avatar">
                    <div class="testimonial-info">
                        <h4>Lê Văn C</h4>
                        <div class="testimonial-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <p class="testimonial-text">
                    "Đội ngũ hỗ trợ khách hàng rất nhiệt tình và chuyên nghiệp. Cảm ơn ShopifyT!"
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="section newsletter">
    <div class="container">
        <div class="text-center">
            <h2 class="mb-4">Đăng ký nhận tin</h2>
            <p class="section-subtitle mb-6">Nhận thông tin về sản phẩm mới và khuyến mãi độc quyền</p>

            <form class="newsletter-form">
                <input type="email" class="newsletter-input" placeholder="Nhập email của bạn..." required>
                <button type="submit" class="newsletter-btn">
                    <i class="fas fa-paper-plane"></i>
                    Đăng ký
                </button>
            </form>

            <div class="d-flex justify-center" style="gap: 2rem; margin-top: 2rem; font-size: 0.875rem;">
                <span><i class="fas fa-check"></i> Không spam</span>
                <span><i class="fas fa-check"></i> Hủy đăng ký bất kỳ lúc nào</span>
                <span><i class="fas fa-check"></i> Ưu đãi độc quyền</span>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <!-- Company Info -->
            <div class="footer-section">
                <div class="d-flex align-center mb-4">
                    <img src="assets/img/logo.png" alt="ShopifyT" style="height: 2rem; width: auto;">
                    <span class="ml-3" style="font-size: 1.25rem; font-weight: 700;">ShopifyT</span>
                </div>
                <p class="mb-4" style="color: var(--gray-300);">
                    Thương mại điện tử hàng đầu Việt Nam với hơn 10,000 sản phẩm chất lượng cao.
                </p>
                <div class="footer-social">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="footer-section">
                <h3>Liên kết nhanh</h3>
                <ul class="footer-links">
                    <li><a href="index.php">Trang chủ</a></li>
                    <li><a href="products.php">Sản phẩm</a></li>
                    <li><a href="#">Về chúng tôi</a></li>
                    <li><a href="#">Liên hệ</a></li>
                    <li><a href="#">Tin tức</a></li>
                </ul>
            </div>

            <!-- Customer Service -->
            <div class="footer-section">
                <h3>Hỗ trợ khách hàng</h3>
                <ul class="footer-links">
                    <li><a href="#">Trung tâm trợ giúp</a></li>
                    <li><a href="#">Chính sách đổi trả</a></li>
                    <li><a href="#">Chính sách bảo mật</a></li>
                    <li><a href="#">Điều khoản sử dụng</a></li>
                    <li><a href="#">FAQ</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="footer-section">
                <h3>Thông tin liên hệ</h3>
                <div style="color: var(--gray-300);">
                    <div class="d-flex align-center mb-3">
                        <i class="fas fa-map-marker-alt mr-3" style="color: var(--primary-color);"></i>
                        <span>123 Đường ABC, Quận 1, TP.HCM</span>
                    </div>
                    <div class="d-flex align-center mb-3">
                        <i class="fas fa-phone mr-3" style="color: var(--primary-color);"></i>
                        <span>1900-xxxx</span>
                    </div>
                    <div class="d-flex align-center mb-3">
                        <i class="fas fa-envelope mr-3" style="color: var(--primary-color);"></i>
                        <span>support@shopifyt.com</span>
                    </div>
                    <div class="d-flex align-center">
                        <i class="fas fa-clock mr-3" style="color: var(--primary-color);"></i>
                        <span>8:00 - 22:00 (T2-CN)</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>
                © 2024 ShopifyT. Tất cả quyền được bảo lưu. |
                <a href="#" class="text-white">Chính sách bảo mật</a> |
                <a href="#" class="text-white">Điều khoản sử dụng</a>
            </p>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<button id="backToTop" class="back-to-top">
    <i class="fas fa-arrow-up"></i>
</button>

<!-- JavaScript -->
<script>
    // Back to top button
    const backToTopBtn = document.getElementById('backToTop');

    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            backToTopBtn.classList.add('visible');
        } else {
            backToTopBtn.classList.remove('visible');
        }
    });

    backToTopBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Add to cart functionality
    function addToCart(productId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'shopping-cart.php';
        form.innerHTML = `
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="${productId}">
                <input type="hidden" name="quantity" value="1">
            `;
        document.body.appendChild(form);
        form.submit();
    }

    // Toggle wishlist
    function toggleWishlist(productId) {
        const btn = event.target.closest('.product-action-btn');
        const icon = btn.querySelector('i');

        if (icon.classList.contains('fas')) {
            icon.classList.remove('fas');
            icon.classList.add('far');
            btn.style.backgroundColor = 'var(--white)';
            btn.style.color = 'var(--gray-600)';
        } else {
            icon.classList.remove('far');
            icon.classList.add('fas');
            btn.style.backgroundColor = 'var(--danger-color)';
            btn.style.color = 'var(--white)';
        }
    }

    // Newsletter subscription
    document.querySelector('.newsletter-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const email = this.querySelector('.newsletter-input').value;
        const btn = this.querySelector('.newsletter-btn');

        if (email) {
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> Đã đăng ký!';
            btn.style.backgroundColor = 'var(--success-color)';
            this.querySelector('.newsletter-input').value = '';

            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.style.backgroundColor = 'var(--accent-color)';
            }, 3000);
        }
    });

    // Search functionality
    document.querySelector('.search-btn').addEventListener('click', function() {
        const searchInput = document.querySelector('.search-input');
        const searchTerm = searchInput.value.trim();

        if (searchTerm) {
            window.location.href = `products.php?search=${encodeURIComponent(searchTerm)}`;
        }
    });

    document.querySelector('.search-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.querySelector('.search-btn').click();
        }
    });

    // Animation on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe all animated elements
    document.querySelectorAll('.animate-fadeInUp').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.8s ease-out, transform 0.8s ease-out';
        observer.observe(el);
    });
</script>
</body>

</html>