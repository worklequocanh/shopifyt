<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';

function registerUser(PDO $pdo, array $data): array
{
  try {
    // Kiểm tra xem email đã tồn tại chưa
    $stmt = $pdo->prepare("SELECT id FROM accounts WHERE email = ?");
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) {
      return ['success' => false, 'message' => 'Email này đã được sử dụng.'];
    }

    // Băm mật khẩu
    $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

    // Thêm người dùng vào CSDL
    $sql = "INSERT INTO accounts (name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      $data['name'],
      $data['email'],
      $hashed_password,
      $data['phone'] ?? null,
      $data['address'] ?? null
    ]);
    return ['success' => true, 'message' => 'Đăng ký tài khoản thành công!'];
  } catch (PDOException $e) {
    return ['success' => false, 'message' => 'Không thể đăng ký. $e->getMessage()'];
  }
}

function loginUser(PDO $pdo, string $email, string $password)
{
  try {
    $stmt = $pdo->prepare("SELECT id, name, role, email, password
                                  FROM accounts
                                  WHERE email = ?
                                    AND is_active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
      session_regenerate_id(true);
      $_SESSION['id'] = $user['id'] ?? 3;
      $_SESSION['name'] = $user['name'] ?? 'Nguyen Van A';
      $_SESSION['role'] = $user['role'] ?? 'customer';

      // redirectIfLoggedIn();
      if ($user['role'] === 'admin' || $user['role'] === 'employee') {
        header("Location: /admin/index.php");
        exit();
      } else {
        header("Location: /index.php");
        exit();
      }
    }

    return ['success' => false, 'message' => 'Email hoặc mật khẩu không đúng'];
  } catch (PDOException $e) {
    return ['success' => false, 'message' => 'Lỗi đăng nhập: ' . $e->getMessage()];
  }
}

function isLoggedIn()
{
  return isset($_SESSION['id']) && isset($_SESSION['role']);
}

function logout(): void
{
  if (isLoggedIn()) {
    $uri = '/';
    if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'employee') {
      $uri = '/login.php';
    } else {
      $uri = '/index.php';
    }
    session_unset();
    session_destroy();
    header("Location: $uri");
    exit();
  }

  header(header: "Location: /");
  exit();
}

function redirectIfLoggedIn()
{
  if (isLoggedIn()) {
    $role = $_SESSION['role'];
    if ($role === 'customer') {
      header("Location: /index.php");
      exit();
    } elseif ($role === 'admin' || $role === 'employee') {
      header("Location: /admin/index.php");
      exit();
    } else {
      // Vai trò không hợp lệ, đăng xuất
      logout();
    }
  }
}

function restrictToRoles($allowedRoles, $redirectIfNotLoggedIn = '/login.php')
{
  if (!isLoggedIn()) {
    header("Location: $redirectIfNotLoggedIn");
    exit();
  }

  $currentRole = $_SESSION['role'];

  if (!in_array($currentRole, ['admin', 'employee', 'customer'])) {
    session_unset();
    session_destroy();

    header('Location: /login.php');
    exit();
  }

  if ($currentRole !== $allowedRoles) {
    if (!in_array($currentRole, $allowedRoles)) {
      if (in_array($currentRole, ["admin", "employee"])) {
        header("Location: /admin/index.php");
        exit();
      } elseif (in_array($currentRole, ["customer"])) {
        header("Location: /index.php");
        exit();
      } else {
        logout();
      }
    }
  }
}
