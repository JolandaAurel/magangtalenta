<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=talenta_db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>
