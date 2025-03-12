<?php

class Category {
    public $id;
    public $name;
    
    private static $db;

    public function __construct($id = null, $name = '') {
        $this->id = $id;
        $this->name = $name;
    }
    
    // Set database connection
    public static function setDatabase($database) {
        self::$db = $database;
    }

    // Save category to database
    public function save() {
        if (!self::$db) {
            throw new Exception("Database connection not established");
        }

        // If ID is set, update existing record
        if ($this->id) {
            $query = "UPDATE categories SET name = :name WHERE id = :id";
            $params = [
                ':id' => $this->id,
                ':name' => $this->name
            ];
        } else {
            $query = "INSERT INTO categories (name) VALUES (:name)";
            $params = [':name' => $this->name];
        }

        $statement = self::$db->query($query, $params);
        
        if (!$this->id) {
            $this->id = self::$db->connection->lastInsertId();
        }
        
        return true;
    }

    // Get all categories
    public static function getAll() {
        if (!self::$db) {
            throw new Exception("Database connection not established");
        }

        $query = "SELECT * FROM categories ORDER BY name ASC";
        
        try {
            $statement = self::$db->query($query);
            $results = $statement->fetchAll(PDO::FETCH_ASSOC);
            return $results;
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Get category by ID
    public static function getById($id) {
        if (!self::$db) {
            throw new Exception("Database connection not established");
        }

        $query = "SELECT * FROM categories WHERE id = :id";
        $params = [':id' => $id];
        
        $statement = self::$db->query($query, $params);
        $data = $statement->fetch();
        
        if (!$data) {
            return null;
        }
        
        return new Category($data['id'], $data['name']);
    }

    // Delete category by ID
    public static function delete($id) {
        if (!self::$db) {
            throw new Exception("Database connection not established");
        }

        // Check if category is used in any documents
        $query = "SELECT COUNT(*) as count FROM documents WHERE category = (SELECT name FROM categories WHERE id = :id)";
        $params = [':id' => $id];
        
        $statement = self::$db->query($query, $params);
        $result = $statement->fetch();
        
        if ($result && $result['count'] > 0) {
            throw new Exception("Cannot delete category because it is used by " . $result['count'] . " document(s)");
        }

        $query = "DELETE FROM categories WHERE id = :id";
        $params = [':id' => $id];
        
        $statement = self::$db->query($query, $params);
        return $statement->rowCount() > 0;
    }
}
