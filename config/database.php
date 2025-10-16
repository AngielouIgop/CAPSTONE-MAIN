<?php
/**
 * Database Configuration
 * This file contains database connection settings
 * Uses environment variables for security
 */

// Load environment variables from .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
    return true;
}

// Try to load .env file
$envLoaded = loadEnv(__DIR__ . '/../.env');

// Database configuration with fallbacks for development
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_USERNAME', $_ENV['DB_USERNAME'] ?? 'root');
define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? '');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'capstone');
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4');

// Application environment
define('APP_ENV', $_ENV['APP_ENV'] ?? 'development');
define('DEBUG_MODE', $_ENV['DEBUG_MODE'] ?? 'true');

// Database options
define('DB_OPTIONS', [
    MYSQLI_OPT_CONNECT_TIMEOUT => 5,
    MYSQLI_OPT_READ_TIMEOUT => 30,
    MYSQLI_INIT_COMMAND => "SET NAMES " . DB_CHARSET
]);

// Security check for production
if (APP_ENV === 'production' && (DB_PASSWORD === '' || DB_USERNAME === 'root')) {
    die('Security Error: Production environment requires secure database credentials. Please check your .env file.');
}

// Display errors only in development
if (APP_ENV === 'development' && DEBUG_MODE === 'true') {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}
?>
