<?php
// config.php - Central configuration with MySQL support

// Admin credentials
define('ADMIN_USER', 'admin');
define('ADMIN_PASS_HASH', '$2y$10$e0N2o6vWv9V4GkJgB1q4ee4Gqz6iQq8GgS0bQf0h3K9QW7u1aHk2e'); // 'changeme'

// Database credentials (cPanel)
define('DB_HOST', 'localhost');
define('DB_NAME', 'zapm8926_db');
define('DB_USER', 'zapm8926_user');
define('DB_PASS', 'W(W%HF25eKFt*49J');
define('USE_DATABASE', true); // Set to false to use JSON fallback

// Fallback: JSON storage directory (optional, for backup)
define('DATA_DIR', __DIR__ . '/data');
if (!file_exists(DATA_DIR)) mkdir(DATA_DIR, 0755, true);

// Session start
session_start();
?>