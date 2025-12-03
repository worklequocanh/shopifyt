<?php
require_once __DIR__ . '/app/Core/Database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    $stmt = $pdo->query("SHOW COLUMNS FROM vouchers");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Columns in 'vouchers' table:\n";
    print_r($columns);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
