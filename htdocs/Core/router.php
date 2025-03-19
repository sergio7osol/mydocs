<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Define APP_ROOT if not already defined
if (!defined('APP_ROOT')) {
  define('APP_ROOT', dirname(__DIR__));
}

$routes = [
  '/'        => base_path('controllers/index.php'),
  '/about'   => base_path('controllers/about.php'),
  '/contact' => base_path('controllers/contact.php'),
];

function routeToController($uri, $routes) {
  if (array_key_exists($uri, $routes)) {
    require $routes[$uri];
  } else if ($uri === '/phpmyadmin') {

  } else {
    abort();
  }
}

function abort($code = 404) {
  http_response_code($code);
  require base_path('views/'.$code.'.php');

  die();
}

routeToController($uri, $routes);