<?php
session_start();
include_once("includes/config.php");
include_once("includes/auth.php");
include_once("includes/functions.php");

redirectIfLoggedIn();

// Kiểm tra nếu form được submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Mảng chứa lỗi
    $errors = [];

    // Validate dữ liệu
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ";
    }

    if (empty($password)) {
        $errors[] = "Mật khẩu là bắt buộc";
    }

    // Nếu không có lỗi validate, kiểm tra thông tin đăng nhập
    if (empty($errors)) {
        try {
            // Truy vấn kiểm tra email
            $stmt = $pdo->prepare("SELECT * FROM accounts WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Kiểm tra xem email tồn tại và mật khẩu có khớp
            if ($user && password_verify($password, $user['password'])) {
                // Lưu thông tin vào session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];

                // Chuyển hướng đến trang chính (hoặc trang bạn muốn)
                header("Location: /");
                exit();
            } else {
                $errors[] = "Email hoặc mật khẩu không đúng";
            }
        } catch (PDOException $e) {
            $errors[] = "Lỗi đăng nhập: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-lg">
        <!-- Login Form -->
        <div id="login-form" class="space-y-6">
            <h2 class="text-2xl font-bold text-center">Login</h2>

            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form id="loginForm" method="POST" action="" class="space-y-4">
                <div>
                    <label for="login-email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input id="login-email" type="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" class="mt-1 w-full px-3 py-2 border rounded-md" required>
                </div>
                <div>
                    <label for="login-password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input id="login-password" type="password" name="password" class="mt-1 w-full px-3 py-2 border rounded-md" required>
                </div>
                <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-md hover:bg-blue-600">Login</button>
            </form>
            <p class="text-center text-sm">Don't have an account? <a href="/register.php" class="text-blue-500">Register</a></p>
        </div>
    </div>

</body>

</html>