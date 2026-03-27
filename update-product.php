<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Metoda niedozwolona. Użyj PUT lub POST.', 405);
}

require_auth();

$data = read_json();

$id          = (int)    ($data['id']          ?? 0);
$name        = trim((string) ($data['name']        ?? ''));
$description = trim((string) ($data['description'] ?? ''));
$price       = isset($data['price'])    ? (float) $data['price']    : null;
$quantity    = isset($data['quantity']) ? (int)   $data['quantity'] : null;
$type        = trim((string) ($data['type']        ?? ''));

if ($id <= 0) {
    json_error('Podaj poprawne id produktu.');
}

$conn = db();
$stmt = $conn->prepare('SELECT id FROM products WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$exists = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$exists) {
    json_error('Produkt nie istnieje.', 404);
}

$fields = [];
$params = [];
$types  = '';

if ($name !== '')        { $fields[] = 'name = ?';        $params[] = $name;        $types .= 's'; }
if ($description !== '') { $fields[] = 'description = ?'; $params[] = $description; $types .= 's'; }
if ($price !== null)     { $fields[] = 'price = ?';       $params[] = $price;       $types .= 'd'; }
if ($quantity !== null)  { $fields[] = 'quantity = ?';    $params[] = $quantity;    $types .= 'i'; }
if ($type !== '')        { $fields[] = 'type = ?';        $params[] = $type;        $types .= 's'; }

if (!$fields) {
    json_error('Brak pól do aktualizacji.');
}

$params[] = $id;
$types   .= 'i';

$stmt = $conn->prepare('UPDATE products SET ' . implode(', ', $fields) . ' WHERE id = ?');
$stmt->bind_param($types, ...$params);
$stmt->execute();
$stmt->close();

$stmt = $conn->prepare('SELECT id, name, description, price, img, type, quantity FROM products WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

json_response([
    'success' => true,
    'message' => 'Produkt zaktualizowany.',
    'product' => normalize_product($product),
]);
