<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-gray-800 fw-bold">Quản lý sản phẩm</h2>
            <p class="text-muted mb-0">Tổng số: <?php echo $products ? count($products) : 0; ?> sản phẩm hiển thị</p>
        </div>
        <a href="/admin/products/create" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-lg"></i> Thêm sản phẩm mới
        </a>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link <?php echo $status === null ? 'active fw-bold' : 'text-muted'; ?>" href="?status=all&search=<?php echo $search; ?>&category=<?php echo $category_id; ?>">
                Tất cả
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $status === 1 ? 'active fw-bold' : 'text-muted'; ?>" href="?status=1&search=<?php echo $search; ?>&category=<?php echo $category_id; ?>">
                Đang bán
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $status === 0 ? 'active fw-bold' : 'text-muted'; ?>" href="?status=0&search=<?php echo $search; ?>&category=<?php echo $category_id; ?>">
                Đã ẩn
            </a>
        </li>
    </ul>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="/admin/products" method="GET" class="row g-3">
                <input type="hidden" name="status" value="<?php echo $status === null ? 'all' : $status; ?>">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 bg-light" placeholder="Tìm kiếm tên sản phẩm..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-select bg-light">
                        <option value="">Tất cả danh mục</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">Lọc</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Product List -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4" style="width: 300px;">Sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Giá bán</th>
                            <th>Tồn kho</th>
                            <th>Trạng thái</th>
                            <th class="text-end pe-4">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $product): ?>
                                <tr id="product-row-<?php echo $product['id']; ?>">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo !empty($product['main_image']) ? $product['main_image'] : 'https://via.placeholder.com/60'; ?>" 
                                                 class="rounded border me-3" 
                                                 style="width: 50px; height: 50px; object-fit: cover;"
                                                 alt="">
                                            <div>
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($product['name']); ?></div>
                                                <small class="text-muted">ID: #<?php echo $product['id']; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?>
                                        </span>
                                    </td>
                                    <td class="fw-bold text-primary">
                                        <?php echo number_format($product['price'], 0, ',', '.'); ?>đ
                                    </td>
                                    <td>
                                        <?php if ($product['stock'] == 0): ?>
                                            <span class="badge bg-danger">Hết hàng</span>
                                        <?php elseif ($product['stock'] <= 10): ?>
                                            <span class="text-warning fw-bold"><?php echo $product['stock']; ?></span>
                                        <?php else: ?>
                                            <span class="text-success fw-bold"><?php echo $product['stock']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" 
                                                   onclick="toggleStatus(<?php echo $product['id']; ?>)" 
                                                   <?php echo $product['is_active'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label small text-muted">
                                                <?php echo $product['is_active'] ? 'Hiện' : 'Ẩn'; ?>
                                            </label>
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <a href="/admin/products/edit/<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-secondary" title="Sửa">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteProduct(<?php echo $product['id']; ?>)" title="Xóa">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Không tìm thấy sản phẩm nào
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="card-footer bg-white py-3">
            <nav>
                <ul class="pagination justify-content-center mb-0">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>&category=<?php echo $category_id; ?>&status=<?php echo $status === null ? 'all' : $status; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleStatus(id) {
    fetch(`/admin/products/toggle/${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAdminToast('Cập nhật trạng thái thành công', 'success');
                
                // If we are in a specific status tab (not 'all'), remove the row
                const currentStatus = '<?php echo $status; ?>';
                if (currentStatus !== '' && currentStatus !== 'all') {
                    const row = document.getElementById(`product-row-${id}`);
                    if (row) {
                        row.style.transition = 'opacity 0.5s';
                        row.style.opacity = '0';
                        setTimeout(() => row.remove(), 500);
                    }
                }
            } else {
                showAdminToast(data.message || 'Có lỗi xảy ra', 'error');
                setTimeout(() => location.reload(), 1000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAdminToast('Lỗi kết nối', 'error');
        });
}

function deleteProduct(id) {
    if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này? Hành động này không thể hoàn tác.')) {
        fetch(`/admin/products/delete/${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAdminToast('Xóa sản phẩm thành công', 'success');
                    const row = document.getElementById(`product-row-${id}`);
                    if (row) {
                        row.remove();
                    }
                } else {
                    showAdminToast(data.message || 'Có lỗi xảy ra', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAdminToast('Lỗi kết nối', 'error');
            });
    }
}
</script>
