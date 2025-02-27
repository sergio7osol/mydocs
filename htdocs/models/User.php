<?php

class User {
    public $id;
    public $email;
    public $firstname;
    public $lastname;

    private static $db;

    public function __construct($id = null, $email = '', $firstname = '', $lastname = '') {
        $this->id = $id;
        $this->email = $email;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
    }

    // Set database connection
    public static function setDatabase($database) {
        self::$db = $database;
    }

    // Get user by ID
    public static function getById($id) {
        if (!self::$db) {
            throw new Exception("Database connection not established");
        }

        $query = "SELECT * FROM users WHERE id = :id";
        $params = [':id' => $id];

        $result = self::$db->select($query, $params);

        if (!empty($result)) {
            $user = $result[0];
            return new User(
                $user['id'],
                $user['email'],
                $user['firstname'],
                $user['lastname']
            );
        }

        return null;
    }

    // Get user by email
    public static function getByEmail($email) {
        if (!self::$db) {
            throw new Exception("Database connection not established");
        }

        try {
            $query = "SELECT * FROM users WHERE email = :email";
            $params = [':email' => $email];

            $statement = self::$db->query($query, $params);
            $user = $statement->fetch();

            if ($user) {
                return new User(
                    $user['id'],
                    $user['email'],
                    $user['firstname'],
                    $user['lastname']
                );
            }

            return null;
        } catch (Exception $e) {
            error_log("Error getting user by email: " . $e->getMessage());
            return null;
        }
    }

    // Get all users
    public static function getAll() {
        if (!self::$db) {
            throw new Exception("Database connection not established");
        }

        try {
            $query = "SELECT * FROM users";
            $statement = self::$db->query($query);
            $result = $statement->fetchAll();

            $users = [];
            foreach ($result as $user) {
                $users[] = new User(
                    $user['id'],
                    $user['email'],
                    $user['firstname'],
                    $user['lastname']
                );
            }

            return $users;
        } catch (Exception $e) {
            error_log("Error getting all users: " . $e->getMessage());
            return []; // Return empty array on error
        }
    }

    // Helper method to find a user ID by name
    public static function findUserIdByName($name) {
        if (!self::$db) {
            throw new Exception("Database connection not established");
        }

        try {
            // Try to match by first name first
            $query = "SELECT id FROM users WHERE firstname LIKE :name";
            $params = [':name' => "%$name%"];
            
            $statement = self::$db->query($query, $params);
            $result = $statement->fetch();

            if ($result) {
                return $result['id'];
            }

            return null;
        } catch (Exception $e) {
            error_log("Error finding user by name: " . $e->getMessage());
            return null;
        }
    }

    // Get default user ID (Sergey)
    public static function getDefaultUserId() {
        try {
            // Find user with firstname 'Sergey'
            $query = "SELECT id FROM users WHERE firstname = 'Sergey' LIMIT 1";
            
            $statement = self::$db->query($query);
            $result = $statement->fetch();

            if ($result) {
                return $result['id'];
            }

            // If not found, return the first user
            $query = "SELECT id FROM users ORDER BY id LIMIT 1";
            $statement = self::$db->query($query);
            $result = $statement->fetch();

            if ($result) {
                return $result['id'];
            }
            
            // If still no user found, return 1 as fallback
            return 1;
        } catch (Exception $e) {
            error_log("Error getting default user: " . $e->getMessage());
            return 1; // Fallback to ID 1
        }
    }
    
    // Get default user (Sergey)
    public static function getDefault() {
        try {
            // Find user with firstname 'Sergey'
            $query = "SELECT * FROM users WHERE firstname = 'Sergey' LIMIT 1";
            $result = self::$db->select($query);

            if (!empty($result)) {
                $user = $result[0];
                return new User(
                    $user['id'],
                    $user['email'],
                    $user['firstname'],
                    $user['lastname']
                );
            }

            // If not found, return the first user
            $query = "SELECT * FROM users ORDER BY id LIMIT 1";
            $result = self::$db->select($query);

            if (!empty($result)) {
                $user = $result[0];
                return new User(
                    $user['id'],
                    $user['email'],
                    $user['firstname'],
                    $user['lastname']
                );
            }

            // If no users exist, create a default user
            return new User(1, 'sergey@example.com', 'Sergey', 'Osokin');
        } catch (Exception $e) {
            // If database error, return a default user
            return new User(1, 'sergey@example.com', 'Sergey', 'Osokin');
        }
    }

    // Save user to database
    public function save() {
        if (!self::$db) {
            throw new Exception("Database connection not established");
        }

        // If ID is set, update existing record
        if ($this->id) {
            $query = "UPDATE users SET 
                        email = :email, 
                        firstname = :firstname,
                        lastname = :lastname
                      WHERE id = :id";
            
            $params = [
                ':id' => $this->id,
                ':email' => $this->email,
                ':firstname' => $this->firstname,
                ':lastname' => $this->lastname
            ];

            self::$db->execute($query, $params);
            return $this->id;
        } 
        // Otherwise insert new record
        else {
            $query = "INSERT INTO users (email, firstname, lastname) 
                      VALUES (:email, :firstname, :lastname)";
            
            $params = [
                ':email' => $this->email,
                ':firstname' => $this->firstname,
                ':lastname' => $this->lastname
            ];

            $this->id = self::$db->insert($query, $params);
            return $this->id;
        }
    }

    // Get full name
    public function getFullName() {
        return trim($this->firstname . ' ' . $this->lastname);
    }
}
?>
