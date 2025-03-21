<?php

// Use different connection settings for different environments
if (getenv('DOCKER_ENV') === 'true') {
    // Docker environment
    return [	
        'database' => [
            'host' => 'db',
            'port' => '3306',
            'dbname' => 'mydocs_database',
            'charset' => 'utf8mb4'
        ]
    ];
} else {
    // Local environment (connecting to Docker from host)
    return [	
        'database' => [
            'host' => 'localhost',
            'port' => '3307',
            'dbname' => 'mydocs_database',
            'charset' => 'utf8mb4'
        ]
    ];
}