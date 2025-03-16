<?php

const BASE_PATH = __DIR__ . '/../';

require_once BASE_PATH . '/utils.php';

include_once base_path('/debug/error_log.php');
// include('debug.php'); 

session_start(); // Start session for flash messages

// Add cache control headers to prevent browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (file_exists('/.dockerenv') || getenv('DOCKER_ENV') === 'true') {
	putenv('DOCKER_ENV=true');
}

require_once base_path('/Database.php');
require_once base_path('/models/Document.php');
require_once base_path('/models/User.php');

// Initialize database connection
$config = require base_path('/config.php');
$db = new Database($config['database']);

// Set the database connection for the models
Document::setDatabase($db);
User::setDatabase($db);

// Add a simple log entry for requests
file_put_contents(
	base_path('/debug/access.log'),
	date('[Y-m-d H:i:s]') . ' ' .
		$_SERVER['REQUEST_METHOD'] . ' ' .
		$_SERVER['REQUEST_URI'] . ' ' .
		'GET: ' . json_encode($_GET) . "\n",
	FILE_APPEND
);

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($requestUri == '/doc/upload') {
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$route = 'upload_post';
	} else {
		$route = 'upload';
	}
} else {
	// Original route determination for backward compatibility
	$route = isset($_GET['route']) ? $_GET['route'] : 'list';
}

switch ($route) {
	case 'list':
		require_once base_path('/controllers/DocumentController.php');
		$controller = new DocumentController($db);
		$controller->listDocuments();
		break;
	case 'upload':
		require_once base_path('/controllers/DocumentController.php');
		$controller = new DocumentController($db);
		$controller->showUploadForm();
		break;
	case 'upload_post':
		require_once base_path('/controllers/DocumentController.php');
		$controller = new DocumentController($db);
		$controller->uploadDocument();
		break;
	case 'view':
		require_once base_path('/controllers/DocumentController.php');
		$controller = new DocumentController($db);
		$controller->viewDocument();
		break;
	case 'download':
		require_once base_path('/controllers/DocumentController.php');
		$controller = new DocumentController($db);
		$controller->downloadDocument();
		break;
	case 'delete':
		require_once base_path('/controllers/DocumentController.php');
		$controller = new DocumentController($db);
		$controller->deleteDocument();
		break;
	case 'add_category':
		require_once base_path('/controllers/DocumentController.php');
		$controller = new DocumentController($db);
		$controller->addCategory();
		break;
	case 'delete_category':
		require_once base_path('/controllers/DocumentController.php');
		$controller = new DocumentController($db);
		$controller->deleteCategory();
		break;
	case 'get_category_count':
		require_once base_path('/controllers/DocumentController.php');
		$controller = new DocumentController($db);
		$controller->getCategoryDocumentCount();
		break;
	default:
		include base_path('/views/404.view.php');
		break;
}
