<?php
// PawMatch Configuration

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pawmatch');
define('DB_PORT', 3306);

// API Configuration
define('API_URL', 'http://localhost/PawMatchV2/backend');
define('APP_URL', 'http://localhost/PawMatchV2');

// Security
define('JWT_SECRET', 'your-secret-key-change-in-production-pawmatch-2024');
define('JWT_ALGORITHM', 'HS256');
define('PASSWORD_SALT', '$2y$10$');

// CORS
define('ALLOWED_ORIGINS', ['http://localhost', 'http://localhost:3000', 'http://localhost:80']);

// Email Configuration (if needed)
define('MAIL_FROM', 'noreply@pawmatch.com');
define('MAIL_FROM_NAME', 'PawMatch');

// Session Configuration
define('SESSION_LIFETIME', 86400); // 24 hours

// Enable error reporting in development
define('DEBUG', true);
if (DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Set default timezone
date_default_timezone_set('America/Mexico_City');

// File upload configuration
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_DIRS', ['images', 'documents']);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
