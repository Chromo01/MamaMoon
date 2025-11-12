<?php
// Allow text output
header('Content-Type: text/plain');

// Set target directory inside the MamaMoon folder
$targetDir = __DIR__ . "/images/";

// Ensure the folder exists
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["imageFile"])) {
    $file = $_FILES["imageFile"];
    $fileName = basename($file["name"]);
    $targetFile = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Allow only certain image types
    $allowed = ["jpg", "jpeg", "png", "gif", "webp"];

    if (!in_array($fileType, $allowed)) {
        exit("❌ Invalid file type. Allowed: JPG, PNG, GIF, or WEBP.");
    }

    // Save file safely
    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        echo "✅ Upload successful! Use this path in your CSV:\nimages/$fileName";
    } else {
        echo "❌ Upload failed. Please try again.";
    }
} else {
    echo "❌ No file uploaded.";
}
?>