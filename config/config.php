<?php
// config/config.php

// ✅ Define database constants for use in database.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'kanban_db');
define('DB_USER', 'root');     
define('DB_PASS', '');          

// ✅ Optional: create a PDO instance here too if needed globally
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
