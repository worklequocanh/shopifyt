<?php
/**
 * Flash Message Component
 * Displays server-side flash messages using the notification system
 */

if (isset($_SESSION['flash_message'])) {
    $flash = $_SESSION['flash_message'];
    $type = $flash['type'] ?? 'info';
    $message = addslashes(htmlspecialchars($flash['message'] ?? '', ENT_QUOTES));
    
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.Notification) {
            window.Notification.{$type}('{$message}');
        }
    });
    </script>";
    
    unset($_SESSION['flash_message']);
}
?>