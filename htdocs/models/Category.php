<?php

class Category {
    public $id;
    public $name;
    
    private static $db;

    public function __construct($id = null, $name = '') {
        $this->id = $id;
        $this->name = $name;
    }
    
    public static function setDatabase($database) {
        self::$db = $database;
    }

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

    public static function delete($id) {
        if (!self::$db) {
            throw new Exception("Database connection not established");
        }

        // Count documents in this category
        $query = "SELECT COUNT(*) as count FROM documents WHERE category = (SELECT name FROM categories WHERE id = :id)";
        $params = [':id' => $id];
        
        $statement = self::$db->query($query, $params);
        $result = $statement->fetch();
        $documentCount = $result ? $result['count'] : 0;

        // Get the category name for deleting associated documents
        $categoryQuery = "SELECT name FROM categories WHERE id = :id";
        $categoryStatement = self::$db->query($categoryQuery, $params);
        $categoryData = $categoryStatement->fetch();
        
        if (!$categoryData) {
            throw new Exception("Category not found");
        }

        $categoryName = $categoryData['name'];
        
        // Begin transaction to ensure all operations succeed or fail together
        self::$db->connection->beginTransaction();
        
        try {
            // If there are documents in this category, delete them first
            if ($documentCount > 0) {
                // Get all documents in this category
                $docsQuery = "SELECT id FROM documents WHERE category = :category";
                $docsParams = [':category' => $categoryName];
                $docsStatement = self::$db->query($docsQuery, $docsParams);
                $documents = $docsStatement->fetchAll(PDO::FETCH_ASSOC);
                
                // Load Document class to use its delete method
                require_once 'Document.php';
                Document::setDatabase(self::$db);
                
                // Delete each document
                foreach ($documents as $doc) {
                    Document::deleteById($doc['id']);
                }
            }
            
            // Now delete the category
            $deleteQuery = "DELETE FROM categories WHERE id = :id";
            $deleteParams = [':id' => $id];
            $deleteStatement = self::$db->query($deleteQuery, $deleteParams);
            
            // Commit transaction
            self::$db->connection->commit();
            
            return [
                'success' => true,
                'count' => $documentCount
            ];
        } catch (Exception $e) {
            // Rollback transaction if any operation fails
            self::$db->connection->rollBack();
            throw $e;
        }
    }
}
