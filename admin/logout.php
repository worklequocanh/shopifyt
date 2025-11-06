<?php
session_start();

// Lưu thông tin để hiển thị thông báo (nếu cần)
$user_name = $_SESSION['name'] ?? 'Người dùng';
$role = $_SESSION['role'] ?? 'customer';

// Xóa tất cả session variables
session_unset();

// Hủy session
session_destroy();

// Xóa cookie session (nếu có)
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng xuất - STYLEX</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <meta http-equiv="refresh" content="3;url=login.php">
</head>
<body class="bg-gradient-to-br from-gray-100 to-gray-200 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="p-12 text-center">
            <!-- Success Icon with Animation -->
            <div class="mb-6 relative">
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gradient-to-r from-green-400 to-emerald-500 animate-bounce">
                    <i class="bi bi-check-circle-fill text-white text-5xl"></i>
                </div>
            </div>

            <!-- Success Message -->
            <h1 class="text-2xl font-bold text-gray-800 mb-3">
                Đăng xuất thành công!
            </h1>
            
            <p class="text-gray-600 mb-6">
                Tạm biệt, <strong><?php echo htmlspecialchars($user_name); ?></strong>! 
                <br>Hẹn gặp lại bạn soon.
            </p>

            <!-- Progress Bar -->
            <div class="w-full bg-gray-200 rounded-full h-2 mb-6 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-2 rounded-full animate-progress"></div>
            </div>

            <p class="text-sm text-gray-500 mb-6">
                <i class="bi bi-clock-history"></i> 
                Đang chuyển hướng về trang đăng nhập...
            </p>

            <!-- Action Buttons -->
            <div class="space-y-3">
                <a href="login.php" 
                   class="block w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold py-3 rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all transform hover:scale-105 shadow-lg">
                    <i class="bi bi-box-arrow-in-right"></i> Đăng nhập lại
                </a>
                
                <a href="../index.php" 
                   class="block w-full bg-white text-gray-700 font-semibold py-3 rounded-lg border-2 border-gray-300 hover:bg-gray-50 transition-all">
                    <i class="bi bi-house"></i> Về trang chủ
                </a>
            </div>

            <!-- Additional Info -->
            <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                <p class="text-xs text-blue-800">
                    <i class="bi bi-shield-check"></i> 
                    Phiên đăng nhập của bạn đã được kết thúc an toàn
                </p>
            </div>
        </div>
    </div>

    <style>
        @keyframes progress {
            from {
                width: 0%;
            }
            to {
                width: 100%;
            }
        }
        
        .animate-progress {
            animation: progress 3s ease-in-out;
        }
    </style>

    <script>
        // Tự động chuyển hướng sau 3 giây nếu meta refresh không hoạt động
        setTimeout(function() {
            window.location.href = 'login.php';
        }, 3000);
        
        // Animation cho bounce effect
        setTimeout(function() {
            const icon = document.querySelector('.animate-bounce');
            if (icon) {
                icon.classList.remove('animate-bounce');
            }
        }, 2000);
    </script>
</body>
</html>
