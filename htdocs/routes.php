<?php

$router->get('/', 'Http/controllers/documents/index.php');
$router->get('/documents', 'Http/controllers/documents/index.php')->only('auth');
$router->get('/document', 'Http/controllers/documents/show.php');
$router->delete('/document', 'Http/controllers/documents/destroy.php');
$router->get('/document/create', 'Http/controllers/documents/create.php');
$router->post('/document/create', 'Http/controllers/documents/store.php');
$router->get('/document/edit', 'Http/controllers/documents/edit.php');

$router->get('/register', 'Http/controllers/registration/create.php')->only('guest');
$router->post('/register', 'Http/controllers/registration/store.php')->only('guest');

$router->get('/sessions', 'Http/controllers/sessions/create.php')->only('guest');
$router->post('/sessions', 'Http/controllers/sessions/store.php')->only('guest');
$router->delete('/sessions', 'Http/controllers/sessions/destroy.php')->only('auth');


$router->get('/categories', 'Http/controllers/categories/index.php')->only('auth');
