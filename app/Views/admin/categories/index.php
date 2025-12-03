<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-gray-800"><i class="bi bi-grid-3x3-gap-fill"></i> Quản lý danh mục</h2>
            <small class="text-muted">Tổng số: <?php echo count($categories); ?> danh mục</small>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="bi bi-plus-circle"></i> Thêm danh mục
        </button>
    </div>

    <!-- Filter -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" action="/admin/categories" class="row g-3 align-items-end">
                <div class="col-md-10">
                    <label class="form-label"><i class="bi bi-search"></i> Tìm kiếm</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Tên danh mục..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Lọc
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4" width="10%">ID</th>
                            <th width="40%">Tên danh mục</th>
                            <th width="20%">Sản phẩm</th>
                            <th width="20%">Ngày tạo</th>
                            <th class="text-end pe-4" width="10%">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td class="ps-4">#<?php echo $category['id']; ?></td>
                                    <td>
                                        <span class="fw-bold text-primary"><?php echo htmlspecialchars($category['name']); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info rounded-pill">
                                            <?php echo $category['product_count']; ?> sản phẩm
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($category['created_at'])); ?></td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-warning"
                                                    onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <a href="/admin/categories/delete/<?php echo $category['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('<?php echo $category['product_count'] > 0 ? "Danh mục này còn {$category['product_count']} sản phẩm. Bạn có chắc muốn xóa?" : "Xóa danh mục này?"; ?>')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Không tìm thấy danh mục nào
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Thêm danh mục mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/admin/categories/store" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Thêm mới</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil"></i> Cập nhật danh mục</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/admin/categories/update" method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editCategory(category) {
    document.getElementById('edit_id').value = category.id;
    document.getElementById('edit_name').value = category.name;
    
    new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
}
</script>
