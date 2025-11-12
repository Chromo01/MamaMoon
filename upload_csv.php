<?php
header('Content-Type: text/plain');
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csvFile']) && isset($_POST['table'])) {
    $table = $_POST['table'];
    $file = $_FILES['csvFile']['tmp_name'];

    if (!file_exists($file)) {
        exit("❌ No file uploaded or file missing.");
    }

    // Read CSV
    $handle = fopen($file, 'r');
    if ($handle === false) {
        exit("❌ Unable to open uploaded file.");
    }

    // Skip header row
    fgetcsv($handle);

    $inserted = 0;
    $pdo->beginTransaction();

    try {
        while (($row = fgetcsv($handle)) !== false) {
            $row = array_map('trim', $row);

            switch ($table) {
                case 'candles':
                    [$name, $description, $price, $image_url, $scent_group] = $row;
                    $stmt = $pdo->prepare("INSERT INTO candles (name, description, price, image_url, scent_group)
                                           VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $description, $price, $image_url, $scent_group]);
                    break;

                case 'bracelets':
                    [$name, $description, $price, $image_url, $material_group] = $row;
                    $stmt = $pdo->prepare("INSERT INTO bracelets (name, description, price, image_url, material_group)
                                           VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $description, $price, $image_url, $material_group]);
                    break;

                case 'necklaces':
                    [$name, $description, $price, $image_url, $material_group] = $row;
                    $stmt = $pdo->prepare("INSERT INTO necklaces (name, description, price, image_url, material_group)
                                           VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $description, $price, $image_url, $material_group]);
                    break;

                case 'other_items':
                    [$name, $description, $price, $image_url, $category] = $row;
                    $stmt = $pdo->prepare("INSERT INTO other_items (name, description, price, image_url, category)
                                           VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $description, $price, $image_url, $category]);
                    break;
            }
            $inserted++;
        }

        $pdo->commit();
        fclose($handle);
        echo "✅ Upload complete! {$inserted} new item(s) added to '{$table}'.";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "❌ Error during upload: " . $e->getMessage();
    }
} else {
    echo "❌ Invalid request. Make sure you selected a file and category.";
}
?>