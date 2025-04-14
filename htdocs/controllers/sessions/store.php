<?php

use Core\App;
use Core\Database;
use Core\Validator;

$email = $_POST['email'];
$password = $_POST['password'];

$errors = [];
if (!Validator::email($email)) {
	$errors['email'] = 'Email is not valid';
}

if (!Validator::string($password, 3, 255)) {
	$errors['password'] = 'Password must be between 7 and 255 characters';
}

if (!empty($errors)) {
	return view('sessions/create.view.php', [
		'errors' => $errors
	]);
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
