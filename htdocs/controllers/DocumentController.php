<?php

class DocumentController {

    public function listDocuments()
    {
        // Get user ID, either from GET parameter or use default (Sergey, ID 1)
        $userId = isset($_GET['user_id']) ? $_GET['user_id'] : 1;
        
        // Get category from either GET 
        $category = isset($_GET['category']) ? $_GET['category'] : '';
        
        // Add debug info to help troubleshoot
        error_log("Loading documents for user ID: " . $userId . ", category: " . $category);
        
        $documents = [];
        
        try {
            // Get documents from database based on whether a category is selected
            if (!empty($category)) {
                // Get documents for the specific category
                $documents = Document::getByCategory($category, $userId);
                error_log("Found " . count($documents) . " documents in database for user " . $userId . " in category " . $category);
            } else {
                // Get all documents if no category is specified
                $documents = Document::getAll($userId);
                error_log("Found " . count($documents) . " documents in database for user " . $userId);
            }
            
            // If no documents in database yet, scan filesystem as fallback
            if (empty($documents)) {
                $documents = $this->scanForDocuments($userId);
                error_log("Using " . count($documents) . " documents from filesystem for user " . $userId);
            }
        } catch (Exception $e) {
            // If database access fails, fall back to filesystem scan
            error_log("Database error: " . $e->getMessage());
            $documents = $this->scanForDocuments($userId);
        }
        
        // Ensure we have the minimum document fields needed for display
        foreach ($documents as &$doc) {
            if (!isset($doc['upload_date']) && isset($doc['date'])) {
                $doc['upload_date'] = $doc['date'];
            }
            
            if (!isset($doc['title'])) {
                $doc['title'] = $doc['filename'] ?? 'Untitled Document';
            }
        }
        
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
        
        // Simulated documents to show if no real uploads exist
        $simulatedDocs = [
            1 => [
                ['id' => 1, 'title' => 'Document A.pdf', 'upload_date' => '2025-02-20', 'category' => 'Personal', 'user_id' => 1],
                ['id' => 2, 'title' => 'Document B.doc', 'upload_date' => '2025-02-21', 'category' => 'Work', 'user_id' => 1],
                ['id' => 3, 'title' => 'Document C.txt', 'upload_date' => '2025-02-22', 'category' => 'Others', 'user_id' => 1]
            ],
            2 => [
                ['id' => 4, 'title' => 'Recipe Collection.pdf', 'upload_date' => '2025-02-18', 'category' => 'Personal', 'user_id' => 2],
                ['id' => 5, 'title' => 'Project Notes.doc', 'upload_date' => '2025-02-19', 'category' => 'Work', 'user_id' => 2],
                ['id' => 6, 'title' => 'Shopping List.txt', 'upload_date' => '2025-02-23', 'category' => 'Others', 'user_id' => 2]
            ]
        ];
        
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
        
        // If no documents found from real files, use simulated data
        if (empty($documents)) {
            if (isset($simulatedDocs[$userId])) {
                $allDocs = $simulatedDocs[$userId];
                
                // If a category is specified, filter the simulated docs by that category
                if (!empty($selectedCategory)) {
                    $documents = array_filter($allDocs, function($doc) use ($selectedCategory) {
                        return $doc['category'] === $selectedCategory;
                    });
                } else {
                    $documents = $allDocs; // Use all docs if no category specified
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
            
            $documents[] = [
                'id' => md5($filePath), // Use file path hash as unique ID
                'title' => $title,
                'filename' => $fileName,
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'upload_date' => $uploadDate,
                'category' => $category,
                'user_id' => $userId
            ];
        }
        
        return $documents;
    }

    public function showUploadForm() {
        $userId = isset($_GET['user_id']) ? $_GET['user_id'] : 1; // Default to user ID 1 (Sergey)
        require 'views/upload.view.php';
    }

    public function uploadDocument() {
        $title = isset($_POST['title']) ? $_POST['title'] : '';
        $description = isset($_POST['description']) ? $_POST['description'] : '';
        $category = isset($_POST['category']) ? $_POST['category'] : 'Personal';
        $userId = isset($_POST['user_id']) ? $_POST['user_id'] : 1; // Default to user ID 1 (Sergey)
        $createdDate = isset($_POST['created_date']) && !empty($_POST['created_date']) ? $_POST['created_date'] : null;
        
        // Get user object for validation
        $user = User::getById($userId);
        if (!$user) {
            $user = User::getDefault(); // Fallback to default user
            $userId = $user->id;
        }
        
        $uploadFile = isset($_FILES['document']) ? $_FILES['document'] : null;
        $message = '';
        
        if (!empty($title) && $uploadFile && $uploadFile['error'] === UPLOAD_ERR_OK) {
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
                $config = require __DIR__ . '/../config.php';
                $db = new Database($config['database']);
                Document::setDatabase($db);
                
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
                    require 'views/upload_success.view.php';
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
        require 'views/upload.view.php';
    }

    public function viewDocument() {
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        $userId = isset($_GET['user_id']) ? $_GET['user_id'] : 1; // Default to user ID 1 (Sergey)
        
        $document = null;
        if (!empty($id)) {
            $document = Document::getById($id, $userId);
        }
        
        if (!$document) {
            // Document not found or access denied
            header('Location: index.php?route=list');
            exit;
        }
        
        include 'views/view.view.php';
    }

    /**
     * Handle direct document downloads
     */
    public function downloadDocument() {
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        $userId = isset($_GET['user_id']) ? $_GET['user_id'] : 1; // Default to user ID 1 (Sergey)
        
        if ($id && strlen($id) === 32 && ctype_xdigit($id)) {
            // This is likely a real file, search for it
            $uploadDir = 'uploads/' . $userId . '/';
            
            if (file_exists($uploadDir)) {
                // Search in all category directories
                $categoryDirs = glob($uploadDir . '*', GLOB_ONLYDIR);
                
                foreach ($categoryDirs as $categoryDir) {
                    $files = glob($categoryDir . '/*.{pdf,doc,docx,txt}', GLOB_BRACE);
                    
                    foreach ($files as $file) {
                        $fileId = md5($file);
                        
                        if ($fileId === $id) {
                            // Found the file
                            $fileName = basename($file);
                            $filePath = $file;
                            
                            // Check if file exists and is readable
                            if (file_exists($filePath) && is_readable($filePath)) {
                                // Get file info
                                $fileSize = filesize($filePath);
                                $fileType = mime_content_type($filePath);
                                
                                // Set headers for download
                                header('Content-Description: File Transfer');
                                header('Content-Type: ' . $fileType);
                                header('Content-Disposition: attachment; filename="' . $fileName . '"');
                                header('Content-Transfer-Encoding: binary');
                                header('Expires: 0');
                                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                                header('Pragma: public');
                                header('Content-Length: ' . $fileSize);
                                
                                // Output file and exit
                                readfile($filePath);
                                exit;
                            }
                        }
                    }
                }
            }
        }
        
        // If file not found or other error
        header('HTTP/1.0 404 Not Found');
        include 'views/404.view.php';
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

}
