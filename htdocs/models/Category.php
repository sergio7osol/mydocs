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
    
    /**
     * Get all categories and organize them into a hierarchical tree structure
     * Using the path field for proper ancestry tracking
     * 
     * @return array Array of category data with level information for UI rendering
     */
    public static function getTree() {
        if (!self::$db) {
            throw new Exception("Database connection not established");
        }
        
        // Get all categories with proper sorting to maintain hierarchy
        $query = "SELECT c.*, p.name as parent_name FROM categories c 
                 LEFT JOIN categories p ON c.parent_id = p.id 
                 WHERE c.is_active = 1 
                 ORDER BY CASE WHEN c.parent_id IS NULL THEN 0 ELSE 1 END, c.display_order, c.name ASC";
        
        try {
            $statement = self::$db->query($query);
            $allCategories = $statement->fetchAll(PDO::FETCH_ASSOC);
            
            // Initialize result array for root categories
            $result = [];
            
            // First pass: Make sure all categories have correct level based on parent_id
            foreach ($allCategories as &$category) {
                // Root categories should have level 0
                if (empty($category['parent_id'])) {
                    $category['level'] = 0;
                }
            }
            
            // Sort categories to ensure parent categories come before children
            usort($allCategories, function($a, $b) {
                // Compare by level first
                $levelA = isset($a['level']) ? (int)$a['level'] : 0;
                $levelB = isset($b['level']) ? (int)$b['level'] : 0;
                
                if ($levelA !== $levelB) {
                    return $levelA - $levelB;
                }
                
                // Then by display_order
                $orderA = isset($a['display_order']) ? (int)$a['display_order'] : 0;
                $orderB = isset($b['display_order']) ? (int)$b['display_order'] : 0;
                
                if ($orderA !== $orderB) {
                    return $orderA - $orderB;
                }
                
                // Finally by name
                return strcmp($a['name'], $b['name']);
            });
            
            // Build the tree structure
            foreach ($allCategories as &$category) {
                // Mark root categories
                if (empty($category['parent_id'])) {
                    $result[] = &$category;
                    continue;
                }
                
                // For non-root categories, find and add to parent
                foreach ($allCategories as &$potential_parent) {
                    if ($potential_parent['id'] == $category['parent_id']) {
                        if (!isset($potential_parent['children'])) {
                            $potential_parent['children'] = [];
                        }
                        
                        // Set the level for this child category based on parent's level
                        $category['level'] = $potential_parent['level'] + 1;
                        
                        $potential_parent['children'][] = &$category;
                        break;
                    }
                }
            }
            
            return $result; // Return the hierarchical tree structure
        } catch (Exception $e) {
            error_log("Error in getTree(): " . $e->getMessage());
            return [];
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

    /**
     * Find a category by name
     * @param string $name The category name to search for
     * @return int|null The ID of the category, or null if not found
     */
    public static function getIdByName($name) {
        if (!self::$db) {
            throw new Exception("Database connection not established");
        }

        $query = "SELECT id FROM categories WHERE name = :name";
        $params = [':name' => $name];
        
        try {
            $statement = self::$db->query($query, $params);
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            
            error_log("Category search for name: '$name', Result: " . print_r($result, true));
            
            return $result ? $result['id'] : null;
        } catch (Exception $e) {
            error_log("Error finding category by name: " . $e->getMessage());
            return null;
        }
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
