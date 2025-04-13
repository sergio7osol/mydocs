<?php

$router->get('/', 'controllers/documents/index.php');
$router->get('/documents', 'controllers/documents/index.php')->only('auth');
$router->get('/document', 'controllers/documents/show.php');
$router->delete('/document', 'controllers/documents/destroy.php');
$router->get('/document/create', 'controllers/documents/create.php');
$router->post('/document/create', 'controllers/documents/store.php');
$router->get('/document/edit', 'controllers/documents/edit.php');

$router->get('/register', 'controllers/registration/create.php')->only('guest');
$router->post('/register', 'controllers/registration/store.php')->only('guest');

$router->get('/sessions', 'controllers/sessions/create.php')->only('guest');
$router->post('/sessions', 'controllers/sessions/store.php')->only('guest');
$router->delete('/sessions', 'controllers/sessions/destroy.php')->only('auth');


$router->get('/categories', 'controllers/categories/index.php')->only('auth');

