<?php
// Database configuration
// Supports environment variables for production deployment
// Falls back to default values for local development

// Load environment variables from .env file if it exists
if (file_exists(__DIR__ . '/.env')) {
    $envFile = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envFile as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Skip comments
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Get database configuration from environment variables or use defaults
$server   = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port     = $_ENV['DB_PORT'] ?? '3306';
$username = $_ENV['DB_USER'] ?? 'online_mart';
$password = $_ENV['DB_PASS'] ?? '';
$database = $_ENV['DB_NAME'] ?? 'mini_mart';

// Construct server string with port if not already included
$serverString = strpos($server, ':') !== false ? $server : $server . ':' . $port;

// Create connection
$conn = mysqli_connect($serverString, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
