<?php

function isLoggedIn()
{
  return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

/**
 * Chuyển hướng nếu người dùng đã đăng nhập
 * Chuyển customer đến /index.php, admin/employee đến /admin/index.php
 */
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

function restrictToRoles($pdo, $allowedRoles, $redirectIfNotLoggedIn = '/login.php')
{
  // Nếu chưa đăng nhập, chuyển hướng đến trang đăng nhập
  if (!isLoggedIn()) {
    header("Location: $redirectIfNotLoggedIn");
    exit();
  }

  $role = $_SESSION['role'];

  // Kiểm tra vai trò có hợp lệ và nằm trong danh sách được phép
  if (!in_array($role, ['admin', 'employee', 'customer']) || !in_array($role, $allowedRoles)) {
    // Chuyển hướng theo vai trò
    if ($role === 'customer') {
      header("Location: /index.php");
      exit();
    } elseif ($role === 'admin' || $role === 'employee') {
      header("Location: /admin/index.php");
      exit();
    } else {
      // Vai trò không hợp lệ, đăng xuất và chuyển về đăng nhập
      session_destroy();
      header("Location: /login.php");
      exit();
    }
  }
}

function logout()
{
  session_unset();
  session_destroy();
  header("Location: /login.php");
  exit();
}
