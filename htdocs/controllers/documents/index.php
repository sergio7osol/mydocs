<?php

use Core\App;
use Core\Database;
use Core\Middleware\Auth;

require_once base_path('models/Category.php');

$userId = isset($_GET['user_id']) ? $_GET['user_id'] : 1;

Auth::checkPermissions($userId);

require_once base_path('controllers/DocumentController.php');
require_once base_path('models/Document.php');

$database = App::resolve(Database::class);

// Create document controller with database connection
$documentController = new DocumentController($database);

// Set the database for Document model
Document::setDatabase($database);

Category::setDatabase($database);

$documentController->clearDocumentCache();

// Get documents from database - database is the single source of truth
try {
  // Get documents directly from the database
  $documents = Document::getAll($userId);
} catch (Exception $e) {
  error_log("Error fetching documents from DB: " . $e->getMessage());
  $documents = [];
}

$allDocuments = $documents;

// Filter by category if selected
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : '';
$selectedCategoryId = null;

// Initialize the $allCategories variable to prevent undefined variable errors
$allCategories = Category::getAll();
$categoryNames = [];

// Create a mapping of category IDs to names for easy lookup
foreach ($allCategories as $cat) {
    $categoryNames[$cat['id']] = $cat['name'];
}

// Get parent_ids for all categories to handle subcategory filtering properly
$categoryParentIds = [];
foreach ($allCategories as $cat) {
  $categoryParentIds[$cat['id']] = $cat['parent_id'] ?? null;
}

// Add full category path to each document
foreach ($documents as &$doc) {
  if (isset($doc['category_id']) && $doc['category_id'] > 0) {
    $doc['category_path'] = '';
    $catPath = [];
    $currCatId = $doc['category_id'];
    
    // Add the current category name
    if (isset($categoryNames[$currCatId])) {
      $catPath[] = $categoryNames[$currCatId];
      
      // Build complete path by traversing up the parent hierarchy
      while (!empty($categoryParentIds[$currCatId])) {
        $currCatId = $categoryParentIds[$currCatId];
        if (isset($categoryNames[$currCatId])) {
          $catPath[] = $categoryNames[$currCatId];
        }
      }
      
      // Reverse the path to have root category first
      $catPath = array_reverse($catPath);
      $doc['category_path'] = implode(' / ', $catPath);
    }
  } else {
    $doc['category_path'] = 'Uncategorized';
  }
}
unset($doc); // Remove the reference to avoid issues

if (!empty($selectedCategory)) {
  error_log("Filtering by category: " . $selectedCategory);

  error_log("Looking for category ID for: '" . $selectedCategory . "'");
  foreach ($allCategories as $cat) {
    error_log("Comparing with: '" . $cat['name'] . "' (ID: " . $cat['id'] . ")");
    if (strcasecmp($cat['name'], $selectedCategory) === 0) {
      $selectedCategoryId = $cat['id'];
      error_log("MATCH FOUND! Setting selectedCategoryId to: " . $selectedCategoryId);
      break;
    }
  }
  
  // Get full category path for the selected category 
  $categoryPath = [];
  if ($selectedCategoryId) {
    $currCatId = $selectedCategoryId;
    $categoryPath[] = $currCatId;
    
    // Build complete path by traversing up the parent hierarchy
    while (!empty($categoryParentIds[$currCatId])) {
      $currCatId = $categoryParentIds[$currCatId];
      $categoryPath[] = $currCatId;
    }
  }
  
  // Log category path for debugging
  error_log("Category path for '$selectedCategory': " . implode('/', $categoryPath));
  
  $documents = array_filter($documents, function ($doc) use ($selectedCategory, $selectedCategoryId, $categoryPath, $allCategories, $categoryNames, $categoryParentIds) {
    // Check if category_id exists in the document record
    $docCategoryId = isset($doc['category_id']) ? (int)$doc['category_id'] : 0;
    
    // Get the category name from the joined query result
    $docCategory = isset($doc['category_name']) ? $doc['category_name'] : '';
    
    // Add logging to see what's being compared
    error_log("Comparing document category_id: " . $docCategoryId . " (name: '" . $docCategory . "') with selected category: '" . $selectedCategory . "'");
    
    // Match by category name (exact match)
    if (!empty($docCategory) && strcasecmp($docCategory, $selectedCategory) === 0) {
      error_log("Matched by category name: " . $docCategory);
      return true;
    }
    
    // If we're viewing a top-level parent category, show all documents in its subcategories too
    if (count($categoryPath) === 1) {
      // We're viewing a top-level category, so include all its subcategories
      // First, check if the document is directly in the selected category
      if ($docCategoryId === $selectedCategoryId) {
        error_log("Matched by exact category ID: " . $docCategoryId);
        return true;
      }
      
      // Check if the document is in any subcategory of the selected category
      if ($docCategoryId > 0 && isset($categoryParentIds[$docCategoryId])) {
        $parentId = $categoryParentIds[$docCategoryId];
        // If the document's category has the selected category as its parent, include it
        if ($parentId === $selectedCategoryId) {
          error_log("Matched as subcategory of " . $selectedCategory);
          return true;
        }
      }
    } else {
      // We're viewing a subcategory, so only show exact matches
      if ($docCategoryId === $selectedCategoryId) {
        error_log("Matched by exact category ID: " . $docCategoryId);
        return true;
      }
    }
    
    return false;
  });

  error_log("Documents matching category '" . $selectedCategory . "': " . count($documents));
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

// Calculate document counts for each category USING ALL DOCUMENTS (not filtered ones)
$categoryDocCounts = [];
foreach ($allDocuments as $doc) {
  if (isset($doc['category_id']) && !empty($doc['category_id'])) {
    $catId = $doc['category_id'];
    
    // Count for the current category
    if (!isset($categoryDocCounts[$catId])) {
      $categoryDocCounts[$catId] = 0;
    }
    $categoryDocCounts[$catId]++;
    
    // Also count for all parent categories (traverse up the hierarchy)
    $parentId = $categoryParentIds[$catId] ?? null;
    while ($parentId) {
      if (!isset($categoryDocCounts[$parentId])) {
        $categoryDocCounts[$parentId] = 0;
      }
      $categoryDocCounts[$parentId]++;
      
      // Move up to the next parent
      $parentId = $categoryParentIds[$parentId] ?? null;
    }
  }
}

// Now organize categories into hierarchical tree
$categoryTree = Category::getTree();

// Also get a flat list of all categories for the dropdown in the modal
$allCategories = Category::getAll();

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

// Add debug logging before passing to view
error_log("Final currentCategory value: " . var_export($selectedCategory, true));
error_log("Final selectedCategoryId value: " . var_export($selectedCategoryId, true));

view('index.view.php', [
  'pageTitle' => $pageTitle,
  'categories' => $categoryTree,
  'allCategories' => $allCategories,
  'documents' => $documents,
  'currentUserId' => $currentUserId,
  'currentCategory' => $selectedCategory,
  'selectedCategoryId' => $selectedCategoryId,
  'users' => $users,
  'userDocCounts' => $userDocCounts,
  'categoryDocCounts' => $categoryDocCounts
]);
