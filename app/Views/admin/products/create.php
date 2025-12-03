<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gray-800">Thêm sản phẩm mới</h2>
        <a href="/admin/products" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="/admin/products/create" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea name="description" class="form-control" rows="5"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Giá bán (VNĐ) <span class="text-danger">*</span></label>
                                <input type="number" name="price" class="form-control" required min="0" step="1000">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Số lượng tồn kho</label>
                                <input type="number" name="stock" class="form-control" value="0" min="0">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- Chọn danh mục --</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Hình ảnh</label>
                            <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                            <small class="text-muted">Có thể chọn nhiều ảnh. Ảnh đầu tiên sẽ là ảnh chính.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Trạng thái</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                                <label class="form-check-label" for="is_active">Đang bán</label>
                            </div>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured">
                                <label class="form-check-label" for="is_featured">Sản phẩm nổi bật</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="bi bi-save"></i> Lưu sản phẩm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="/assets/js/image-compressor.js"></script>
<script>
document.querySelector('input[type="file"]').addEventListener('change', async function() {
    const submitBtn = document.getElementById('submitBtn');
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
</script>
