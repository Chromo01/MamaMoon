<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mamamoon";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$category = $_GET['category'] ?? '';
$group = $_GET['group'] ?? '';

if ($group) {
    $sql = "SELECT * FROM candles WHERE scent_group = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $group);
} elseif ($category) {
    $sql = "SELECT * FROM $category";
    $stmt = $conn->prepare($sql);
} else {
    $sql = "SELECT * FROM candles";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
$conn->close();
?>