<?php

// Check if running in Docker environment
// If inside nginx/PHP container, set Docker env
if (file_exists('/.dockerenv') || getenv('DOCKER_ENV') === 'true') {
    putenv('DOCKER_ENV=true');
}

// Load config and database
require_once 'Database.php';
require_once 'models/Document.php';

// Initialize database connection
$config = require 'config.php';
$db = new Database($config['database']);

// Set the database connection for the Document model
Document::setDatabase($db);

// Basic front controller with simple routing
$route = isset($_GET['route']) ? $_GET['route'] : 'list';

switch($route){
    case 'list': 
        require_once 'controllers/DocumentController.php';
        $controller = new DocumentController();
        $controller->listDocuments();
        break;
    case 'upload':
        require_once 'controllers/DocumentController.php';
        $controller = new DocumentController();
        $controller->uploadDocument();
        break;
    case 'view':
        require_once 'controllers/DocumentController.php';
        $controller = new DocumentController();
        $controller->viewDocument();
        break;
    case 'download':
        require_once 'controllers/DocumentController.php';
        $controller = new DocumentController();
        $controller->downloadDocument();
        break;
    default:
        include 'views/404.view.php';
        break;
}