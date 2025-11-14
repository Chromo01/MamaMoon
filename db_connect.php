<?php
// db_connect.php — NO OUTPUT, NO CLOSING PHP TAG

$DB_HOST = '127.0.0.1';   // force TCP, avoid socket/IPv6 weirdness
$DB_PORT = 3306;          // <— change if your my.ini shows a different port
$DB_NAME = 'MamaMoon';    // your database name
$DB_USER = 'root';        // or your chosen user
$DB_PASS = '';            // XAMPP default is empty for root

$dsn = "mysql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};charset=utf8mb4";

$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
  $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (Throwable $e) {
  // Throwing here lets the caller decide how to respond (JSON, etc.)
  throw $e;
}