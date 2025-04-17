<?php

use Core\Athenticator;
use Http\Forms\LoginForm;

$email = $_POST['email'];
$password = $_POST['password'];

$form = new LoginForm();

if ($form->validate($email, $password)) {
	if ((new Athenticator)->attempt($email, $password)) {
		redirect('/');
	}

	$form->addError('email', 'No account with that email address or password');
}

return view('sessions/create.view.php', [
	'errors' => $form->getErrors()
]);
