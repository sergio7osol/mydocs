<?php

use Core\App;
use Core\Auth;
use Core\Database;

$userId = isset($_GET['user_id']) ? $_GET['user_id'] : 1; // ID 1 (Sergey)
$documentId = isset($_GET['id']) ? $_GET['id'] : null;

if (!$documentId) {
    header("Location: /?user_id={$userId}");
    exit();
}

Auth::checkPermissions($userId);

require_once base_path('controllers/DocumentController.php');

$database = App::resolve(Database::class);

// Create document controller with database connection
$documentController = new DocumentController($database);

// Load document for editing
require_once base_path('models/Document.php');
Document::setDatabase($database);
$document = Document::getById($documentId);

// Check if document exists and user has permission to edit it
if (!$document || $document->user_id != $userId) {
    header("Location: /?user_id={$userId}&error=nopermission");
    exit();
}

// Set preselected category from document
$preselectedCategory = $document->category;

// Load categories for the view
require_once base_path('models/Category.php');
Category::setDatabase($database);
$categories = Category::getAll();

// Get users and their document counts for the header
require_once base_path('models/User.php');
User::setDatabase($database);

try {
    $users = User::getAll();

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

view('create/index.view.php', [
    'pageTitle'=> 'Edit Document',
    'categories' => $categories,
    'preselectedCategory' => $preselectedCategory,
    'currentUserId' => $userId,
    'users' => $users,
    'userDocCounts' => $userDocCounts,
    'document' => $document
]);
