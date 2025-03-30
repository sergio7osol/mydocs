<?php

class Document {
    public $id;
    public $title;
    public $description; 
    public $upload_date;
    public $created_date;
    public $category_id;
    public $old_category; // For backward compatibility with existing code
    public $file_path;    // Changed from filepath
    public $filename;
    public $file_size;
    public $file_type;
    public $user_id;
    public $category_name; 

    private static $db;

    public function __construct($id = null, $title = '', $description = '', $upload_date = '', $created_date = null, $category_id = null, $file_path = '', $filename = '', $file_size = 0, $file_type = '', $user_id = 1) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->upload_date = $upload_date;
        $this->created_date = $created_date;
        $this->category_id = $category_id;
        $this->old_category = ''; // Initialize for backward compatibility
        $this->file_path = $file_path;
        $this->filename = $filename;
        $this->file_size = $file_size;
        $this->file_type = $file_type;
        $this->user_id = $user_id;
    }

    // Set database connection
    public static function setDatabase($database) {
        self::$db = $database;
    }

    public function save() {
        if (!self::$db) {
            throw new Exception("Database connection not established");
        }

        $this->id = $this->id ? filter_var($this->id, FILTER_VALIDATE_INT) : null; // id: INT(11) - AUTO_INCREMENT
        
        $this->title = trim(mb_substr($this->title, 0, 70)); // title: VARCHAR(255) - utf8mb4_unicode_ci
        
        // description: MEDIUMTEXT - utf8mb4_unicode_ci, can be NULL
        if ($this->description !== null) {
            $this->description = trim($this->description);
        }
        
        $this->filename = trim(mb_substr($this->filename, 0, 50)); // filename: VARCHAR(255) - utf8mb4_unicode_ci
        
        $this->file_path = trim(mb_substr($this->file_path, 0, 255)); // file_path: VARCHAR(255) - utf8mb4_unicode_ci
        
        $this->file_size = (int)$this->file_size; // file_size: INT(11)
        
        $this->file_type = trim(mb_substr($this->file_type, 0, 50)); // file_type: VARCHAR(50)
        
        // Store old_category field for backward compatibility (it's in the DB schema)
        $this->old_category = is_string($this->old_category) ? trim(mb_substr($this->old_category, 0, 50)) : '';
        
        // category_id: INT(11)
        $this->category_id = (int)$this->category_id;
        error_log("Document upload - Category ID to save: " . $this->category_id);
        
        $this->user_id = (int)$this->user_id; // user_id: INT(11)
        
        // upload_date: DATETIME
        if (empty($this->upload_date)) {
            $this->upload_date = date('Y-m-d H:i:s');
        } else if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $this->upload_date)) {
            // Convert to proper MySQL DATETIME format if not already
            $timestamp = strtotime($this->upload_date);
            if ($timestamp) {
                $this->upload_date = date('Y-m-d H:i:s', $timestamp);
            } else {
                $this->upload_date = date('Y-m-d H:i:s');
            }
        }
        
        // created_date: DATE - can be NULL
        if ($this->created_date !== null && $this->created_date !== '') {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->created_date)) {
                // Convert to proper MySQL DATE format if not already
                $timestamp = strtotime($this->created_date);
                if ($timestamp) {
                    $this->created_date = date('Y-m-d', $timestamp);
                } else {
                    $this->created_date = null; // Set to NULL if invalid
                }
            }
        } else {
            $this->created_date = null; // Explicitly set to NULL if empty
        }

        // If ID is set, update existing record
        if ($this->id) {
            $query = "UPDATE documents SET 
                        title = :title, 
                        description = :description,
                        upload_date = :upload_date,
                        created_date = :created_date,
                        category_id = :category_id, 
                        file_path = :file_path,
                        filename = :filename,
                        file_size = :file_size,
                        file_type = :file_type,
                        user_id = :user_id
                      WHERE id = :id";
            $params = [
                ':id' => $this->id,
                ':title' => $this->title,
                ':description' => $this->description,
                ':upload_date' => $this->upload_date,
                ':created_date' => $this->created_date,
                ':category_id' => $this->category_id,
                ':file_path' => $this->file_path,
                ':filename' => $this->filename,
                ':file_size' => $this->file_size,
                ':file_type' => $this->file_type,
                ':user_id' => $this->user_id
            ];
            
            try {
                $statement = self::$db->query($query, $params);
                return $statement->rowCount() > 0;
            } catch (Exception $e) {
                error_log("Error updating document: " . $e->getMessage());
                throw $e;
            }
        } else {
            try {
                $sql = "INSERT INTO documents (title, description, upload_date, created_date, category_id, file_path, filename, file_size, file_type, user_id) 
                          VALUES (:title, :description, :upload_date, :created_date, :category_id, :file_path, :filename, :file_size, :file_type, :user_id)";
                
                error_log("Executing SQL: " . $sql);
                error_log("SQL Parameters - Title: '" . $this->title . "', Category ID: '" . $this->category_id . "'");
                
                $params = [
                    ':title' => $this->title,
                    ':description' => $this->description,
                    ':upload_date' => $this->upload_date,
                    ':created_date' => $this->created_date,
                    ':category_id' => $this->category_id,
                    ':file_path' => $this->file_path,
                    ':filename' => $this->filename,
                    ':file_size' => $this->file_size,
                    ':file_type' => $this->file_type,
                    ':user_id' => $this->user_id
                ];
                
                $statement = self::$db->query($sql, $params);
                
                $this->id = self::$db->connection->lastInsertId();
                
                error_log("Document saved successfully with ID: " . $this->id);
                return true;
            } catch (Exception $e) {
                error_log("Error saving document: " . $e->getMessage());
                throw $e;
            }
        }

        return true;
    }

    public static function getAll($user = null) {
        if (!self::$db) {
            throw new Exception("Database connection not established");
        }

        // Join with categories table to get category information
        $query = "SELECT d.*, c.name as category_name 
                 FROM documents d
                 LEFT JOIN categories c ON d.category_id = c.id";
        $params = [];
        
        if ($user) {
            // Handle both user ID (number) and legacy username (string)
            if (is_numeric($user)) {
                $query .= " WHERE d.user_id = :user_id";
                $params[':user_id'] = $user;
            } else {
                // Try to find the user ID by name/email for backward compatibility
                try {
                    require_once 'User.php';
                    User::setDatabase(self::$db);
                    $userId = User::findUserIdByName($user);
                    if ($userId) {
                        $query .= " WHERE d.user_id = :user_id";
                        $params[':user_id'] = $userId;
                    }
                } catch (Exception $e) {
                    // If user lookup fails, don't filter by user
                }
            }
        }
        
        $query .= " ORDER BY d.upload_date DESC";
        
        $statement = self::$db->query($query, $params);
        return $statement->fetchAll();
    }

    public static function getById($id, $userId = null) {
        if (!self::$db) {
            throw new Exception("Database connection not established");
        }

        $query = "SELECT d.*, c.name as category_name 
                 FROM documents d
                 LEFT JOIN categories c ON d.category_id = c.id
                 WHERE d.id = :id";
        $params = [':id' => $id];
        
        // If user ID is provided, ensure the document belongs to the user
        if ($userId !== null) {
            $query .= " AND d.user_id = :user_id";
            $params[':user_id'] = $userId;
        }
        
        $statement = self::$db->query($query, $params);
        $data = $statement->fetch();
        
        if (!$data) {
            return null;
        }
        
        $document = new Document(
            $data['id'],
            $data['title'],
            $data['description'],
            $data['upload_date'],
            $data['created_date'],
            $data['category_id'],
            $data['file_path'],
            $data['filename'],
            $data['file_size'],
            $data['file_type'],
            $data['user_id']
        );
        
        // Store the category name in a property for display purposes
        $document->category_name = $data['category_name'] ?? 'Uncategorized';
        
        return $document;
    }

    public static function getByCategory($category_id, $user = null) {
        if (!self::$db) {
            throw new Exception("Database connection not established");
        }

        $query = "SELECT * FROM documents WHERE category_id = :category_id";
        $params = [':category_id' => $category_id];
        
        if ($user) {
            // Handle both user ID (number) and legacy username (string)
            if (is_numeric($user)) {
                $query .= " AND user_id = :user_id";
                $params[':user_id'] = $user;
            } else {
                // Try to find the user ID by name for backward compatibility
                try {
                    require_once 'User.php';
                    User::setDatabase(self::$db);
                    $userId = User::findUserIdByName($user);
                    if ($userId) {
                        $query .= " AND user_id = :user_id";
                        $params[':user_id'] = $userId;
                    }
                } catch (Exception $e) {
                    // If user lookup fails, don't filter by user
                }
            }
        }
        
        $query .= " ORDER BY upload_date DESC";
        
        $statement = self::$db->query($query, $params);
        return $statement->fetchAll();
    }

    public static function deleteById($id) {
        if (!self::$db) {
            throw new Exception("Database connection not established");
        }

        $document = self::getById($id);
        
        // Delete the physical file
        if ($document && file_exists($document->file_path)) {
            unlink($document->file_path); 
        }
        
        // Delete from database
        $statement = self::$db->query("DELETE FROM documents WHERE id = :id", [':id' => $id]);
        return $statement->rowCount() > 0;
    }

    public static function hasDocuments() {
        if (!self::$db) {
            throw new Exception("Database connection not established");
        }

        $query = "SELECT COUNT(*) as count FROM documents";
        $statement = self::$db->query($query);
        $result = $statement->fetch();
        
        return $result['count'] > 0;
    }

    // DD.MM.YYYY
    public function getFormattedCreatedDate() {
        if (empty($this->created_date)) {
            return '';
        }
        $date = new DateTime($this->created_date);
        return $date->format('d.m.Y');
    }
    
    // DD.MM.YYYY HH:MM:SS
    public function getFormattedUploadDate() {
        if (empty($this->upload_date)) {
            return '';
        }
        $dateTime = new DateTime($this->upload_date);
        return $dateTime->format('d.m.Y - H:i:s');
    }
}
