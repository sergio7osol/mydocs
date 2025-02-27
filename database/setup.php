<?php

// Load database configuration
require_once __DIR__ . '/../htdocs/config.php';
require_once __DIR__ . '/../htdocs/database.php';

// For debugging - display errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Loading configuration...\n";
// Initialize the database connection
$config = require __DIR__ . '/../htdocs/config.php';
echo "Configuration loaded: " . json_encode($config) . "\n";

try {
    echo "Initializing database connection...\n";
    $db = new Database($config['database']);
    echo "Database connection established\n";
    
    // Read the SQL file
    echo "Reading SQL file...\n";
    $sql = file_get_contents(__DIR__ . '/create_tables.sql');
    
    // Split the SQL into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($statement) {
            return !empty($statement);
        }
    );
    
    // Execute each statement
    echo "Executing " . count($statements) . " SQL statements...\n";
    foreach ($statements as $statement) {
        echo "Executing: " . substr($statement, 0, 40) . "...\n";
        $db->query($statement);
    }
    
    echo "Database setup completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error setting up database: " . $e->getMessage() . "\n";
}
