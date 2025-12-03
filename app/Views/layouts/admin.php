<!DOCTYPE html>
<html lang="vi">
<head>
  <?php include __DIR__ . '/../admin/components/head.php'; ?>
</head>

<body class="bg-light">
  <?php include __DIR__ . '/../admin/components/header.php'; ?>

  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-md-2 col-lg-2 px-0">
        <?php include __DIR__ . '/../admin/components/sidebar.php'; ?>
      </div>

      <!-- Main Content -->
      <div class="col-md-10 col-lg-10">
        <main class="p-4">
          <?php echo $content; ?>
        </main>
      </div>
    </div>
  </div>

  <!-- Toast Container -->
  <div class="toast-container position-fixed p-3" style="top: 0; right: 0; z-index: 1100"></div>

  <script>
    function showAdminToast(message, type = 'success') {
        const toastContainer = document.querySelector('.toast-container');
        const bgClass = type === 'success' ? 'bg-success' : (type === 'error' ? 'bg-danger' : 'bg-primary');
        const icon = type === 'success' ? 'bi-check-circle' : (type === 'error' ? 'bi-exclamation-circle' : 'bi-info-circle');
        
        const toastHtml = `
            <div class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi ${icon} me-2"></i> ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        const toastElement = toastContainer.lastElementChild;
        const toast = new bootstrap.Toast(toastElement);
        toast.show();
        
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }

    // Check for session flash messages
    <?php if (isset($_SESSION['flash_message'])): ?>
        <?php 
            $flash = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
        ?>
        document.addEventListener('DOMContentLoaded', () => {
            showAdminToast('<?php echo addslashes($flash['message']); ?>', '<?php echo $flash['type']; ?>');
        });
    <?php endif; ?>
  </script>

  <?php include __DIR__ . '/../admin/components/footer.php'; ?>
</body>
</html>
