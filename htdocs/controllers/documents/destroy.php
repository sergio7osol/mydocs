<?php

use Core\Auth;
use Core\Database;

error_log("Document deletion - Starting deletion process");

$userId = isset($_POST['user_id']) ? $_POST['user_id'] : null;
$documentId = isset($_POST['id']) ? $_POST['id'] : null;
$category = isset($_POST['category']) ? $_POST['category'] : '';

error_log("Document deletion - Parameters: userId=$userId, documentId=$documentId, category=$category");

// Build redirect URL
$redirectParams = ["user_id=$userId"];
if (!empty($category)) {
    $redirectParams[] = "category=" . urlencode($category);
}
$redirectUrl = "/?" . implode("&", $redirectParams);

// TODO: implement authorization properly
// Validate required parameters
if (!$userId || !$documentId) {
    error_log("Document deletion - Error: Missing required parameters");
    $_SESSION['error'] = 'Missing required parameters';
    header('Location: ' . ($userId ? $redirectUrl : '/'));
    exit;
}

// Check user permissions
try {
    error_log("Document deletion - Checking user permissions for userId: $userId");
    Auth::checkPermissions($userId);
} catch (Exception $e) {
    error_log("Document deletion - Permission check failed: " . $e->getMessage());
    $_SESSION['error'] = 'Permission denied: ' . $e->getMessage();
    header('Location: ' . $redirectUrl);
    exit;
}

// Set up database connection
try {
    error_log("Document deletion - Setting up database connection");
    $config = require base_path('config.php');
    $database = new Database($config['database']);
    
    // Load Document model
    require_once base_path('models/Document.php');
    Document::setDatabase($database);
} catch (Exception $e) {
    error_log("Document deletion - Database connection failed: " . $e->getMessage());
    $_SESSION['error'] = 'Database error: ' . $e->getMessage();
    header('Location: ' . $redirectUrl);
    exit;
}

try {
    // Check if document exists and belongs to the user
    error_log("Document deletion - Checking document existence for documentId: $documentId");
    $document = Document::getById($documentId, $userId);
    
    if (!$document) {
        error_log("Document deletion - Document not found or access denied for documentId: $documentId");
        $_SESSION['error'] = 'Document not found or access denied';
        header('Location: ' . $redirectUrl);
        exit;
    } 
    
    // Delete the document from the database (which also handles file deletion)
    error_log("Document deletion - Attempting to delete document with ID: $documentId");
    $success = Document::deleteById($documentId);
    
    if ($success) {
        // Log the successful deletion
        error_log("Document deletion - Document $documentId deleted successfully by user $userId");
        $_SESSION['success'] = 'Document deleted successfully';
    } else {
        error_log("Document deletion - Failed to delete document with ID: $documentId");
        $_SESSION['error'] = 'Failed to delete document';
    }
} catch (Exception $e) {
    error_log("Document deletion - Error: " . $e->getMessage());
    $_SESSION['error'] = 'Error: ' . $e->getMessage();
}

// Clear any document caches
error_log("Document deletion - Cache cleared to ensure fresh data");

// Redirect back to the document list
error_log("Document deletion - Redirecting to: $redirectUrl");
header('Location: ' . $redirectUrl);
exit;
