<?php

class Document {
    public $id;
    public $title;
    public $description; 
    public $upload_date;  // Changed from date
    public $category;
    public $file_path;    // Changed from filepath
    public $filename;
    public $file_size;
    public $file_type;    // Added
    public $user_id;      // Changed from user

    private static $db;

    public function __construct($id = null, $title = '', $description = '', $upload_date = '', $category = '', $file_path = '', $filename = '', $file_size = 0, $file_type = '', $user_id = 'sergey') {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->upload_date = $upload_date;
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
                ':category' => $this->category,
                ':file_path' => $this->file_path,
                ':filename' => $this->filename,
                ':file_size' => $this->file_size,
                ':file_type' => $this->file_type,
                ':user_id' => $this->user_id
            ];
        } else {
            // Insert new record
            $query = "INSERT INTO documents (title, description, upload_date, category, file_path, filename, file_size, file_type, user_id) 
                      VALUES (:title, :description, :upload_date, :category, :file_path, :filename, :file_size, :file_type, :user_id)";
            $params = [
                ':title' => $this->title,
                ':description' => $this->description,
                ':upload_date' => $this->upload_date ? $this->upload_date : date('Y-m-d H:i:s'),
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
            $query .= " WHERE user_id = :user_id";
            $params[':user_id'] = $user;
        }
        
        $query .= " ORDER BY upload_date DESC";
        
        $statement = self::$db->query($query, $params);
        return $statement->fetchAll();
    }

    // Get document by ID
    public static function getById($id) {
        if (!self::$db) {
            throw new Exception("Database connection not established");
        }

        $statement = self::$db->query("SELECT * FROM documents WHERE id = :id", [':id' => $id]);
        $data = $statement->fetch();
        
        if (!$data) {
            return null;
        }
        
        return new Document(
            $data['id'],
            $data['title'],
            $data['description'],
            $data['upload_date'],
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
            $query .= " AND user_id = :user_id";
            $params[':user_id'] = $user;
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
}
