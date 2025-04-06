<?php

namespace Core;

use Core\Middleware\Guest;
use Core\Middleware\Auth;
use Core\Middleware\Middleware;


class Router {
  protected $routes = [];
  protected $database;

  public function __construct($database = null) {
    $this->database = $database;
  }

  public function get($uri, $controller) {
    return $this->add('GET', $uri, $controller);
  }

  public function post($uri, $controller) {
    return $this->add('POST', $uri, $controller);
  }

  public function delete($uri, $controller) { 
    return $this->add('DELETE', $uri, $controller);
  }  

  public function patch($uri, $controller) {
    $this->routes[] = [
      'uri' => $uri,
      'controller' => $controller,
      'method' => 'PATCH'
    ];

    return $this;
  }

	public function put($uri, $controller) {
		$this->routes[] = [
			'uri' => $uri,
			'controller' => $controller,
			'method' => 'PUT'
		];

		return $this;
	}

  public function only($key) {
    $this->routes[array_key_last($this->routes)]['middleware'] = $key;

		return $this;
  }

  public function route($uri, $method) {
    $uri = $uri === '/' ? '/' : rtrim($uri, '/');
    
    foreach ($this->routes as $route) {
      if ($route['uri'] === $uri && $route['method'] === strtoupper($method)) { 
        // Make the database available to the controller
        if ($this->database) {
          $db = $this->database;
        }

				if ($route['middleware']) {
					Middleware::resolve($route['middleware']);
				}
        
        return require_once base_path($route['controller']);
      }
    }
  }

	public function add($method, $uri, $controller)
	{
		$this->routes[] = [
			'uri' => $uri,
			'controller' => $controller,
			'method' => $method,
			'middleware' => null
		];

		return $this;
	}

  protected function abort($code = 404) {
    http_response_code($code);

    require base_path("views/{$code}.view.php");

    die();
  }
}