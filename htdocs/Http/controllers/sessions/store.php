<?php

use Core\Athenticator;
use Http\Forms\LoginForm;

$form = LoginForm::validate($attributes = [
	'email' => $_POST['email'],
	'password' => $_POST['password'],
]);

$signedIn = (new Athenticator())->attempt($attributes['email'], $attributes['password']);

if (!$signedIn) {
	$form->addError('email', 'No account with that email address or password')->throw();
}

redirect('/');
