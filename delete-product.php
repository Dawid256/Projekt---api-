<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Metoda niedozwolona. Użyj DELETE lub POST.', 405);
}

require_auth();

$data = read_json();
$id   = (int) ($data['id'] ?? $_GET['id'] ?? 0);

if ($id <= 0) {
    json_error('Podaj poprawne id produktu.');
}

$conn = db();
$stmt = $conn->prepare('SELECT id, name FROM products WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    json_error('Produkt nie istnieje.', 404);
}

$stmt = $conn->prepare('DELETE FROM products WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();

json_response([
    'success' => true,
    'message' => 'Produkt "' . $product['name'] . '" został usunięty.',
]);
