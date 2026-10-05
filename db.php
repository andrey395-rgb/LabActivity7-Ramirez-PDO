<?php
// Database configuration and PDO instance.
// Import with: require __DIR__ . '/db.php';

$dbHost = '127.0.0.1';
$dbName = 'blog_site';
$dbUser = 'root';
$dbPass = '';

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw exceptions on SQL errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // fetch rows as associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // use real prepared statements
];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Database connection failed. Please check the settings in db.php.');
}
