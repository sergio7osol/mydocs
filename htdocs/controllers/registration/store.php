<?php

use Core\App;
use Core\Database;
use Core\Validator;

$email = $_POST['email'];
$password = $_POST['password'];
$firstname = $_POST['firstname'];
$lastname = $_POST['lastname'];

$errors = [];

if (!Validator::email($email)) {
    $errors['email'] = 'Email is not valid';
}

if (!Validator::string($firstname, 1, 50)) {
    $errors['firstname'] = 'Firstname must be between 1 and 255 characters';
}

if (!Validator::string($lastname, 1, 50)) {
    $errors['lastname'] = 'Lastname must be between 1 and 255 characters';
}

if (!Validator::string($password, 3, 255)) {
    $errors['password'] = 'Password must be between 7 and 255 characters';
}

if (!empty($errors)) {
    return view('registration/create.view.php', ['errors' => $errors]);
}

$db = App::resolve(Database::class);

$user = $db->query('select * from users where email = :email', [
  'email' => $email
])->fetch();

if ($user) {
    header('Location: /');
    exit;
} else {
    $db->query('insert into users (email, firstname, lastname, password) values (:email, :firstname, :lastname, :password)', [
        'email' => $email,
        'firstname' => $firstname,
        'lastname' => $lastname,
        'password' => password_hash($password, PASSWORD_BCRYPT)
    ]);

		login([
			'email' => $email,
			'firstname' => $firstname,
			'lastname' => $lastname
		]);

    header('Location: /');
    exit;
}