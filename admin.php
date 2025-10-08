<?php

session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: login.php");
    exit;
}

include "config.php";

// Xử lý thêm sản phẩm
if (isset($_POST['add'])) {
    $name  = $_POST['name'];
    $price = $_POST['price'];
    $img   = $_POST['img'];

    $sql = "INSERT INTO products (name, price, img) VALUES ('$name', '$price', '$img')";
    $conn->query($sql);
    header("Location: admin.php");
    exit;
}

// Xử lý xóa sản phẩm
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM products WHERE id=$id");
    header("Location: admin.php");
    exit;
}

// Lấy danh sách sản phẩm
$result = $conn->query("SELECT * FROM products");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Admin - Quản lý sản phẩm</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container my-5">
  <h2>🔧 Quản lý sản phẩm</h2>

  <!-- Form thêm sản phẩm -->
  <form method="post" class="row g-2 mb-4">
    <div class="col"><input class="form-control" name="name" placeholder="Tên sản phẩm" required></div>
    <div class="col"><input class="form-control" name="price" placeholder="Giá" required></div>
    <div class="col"><input class="form-control" name="img" placeholder="Link ảnh" required></div>
    <div class="col"><button class="btn btn-primary w-100" type="submit" name="add">Thêm</button></div>
  </form>

  <!-- Bảng sản phẩm -->
  <table class="table table-bordered">
    <thead><tr><th>ID</th><th>Tên</th><th>Giá</th><th>Ảnh</th><th>Hành động</th></tr></thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= $row['name'] ?></td>
          <td>$<?= $row['price'] ?></td>
          <td><img src="<?= $row['img'] ?>" width="80"></td>
          <td>
            <a class="btn btn-sm btn-warning" href="edit.php?id=<?= $row['id'] ?>">Sửa</a>
            <a class="btn btn-sm btn-danger" href="admin.php?delete=<?= $row['id'] ?>" onclick="return confirm('Xóa sản phẩm này?')">Xóa</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  <a href="logout.php" class="btn btn-danger mb-3">Đăng xuất</a>
</body>
</html>