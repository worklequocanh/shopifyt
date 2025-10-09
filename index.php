<?php
// Gọi file config (tự đọc thông tin từ .env)
include_once __DIR__ . '/includes/config.php';

// Thử truy vấn kiểm tra kết nối
// try {
  
//   $username = "Nguyen Van A";
//   $password = password_hash("123", PASSWORD_DEFAULT);
//   $email = "email@email.com";

//   $stmt = $pdo->prepare("INSERT INTO users (username, password, email) VALUES (:username, :password, :email)");

//   $stmt->bindParam(":username", $username);
//   $stmt->bindParam(":password", $password);
//   $stmt->bindParam(":email", $email);

//   $stmt->execute();

//   echo "Them thanh cong";

// } catch (PDOException $e) {
//     echo "<h2 style='color:red;'>Kết nối thất bại ❌</h2>";
//     echo "<pre>" . $e->getMessage() . "</pre>";
// }

// try {
//   $stmt = $pdo->query("SELECT * FROM users");
//   $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
//   echo "<pre>";
//   print_r($users);
//   echo "</pre>";
// } catch (Exception $e) {
//   echo ''. $e->getMessage() .'';
// }

$pdo = null;
?>
