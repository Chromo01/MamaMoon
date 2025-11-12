<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mamamoon";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $table = $_POST['table'] ?? '';

  $allowed = ['candles', 'bracelets', 'necklaces', 'other_items'];
  if (!in_array($table, $allowed)) {
    die("Invalid table name.");
  }

  $sql = "TRUNCATE TABLE $table";
  if ($conn->query($sql) === TRUE) {
    echo "✅ All products deleted from $table.";
  } else {
    echo "Error deleting products: " . $conn->error;
  }
}

$conn->close();
?>