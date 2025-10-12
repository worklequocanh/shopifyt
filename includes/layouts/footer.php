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

    <!-- Common JavaScript -->
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

        // Common functions
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
    </script>

    <!-- Page-specific JavaScript -->
    <?php if (isset($page_scripts)): ?>
        <script>
            <?php echo $page_scripts; ?>
        </script>
    <?php endif; ?>
    </body>

    </html>