<?php

namespace Core\Middleware;

class Auth {
	public function handle() {
		if (!$_SESSION['user'] ?? false) {
			header('Location: /');
			exit;
		}
	}

	/**
	 * Check user permissions for accessing documents
	 *
	 * @param int $userId User ID to check permissions for
	 * @return bool True if user has permission, false otherwise
	 */
	public static function checkPermissions($userId) {
		// For now, just ensure the user ID is valid
		if (!is_numeric($userId) || $userId <= 0) {
			error_log("Invalid user ID: " . $userId);
			header('Location: /?error=invalid_user');
			exit;
		}

		// In the future, we could check if the current user has permission to view this user's documents
		// For now, we're allowing all users to view all documents
		return true;
	}
}
