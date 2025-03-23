<?php

$router->get('/', 'controllers/documents/list.php');
$router->get('/document/show', 'controllers/documents/show.php'); 
$router->get('/document/create', 'controllers/documents/create.php');
$router->post('/document/create', 'controllers/documents/store.php');