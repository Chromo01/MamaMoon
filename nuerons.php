<?php
header('Content-Type: application/json');

// --- Database connection ---
$servername = "localhost";
$username = "root";      // or whatever your XAMPP username is
$password = "";          // default is blank in XAMPP
$dbname = "mamamoon";    // make sure this matches your DB name

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

// --- Get URL params ---
$query = isset($_GET['query']) ? $_GET['query'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$group = isset($_GET['group']) ? $_GET['group'] : '';

// --- Base SQL ---
$sql = "SELECT * FROM candles";

// --- WHERE logic ---
$conditions = [];
if (!empty($query)) {
    $querySafe = $conn->real_escape_string($query);
    $conditions[] = "(name LIKE '%$querySafe%' OR description LIKE '%$querySafe%')";
}

if (!empty($group)) {
    $groupSafe = $conn->real_escape_string($group);
    $conditions[] = "scent_group LIKE '%$groupSafe%'";
}

// Optional: if you later add bracelets, necklaces, etc.
// we’ll handle `$category` to switch tables dynamically.
if (!empty($category) && $category !== 'candles') {
    $categorySafe = $conn->real_escape_string($category);
    $sql = "SELECT * FROM $categorySafe";  // assumes other tables exist
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$result = $conn->query($sql);

// --- Return JSON ---
$data = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode($data);

$conn->close();
?>
