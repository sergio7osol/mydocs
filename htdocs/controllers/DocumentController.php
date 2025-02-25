<?php

class DocumentController {

    public function listDocuments()
    {
        // Get filter parameters
        $category = isset($_GET['category']) ? $_GET['category'] : null;
        $search = isset($_GET['search']) ? $_GET['search'] : null;
        $user = isset($_GET['user']) ? $_GET['user'] : 'sergey'; // Default to Sergey

        // In a real app, we would fetch documents from a database
        // For now, we'll simulate with an array
        $documents = [];

        // If no documents returned, simulate data
        if (empty($documents)) {
            $sergeyDocs = [
                ['id' => 1, 'title' => 'Document A.pdf', 'date' => '2025-02-20', 'category' => 'Personal', 'user' => 'sergey'],
                ['id' => 2, 'title' => 'Document B.doc', 'date' => '2025-02-21', 'category' => 'Work', 'user' => 'sergey'],
                ['id' => 3, 'title' => 'Document C.txt', 'date' => '2025-02-22', 'category' => 'Others', 'user' => 'sergey']
            ];
            
            $galinaDocs = [
                ['id' => 4, 'title' => 'Recipe Collection.pdf', 'date' => '2025-02-18', 'category' => 'Personal', 'user' => 'galina'],
                ['id' => 5, 'title' => 'Project Notes.doc', 'date' => '2025-02-19', 'category' => 'Work', 'user' => 'galina'],
                ['id' => 6, 'title' => 'Shopping List.txt', 'date' => '2025-02-23', 'category' => 'Others', 'user' => 'galina']
            ];
            
            // Combine all documents
            $allDocs = array_merge($sergeyDocs, $galinaDocs);
            
            // Filter by user if specified
            if ($user) {
                $documents = array_filter($allDocs, function($doc) use ($user) {
                    return $doc['user'] === $user;
                });
            } else {
                $documents = $allDocs;
            }
        }

        // Filter by category if specified
        if ($category) {
            $documents = array_filter($documents, function($doc) use ($category) {
                return $doc['category'] === $category;
            });
        }

        // Filter by search term if specified
        if ($search) {
            $documents = array_filter($documents, function($doc) use ($search) {
                return stripos($doc['title'], $search) !== false;
            });
        }

        // Include the view
        include 'views/index.view.php';
    }

    // Future actions like updateDocument, etc., will be added here

    public function uploadDocument() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_FILES['document']) && $_FILES['document']['error'] === 0) {
                $uploadDir = 'uploads/';
                $uploadFile = $uploadDir . basename($_FILES['document']['name']);
                if (move_uploaded_file($_FILES['document']['tmp_name'], $uploadFile)) {
                    $message = "File successfully uploaded!";
                } else {
                    $message = "File upload failed!";
                }
            } else {
                $message = "No file selected or error during upload.";
            }
            include 'views/upload.view.php';
        } else {
            include 'views/upload.view.php';
        }
    }

    public function viewDocument() {
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        // Simulated document details using mock data
        $documents = [
            '1' => ['id' => 1, 'title' => 'Document A.pdf', 'date' => '2025-02-20', 'category' => 'Personal', 'description' => 'Description for Document A.'],
            '2' => ['id' => 2, 'title' => 'Document B.doc', 'date' => '2025-02-21', 'category' => 'Work', 'description' => 'Description for Document B.']
        ];
        if ($id && isset($documents[$id])) {
            $document = $documents[$id];
        } else {
            $document = null;
        }
        include 'views/document.view.php';
    }

}
