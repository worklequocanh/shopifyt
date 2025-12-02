<?php

require_once __DIR__ . '/BaseModel.php';

/**
 * Product Model
 * Handles product data and operations
 */
class Product extends BaseModel
{
    protected $table = 'products';

    /**
     * Get all products with pagination
     */
    public function getAll(int $limit = 12, int $offset = 0, ?int $categoryId = null): array
    {
        $sql = "SELECT 
                    p.*, 
                    pi.image_url AS main_image,
                    c.name AS category_name
                FROM 
                    products AS p
                LEFT JOIN 
                    product_images AS pi ON p.id = pi.product_id AND pi.is_main = TRUE
                LEFT JOIN
                    categories AS c ON p.category_id = c.id
                WHERE 
                    p.is_active = TRUE";
        
        if ($categoryId) {
            $sql .= " AND p.category_id = :categoryId";
        }
        
        $sql .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        if ($categoryId) {
            $stmt->bindValue(':categoryId', $categoryId, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Count all active products
     */
    public function countAll(?int $categoryId = null): int
    {
        $sql = "SELECT COUNT(id) FROM products WHERE is_active = 1";
        
        if ($categoryId) {
            $sql .= " AND category_id = :categoryId";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':categoryId', $categoryId, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $this->pdo->query($sql);
        }
        
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get product by ID with images
     */
    public function getById(int $id)
    {
        $sql = "SELECT 
                    p.*,
                    c.name AS category_name
                FROM 
                    products AS p
                LEFT JOIN
                    categories AS c ON p.category_id = c.id
                WHERE 
                    p.id = ? AND p.is_active = 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $product = $stmt->fetch();

        if ($product) {
            $product['images'] = $this->getProductImages($id);
        }

        return $product;
    }

    /**
     * Get product images
     */
    public function getProductImages(int $productId): array
    {
        $stmt = $this->pdo->prepare("SELECT image_url, is_main 
                                     FROM product_images 
                                     WHERE product_id = ? 
                                     ORDER BY is_main DESC, created_at ASC");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    /**
     * Get featured products
     */
    public function getFeatured(int $limit = 4): array
    {
        $sql = "SELECT
                    p.id,
                    p.name,
                    p.price,
                    p.description,
                    pi.image_url AS main_image
                FROM
                    products AS p
                LEFT JOIN
                    product_images AS pi ON p.id = pi.product_id AND pi.is_main = TRUE
                WHERE
                    p.is_featured = 1
                    AND p.is_active = 1
                ORDER BY
                    p.updated_at DESC
                LIMIT ?";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi khi lấy sản phẩm nổi bật: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get products by category
     */
    public function getByCategory(int $categoryId, int $limit = 12, int $offset = 0): array
    {
        return $this->getAll($limit, $offset, $categoryId);
    }

    /**
     * Search products
     */
    public function search(string $keyword, int $limit = 12, int $offset = 0): array
    {
        $searchTerm = "%{$keyword}%";
        
        $stmt = $this->pdo->prepare(
            "SELECT p.*, c.name as category_name,
                    (SELECT image_url FROM product_images 
                     WHERE product_id = p.id AND is_main = 1 
                     LIMIT 1) as main_image
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.is_active = 1 
             AND (p.name LIKE ? OR p.description LIKE ?)
             ORDER BY p.created_at DESC
             LIMIT ? OFFSET ?"
        );
        
        $stmt->bindValue(1, $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(2, $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(3, $limit, PDO::PARAM_INT);
        $stmt->bindValue(4, $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Count search results
     */
    public function countSearch(string $keyword): int
    {
        $searchTerm = "%{$keyword}%";
        
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(id) FROM products 
             WHERE is_active = 1 
             AND (name LIKE ? OR description LIKE ?)"
        );
        
        $stmt->bindValue(1, $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(2, $searchTerm, PDO::PARAM_STR);
        $stmt->execute();
        
        return (int)$stmt->fetchColumn();
    }

    /**
     * Check stock availability
     */
    public function checkStock(int $productId, int $quantity): bool
    {
        $product = $this->find($productId);
        return $product && $product['stock'] >= $quantity;
    }

    /**
     * Decrease stock
     */
    public function decreaseStock(int $productId, int $quantity): bool
    {
        $sql = "UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$quantity, $productId, $quantity]);
    }

    /**
     * Increase stock (for order cancellation)
     */
    public function increaseStock(int $productId, int $quantity): bool
    {
        $sql = "UPDATE products SET stock = stock + ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$quantity, $productId]);
    }
}
