<?php
// Basic config - change password after deploy
// Password hashed with password_hash('changeme', PASSWORD_DEFAULT)
define('ADMIN_USER', 'admin');
define('ADMIN_PASS_HASH', '$2y$10$e0N2o6vWv9V4GkJgB1q4ee4Gqz6iQq8GgS0bQf0h3K9QW7u1aHk2e');
// Data storage path
define('DATA_DIR', __DIR__ . '/data');
if (!file_exists(DATA_DIR)) mkdir(DATA_DIR, 0755, true);
?>