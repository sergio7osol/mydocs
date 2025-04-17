<?php

namespace Http\Forms;

use Core\Validator;


class LoginForm
{
	protected $errors = [];

	public function validate($email, $password): bool
	{
		if (!Validator::email($email)) { 
			$this->errors['email'] = 'Email is not valid'; 
		}

		if (!Validator::string($password, 3, 255)) {
			$this->errors['password'] = 'Password must be between 7 and 255 characters';
		}

		return empty($this->errors);
	}

	public function getErrors()
	{
		return $this->errors;
	}

	public function hasErrors(): bool
	{
		return !empty($this->errors);
	}


	public function addError ($field, $message): void
	{
		$this->errors[$field] = $message;
	}
}


