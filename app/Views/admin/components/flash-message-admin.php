<?php
/**
 * Admin Flash Message Component (Bootstrap)
 * Displays server-side flash messages using Bootstrap toasts
 */

if (isset($_SESSION['flash_message'])) {
    $flash = $_SESSION['flash_message'];
    $type = $flash['type'] ?? 'info';
    $message = addslashes(htmlspecialchars($flash['message'] ?? '', ENT_QUOTES));
    
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof showAdminToast === 'function') {
            showAdminToast('{$message}', '{$type}');
        }
    });
    </script>";
    
    unset($_SESSION['flash_message']);
}
?>
