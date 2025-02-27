<?php

class Document {
    public $id;
    public $title;
    public $description; 
    public $upload_date;  // Changed from date
    public $created_date; // The date the document was created (not when it was uploaded)
    public $category;
    public $file_path;    // Changed from filepath
    public $filename;
    public $file_size;
    public $file_type;    // Added
    public $user_id;      // Changed from user

    private static $db;

    public function __construct($id = null, $title = '', $description = '', $upload_date = '', $created_date = null, $category = '', $file_path = '', $filename = '', $file_size = 0, $file_type = '', $user_id = 1) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->upload_date = $upload_date;
        $this->created_date = $created_date;
        $this->category = $category;
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

    // Save document to database
    public function save() {
        if (!self::$db) {
            throw new Exception("Database connection not established");
        }

        // If ID is set, update existing record
        if ($this->id) {
            $query = "UPDATE documents SET 
                        title = :title, 
                        description = :description,
                        upload_date = :upload_date,
                        created_date = :created_date,
                        category = :category, 
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
                ':category' => $this->category,
                ':file_path' => $this->file_path,
                ':filename' => $this->filename,
                ':file_size' => $this->file_size,
                ':file_type' => $this->file_type,
                ':user_id' => $this->user_id
            ];
        } else {
            // Insert new record
            $query = "INSERT INTO documents (title, description, upload_date, created_date, category, file_path, filename, file_size, file_type, user_id) 
                      VALUES (:title, :description, :upload_date, :created_date, :category, :file_path, :filename, :file_size, :file_type, :user_id)";
            $params = [
                ':title' => $this->title,
                ':description' => $this->description,
                ':upload_date' => $this->upload_date ? $this->upload_date : date('Y-m-d H:i:s'),
                ':created_date' => $this->created_date,
                ':category' => $this->category,
                ':file_path' => $this->file_path,
                ':filename' => $this->filename,
                ':file_size' => $this->file_size,
                ':file_type' => $this->file_type,
                ':user_id' => $this->user_id
            ];
        }

        $statement = self::$db->query($query, $params);
        
        if (!$this->id) {
            $this->id = self::$db->connection->lastInsertId();
        }
        
        return true;
    }

    // Get all documents
    public static function getAll($user = null) {
        if (!self::$db) {
            throw new Exception("Database connection not established");
        }

        $query = "SELECT * FROM documents";
        $params = [];
        
        if ($user) {
            // Handle both user ID (number) and legacy username (string)
            if (is_numeric($user)) {
                $query .= " WHERE user_id = :user_id";
                $params[':user_id'] = $user;
            } else {
                // Try to find the user ID by name/email for backward compatibility
                try {
                    require_once 'User.php';
                    User::setDatabase(self::$db);
                    $userId = User::findUserIdByName($user);
                    if ($userId) {
                        $query .= " WHERE user_id = :user_id";
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

    // Get document by ID
    public static function getById($id, $userId = null) {
        if (!self::$db) {
            throw new Exception("Database connection not established");
        }

        $query = "SELECT * FROM documents WHERE id = :id";
        $params = [':id' => $id];
        
        // If user ID is provided, ensure the document belongs to the user
        if ($userId !== null) {
            $query .= " AND user_id = :user_id";
            $params[':user_id'] = $userId;
        }
        
        $statement = self::$db->query($query, $params);
        $data = $statement->fetch();
        
        if (!$data) {
            return null;
        }
        
        return new Document(
            $data['id'],
            $data['title'],
            $data['description'],
            $data['upload_date'],
            $data['created_date'],
            $data['category'],
            $data['file_path'],
            $data['filename'],
            $data['file_size'],
            $data['file_type'],
            $data['user_id']
        );
    }

    // Get documents by category
    public static function getByCategory($category, $user = null) {
        if (!self::$db) {
            throw new Exception("Database connection not established");
        }

        $query = "SELECT * FROM documents WHERE category = :category";
        $params = [':category' => $category];
        
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

    // Delete document by ID
    public static function deleteById($id) {
        if (!self::$db) {
            throw new Exception("Database connection not established");
        }

        $document = self::getById($id);
        
        if ($document && file_exists($document->file_path)) {
            // Delete the physical file
            unlink($document->file_path);
        }
        
        // Delete from database
        $statement = self::$db->query("DELETE FROM documents WHERE id = :id", [':id' => $id]);
        return $statement->rowCount() > 0;
    }

    // Check if documents exist in the database
    public static function hasDocuments() {
        if (!self::$db) {
            throw new Exception("Database connection not established");
        }

        $query = "SELECT COUNT(*) as count FROM documents";
        $statement = self::$db->query($query);
        $result = $statement->fetch();
        
        return $result['count'] > 0;
    }
}
