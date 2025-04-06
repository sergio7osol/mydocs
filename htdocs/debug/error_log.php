<?php

// Add this at the beginning of index.php to enable error logging
ini_set('display_errors', 0); // Changed to 0 to hide errors from output
ini_set('display_startup_errors', 0); // Changed to 0 to hide startup errors
error_reporting(E_ALL);

// Log errors to a file
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

// Define a custom error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    $message = date('[Y-m-d H:i:s]') . " Error ($errno): $errstr in $errfile on line $errline\n";
    error_log($message, 3, __DIR__ . '/php_errors.log');
    // Don't output anything to the browser
    return true; // Prevents the standard PHP error handler from running
});

// Define a custom exception handler
set_exception_handler(function($exception) {
    $message = date('[Y-m-d H:i:s]') . " Exception: " . $exception->getMessage() . 
               " in " . $exception->getFile() . " on line " . $exception->getLine() . 
               "\nStack trace: " . $exception->getTraceAsString() . "\n";
    error_log($message, 3, __DIR__ . '/php_errors.log');
    
    // Only display error message for real users, not for AJAX requests
//    if (!isset($_GET['ajax']) || $_GET['ajax'] !== 'true') {
//        echo "<h1>An error occurred</h1>";
//        echo "<p>We're sorry, but something went wrong. The error has been logged and will be addressed soon.</p>";
//    }

	echo $message;
});
