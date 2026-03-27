<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Metoda niedozwolona. Użyj POST.', 405);
}


require_auth();

$name         = trim((string) ($_POST['name']        ?? ''));
$description  = trim((string) ($_POST['description'] ?? ''));
$price        = (float)       ($_POST['price']       ?? 0);
$quantity     = (int)         ($_POST['quantity']    ?? 0);
$existingType = trim((string) ($_POST['type']        ?? ''));
$newType      = trim((string) ($_POST['newtype']     ?? ''));
$type         = $newType !== '' ? $newType : $existingType;
$img          = $_FILES['img'] ?? null;

if ($name === '' || $description === '' || $price < 0.01 || $quantity < 1 || $type === '') {
    json_error('Wypełnij poprawnie wszystkie pola (name, description, price >= 0.01, quantity >= 1, type).');
}

if (!$img || (int) ($img['error'] ?? 1) !== 0) {
    json_error('Dodaj zdjęcie produktu (pole img).');
}

$ext     = strtolower(pathinfo((string) $img['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png'];

if (!in_array($ext, $allowed, true)) {
    json_error('Dozwolone są tylko pliki jpg, jpeg i png.');
}

$filename    = uniqid('', true) . '.' . $ext;
$destination = ensure_upload_dir() . '/' . $filename;

if (!move_uploaded_file((string) $img['tmp_name'], $destination)) {
    json_error('Nie udało się zapisać pliku.', 500);
}

$conn = db();
$stmt = $conn->prepare('INSERT INTO products (name, description, price, img, type, quantity) VALUES (?, ?, ?, ?, ?, ?)');
$stmt->bind_param('ssdssi', $name, $description, $price, $filename, $type, $quantity);
$stmt->execute();
$productId = $stmt->insert_id;
$stmt->close();

$stmt = $conn->prepare('SELECT id, name, description, price, img, type, quantity FROM products WHERE id = ?');
$stmt->bind_param('i', $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

json_response([
    'success' => true,
    'message' => 'Produkt dodany pomyślnie.',
    'product' => normalize_product($product),
], 201);
