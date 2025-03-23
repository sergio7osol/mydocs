<?php

namespace Core;

class Router {
  protected $routes = [];
  protected $database;

  public function __construct($database = null) {
    $this->database = $database;
  }

  public function get($uri, $controller) {
    $this->add('GET', $uri, $controller);
  }

  public function post($uri, $controller) {
    $this->add('POST', $uri, $controller);
  }

  public function delete($uri, $controller) { 
    $this->add('DELETE', $uri, $controller);
  }  

  public function patch($uri, $controller) {
    $this->routes[] = [
      'uri' => $uri,
      'controller' => $controller,
      'method' => 'PATCH'
    ];
  }

  public function put($uri, $controller) {
    $this->routes[] = [
      'uri' => $uri,
      'controller' => $controller,
      'method' => 'PUT'
    ];
  }

  public function route($uri, $method) {
    $uri = $uri === '/' ? '/' : rtrim($uri, '/');
    
    foreach ($this->routes as $route) {
      if ($route['uri'] === $uri && $route['method'] === strtoupper($method)) { 
        // Make the database available to the controller
        if ($this->database) {
          $db = $this->database;
        }
        
        return require_once base_path($route['controller']);
      }
    }
 
    $this->abort();
  }

  public function add($method, $uri, $controller) {
    $this->routes[] = [
      'uri' => $uri,
      'controller' => $controller,
      'method' => $method
    ];
  }

  protected function abort($code = 404) {
    http_response_code($code);

    require base_path("views/{$code}.view.php");

    die();
  }
}