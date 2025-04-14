<?php

use Core\App;
use Core\Database;
use Http\Forms\LoginForm;

$email = $_POST['email'];
$password = $_POST['password'];

$loginForm = new LoginForm();
$loginForm->validate($email, $password);

if (!$loginForm->validate($email, $password)) {
	if (!empty($errors)) {
		return view('sessions/create.view.php', [
			'errors' => $loginForm->$errors
		]);
	}
}

$db = App::resolve(Database::class);

$user = $db->query('select * from users where email = :email', [
	'email' => $email
])->fetch();

if ($user && password_verify($password, $user['password'])) {
	login([
		'email' => $email,
		'firstname' => $user['firstname'],
		'lastname' => $user['lastname']
	]);

	header('location: /');
	exit();
}

return view('sessions/create.view.php', [
	'errors' => [
		'email' => 'No account with that email address or password'
	]
]);
