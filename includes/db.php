<?php
// Database connection (PDO + prepared statements everywhere).
// For deployment, copy includes/config.sample.php to includes/config.local.php and enter your host's details.
$host = 'localhost'; $db = 'cafe_app'; $user = 'root'; $pass = '';
if (file_exists(__DIR__ . '/config.local.php')) require __DIR__ . '/config.local.php';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $ex) {
    http_response_code(500);
    die('We can’t reach the database right now. Check includes/db.php settings and that cafe_app.sql is imported.');
}
