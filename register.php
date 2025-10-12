<?php
session_start();
include_once("includes/config.php");
include_once("includes/auth.php");
include_once("includes/functions.php");

redirectIfLoggedIn();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm-password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // Mảng chứa lỗi
    $errors = [];

    // Validate dữ liệu
    if (empty($name)) {
        $errors[] = "Tên là bắt buộc";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ";
    }

    if (empty($password)) {
        $errors[] = "Mật khẩu là bắt buộc";
    } elseif (strlen($password) < 6) {
        $errors[] = "Mật khẩu phải dài ít nhất 6 ký tự";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Mật khẩu xác nhận không khớp";
    }

    if (!empty($phone) && !preg_match("/^[0-9]{10,11}$/", $phone)) {
        $errors[] = "Số điện thoại không hợp lệ";
    }

    // Kiểm tra email đã tồn tại
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM accounts WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Email đã được sử dụng";
        }
    } catch (PDOException $e) {
        $errors[] = "Lỗi kiểm tra email: " . $e->getMessage();
    }

    // Nếu không có lỗi, tiến hành đăng ký
    if (empty($errors)) {
        try {
            // Mã hóa mật khẩu
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Chuẩn bị câu lệnh SQL
            $sql = "INSERT INTO accounts (name, email, password, phone, address) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);

            // Thực thi câu lệnh với dữ liệu
            $stmt->execute([$name, $email, $hashed_password, $phone, $address]);

            // Chuyển hướng hoặc thông báo thành công
            $success = "Đăng ký thành công! Vui lòng <a href='/login.php' class='text-blue-500'>đăng nhập</a>.";
        } catch (PDOException $e) {
            $errors[] = "Lỗi đăng ký: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-lg">
        <!-- Register Form -->
        <div id="register-form" class="space-y-6">
            <h2 class="text-2xl font-bold text-center">Register</h2>

            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <p><?php echo $success; ?></p>
                </div>
            <?php endif; ?>

            <form id="registerForm" method="POST" action="" class="space-y-4">
                <div>
                    <label for="reg-name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input id="reg-name" type="text" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" class="mt-1 w-full px-3 py-2 border rounded-md" required>
                </div>
                <div>
                    <label for="reg-email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input id="reg-email" type="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" class="mt-1 w-full px-3 py-2 border rounded-md" required>
                </div>
                <div>
                    <label for="reg-password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input id="reg-password" type="password" name="password" class="mt-1 w-full px-3 py-2 border rounded-md" required>
                </div>
                <div>
                    <label for="reg-confirm-password" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <input id="reg-confirm-password" type="password" name="confirm-password" class="mt-1 w-full px-3 py-2 border rounded-md" required>
                </div>
                <div>
                    <label for="reg-phone" class="block text-sm font-medium text-gray-700">Phone (Optional)</label>
                    <input id="reg-phone" type="text" name="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>" class="mt-1 w-full px-3 py-2 border rounded-md">
                </div>
                <div>
                    <label for="reg-address" class="block text-sm font-medium text-gray-700">Address (Optional)</label>
                    <textarea id="reg-address" name="address" class="mt-1 w-full px-3 py-2 border rounded-md"><?php echo htmlspecialchars($address ?? ''); ?></textarea>
                </div>
                <button type="submit" class="w-full bg-green-500 text-white py-2 rounded-md hover:bg-green-600">Register</button>
            </form>
            <p class="text-center text-sm">Already have an account? <a href="/login.php" class="text-blue-500">Login</a></p>
        </div>
    </div>

</body>

</html>