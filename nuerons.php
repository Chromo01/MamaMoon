<?php
// Always emit JSON (and nothing else)
header('Content-Type: application/json; charset=utf-8');

// In production, don't echo warnings as HTML
ini_set('display_errors', 0);
ini_set('html_errors', 0);

// If your db_connect.php echoes anything, fix that first.
// It should only define $pdo and NOT echo.
require_once __DIR__ . '/db_connect.php';

try {
    // --- Inputs (optional): ?query=...&group=...&category=candles ---
    $query    = isset($_GET['query']) ? trim($_GET['query']) : '';
    $group    = isset($_GET['group']) ? trim($_GET['group']) : '';
    $category = isset($_GET['category']) ? trim($_GET['category']) : 'candles';

    // Whitelist table names if you plan to reuse this endpoint:
    $allowedTables = ['candles','bracelets','necklaces','other_items'];
    if (!in_array($category, $allowedTables, true)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Invalid category']);
        exit;
    }

    // Column name for the “group” filter differs per table
    $groupCol = match ($category) {
        'candles'     => 'scent_group',
        'bracelets'   => 'material_group',
        'necklaces'   => 'material_group',
        'other_items' => 'category',
        default       => 'scent_group',
    };

    // --- Build SQL with optional filters ---
    $sql = "SELECT id, name, description, price, image_url, {$groupCol} AS group_name
            FROM {$category}";
    $where = [];
    $params = [];

    if ($query !== '') {
        $where[] = "(name LIKE :q OR description LIKE :q)";
        $params[':q'] = "%{$query}%";
    }
    if ($group !== '' && strtolower($group) !== 'all') {
        $where[] = "{$groupCol} = :g";
        $params[':g'] = $group;
    }
    if ($where) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    $sql .= " ORDER BY name ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // Normalize data types / keys expected by your JS:
    // - keep image_url as-is
    // - price as number
    foreach ($rows as &$r) {
        if (isset($r['price'])) $r['price'] = (float)$r['price'];
        // Expose scent_group consistently for candles:
        if ($category === 'candles') {
            $r['scent_group'] = $r['group_name'] ?? null;
        }
    }
    unset($r);

    echo json_encode($rows, JSON_UNESCAPED_SLASHES);
    exit;

} catch (Throwable $e) {
        // Log server-side; don’t dump HTML to client
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Server error']);
        exit;
}