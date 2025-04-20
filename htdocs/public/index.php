<?php

use Core\Database;
use Core\Router;
use Core\Session;
use Core\ValidationException;

define('BASE_PATH', dirname(__DIR__) . '/'); // htdocs directory

require_once BASE_PATH . 'Core/utils.php';
include_once base_path('debug/error_log.php');
// include('debug.php'); 

require_once base_path('vendor/autoload.php');

session_start(); // Start session for flash messages

// Add cache control headers to prevent browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

putenv('DOCKER_ENV=true'); // Force Docker environment for now since we're running in Docker

require_once base_path('bootstrap.php');

// require_once base_path('Core/Database.php');
// require_once base_path('Core/Router.php');
require_once base_path('models/Document.php');
require_once base_path('models/User.php');

// Initialize database connection
$config = require base_path('config.php');
$db = new Database($config['database']);

// Set the database connection for the models
Document::setDatabase($db);
User::setDatabase($db);

// Add a simple log entry for requests
file_put_contents(
	base_path('debug/access.log'),
	date('[Y-m-d H:i:s]') . ' ' .
		$_SERVER['REQUEST_METHOD'] . ' ' .
		$_SERVER['REQUEST_URI'] . ' ' .
		'GET: ' . json_encode($_GET) . "\n",
	FILE_APPEND
);

$router = new Router($db);

$routes = require base_path('routes.php');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$method = $_POST['_method'] ?? $_SERVER['REQUEST_METHOD'];

try {
	$router->route($uri, $method);
} catch (ValidationException $exception) {
	Session::flash('errors', $exception->errors);
	Session::flash('old', $exception->old);

	return redirect($router->previousUrl());
}

Session::unflash();
 
// $controller = new DocumentController($db);
// $router->get('/', $controller->listDocuments());

// $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);


// switch ($route) {
// 	case 'download':
// 		require_once base_path('Http/controllers/DocumentController.php');
// 		$controller = new DocumentController($db);
// 		$controller->downloadDocument();
// 		break;
// 	case 'delete':
// 		require_once base_path('Http/controllers/DocumentController.php');
// 		$controller = new DocumentController($db);
// 		$controller->deleteDocument();
// 		break;
// 	case 'add_category':
// 		require_once base_path('Http/controllers/DocumentController.php');
// 		$controller = new DocumentController($db);
// 		$controller->addCategory();
// 		break;
// 	case 'delete_category':
// 		require_once base_path('Http/controllers/DocumentController.php');
// 		$controller = new DocumentController($db);
// 		$controller->deleteCategory();
// 		break;
// 	case 'get_category_count':
// 		require_once base_path('Http/controllers/DocumentController.php');
// 		$controller = new DocumentController($db);
// 		$controller->getCategoryDocumentCount();
// 		break;
// 	default:
// 		view('404.view.php', ['pageTitle' => '404 - Page Not Found']);
// 		break;
// }
