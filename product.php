<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    json_error('Brak poprawnego id produktu.', 400);
}

$conn = db();
$stmt = $conn->prepare('SELECT id, name, description, price, img, type, quantity FROM products WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    json_error('Nie znaleziono produktu.', 404);
}

json_response(['success' => true, 'product' => normalize_product($product)]);
