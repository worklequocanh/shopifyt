<!-- Notification System -->
<script src="/assets/js/notification.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
  // Auto-dismiss alerts
  setTimeout(() => {
    document.querySelectorAll('.alert').forEach(alert => {
      if (bootstrap.Alert) {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
      }
    });
  }, 5000);
</script>
