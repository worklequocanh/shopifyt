<?php
/**
 * Migration: Add Voucher Support to Orders Table
 * Run this via web browser: http://localhost/migrate_orders_voucher.php
 */

// Database configuration
$host = '127.0.0.1';
$dbname = 'shopifyt';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Migration: Add Voucher Support to Orders</h2>";
    echo "<pre>";
    
    // Check if voucher_id column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM `orders` LIKE 'voucher_id'");
    if ($stmt->rowCount() == 0) {
        echo "Adding voucher_id column...\n";
        $pdo->exec("ALTER TABLE `orders` ADD COLUMN `voucher_id` INT UNSIGNED DEFAULT NULL AFTER `total_amount`");
        echo "✓ Added voucher_id column\n";
    } else {
        echo "✓ voucher_id column already exists\n";
    }
    
    // Check if discount_amount column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM `orders` LIKE 'discount_amount'");
    if ($stmt->rowCount() == 0) {
        echo "Adding discount_amount column...\n";
        $pdo->exec("ALTER TABLE `orders` ADD COLUMN `discount_amount` DECIMAL(10,2) DEFAULT 0.00 AFTER `voucher_id`");
        echo "✓ Added discount_amount column\n";
    } else {
        echo "✓ discount_amount column already exists\n";
    }
    
    // Check if foreign key constraint exists
    $stmt = $pdo->query("
        SELECT CONSTRAINT_NAME 
        FROM information_schema.TABLE_CONSTRAINTS 
        WHERE TABLE_SCHEMA = '$dbname' 
        AND TABLE_NAME = 'orders' 
        AND CONSTRAINT_NAME LIKE '%voucher%'
    ");
    
    if ($stmt->rowCount() == 0) {
        echo "Adding foreign key constraint for voucher_id...\n";
        // First, add index if not exists
        $pdo->exec("ALTER TABLE `orders` ADD INDEX `fk_voucher` (`voucher_id`)");
        // Then add foreign key
        $pdo->exec("ALTER TABLE `orders` ADD CONSTRAINT `fk_orders_vouchers` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers`(`id`) ON DELETE SET NULL");
        echo "✓ Added foreign key constraint\n";
    } else {
        echo "✓ Foreign key constraint already exists\n";
    }
    
    // Verify final structure
    echo "\n--- Current Orders Table Structure (voucher-related columns) ---\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM `orders` WHERE Field IN ('total_amount', 'voucher_id', 'discount_amount')");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf("%-20s %-20s %s\n", $row['Field'], $row['Type'], $row['Null'] == 'YES' ? 'NULL' : 'NOT NULL');
    }
    
    echo "\n✅ Migration completed successfully!\n";
    echo "\nColumn meanings:\n";
    echo "- total_amount: Final amount to pay (after discount)\n";
    echo "- voucher_id: Reference to applied voucher (if any)\n";
    echo "- discount_amount: Amount discounted from original total\n";
    echo "\n</pre>";
    
} catch (PDOException $e) {
    echo "<pre style='color: red;'>";
    echo "❌ Migration failed: " . $e->getMessage();
    echo "\n</pre>";
}
?>
