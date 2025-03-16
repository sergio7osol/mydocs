<?php

require_once __DIR__ . '/../Validator.php';

class DocumentController {
    const MAX_FILE_SIZE = 15728640; // 15 MB
    
    private $database;
    
    public function __construct($db) {
        $this->database = $db;
    }

    public function listDocuments()
    {
        $userId = isset($_GET['user_id']) ? $_GET['user_id'] : 1;
        
        $this->checkPermissions($userId);

        self::clearDocumentCache();
        
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
            $documents = array_filter($documents, function($doc) use ($selectedCategory) {
                return strcasecmp($doc['category'], $selectedCategory) === 0;
            });
        }

        // Filter by search term if provided
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $searchTerm = $_GET['search'];
            $documents = array_filter($documents, function($doc) use ($searchTerm) {
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
        require_once __DIR__ . '/../models/Category.php';
        Category::setDatabase($this->database);
        $categories = Category::getAll();

        include 'views/index.view.php';
    }
    
    /**
     * Scan the filesystem for documents
     * 
     * @param int $userId The user ID
     * @return array List of documents
     */
    private function scanForDocuments($userId) {
        $documents = [];
        // Define the upload directory
        $uploadDir = 'uploads/' . $userId . '/';
        
        // Get the requested category if any
        $selectedCategory = isset($_GET['category']) ? $_GET['category'] : '';
        
        // Check if user directory exists
        if (file_exists($uploadDir)) {
            // If a category is specified, only look in that category folder
            if (!empty($selectedCategory) && file_exists($uploadDir . $selectedCategory)) {
                $documents = array_merge($documents, $this->scanCategoryDirectory($uploadDir . $selectedCategory . '/', $selectedCategory, $userId));
            } 
            // If no category is specified, scan all category folders
            elseif (empty($selectedCategory)) {
                // Get all subdirectories (categories)
                $categoryDirs = glob($uploadDir . '*', GLOB_ONLYDIR);
                foreach ($categoryDirs as $categoryDir) {
                    $categoryName = basename($categoryDir);
                    $documents = array_merge($documents, $this->scanCategoryDirectory($categoryDir . '/', $categoryName, $userId));
                }
            }
        }

        // Filter by search term if specified
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $documents = array_filter($documents, function($doc) {
                return stripos($doc['title'], $_GET['search']) !== false;
            });
        }
        
        return $documents;
    }
    
    /**
     * Scan a category directory for documents
     * 
     * @param string $directory The directory path to scan
     * @param string $category The category name
     * @param int $userId The user ID
     * @return array List of documents
     */
    private function scanCategoryDirectory($directory, $category, $userId) {
        $documents = [];
        $files = glob($directory . '*.{pdf,doc,docx,txt}', GLOB_BRACE);
        
        // Debug log for scanning directory
        error_log("Scanning directory: {$directory} for user {$userId} in category {$category}");
        error_log("Found " . count($files) . " files in {$directory}");
        
        foreach ($files as $file) {
            $fileName = basename($file);
            $filePath = $file;
            $fileSize = filesize($file);
            $fileModified = filemtime($file);
            
            // Extract the document title from the filename
            // For files uploaded through our system, the format is: timestamp_originalname.ext
            $parts = explode('_', $fileName, 2);
            if (count($parts) > 1 && is_numeric($parts[0])) {
                // This is a file uploaded through our system
                $title = pathinfo($parts[1], PATHINFO_FILENAME);
                $uploadDate = date('Y-m-d', $parts[0]);
            } else {
                // This is a manually added file or has a different naming convention
                $title = pathinfo($fileName, PATHINFO_FILENAME);
                $uploadDate = date('Y-m-d', $fileModified);
            }
            
            // Use consistent category capitalization - important for SQL filtering later
            $normalizedCategory = ucfirst(strtolower($category));
            
            // IMPORTANT: Use path-based ID to ensure consistency and match the database ID format
            $docId = md5($filePath);
            
            $doc = [
                'id' => $docId, // Use file path hash as unique ID
                'title' => $title,
                'filename' => $fileName,
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'upload_date' => $uploadDate,
                'category' => $normalizedCategory,
                'user_id' => $userId
            ];
            
            error_log("Found document: {$title} in category {$normalizedCategory} with ID {$docId}");
            $documents[] = $doc;
        }
        
        return $documents;
    }

    /**
     * Check user permissions for accessing documents
     * 
     * @param int $userId User ID to check permissions for
     * @return void
     */
    private function checkPermissions($userId) {
        // For now, just ensure the user ID is valid
        if (!is_numeric($userId) || $userId <= 0) {
            error_log("Invalid user ID: " . $userId);
            header('Location: index.php?error=invalid_user');
            exit;
        }
        
        // In the future, we could check if the current user has permission to view this user's documents
        // For now, we're allowing all users to view all documents
        return true;
    }

    public function showUploadForm() {
        $userId = isset($_GET['user_id']) ? $_GET['user_id'] : 1; // Default to user ID 1 (Sergey)
        $preselectedCategory = isset($_GET['category']) ? $_GET['category'] : ''; // Get category from URL if available
        
        // Load categories for the view
        require_once __DIR__ . '/../models/Category.php';
        Category::setDatabase($this->database);
        $categories = Category::getAll();
        
        require 'views/create/index.view.php';
    }

    public function uploadDocument() {
        $title = isset($_POST['title']) ? $_POST['title'] : '';
        $description = isset($_POST['description']) ? $_POST['description'] : '';
        $category = isset($_POST['category']) ? trim($_POST['category']) : 'Personal';
        $userId = isset($_POST['user_id']) ? $_POST['user_id'] : 1; 
        $createdDate = isset($_POST['created_date']) && !empty($_POST['created_date']) ? $_POST['created_date'] : null;
        
        error_log("Document upload - POST data received: " . print_r($_POST, true));
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
            $errors['document'] = "File upload error: " . $this->getFileUploadErrorMessage($uploadFile['error']);
        } elseif ($uploadFile['size'] > self::MAX_FILE_SIZE) {
            $errors['document'] = "The uploaded file exceeds the maximum allowed size of " . $this->formatFileSize(self::MAX_FILE_SIZE);
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
        require_once __DIR__ . '/../models/Category.php';
        Category::setDatabase($this->database);
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
        
        if (!empty($errors)) {
            require 'views/create/index.view.php';
            return;
        }
        
        $message = '';
        
        if (!empty($title) && $uploadFile && $uploadFile['error'] === UPLOAD_ERR_OK) {
            if ($uploadFile['size'] > self::MAX_FILE_SIZE) {
                $message = "Error: The uploaded file exceeds the maximum allowed size of " . $this->formatFileSize(self::MAX_FILE_SIZE) . ".";
                require 'views/create/index.view.php';
                return;
            }
            
            // Keep the original filename
            $originalFileName = $uploadFile['name'];
            $targetFileName = $originalFileName;
            
            // Create category-specific directory structure
            $targetPath = getenv('DOCKER_ENV') === 'true' ? '/var/www/html/uploads/' : __DIR__ . '/../uploads/';
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
                // Initialize database connection
                Document::setDatabase($this->database);
                
                // Create and save the document
                $document = new Document(
                    0,                       // id
                    $title,                  // title
                    $description,            // description
                    date('Y-m-d H:i:s'),     // upload_date
                    $createdDate,            // created_date
                    $category,               // category
                    $targetFilePath,         // file_path
                    $targetFileName,         // filename
                    $uploadFile['size'],     // file_size
                    $uploadFile['type'],     // file_type
                    $userId                  // user_id
                );
                
                try {
                    $document->save();
                    
                    // Prepare document details for the success page
                    $documentDetails = [
                        'id' => $document->id,
                        'title' => $title,
                        'description' => $description,
                        'upload_date' => date('Y-m-d H:i:s'),
                        'created_date' => $createdDate,
                        'category' => $category,
                        'file_path' => $targetFilePath,
                        'original_filename' => $originalFileName,  // This is the original filename from the upload
                        'filename' => $targetFileName,  // This might be modified if there was a duplicate
                        'file_size' => $this->formatFileSize($uploadFile['size']),
                        'file_type' => $uploadFile['type'],
                        'user_id' => $userId
                    ];
                    
                    // Show success page with document details
                    require 'views/create/success.view.php';
                    return;
                } catch (Exception $e) {
                    // If document save fails, display error
                    $message = "Error saving document to database: " . $e->getMessage();
                    error_log($message);
                }
            } else {
                $message = "Error uploading file: " . $this->getFileUploadErrorMessage($uploadFile['error']);
            }
        } else if ($uploadFile && $uploadFile['error'] !== UPLOAD_ERR_OK) {
            $message = "File upload error: " . $this->getFileUploadErrorMessage($uploadFile['error']);
        }
        
        // If we get here, something went wrong
        require 'views/create/index.view.php';
    }

    public function viewDocument() {
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        $userId = isset($_GET['user_id']) ? $_GET['user_id'] : 1; // Default to user ID 1 (Sergey)
        
        $document = null;
        if (!empty($id)) {
            $document = Document::getById($id, $userId);
        }
        
        if (!$document) {
            header('Location: index.php?route=list'); // Document not found or access denied
            exit;
        }
        
        include 'views/show.view.php';
    }

    /**
     * Handle direct document downloads
     */
    public function downloadDocument() {
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        $userId = isset($_GET['user_id']) ? $_GET['user_id'] : 1; // Default to user ID 1 (Sergey)
        
        if ($id) {
            try {
                // Get document from database
                $document = Document::getById($id, $userId);
                
                if ($document) {
                    // Convert Docker path to local path
                    $dockerPath = $document->file_path;
                    $localPath = str_replace('/var/www/html/', '', $dockerPath);
                    
                    // Check if file exists and is readable
                    if (file_exists($localPath) && is_readable($localPath)) {
                        // Get file info
                        $fileSize = filesize($localPath);
                        $fileType = $document->file_type;
                        
                        // Set headers for download
                        header('Content-Description: File Transfer');
                        header('Content-Type: ' . $fileType);
                        header('Content-Disposition: attachment; filename="' . $document->filename . '"');
                        header('Content-Transfer-Encoding: binary');
                        header('Expires: 0');
                        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                        header('Pragma: public');
                        header('Content-Length: ' . $fileSize);
                        
                        // Output file and exit
                        readfile($localPath);
                        exit;
                    } else {
                        error_log("File not found or not readable: " . $localPath);
                    }
                }
            } catch (Exception $e) {
                error_log("Error downloading document: " . $e->getMessage());
            }
        }
        
        // If file not found or other error
        header('HTTP/1.0 404 Not Found');
        include 'views/404.view.php';
    }
    
    /**
     * Delete a document
     */
    public function deleteDocument() {
        // Check if the user is logged in
        $userId = isset($_GET['user_id']) ? $_GET['user_id'] : null;
        $documentId = isset($_GET['id']) ? $_GET['id'] : null;
        
        if (!$userId || !$documentId) {
            $_SESSION['error'] = 'Missing required parameters';
            header('Location: index.php?user_id=' . $userId);
            exit;
        }
        
        try {
            // First verify the document belongs to the user
            $document = Document::getById($documentId, $userId);
            
            if (!$document) {
                $_SESSION['error'] = 'Document not found or access denied';
                header('Location: index.php?user_id=' . $userId);
                exit;
            }
            
            // Delete the document
            $success = Document::deleteById($documentId);
            
            if ($success) {
                error_log("Document $documentId deleted successfully by user $userId");
                $_SESSION['success'] = 'Document deleted successfully';
            } else {
                $_SESSION['error'] = 'Failed to delete document';
            }
        } catch (Exception $e) {
            error_log("Error deleting document: " . $e->getMessage());
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
        
        // Return to the document list, preserving any category filter
        $category = isset($_GET['category']) ? '&category=' . urlencode($_GET['category']) : '';
        header('Location: index.php?user_id=' . $userId . $category);
        exit;
    }
    
    /**
     * Scan for documents for a user - can be called from header
     * Public version of scanForDocuments to use from the header
     * 
     * @param int $userId The user ID
     * @return array List of documents
     */
    public function scanForDocumentsInternal($userId) {
        return $this->scanForDocuments($userId);
    }
    
    /**
     * Count actual documents for a user, used for accurate header counts
     * 
     * @param int $userId The user ID
     * @return int The count of documents
     */
    public static function countUserDocuments($userId) {
        try {
            // Get documents from database only
            $docs = Document::getAll($userId);
            
            // Deduplicate by ID
            $uniqueIds = [];
            foreach ($docs as $doc) {
                $docId = is_array($doc) ? $doc['id'] : $doc->id;
                $uniqueIds[$docId] = true;
            }
            
            $count = count($uniqueIds);
            error_log("User {$userId} document count from database: {$count}");
            return $count;
        } catch (Exception $e) {
            error_log("Error counting documents for user {$userId}: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Clear any document caches 
     * 
     * @return void
     */
    public static function clearDocumentCache() {
        // If we implement caching in the future, clear it here
        // For now, just log that we're ensuring fresh document data
        error_log("Document cache cleared to ensure fresh data");
    }
    
    /**
     * Import documents from filesystem into database - ADMIN USE ONLY
     * 
     * This is a special administrative function for data recovery, migration, or repair.
     * It should NOT be used in normal application flow as the database is the only source of truth.
     * This method is intentionally kept for administrative purposes only and should never be
     * called from regular user-facing functionality.
     * 
     * @param int $userId The user ID to import documents for
     * @return int The number of documents imported
     */
    public function importFilesystemDocuments($userId = null) {
        if ($userId === null) {
            $userId = isset($_GET['user_id']) ? $_GET['user_id'] : 1;
        }
        
        $this->checkPermissions($userId);
        
        $importCount = 0;
        $categories = ['Personal', 'Work', 'Others', 'State Office']; // Updated categories list to match database ENUM
        $categoryMap = [];
        
        // Create mapping of lowercase category names to standard capitalization
        foreach ($categories as $category) {
            $categoryMap[strtolower($category)] = $category;
        }
        
        // Scan filesystem for documents
        $filesystemDocs = $this->scanForDocuments($userId);
        error_log("Found " . count($filesystemDocs) . " documents in filesystem for user {$userId}");
        
        // Get existing database documents to avoid duplicates
        $dbDocs = Document::getAll($userId);
        $existingDocIds = [];
        foreach ($dbDocs as $doc) {
            $docId = is_array($doc) ? $doc['id'] : $doc->id;
            $existingDocIds[$docId] = true;
        }
        
        // Import new documents into database
        foreach ($filesystemDocs as $doc) {
            if (!isset($existingDocIds[$doc['id']])) {
                // Standardize category capitalization
                $lowercaseCategory = strtolower($doc['category']);
                if (isset($categoryMap[$lowercaseCategory])) {
                    $doc['category'] = $categoryMap[$lowercaseCategory];
                }
                
                // Create a new document object - leave description as NULL
                $document = new Document(
                    $doc['id'],
                    $doc['title'],
                    null, // NULL description as requested
                    $doc['upload_date'],
                    $doc['created_date'] ?? null,
                    $doc['category'],
                    $doc['file_path'],
                    $doc['filename'],
                    $doc['file_size'],
                    $doc['file_type'] ?? '',
                    $doc['user_id']
                );
                
                // Save to database
                try {
                    Document::setDatabase($this->database);
                    $document->save();
                    
                    // Prepare document details for the success page
                    $documentDetails = [
                        'id' => $document->id,
                        'title' => $doc['title'],
                        'description' => null, // NULL description as requested
                        'upload_date' => $doc['upload_date'],
                        'created_date' => $doc['created_date'] ?? null,
                        'category' => $doc['category'],
                        'file_path' => $doc['file_path'],
                        'original_filename' => $doc['filename'],  // This is the original filename from the upload
                        'filename' => $doc['filename'],  // This might be modified if there was a duplicate
                        'file_size' => $this->formatFileSize($doc['file_size']),
                        'file_type' => $doc['file_type'] ?? '',
                        'user_id' => $doc['user_id']
                    ];
                    
                    // Show success page with document details
                    require 'views/create/success.view.php';
                    return;
                } catch (Exception $e) {
                    // If document save fails, display error
                    $message = "Error saving document to database: " . $e->getMessage();
                    error_log($message);
                }
            }
        }
        
        error_log("Imported {$importCount} new documents from filesystem to database");
        return $importCount;
    }
    
    /**
     * Repair and normalize document categories in the database
     * 
     * @param int $userId The user ID to repair documents for
     * @return int Number of documents repaired
     */
    public function repairDocumentCategories($userId = null) {
        if ($userId === null) {
            $userId = isset($_GET['user_id']) ? $_GET['user_id'] : 1;
        }
        
        $this->checkPermissions($userId);
        
        // Get documents from database
        try {
            $documents = Document::getAll($userId);
            error_log("Found " . count($documents) . " documents to check for category repair");
            
            $repairedCount = 0;
            $standardCategories = ['Personal', 'Work', 'Others', 'State Office'];
            $categoryMap = [];
            
            // Create mapping of lowercase category names to standard capitalization
            foreach ($standardCategories as $category) {
                $categoryMap[strtolower($category)] = $category;
            }
            
            // Repair each document's category
            foreach ($documents as $doc) {
                if (!is_array($doc)) {
                    // Convert object to array for consistent handling
                    $doc = [
                        'id' => $doc->id,
                        'title' => $doc->title,
                        'category' => $doc->category,
                        // other fields would be here
                        'user_id' => $doc->user_id
                    ];
                }
                
                $originalCategory = $doc['category'];
                $lowercaseCategory = strtolower($originalCategory);
                
                // Check if this category needs standardization
                if (isset($categoryMap[$lowercaseCategory]) && $categoryMap[$lowercaseCategory] !== $originalCategory) {
                    $standardizedCategory = $categoryMap[$lowercaseCategory];
                    
                    // Update the document with the standardized category
                    try {
                        // If we have the full document object, create and save it
                        $document = new Document(
                            $doc['id'], 
                            $doc['title'],
                            $doc['description'] ?? null,
                            $doc['upload_date'] ?? '',
                            $doc['created_date'] ?? null,
                            $standardizedCategory, // Standardized category
                            $doc['file_path'] ?? '',
                            $doc['filename'] ?? '',
                            $doc['file_size'] ?? 0,
                            $doc['file_type'] ?? '',
                            $doc['user_id']
                        );
                        
                        // Save the updated document
                        Document::setDatabase($this->database);
                        $document->save();
                        $repairedCount++;
                        
                        error_log("Repaired document category: '{$doc['title']}' from '{$originalCategory}' to '{$standardizedCategory}'");
                    } catch (Exception $e) {
                        error_log("Error repairing document category: " . $e->getMessage());
                    }
                }
            }
            
            error_log("Repaired {$repairedCount} document categories");
            return $repairedCount;
            
        } catch (Exception $e) {
            error_log("Error getting documents for category repair: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Format a file size in bytes to a human-readable format
     * 
     * @param int $bytes File size in bytes
     * @param int $precision Number of decimal places to round to
     * @return string Formatted file size
     */
    public function formatFileSize($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Get a human-readable error message for file upload errors
     * 
     * @param int $errorCode PHP file upload error code
     * @return string Human-readable error message
     */
    private function getFileUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return "The uploaded file exceeds the upload_max_filesize directive in php.ini.";
            case UPLOAD_ERR_FORM_SIZE:
                return "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.";
            case UPLOAD_ERR_PARTIAL:
                return "The uploaded file was only partially uploaded.";
            case UPLOAD_ERR_NO_FILE:
                return "No file was uploaded.";
            case UPLOAD_ERR_NO_TMP_DIR:
                return "Missing a temporary folder.";
            case UPLOAD_ERR_CANT_WRITE:
                return "Failed to write file to disk.";
            case UPLOAD_ERR_EXTENSION:
                return "A PHP extension stopped the file upload.";
            default:
                return "Unknown upload error.";
        }
    }

    // Add a new category
    public function addCategory() {
        if (!isset($_POST['name']) || empty($_POST['name'])) {
            echo json_encode(['success' => false, 'error' => 'Category name is required']);
            return;
        }
        
        $name = trim($_POST['name']);
        
        try {
            require_once __DIR__ . '/../models/Category.php';
            Category::setDatabase($this->database);
            
            $category = new Category(null, $name);
            $category->save();
            
            echo json_encode(['success' => true, 'id' => $category->id]);
            
        } catch (Exception $e) {
            error_log("Error adding category: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    
    // Get category document count
    public function getCategoryDocumentCount() {
        if (!isset($_POST['id']) || empty($_POST['id'])) {
            echo json_encode(['success' => false, 'error' => 'Category ID is required']);
            return;
        }
        
        $categoryId = $_POST['id'];
        
        try {
            require_once __DIR__ . '/../models/Category.php';
            Category::setDatabase($this->database);
            
            // Get category name
            $category = Category::getById($categoryId);
            if (!$category) {
                echo json_encode(['success' => false, 'error' => 'Category not found']);
                return;
            }
            
            // Count documents in this category
            require_once __DIR__ . '/../models/Document.php';
            Document::setDatabase($this->database);
            $documents = Document::getByCategory($category->name);
            $count = count($documents);
            
            echo json_encode(['success' => true, 'count' => $count, 'name' => $category->name]);
            
        } catch (Exception $e) {
            error_log("Error getting category document count: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    
    // Delete a category and its documents
    public function deleteCategory() {
        if (!isset($_POST['id']) || empty($_POST['id'])) {
            echo json_encode(['success' => false, 'error' => 'Category ID is required']);
            return;
        }
        
        $categoryId = $_POST['id'];
        
        try {
            require_once __DIR__ . '/../models/Category.php';
            Category::setDatabase($this->database);
            
            // Delete the category and all associated documents
            $result = Category::delete($categoryId);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Category deleted successfully along with ' . $result['count'] . ' document(s)'
            ]);
            
        } catch (Exception $e) {
            error_log("Error deleting category: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    
    // Helper function to send JSON response
    private function sendJsonResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
