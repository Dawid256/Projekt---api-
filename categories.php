<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$result     = db()->query('SELECT DISTINCT type FROM products ORDER BY type');
$categories = [];

while ($row = $result->fetch_assoc()) {
    $categories[] = ['type' => (string) $row['type']];
}

json_response(['success' => true, 'categories' => $categories]);
