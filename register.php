<?php
session_start();
include_once("includes/config.php");
include_once("includes/functions.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $regName = trim($_POST["reg-name"] ?? "");
    $regEmail = trim($_POST["reg-email"] ?? "");
    $regPassword = trim($_POST["reg-password"] ?? "");
    $regPhone = trim($_POST["reg-phone"] ?? "");
    $regAddress = trim($_POST["reg-address"] ?? "");
    echo "$regName $regEmail $regPassword $regPhone $regAddress";

    if ($regEmail === "" || $regPassword === "" || $regName === "") {
        $error = "Vui lòng nhập đầy đủ thông tin.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM accounts WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user["password"] === $pass) { // ❗ Chỉ so sánh chuỗi — nên mã hóa sau
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_name"] = $user["name"];
            $_SESSION["user_role"] = $user["role"];

            // Điều hướng theo quyền
            if ($user["role"] === "admin") {
                header("Location: admin_dashboard.php");
            } elseif ($user["role"] === "employee") {
                header("Location: employee_dashboard.php");
            } else {
                header("Location: customer_home.php");
            }
            exit;
        } else {
            $error = "Sai email hoặc mật khẩu.";
        }
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login and Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-lg">
        <!-- Register Form -->
        <div id="register-form" class="space-y-6">
            <h2 class="text-2xl font-bold text-center">Register</h2>
            <form id="registerForm" class="space-y-4">
                <div>
                    <label for="reg-name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input id="reg-name" type="text" name="name" class="mt-1 w-full px-3 py-2 border rounded-md" required>
                </div>
                <div>
                    <label for="reg-email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input id="reg-email" type="email" name="email" class="mt-1 w-full px-3 py-2 border rounded-md" required>
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
                    <input id="reg-phone" type="text" name="phone" class="mt-1 w-full px-3 py-2 border rounded-md">
                </div>
                <div>
                    <label for="reg-address" class="block text-sm font-medium text-gray-700">Address (Optional)</label>
                    <textarea id="reg-address" name="address" class="mt-1 w-full px-3 py-2 border rounded-md"></textarea>
                </div>
                <button type="submit" class="w-full bg-green-500 text-white py-2 rounded-md hover:bg-green-600">Register</button>
            </form>
            <p class="text-center text-sm">Already have an account? <a href="#" onclick="showLogin()" class="text-blue-500">Login</a></p>
        </div>
    </div>

    <script src="assets/js/login.js"></script>
</body>
</html>