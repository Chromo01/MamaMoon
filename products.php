<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "MamaMoon";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$category = $_GET['category'] ?? 'candles';

switch ($category) {
  case 'candles':
    $table = 'candles';
    break;
  case 'bracelets':
    $table = 'bracelets';
    break;
  case 'necklaces':
    $table = 'necklaces';
    break;
  case 'other':
    $table = 'other_items';
    break;
  default:
    echo json_encode(["error" => "Invalid category"]);
    exit;
}

$sql = "SELECT name, description, price, image_url FROM $table";
$result = $conn->query($sql);

$products = [];

if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $products[] = $row;
  }
} else {
  echo json_encode(["error" => "No results from table '$table'"]);
  exit;
}

header('Content-Type: application/json');
echo json_encode($products);

$conn->close();
?>