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

function redirect($path) {
	header("Location: {$path}");
	exit();
}

function old($key, $value = '')
{
	return Core\Session::get('old')[$key] ?? $value;
}