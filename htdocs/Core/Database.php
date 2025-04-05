<?php

namespace Core;

use PDO; 
use PDOException;
use Exception;

class Database  
{
    public $connection;
    public $statement;

    public function __construct($config, $username = 'myuser', $password = 'myuserpass')
    {
        try {
            $dsn = 'mysql:' . http_build_query($config, '', ';');
            
            $options = [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false  
            ];

            // Create PDO connection
            $this->connection = new PDO($dsn, $username, $password, $options);
            
            // Set character encoding to utf8mb4 for proper handling of special characters
            $this->connection->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
            $this->connection->exec("SET CHARACTER SET utf8mb4");
            
            // Log successful connection in development environments
            if (getenv('DEBUG') === 'true') {
                error_log("Database connected successfully to {$config['host']}:{$config['port']}");
                error_log("Character set and collation set to utf8mb4_unicode_ci");
            }
        } catch (PDOException $e) {
            // Log error details
            error_log("Database connection failed: " . $e->getMessage());
            error_log("Connection details: host={$config['host']}, port={$config['port']}, dbname={$config['dbname']}");
            
            // Re-throw the exception
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
 
    public function query($query, $params = [])
    {
        try {
            $this->statement = $this->connection->prepare($query);
            $this->statement->execute($params);
            return $this->statement;
        } catch (PDOException $e) {
            error_log("Query failed: " . $query);
            error_log("Error: " . $e->getMessage());
            
            // Re-throw the exception
            throw new Exception("Database query failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get the last inserted ID
     * 
     * @return int The ID of the last inserted row
     */
    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Begin a transaction
     */
    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Commit a transaction
     */
    public function commit()
    {
        return $this->connection->commit();
    }
    
    /**
     * Roll back a transaction
     */
    public function rollBack()
    {
        return $this->connection->rollBack();
    }
    
    /**
     * Execute a query that doesn't return results (INSERT, UPDATE, DELETE)
     */
    public function execute($query, $params = [])
    {
        $statement = $this->query($query, $params);
        return $statement->rowCount();
    }
    
    /**
     * Select records from the database
     */
    public function select($query, $params = [])
    {
        $statement = $this->query($query, $params);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Insert a record and return the last insert ID
     */
    public function insert($query, $params = [])
    {
        $this->query($query, $params);
        return $this->connection->lastInsertId();
    }

    public function find()
    {
        return $this->statement->fetch();
    }

    public function findOrFail()
    {
        $result = $this->find();

        if (! $result) {
            abort();
        }

        return $result;
    }
}
