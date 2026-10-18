<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';
try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    echo "SUCCESS\n";
} catch (Exception $e) { echo "Error: " . $e->getMessage(); }
?>
