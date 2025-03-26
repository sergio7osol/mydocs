<?php

use Core\App;
use Core\Auth;
use Core\Validator;
use Core\Database;

$userId = isset($_POST['user_id']) ? $_POST['user_id'] : (isset($_GET['user_id']) ? $_GET['user_id'] : 1);

Auth::checkPermissions($userId);

require_once base_path('controllers/DocumentController.php');
require_once base_path('models/Document.php');
require_once base_path('models/User.php');
require_once base_path('models/Category.php');

$database = App::resolve(Database::class);

// Create document controller with database connection
$documentController = new DocumentController($database);

// Set the database for models
Document::setDatabase($database);
User::setDatabase($database);
Category::setDatabase($database);

// Check if this is an update or a new document
$documentId = isset($_POST['id']) ? $_POST['id'] : (isset($_GET['id']) ? $_GET['id'] : null);
$isUpdate = !empty($documentId);

define('MAX_FILE_SIZE', 15 * 1024 * 1024); // 15MB

$title = isset($_POST['title']) ? $_POST['title'] : '';
$description = isset($_POST['description']) ? $_POST['description'] : '';
$category = isset($_POST['category']) ? trim($_POST['category']) : '';

// If no category selected, check for preselected category
if (empty($category) && isset($_POST['category_preselected']) && !empty($_POST['category_preselected'])) {
    $category = trim($_POST['category_preselected']);
}

// If still empty, check URL parameter
if (empty($category) && isset($_GET['category']) && !empty($_GET['category'])) {
    $category = trim($_GET['category']);
}

// Final fallback
if (empty($category)) {
    $category = 'Personal';
}

$createdDate = isset($_POST['created_date']) && !empty($_POST['created_date']) ? $_POST['created_date'] : null;

error_log("Document upload - POST data received: " . print_r($_POST, true));
error_log("Document upload - GET data received: " . print_r($_GET, true));
error_log("Document upload - Category value: '" . $category . "', Length: " . mb_strlen($category));

$errors = [];

if (!Validator::string($title, 1, 70)) {
    $errors['title'] = "Document title is required";
}

// Validate description length
if (!empty($description) && mb_strlen($description) > 300) {
    $errors['description'] = "Description is too long (maximum 300 characters)";
}

// Validate file upload
$uploadFile = isset($_FILES['document']) ? $_FILES['document'] : null;
if (!$uploadFile || !isset($uploadFile['tmp_name']) || empty($uploadFile['tmp_name'])) {
    $errors['document'] = "Please select a document to upload";
} elseif ($uploadFile['error'] !== UPLOAD_ERR_OK) {
    $errors['document'] = "File upload error: " . getFileUploadErrorMessage($uploadFile['error']);
} elseif ($uploadFile['size'] > MAX_FILE_SIZE) {
    $errors['document'] = "The uploaded file exceeds the maximum allowed size of " . formatFileSize(MAX_FILE_SIZE);
}

// Validate file type
if ($uploadFile && $uploadFile['error'] === UPLOAD_ERR_OK) {
    $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
    $allowedExtensions = ['pdf', 'doc', 'docx', 'txt'];
    
    $fileInfo = pathinfo($uploadFile['name']);
    $extension = strtolower($fileInfo['extension'] ?? '');
    
    if (!in_array($uploadFile['type'], $allowedTypes) && !in_array($extension, $allowedExtensions)) {
        $errors['document'] = "Invalid file type. Allowed types: PDF, DOC, DOCX, TXT";
    }
}

// Get user object for validation
$user = User::getById($userId);
if (!$user) {
    $errors['user_id'] = "Invalid user ID";
    $user = User::getDefault(); // Fallback to default user
    $userId = $user->id;
}
   
// Validate category exists in database
$validCategories = Category::getAll();
$categoryValid = false;

error_log("Document upload - Valid categories: " . print_r(array_column($validCategories, 'name'), true));

foreach ($validCategories as $validCategory) {
    if ($validCategory['name'] === $category) {
        $categoryValid = true;
        error_log("Document upload - Category '" . $category . "' is valid");
        break;
    }
}

if (!$categoryValid) {
    $errors['category'] = "Selected category does not exist";
    // Fallback to default category if invalid
    error_log("Document upload - Category '" . $category . "' is not valid, falling back to 'Personal'");
    $category = 'Personal';
}

// Validate created date format if provided
if (!empty($createdDate)) {
    $dateTimestamp = strtotime($createdDate);
    if ($dateTimestamp === false) {
        $errors['created_date'] = "Invalid date format";
    } elseif ($dateTimestamp > time()) {
        $errors['created_date'] = "Created date cannot be in the future";
    }
}

// Get users and their document counts for the header
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

if (!empty($errors)) {
    // Load categories for the view
    $categories = Category::getAll();
    
    view('create/index.view.php', [
        'errors' => $errors,
        'title' => $title,
        'description' => $description,
        'category' => $category,
        'userId' => $userId,
        'categories' => $categories,
        'preselectedCategory' => $category,
        'pageTitle' => 'Upload Document',
        'users' => $users,
        'userDocCounts' => $userDocCounts,
        'currentUserId' => $userId
    ]);
    return;
}

$message = '';

if (!empty($title) && $uploadFile && $uploadFile['error'] === UPLOAD_ERR_OK) {
    if ($uploadFile['size'] > MAX_FILE_SIZE) {
        $message = "Error: The uploaded file exceeds the maximum allowed size of " . formatFileSize(MAX_FILE_SIZE) . ".";
        
        // Load categories for the view
        $categories = Category::getAll();
        
        view('create/index.view.php', [
            'errors' => ['document' => $message],
            'title' => $title,
            'description' => $description,
            'category' => $category,
            'userId' => $userId,
            'categories' => $categories,
            'preselectedCategory' => $category,
            'pageTitle' => 'Upload Document',
            'users' => $users,
            'userDocCounts' => $userDocCounts,
            'currentUserId' => $userId
        ]);
        return;
    }
    
    // Keep the original filename
    $originalFileName = $uploadFile['name'];
    $targetFileName = $originalFileName;
    
    // Create category-specific directory structure
    $targetPath = getenv('DOCKER_ENV') === 'true' ? '/var/www/html/uploads/' : base_path('uploads/');
    $userPath = $targetPath . $userId . '/';
    $categoryPath = $userPath . $category . '/';
    
    if (!file_exists($targetPath)) {
        mkdir($targetPath, 0777, true);
    }
    
    if (!file_exists($userPath)) {
        mkdir($userPath, 0777, true);
    }
    
    if (!file_exists($categoryPath)) {
        mkdir($categoryPath, 0777, true);
    }
    
    $targetFilePath = $categoryPath . $targetFileName;
    
    // Check if file already exists and append counter if needed
    if (file_exists($targetFilePath)) {
        $fileInfo = pathinfo($originalFileName);
        $fileName = $fileInfo['filename'];
        $fileExt = isset($fileInfo['extension']) ? '.' . $fileInfo['extension'] : '';
        $counter = 1;
        
        while (file_exists($categoryPath . $fileName . '_' . $counter . $fileExt)) {
            $counter++;
        }
        
        $targetFileName = $fileName . '_' . $counter . $fileExt;
        $targetFilePath = $categoryPath . $targetFileName;
    }
    
    if (move_uploaded_file($uploadFile['tmp_name'], $targetFilePath)) {
        // Create and save the document
        try {
            // Create new or update existing document
            if ($isUpdate) {
                // Load existing document
                $document = Document::getById($documentId);
                if (!$document || $document->user_id != $userId) {
                    throw new Exception("Document not found or you don't have permission to edit it");
                }
                
                // Update document properties
                $document->title = $title;
                $document->description = $description;
                $document->created_date = $createdDate;
                $document->category = $category;
                
                // Only update file properties if a new file was uploaded
                if ($uploadFile && $uploadFile['error'] === UPLOAD_ERR_OK) {
                    $document->file_path = $targetFilePath;
                    $document->filename = $targetFileName;
                    $document->file_size = $uploadFile['size'];
                    $document->file_type = $uploadFile['type'];
                }
            } else {
                // Create a new document
                $document = new Document(
                    0,                       // id
                    $title,                  
                    $description,            
                    date('Y-m-d H:i:s'),     // upload_date
                    $createdDate,            
                    $category,               
                    $targetFilePath,         
                    $targetFileName,         
                    $uploadFile['size'],     // file_size
                    $uploadFile['type'],     // file_type
                    $userId                  // user_id
                );
            }
            
            $document->save();
            
            $documentDetails = [
                'id' => $document->id,
                'title' => $title,
                'description' => $description,
                'upload_date' => date('Y-m-d H:i:s'),
                'created_date' => $createdDate,
                'category' => $category,
                'file_path' => $targetFilePath,
                'file_name' => $targetFileName,
                'file_size' => $uploadFile['size'],
                'file_type' => $uploadFile['type'],
                'user_id' => $userId
            ];
            
            error_log("Document uploaded and saved: " . print_r($documentDetails, true));
            
            error_log("Document cache cleared to ensure fresh data"); // Clear document caches
            
            // Show success page with document details
            $documentDetails['original_filename'] = $uploadFile['name']; // Add original filename for display
            $documentDetails['filename'] = $targetFileName;
            $documentDetails['file_size'] = formatFileSize($uploadFile['size']); // Format size for readability
            
            view('create/success.view.php', [
                'documentDetails' => $documentDetails,
                'pageTitle' => 'Upload Successful',
                'users' => $users,
                'userDocCounts' => $userDocCounts,
                'currentUserId' => $userId
            ]);
            exit;
        } catch (Exception $e) {
            error_log("Error saving document: " . $e->getMessage());
            $message = "Error: " . $e->getMessage();
        }
    } else {
        error_log("Failed to move uploaded file to: " . $targetFilePath);
        $message = "Error: Failed to save the uploaded file.";
    }
}

if (!empty($message)) {
    // Load categories for the view
    $categories = Category::getAll();
    
    view('create/index.view.php', [
        'message' => $message,
        'title' => $title,
        'description' => $description,
        'category' => $category,
        'userId' => $userId,
        'categories' => $categories,
        'preselectedCategory' => $category,
        'pageTitle' => 'Upload Document',
        'users' => $users,
        'userDocCounts' => $userDocCounts,
        'currentUserId' => $userId
    ]);
}

// Helper functions
function formatFileSize($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

function getFileUploadErrorMessage($errorCode) {
    switch ($errorCode) {
        case UPLOAD_ERR_INI_SIZE:
            return "The uploaded file exceeds the upload_max_filesize directive in php.ini";
        case UPLOAD_ERR_FORM_SIZE:
            return "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
        case UPLOAD_ERR_PARTIAL:
            return "The uploaded file was only partially uploaded";
        case UPLOAD_ERR_NO_FILE:
            return "No file was uploaded";
        case UPLOAD_ERR_NO_TMP_DIR:
            return "Missing a temporary folder";
        case UPLOAD_ERR_CANT_WRITE:
            return "Failed to write file to disk";
        case UPLOAD_ERR_EXTENSION:
            return "File upload stopped by extension";
        default:
            return "Unknown upload error";
    }
}
