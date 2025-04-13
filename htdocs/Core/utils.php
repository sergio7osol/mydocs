<?php

function dd($value) {
  echo '<pre>';
  var_dump($value);
  echo '</pre>';
 
  die();
}

function urlIs($value) {
  return $_SERVER['REQUEST_URI'] === $value;  
}

function base_path($path) {
  return rtrim(BASE_PATH, '/') . '/' . ltrim($path, '/');
}

function view($path, $attributes = []) {
    extract($attributes);
    require_once base_path('views/' . $path);
}

function login($user) {
  $_SESSION['user'] = [
    'email' => $user['email'],
	  'firstname' => $user['firstname'],
	  'lastname' => $user['lastname']
  ];

  session_regenerate_id(true);
}

function logout() {
	$_SESSION = [];

	session_destroy();

	$params = session_get_cookie_params();

	setcookie('PHPSESSID', '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
  