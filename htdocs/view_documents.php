<?php
// Simple script to view documents from the database
require_once 'Database.php';
require_once 'models/Document.php';

// Force Docker environment settings for this script 
// to ensure proper container-to-container communication
putenv('DOCKER_ENV=true');

// Set page title
$pageTitle = "Documents from Database";

// Bootstrap CSS for better styling
echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $pageTitle . '</title>
    <link rel="stylesheet" href="/public/base.css">
    <style>
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 20px;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 20px;
            background-color: #f9f9f9;
        }
        .card-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .card-title {
            font-size: 1.5em;
            margin: 0;
            color: #333;
        }
        .card-body {
            color: #666;
        }
        .card-footer {
            border-top: 1px solid #eee;
            padding-top: 10px;
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
            font-size: 0.9em;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            background-color: #6c757d;
            color: white;
            font-size: 0.8em;
        }
        .badge-primary { background-color: #007bff; }
        .badge-success { background-color: #28a745; }
        .badge-warning { background-color: #ffc107; color: #333; }
        .badge-info { background-color: #17a2b8; }
        .file-info {
            color: #6c757d;
            font-size: 0.9em;
        }
        .actions a {
            margin-left: 10px;
            text-decoration: none;
            color: #007bff;
        }
        .actions a:hover {
            text-decoration: underline;
        }
        .doc-count {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #e9ecef;
            border-radius: 4px;
            text-align: center;
        }
        .button {
            display: inline-block;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            cursor: pointer;
            font-size: 16px;
        }
        .button:hover {
            background-color: #0069d9;
        }
    </style>
</head>
<body>
    <header>
        <h1>' . $pageTitle . '</h1>
    </header>
    <div class="container">';

try {
    // Load database configuration
    $config = require 'config.php';
    
    // Create database connection
    $db = new Database($config['database']);
    echo '<div class="card">
            <div class="card-header">
                <h2 class="card-title">Database Connection</h2>
            </div>
            <div class="card-body">
                <p>✅ Successfully connected to MySQL database at ' . $config['database']['host'] . ':' . $config['database']['port'] . '</p>
            </div>
        </div>';
    
    // Set database for Document model
    Document::setDatabase($db);
    
    // Get all documents
    $documents = Document::getAll();
    $docCount = count($documents);
    
    echo '<div class="doc-count">
            <h2>Found ' . $docCount . ' documents in database</h2>
          </div>';
    
    // Display documents
    if ($docCount > 0) {
        foreach ($documents as $doc) {
            // Format file size
            $fileSize = $doc['file_size'];
            if ($fileSize < 1024) {
                $formattedSize = $fileSize . ' B';
            } elseif ($fileSize < 1048576) {
                $formattedSize = round($fileSize / 1024, 2) . ' KB';
            } else {
                $formattedSize = round($fileSize / 1048576, 2) . ' MB';
            }
            
            // Get file extension
            $extension = pathinfo($doc['filename'], PATHINFO_EXTENSION);
            
            echo '<div class="card">
                    <div class="card-header">
                        <h3 class="card-title">' . htmlspecialchars($doc['title']) . ' <span class="badge badge-primary">' . htmlspecialchars($doc['category']) . '</span></h3>
                    </div>
                    <div class="card-body">
                        <p>' . htmlspecialchars($doc['description'] ?: 'No description available.') . '</p>
                        <div class="file-info">
                            <p><strong>File:</strong> ' . htmlspecialchars($doc['filename']) . ' <span class="badge badge-info">' . strtoupper($extension) . '</span></p>
                            <p><strong>Size:</strong> ' . $formattedSize . '</p>
                            <p><strong>Type:</strong> ' . htmlspecialchars($doc['file_type']) . '</p>
                            <p><strong>Path:</strong> ' . htmlspecialchars($doc['file_path']) . '</p>
                            <p><strong>Upload Date:</strong> ' . htmlspecialchars($doc['upload_date']) . '</p>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div>
                            <span class="badge badge-success">User: ' . htmlspecialchars($doc['user_id']) . '</span>
                            <span class="badge badge-warning">ID: ' . $doc['id'] . '</span>
                        </div>
                        <div class="actions">
                            <a href="index.php?route=view&id=' . $doc['id'] . '&user=' . $doc['user_id'] . '">View</a>
                            <a href="index.php?route=download&id=' . $doc['id'] . '">Download</a>
                        </div>
                    </div>
                </div>';
        }
    } else {
        echo '<div class="card">
                <div class="card-body">
                    <p>No documents found in the database.</p>
                </div>
            </div>';
    }
    
    // Link back to main app
    echo '<p><a href="index.php" class="button">Back to Document Management System</a></p>';
    
} catch (Exception $e) {
    echo '<div class="card">
            <div class="card-header">
                <h2 class="card-title">Error</h2>
            </div>
            <div class="card-body">
                <p>❌ ' . htmlspecialchars($e->getMessage()) . '</p>
            </div>
        </div>';
}

echo '</div></body></html>';
?>
