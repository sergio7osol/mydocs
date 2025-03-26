<?php

$router->get('/', 'controllers/documents/index.php');
$router->get('/documents', 'controllers/documents/index.php');
$router->get('/document', 'controllers/documents/show.php'); 
$router->delete('/document', 'controllers/documents/destroy.php');
$router->get('/document/create', 'controllers/documents/create.php');
$router->post('/document/create', 'controllers/documents/store.php'); 
$router->get('/document/edit', 'controllers/documents/edit.php');
