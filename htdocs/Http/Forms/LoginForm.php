<?php

namespace Http\Forms;

use Core\ValidationException;
use Core\Validator;

class LoginForm
{
	protected $errors = [];

	public function __construct(public array$attributes = [])
	{
		if (!Validator::email($attributes['email'])) {
			$this->errors['email'] = 'Email is not valid';
		}

		if (!Validator::string($attributes['password'], 3, 255)) {
			$this->errors['password'] = 'Password must be between 7 and 255 characters';
		}
	}

	static function validate($attributes)
	{
		$instance = new static($attributes);

		if ($instance->hasErrors()) {
			$instance->throw();
		}

		return $instance;
	}

	public function throw()
	{
		ValidationException::throw($this->errors(), $this->attributes);
	}

	public function hasErrors()
	{
		return count($this->errors);
	}

	public function errors()
	{
		return $this->errors;
	}

	public function addError ($field, $message)
	{
		$this->errors[$field] = $message;

		return $this;
	}
}


