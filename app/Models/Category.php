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
    public function getAll(): array
    {
        return $this->findAll();
    }

    /**
     * Get category by ID
     */
    public function getById(int $id)
    {
        return $this->find($id);
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
