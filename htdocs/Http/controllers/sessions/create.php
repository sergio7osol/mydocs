<?php

use Core\Session;

view('sessions/create.view.php', [
	'errors' => Session::get('errors'),
	'old' => Session::get('old')
]);

//Session::flash('errors', $exception->errors)