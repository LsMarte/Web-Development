<?php
/**
 * Database Configuration
 * Luis Marte Portfolio Backend
 * 
 * IMPORTANT: Update these settings for your environment
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'luis_portfolio');
define('DB_USER', 'root'); // Change to your database username
define('DB_PASS', ''); // Change to your database password
define('DB_CHARSET', 'utf8mb4');

// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com'); // Change to your SMTP host
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com'); // Change to your email
define('SMTP_PASSWORD', 'your-app-password'); // Change to your app password
define('SMTP_ENCRYPTION', 'tls');

// Site Configuration
define('SITE_NAME', 'Luis Marte Portfolio');
define('ADMIN_EMAIL', 'Roandy1017@gmail.com');
define('FROM_EMAIL', 'noreply@luismarte.dev'); // Change to your domain
define('FROM_NAME', 'Luis Marte');

// Security Configuration
define('JWT_SECRET', 'your-jwt-secret-key-change-this'); // Change this!
define('ENCRYPTION_KEY', 'your-encryption-key-change-this'); // Change this!
define('ADMIN_SESSION_TIMEOUT', 3600); // 1 hour

// File Upload Configuration
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('UPLOAD_PATH', '../uploads/');

// API Configuration
define('API_RATE_LIMIT', 100); // Requests per hour per IP
define('CORS_ORIGINS', ['http://localhost', 'https://luismarte.dev']); // Add your domains

// Development/Production Settings
define('DEBUG_MODE', true); // Set to false in production
define('LOG_ERRORS', true);
define('ERROR_LOG_FILE', '../logs/error.log');

// reCAPTCHA Configuration (optional)
define('RECAPTCHA_SITE_KEY', ''); // Add your reCAPTCHA site key
define('RECAPTCHA_SECRET_KEY', ''); // Add your reCAPTCHA secret key

// Timezone
date_default_timezone_set('America/New_York');

// Error Reporting
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);