<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$conn   = db();
$type   = trim((string) ($_GET['type']   ?? ''));
$search = trim((string) ($_GET['search'] ?? ''));
$idsRaw = trim((string) ($_GET['ids']    ?? ''));

$sql    = 'SELECT id, name, description, price, img, type, quantity FROM products WHERE 1=1';
$params = [];
$types  = '';

if ($idsRaw !== '') {
    $ids = array_values(array_filter(array_map('intval', explode(',', $idsRaw)), fn(int $i) => $i > 0));
    if (!$ids) {
        json_response(['success' => true, 'products' => []]);
    }
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql    .= " AND id IN ($placeholders)";
    $params  = array_merge($params, $ids);
    $types  .= str_repeat('i', count($ids));
}

if ($type !== '' && $type !== 'all') {
    $sql    .= ' AND type = ?';
    $params[] = $type;
    $types  .= 's';
}

if ($search !== '') {
    $sql    .= ' AND name LIKE ?';
    $params[] = '%' . $search . '%';
    $types  .= 's';
}

$sql .= ' ORDER BY id DESC';
$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result   = $stmt->get_result();
$products = [];

while ($row = $result->fetch_assoc()) {
    $products[] = normalize_product($row);
}

$stmt->close();

json_response(['success' => true, 'products' => $products]);
