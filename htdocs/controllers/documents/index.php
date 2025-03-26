<?php

use Core\App;
use Core\Auth;
use Core\Database;

$userId = isset($_GET['user_id']) ? $_GET['user_id'] : 1;

Auth::checkPermissions($userId);

require_once base_path('controllers/DocumentController.php');
require_once base_path('models/Document.php');

$database = App::resolve(Database::class);

// Create document controller with database connection
$documentController = new DocumentController($database);

// Set the database for Document model
Document::setDatabase($database);

$documentController->clearDocumentCache();

// Get documents from database - database is the single source of truth
try {
  // Get documents directly from the database
  $documents = Document::getAll($userId);
} catch (Exception $e) {
  error_log("Error fetching documents from DB: " . $e->getMessage());
  $documents = [];
}

// Filter by category if selected
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : '';
if (!empty($selectedCategory)) {
  $documents = array_filter($documents, function ($doc) use ($selectedCategory) {
    return strcasecmp($doc['category'], $selectedCategory) === 0;
  });
}

// Filter by search term if provided
if (isset($_GET['search']) && !empty($_GET['search'])) {
  $searchTerm = $_GET['search'];
  $documents = array_filter($documents, function ($doc) use ($searchTerm) {
    return (
      stripos($doc['title'], $searchTerm) !== false ||
      stripos($doc['description'], $searchTerm) !== false ||
      stripos($doc['filename'], $searchTerm) !== false
    );
  });
}

// Pass the correct current user ID and fetch categories
$currentUserId = $userId;

// Load categories for the view
require_once base_path('models/Category.php');
Category::setDatabase($database);
$categories = Category::getAll();

$pageTitle = 'Document Management System';

// Get users and their document counts for the header
require_once base_path('models/User.php');
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

view('index.view.php', [
  'pageTitle' => $pageTitle,
  'categories' => $categories,
  'documents' => $documents,
  'currentUserId' => $currentUserId,
  'currentCategory' => $selectedCategory,
  'users' => $users,
  'userDocCounts' => $userDocCounts
]);
