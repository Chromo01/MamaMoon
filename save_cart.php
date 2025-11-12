<?php
header('Content-Type: application/json');

// Database connection
$conn = new mysqli('localhost', 'root', '', 'MamaMoon');

if ($conn->connect_error) {
    echo json_encode(['message' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Get POST body
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !is_array($data)) {
    echo json_encode(['message' => 'Invalid cart data']);
    exit;
}

// Create table if it doesn't exist
$conn->query("
    CREATE TABLE IF NOT EXISTS carts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255),
        price DECIMAL(10,2),
        image TEXT,
        quantity INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

$stmt = $conn->prepare("INSERT INTO carts (name, price, image, quantity) VALUES (?, ?, ?, ?)");

foreach ($data as $item) {
    $name = $item['name'];
    $price = $item['price'];
    $image = $item['image'];
    $quantity = $item['quantity'];

    $stmt->bind_param("sdsi", $name, $price, $image, $quantity);
    $stmt->execute();
}

$stmt->close();
$conn->close();

echo json_encode(['message' => 'Cart saved successfully to database!']);
?>