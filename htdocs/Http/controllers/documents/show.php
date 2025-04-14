<?php

use Core\App;
use Core\Database;

require_once base_path('models/Document.php');
require_once base_path('models/User.php');
require_once base_path('Http/controllers/DocumentController.php');

$database = App::resolve(Database::class);

// Create document controller with database connection
$documentController = new DocumentController($database);

// Set the database connection for the Document model
Document::setDatabase($database);

$id = isset($_GET['id']) ? $_GET['id'] : null;
$userId = isset($_GET['user_id']) ? $_GET['user_id'] : 1; // Default to user ID 1 (Sergey)

// Get user information
try {
  $user = User::getById($userId);
  $userName = $user ? $user->firstname : 'User ' . $userId;
} catch (Exception $e) {
  $userName = 'User ' . $userId;
}

$document = null;
if (!empty($id)) {
  $document = Document::getById($id, $userId);
}

if (!$document) {
  header('Location: /'); // Document not found or access denied
  exit;
}

// Process file path information for display
$dockerPath = $document->file_path;
$localPath = str_replace('/var/www/html/', '', $dockerPath);

// For debugging
error_log("Original file path: " . $dockerPath);
error_log("Simplified path: " . $localPath);

// Get actual Windows-style absolute path for the user
$windowsBasePath = 'c:\\Users\\Sergey_Osokin\\IT\\Programming\\PHP\\mydocs-docs\\htdocs\\';
$windowsFilePath = $windowsBasePath . str_replace('/', '\\', $localPath);

// Get directory path (without the filename) - make sure to use the Windows path separator
$windowsDirectoryPath = substr($windowsFilePath, 0, strrpos($windowsFilePath, '\\'));

error_log("Windows absolute path for clipboard: " . $windowsFilePath);
error_log("Windows directory path for clipboard: " . $windowsDirectoryPath);

// Get users and their document counts for the header
User::setDatabase($database);

try {
  $users = User::getAll();
  
  // Get document counts per user
  $userDocCounts = [];
  foreach ($users as $user) {
    try {
      $userDocCounts[$user->id] = $documentController->countUserDocuments($user->id);
    } catch (Exception $e) {
      error_log("Error getting document count for user {$user->id}: " . $e->getMessage());
      $userDocCounts[$user->id] = 0;
    }
  }
} catch (Exception $e) {
  $users = [
    new User(1, 'sergey@example.com', 'Sergey', 'Osokin'),
    new User(2, 'galina@example.com', 'Galina', 'Treneva')
  ];
  $userDocCounts = [1 => 0, 2 => 0];
}

view('show.view.php', [
  'document' => $document,
  'pageTitle' => 'View Document: ' . $document->title,
  'currentUserId' => $userId,
  'userName' => $userName,
  'localPath' => $localPath,
  'windowsFilePath' => $windowsFilePath,
  'windowsDirectoryPath' => $windowsDirectoryPath,
  'users' => $users,
  'userDocCounts' => $userDocCounts
]);
