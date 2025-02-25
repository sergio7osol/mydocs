<?php

class DocumentController {

    public function listDocuments()
    {
        $user = isset($_GET['user']) ? $_GET['user'] : 'sergey';
        $documents = [];
        
        try {
            // Get documents from database using the Document model
            $documents = Document::getAll($user);
            
            // If no documents in database yet, scan filesystem as fallback
            if (empty($documents)) {
                $documents = $this->scanForDocuments($user);
            }
        } catch (Exception $e) {
            // If database access fails, fall back to filesystem scan
            $documents = $this->scanForDocuments($user);
        }
        
        include 'views/index.view.php';
    }
    
    /**
     * Scan the filesystem for documents
     * 
     * @param string $user The user name
     * @return array List of documents
     */
    private function scanForDocuments($user) {
        // Define the upload directory
        $uploadDir = 'uploads/' . $user . '/';
        
        // Simulated documents to show if no real uploads exist
        $simulatedDocs = [
            'sergey' => [
                ['id' => 1, 'title' => 'Document A.pdf', 'date' => '2025-02-20', 'category' => 'Personal', 'user' => 'sergey'],
                ['id' => 2, 'title' => 'Document B.doc', 'date' => '2025-02-21', 'category' => 'Work', 'user' => 'sergey'],
                ['id' => 3, 'title' => 'Document C.txt', 'date' => '2025-02-22', 'category' => 'Others', 'user' => 'sergey']
            ],
            'galina' => [
                ['id' => 4, 'title' => 'Recipe Collection.pdf', 'date' => '2025-02-18', 'category' => 'Personal', 'user' => 'galina'],
                ['id' => 5, 'title' => 'Project Notes.doc', 'date' => '2025-02-19', 'category' => 'Work', 'user' => 'galina'],
                ['id' => 6, 'title' => 'Shopping List.txt', 'date' => '2025-02-23', 'category' => 'Others', 'user' => 'galina']
            ]
        ];
        
        // Check if user directory exists
        if (file_exists($uploadDir)) {
            // If a category is specified, only look in that category folder
            if (isset($_GET['category']) && file_exists($uploadDir . $_GET['category'])) {
                $documents = array_merge($documents, $this->scanCategoryDirectory($uploadDir . $_GET['category'] . '/', $_GET['category'], $user));
            } 
            // If no category is specified, scan all category folders
            elseif (!isset($_GET['category'])) {
                // Get all subdirectories (categories)
                $categoryDirs = glob($uploadDir . '*', GLOB_ONLYDIR);
                foreach ($categoryDirs as $categoryDir) {
                    $categoryName = basename($categoryDir);
                    $documents = array_merge($documents, $this->scanCategoryDirectory($categoryDir . '/', $categoryName, $user));
                }
            }
        }
        
        // If no documents found from real files, use simulated data
        if (empty($documents)) {
            if (isset($simulatedDocs[$user])) {
                $documents = $simulatedDocs[$user];
            }
        }

        // Filter by category if specified
        if (isset($_GET['category'])) {
            $documents = array_filter($documents, function($doc) {
                return $doc['category'] === $_GET['category'];
            });
        }

        // Filter by search term if specified
        if (isset($_GET['search'])) {
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
     * @param string $user The user name
     * @return array List of documents
     */
    private function scanCategoryDirectory($directory, $category, $user) {
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
                'date' => $uploadDate,
                'category' => $category,
                'user' => $user
            ];
        }
        
        return $documents;
    }

    public function uploadDocument() {
        $message = '';
        $success = false;
        $document = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Check if file was uploaded without errors
            if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
                try {
                    $fileInfo = $_FILES['document'];
                    $title = isset($_POST['title']) ? trim($_POST['title']) : pathinfo($fileInfo['name'], PATHINFO_FILENAME);
                    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
                    $category = isset($_POST['category']) ? trim($_POST['category']) : 'Others';
                    $user = isset($_POST['user']) ? trim($_POST['user']) : 'sergey';
                    
                    // Validate file type
                    $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
                    $fileType = mime_content_type($fileInfo['tmp_name']);
                    
                    if (!in_array($fileType, $allowedTypes)) {
                        throw new Exception('Invalid file type. Only PDF, DOC, DOCX, and TXT files are allowed.');
                    }
                    
                    // Validate file size (max 5MB)
                    $maxSize = 5 * 1024 * 1024; // 5MB
                    if ($fileInfo['size'] > $maxSize) {
                        throw new Exception('File size exceeds the maximum limit of 5MB.');
                    }
                    
                    // Create user/category upload directory if it doesn't exist
                    $uploadDir = 'uploads/' . $user . '/' . $category . '/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    // Generate a unique filename to avoid overwriting
                    $timestamp = time();
                    $extension = pathinfo($fileInfo['name'], PATHINFO_EXTENSION);
                    $originalName = pathinfo($fileInfo['name'], PATHINFO_FILENAME);
                    $safeTitle = preg_replace('/[^a-zA-Z0-9_-]/', '_', $title);
                    $uniqueFilename = $timestamp . '_' . $safeTitle . '.' . $extension;
                    $filePath = $uploadDir . $uniqueFilename;
                    
                    // Move uploaded file to destination
                    if (move_uploaded_file($fileInfo['tmp_name'], $filePath)) {
                        // Create a new Document object and save to database
                        $document = new Document(
                            null,                            // id (will be assigned by database)
                            $title,                          // title
                            $description,                    // description
                            date('Y-m-d'),                   // date
                            $category,                       // category
                            $filePath,                       // filepath
                            $uniqueFilename,                 // filename
                            $fileInfo['size'],               // file_size
                            $user                            // user
                        );
                        
                        // Save to database
                        $document->save();
                        
                        // Set success message
                        $success = true;
                        $message = 'Document uploaded successfully.';
                        
                        // Include details for success view
                        $fileName = $title;
                        $fileDate = date('Y-m-d');
                        $fileCategory = $category;
                        $fileSize = $this->formatFileSize($fileInfo['size']);
                        $fileId = $document->id;
                        
                        include 'views/upload_success.view.php';
                        return;
                    } else {
                        throw new Exception('Failed to move uploaded file.');
                    }
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                }
            } elseif (isset($_FILES['document'])) {
                // Handle file upload errors
                $errorMessages = [
                    UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
                    UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
                    UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                    UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.'
                ];
                
                $errorCode = $_FILES['document']['error'];
                $message = isset($errorMessages[$errorCode]) ? $errorMessages[$errorCode] : 'Unknown upload error.';
            }
        }
        
        include 'views/upload.view.php';
    }
    
    // Helper function to get a descriptive error message for file upload errors
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
    
    // Helper method to format file sizes in a human-readable format
    public function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    public function viewDocument() {
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        $user = isset($_GET['user']) ? $_GET['user'] : 'sergey';
        $document = null;
        
        // Try to find document in database first
        try {
            $document = Document::getById($id);
            
            // If found in database, set the can_download flag
            if ($document) {
                $documentArray = [
                    'id' => $document->id,
                    'title' => $document->title,
                    'description' => $document->description,
                    'date' => $document->date,
                    'category' => $document->category,
                    'file_path' => $document->filepath,
                    'filename' => $document->filename,
                    'file_size' => $document->file_size,
                    'user' => $document->user,
                    'can_download' => file_exists($document->filepath)
                ];
                $document = $documentArray;
                include 'views/document.view.php';
                return;
            }
        } catch (Exception $e) {
            // If database error, continue with file-based lookup
        }
        
        // If not found in database, try file-based lookup for backward compatibility
        if (!$document && $id && strlen($id) === 32 && ctype_xdigit($id)) {
            // This is likely a real file, search for it
            $uploadDir = 'uploads/' . $user . '/';
            
            if (file_exists($uploadDir)) {
                // Search in all category directories
                $categoryDirs = glob($uploadDir . '*', GLOB_ONLYDIR);
                
                foreach ($categoryDirs as $categoryDir) {
                    $categoryName = basename($categoryDir);
                    $files = glob($categoryDir . '/*.{pdf,doc,docx,txt}', GLOB_BRACE);
                    
                    foreach ($files as $file) {
                        $fileId = md5($file);
                        
                        if ($fileId === $id) {
                            // Found the file
                            $fileName = basename($file);
                            $filePath = $file;
                            $fileSize = filesize($file);
                            $fileModified = filemtime($file);
                            
                            // Extract title from filename
                            $parts = explode('_', $fileName, 2);
                            if (count($parts) > 1 && is_numeric($parts[0])) {
                                $title = pathinfo($parts[1], PATHINFO_FILENAME);
                                $uploadDate = date('Y-m-d', $parts[0]);
                            } else {
                                $title = pathinfo($fileName, PATHINFO_FILENAME);
                                $uploadDate = date('Y-m-d', $fileModified);
                            }
                            
                            $document = [
                                'id' => $id,
                                'title' => $title,
                                'filename' => $fileName,
                                'file_path' => $filePath,
                                'file_size' => $fileSize,
                                'date' => $uploadDate,
                                'category' => $categoryName,
                                'user' => $user,
                                'description' => 'View or download this document.',
                                'can_download' => true
                            ];
                            
                            // Also save this to database for future lookups
                            try {
                                $newDoc = new Document(
                                    null,
                                    $title,
                                    'View or download this document.',
                                    $uploadDate,
                                    $categoryName,
                                    $filePath,
                                    $fileName,
                                    $fileSize,
                                    $user
                                );
                                $newDoc->save();
                            } catch (Exception $e) {
                                // Ignore database errors
                            }
                            
                            break 2; // Exit both loops
                        }
                    }
                }
            }
        }
        
        // If no real document found and the ID is numeric, use simulated data
        if (!$document && $id && is_numeric($id)) {
            // Simulated document details using mock data
            $documents = [
                // Sergey's documents
                '1' => ['id' => 1, 'title' => 'Document A.pdf', 'date' => '2025-02-20', 'category' => 'Personal', 'description' => 'Description for Document A.', 'user' => 'sergey'],
                '2' => ['id' => 2, 'title' => 'Document B.doc', 'date' => '2025-02-21', 'category' => 'Work', 'description' => 'Description for Document B.', 'user' => 'sergey'],
                '3' => ['id' => 3, 'title' => 'Document C.txt', 'date' => '2025-02-22', 'category' => 'Others', 'description' => 'Description for Document C.', 'user' => 'sergey'],
                
                // Galina's documents
                '4' => ['id' => 4, 'title' => 'Recipe Collection.pdf', 'date' => '2025-02-18', 'category' => 'Personal', 'description' => 'A collection of favorite family recipes.', 'user' => 'galina'],
                '5' => ['id' => 5, 'title' => 'Project Notes.doc', 'date' => '2025-02-19', 'category' => 'Work', 'description' => 'Notes from the latest project meeting.', 'user' => 'galina'],
                '6' => ['id' => 6, 'title' => 'Shopping List.txt', 'date' => '2025-02-23', 'category' => 'Others', 'description' => 'Weekly shopping list with items and prices.', 'user' => 'galina']
            ];
            
            if (isset($documents[$id])) {
                $document = $documents[$id];
            }
        }
        
        include 'views/document.view.php';
    }

    /**
     * Handle direct document downloads
     */
    public function downloadDocument() {
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        $user = isset($_GET['user']) ? $_GET['user'] : 'sergey';
        
        if ($id && strlen($id) === 32 && ctype_xdigit($id)) {
            // This is likely a real file, search for it
            $uploadDir = 'uploads/' . $user . '/';
            
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
}
