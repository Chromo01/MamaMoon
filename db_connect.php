<?php
$dsn = 'mysql:host=localhost;dbname=MamaMoon;charset=utf8mb4';
$user = 'root';
$pass = ''; // Change if you set a MySQL password in XAMPP

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    exit('❌ Database connection failed: ' . $e->getMessage());
}