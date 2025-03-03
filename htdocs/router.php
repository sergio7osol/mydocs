<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$routes = [
  '/'        => 'controllers/index.php',
  '/about'   => 'controllers/about.php',
  '/contact' => 'controllers/contact.php',
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
  require 'views/'.$code.'.php';

  die();
}

routeToController($uri, $routes);