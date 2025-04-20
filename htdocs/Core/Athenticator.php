<?php

namespace Core;

class Athenticator
{
	public function attempt($email, $password)
	{
		$db = App::resolve(Database::class);

		$user = $db->query('select * from users where email = :email', [
			'email' => $email
		])->fetch();

		if ($user && password_verify($password, $user['password'])) {
			$this->login([
				'email' => $email,
				'firstname' => $user['firstname'],
				'lastname' => $user['lastname']
			]);

			return true;
		}

		return false;
	}

	public function login($user)
	{
		$_SESSION['user'] = [
			'email' => $user['email'],
			'firstname' => $user['firstname'],
			'lastname' => $user['lastname']
		];

		session_regenerate_id(true);
	}

	public function logout()
	{
		Session::destroy();
	}
}
