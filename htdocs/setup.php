<?php
/**
 * Web-accessible script to set up the database
 * Access this via your browser at http://localhost:8080/setup.php
 */

// For debugging - display errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Create HTML output
echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Setup</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .success { color: green; }
        .error { color: red; }
        .log { font-family: monospace; background: #f5f5f5; padding: 10px; border: 1px solid #ddd; white-space: pre-wrap; }
        h1 { color: #333; }
    </style>
</head>
<body>
    <h1>MyDocs Database Setup</h1>";

echo "<div class='log'>";

try {
    // Load the configuration
    echo "Loading configuration...\n";
    $config = require 'config.php';
    echo "Configuration loaded: " . json_encode($config) . "\n";
    
    // Include database class
    require_once 'database.php';
    
    // Initialize database connection
    echo "Initializing database connection...\n";
    $db = new Database($config['database']);
    echo "Database connection established\n";
    
    // Read the SQL file
    echo "Reading SQL file...\n";
    $sql = file_get_contents(__DIR__ . '/../database/create_tables.sql');
    
    // Split the SQL into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($statement) {
            return !empty($statement);
        }
    );
    
    // Execute each statement
    echo "Executing " . count($statements) . " SQL statements...\n";
    foreach ($statements as $index => $statement) {
        echo "Executing SQL " . ($index + 1) . ":\n";
        echo htmlspecialchars(substr($statement, 0, 100)) . "...\n";
        
        try {
            $db->query($statement);
            echo "Query executed successfully\n";
        } catch (Exception $e) {
            echo "Warning: Query execution issue: " . $e->getMessage() . "\n";
            echo "This might be okay if tables already exist.\n";
        }
    }
    
    echo "</div>";
    echo "<h2 class='success'>✅ Database setup completed successfully!</h2>";
    echo "<p>You can now:</p>
    <ul>
        <li><a href='index.php'>Go to Document Management System</a></li>
        <li><a href='index.php?route=upload&user_id=1'>Upload documents as Sergey</a></li>
        <li><a href='index.php?route=upload&user_id=2'>Upload documents as Galina</a></li>
    </ul>";
    
} catch (Exception $e) {
    echo htmlspecialchars($e->getMessage()) . "\n";
    echo "</div>";
    echo "<h2 class='error'>❌ Database setup failed</h2>";
    echo "<p>Error details: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body>
</html>";
