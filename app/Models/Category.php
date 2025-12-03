<?php

require_once __DIR__ . '/BaseModel.php';

/**
 * Category Model
 * Handles product categories
 */
class Category extends BaseModel
{
    protected $table = 'categories';

    /**
     * Get all active categories
     */
    /**
     * Get all categories with optional search
     */
    public function getAll($search = ''): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND name LIKE ?";
            $params[] = "%$search%";
        }

        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Check if category name exists
     */
    public function exists($name, $excludeId = null): bool
    {
        $sql = "SELECT id FROM {$this->table} WHERE name = ?";
        $params = [$name];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetch();
    }

    /**
     * Get category with product count
     */
    public function getAllWithProductCount(): array
    {
        $sql = "SELECT 
                    c.*,
                    COUNT(p.id) as product_count
                FROM 
                    categories c
                LEFT JOIN 
                    products p ON c.id = p.category_id AND p.is_active = 1
                GROUP BY 
                    c.id
                ORDER BY 
                    c.name ASC";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
}
