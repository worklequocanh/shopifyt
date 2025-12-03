<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gray-800">Chỉnh sửa sản phẩm</h2>
        <a href="/admin/products" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="/admin/products/edit/<?php echo $product['id']; ?>" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($product['name']); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea name="description" class="form-control" rows="5"><?php echo htmlspecialchars($product['description']); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Giá bán (VNĐ) <span class="text-danger">*</span></label>
                                <input type="number" name="price" class="form-control" required min="0" step="1000" value="<?php echo $product['price']; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Số lượng tồn kho</label>
                                <input type="number" name="stock" class="form-control" min="0" value="<?php echo $product['stock']; ?>">
                            </div>
                        </div>

                        <!-- Image Management -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Quản lý hình ảnh</label>
                            <div class="row g-3" id="image-gallery">
                                <?php if (!empty($images)): ?>
                                    <?php foreach ($images as $img): ?>
                                        <div class="col-md-3 col-6" id="image-card-<?php echo $img['id']; ?>">
                                            <div class="card h-100 border-0 shadow-sm">
                                                <div class="position-relative">
                                                    <img src="<?php echo $img['image_url']; ?>" class="card-img-top rounded" style="height: 150px; object-fit: cover;">
                                                    <?php if ($img['is_main']): ?>
                                                        <span class="position-absolute top-0 start-0 badge bg-primary m-2">Ảnh chính</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="card-body p-2 text-center">
                                                    <?php if (!$img['is_main']): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-primary w-100 mb-1" 
                                                                onclick="setMainImage(<?php echo $img['id']; ?>)">
                                                            Đặt làm chính
                                                        </button>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger w-100" 
                                                            onclick="deleteImage(<?php echo $img['id']; ?>)">
                                                        Xóa ảnh
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted">Chưa có hình ảnh nào.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- Chọn danh mục --</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo $category['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Thêm hình ảnh mới</label>
                            <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                            <small class="text-muted d-block mt-1">
                                <i class="bi bi-info-circle"></i> Có thể chọn nhiều ảnh cùng lúc.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Trạng thái</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?php echo $product['is_active'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_active">Đang bán</label>
                            </div>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" <?php echo $product['is_featured'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_featured">Sản phẩm nổi bật</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Cập nhật sản phẩm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="/assets/js/image-compressor.js"></script>
<script>
// File processing
document.querySelector('input[type="file"]').addEventListener('change', async function() {
    const submitBtn = document.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Disable submit button and show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý ảnh...';
    
    try {
        const files = this.files;
        if (files.length === 0) return;

        // Process files
        const processedFiles = await ImageCompressor.processFiles(files);
        
        // Update input files
        this.files = processedFiles;
        
        // Check total size again just in case
        let totalSize = 0;
        for (let i = 0; i < processedFiles.length; i++) {
            totalSize += processedFiles[i].size;
        }
        
        if (totalSize > 2 * 1024 * 1024) {
            showAdminToast(`Tổng dung lượng sau khi nén vẫn là ${(totalSize / 1024 / 1024).toFixed(2)}MB, lớn hơn 2MB. Vui lòng chọn ít ảnh hơn.`, 'error');
            this.value = '';
        } else {
            showAdminToast('Đã tối ưu hóa hình ảnh thành công!', 'success');
        }
        
    } catch (error) {
        console.error(error);
        showAdminToast('Có lỗi xảy ra khi xử lý ảnh.', 'error');
        this.value = '';
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

function deleteImage(imageId) {
    if (!confirm('Bạn có chắc muốn xóa ảnh này?')) return;

    fetch(`/admin/products/delete-image/${imageId}`, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAdminToast('Đã xóa ảnh', 'success');
            document.getElementById(`image-card-${imageId}`).remove();
            
            // If a new main image was assigned, update the UI
            if (data.new_main_id) {
                const newMainCard = document.getElementById(`image-card-${data.new_main_id}`);
                if (newMainCard) {
                    // Add badge
                    const imgContainer = newMainCard.querySelector('.position-relative');
                    if (!imgContainer.querySelector('.badge')) {
                        const badge = document.createElement('span');
                        badge.className = 'position-absolute top-0 start-0 badge bg-primary m-2';
                        badge.textContent = 'Ảnh chính';
                        imgContainer.appendChild(badge);
                    }
                    
                    // Remove "Set as Main" button
                    const setMainBtn = newMainCard.querySelector('button[onclick^="setMainImage"]');
                    if (setMainBtn) {
                        setMainBtn.remove();
                    }
                }
            }
        } else {
            showAdminToast(data.message || 'Lỗi khi xóa ảnh', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAdminToast('Lỗi kết nối', 'error');
    });
}

function setMainImage(imageId) {
    fetch(`/admin/products/set-main-image/${imageId}`, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAdminToast('Đã đặt làm ảnh chính', 'success');
            location.reload(); // Reload to update UI badges
        } else {
            showAdminToast(data.message || 'Lỗi khi cập nhật', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAdminToast('Lỗi kết nối', 'error');
    });
}
</script>
