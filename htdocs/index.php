<?php

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
    default:
        include 'views/404.view.php';
        break;
}