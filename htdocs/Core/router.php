<?php

namespace Core;

class Router {
  protected $routes = [];

  public function get($uri, $controller) {
    $this->routes[] = [
      'uri' => $uri,
      'controller' => $controller,
      'method' => 'GET'
    ];
  }

  public function post() {

  }

  public function delete() { 

  }  

  public function patch() {

  }

  public function put() {

  }

  public function route($uri, $method) {
    $uri = $uri === '/' ? '/' : rtrim($uri, '/');
    
    foreach ($this->routes as $route) {
      if ($route['uri'] === $uri && $route['method'] === strtoupper($method)) { 
        return require_once base_path($route['controller']);
      }
    }
 
    $this->abort();
  }

  protected function abort($code = 404) {
    http_response_code($code);
    require base_path('views/'.$code.'.view.php');
  
    die();
  }
}