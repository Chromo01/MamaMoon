<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mamamoon";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

$table = isset($_GET['table']) ? $_GET['table'] : '';
$allowed = ['candles', 'bracelets', 'necklaces', 'other_items'];

if (!in_array($table, $allowed)) {
  die(json_encode(["error" => "Invalid table name."]));
}

$sql = "SELECT * FROM $table";
$result = $conn->query($sql);

$data = [];
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $data[] = $row;
  }
}

echo json_encode($data);
$conn->close();
?>