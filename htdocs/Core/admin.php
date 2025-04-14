<?php

use Core\Database;

// Define application root path
define('APP_ROOT', dirname(__DIR__));

// Include essential files
require_once base_path('debug/error_log.php');
require_once base_path('Core/Database.php');
require_once base_path('models/Document.php');
require_once base_path('Http/controllers/DocumentController.php');

// Set error display for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize database connection
$config = require base_path('Core/config.php');
$db = new Database($config['database']);

// Initialize document controller
$documentController = new DocumentController($db);

// Handle repair action if requested
$message = '';
$repaired = 0;

if (isset($_GET['action'])) {
    $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 1;
    
    if ($_GET['action'] === 'repair_categories') {
        $repaired = $documentController->repairDocumentCategories($userId);
        $message = "Database repair completed. Fixed {$repaired} document categories.";
    }
    else if ($_GET['action'] === 'import_documents') {
        // Special admin-only function for manual import from filesystem
        // This is intentionally kept as an administrative tool for data recovery/migration
        // but should not be part of normal application flow as database is the only source of truth
        $imported = $documentController->importFilesystemDocuments($userId);
        $message = "Document import completed. Imported {$imported} new documents.";
    }
}

// Get document stats
$userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 1;
$docCount = $documentController->countUserDocuments($userId);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyDocs Admin</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .admin-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .admin-header {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .admin-actions {
            margin: 20px 0;
        }
        .action-button {
            display: inline-block;
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 3px;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .admin-message {
            margin: 20px 0;
            padding: 10px;
            background-color: #f9f9f9;
            border-left: 5px solid #4CAF50;
        }
        .admin-stats {
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <?php include base_path('views/partials/header.php'); ?>
    
    <div class="admin-container">
        <div class="admin-header">
            <h1>MyDocs Admin</h1>
            <p>Database and document management</p>
        </div>
        
        <?php if (!empty($message)): ?>
        <div class="admin-message">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>
        
        <div class="admin-stats">
            <h2>Document Statistics</h2>
            <p>Total documents for user <?php echo $userId; ?>: <?php echo $docCount; ?></p>
        </div>
        
        <div class="admin-actions">
            <h2>Database Maintenance</h2>
            <a href="admin.php?action=repair_categories&user_id=<?php echo $userId; ?>" class="action-button">Repair Document Categories</a>
            <a href="admin.php?action=import_documents&user_id=<?php echo $userId; ?>" class="action-button">Import New Documents</a>
            <a href="/" class="action-button" style="background-color: #607D8B;">Back to Documents</a>
        </div>
    </div>
    
    <?php include base_path('views/partials/header.php'); ?>
</body>
</html>
