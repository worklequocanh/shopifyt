<?php

/**
 * Base Model
 * Parent class for all models
 */
class BaseModel
{
    protected $db;
    protected $pdo;
    protected $table;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->pdo = $this->db->getConnection();
    }

    /**
     * Find a record by ID
     */
    public function find($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Find all records
     */
    public function findAll()
    {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }

    /**
     * Find records with conditions
     */
    public function where($column, $value)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$column} = ?");
        $stmt->execute([$value]);
        return $stmt->fetchAll();
    }

    /**
     * Find one record with conditions
     */
    public function findWhere($column, $value)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$column} = ?");
        $stmt->execute([$value]);
        return $stmt->fetch();
    }

    /**
     * Create a new record
     */
    public function create(array $data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($data));
        
        return $this->pdo->lastInsertId();
    }

    /**
     * Update a record
     */
    public function update($id, array $data)
    {
        $sets = [];
        foreach (array_keys($data) as $column) {
            $sets[] = "{$column} = ?";
        }
        $setString = implode(', ', $sets);
        
        $sql = "UPDATE {$this->table} SET {$setString} WHERE id = ?";
        $values = array_values($data);
        $values[] = $id;
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Delete a record
     */
    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Count records
     */
    public function count($where = null, $params = [])
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['count'];
    }

    /**
     * Execute raw query
     */
    protected function query($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
