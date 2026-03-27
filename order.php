<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Metoda niedozwolona. Użyj POST.', 405);
}


$authUser = require_auth();

$data = read_json();

$firstName  = trim((string) ($data['first_name']  ?? ''));
$lastName   = trim((string) ($data['last_name']   ?? ''));
$address    = trim((string) ($data['address']     ?? ''));
$city       = trim((string) ($data['city']        ?? ''));
$postalCode = trim((string) ($data['postal_code'] ?? ''));
$items      = $data['items'] ?? null;

if ($firstName === '' || $lastName === '' || $address === '' || $city === '' || $postalCode === '') {
    json_error('Uzupełnij wszystkie dane wysyłki: first_name, last_name, address, city, postal_code.');
}

if (!is_array($items) || !$items) {
    json_error('Koszyk jest pusty. Dodaj produkty do zamówienia.');
}

$normalizedItems = [];
foreach ($items as $item) {
    $productId = (int) ($item['id']       ?? 0);
    $quantity  = (int) ($item['quantity'] ?? 0);

    if ($productId <= 0 || $quantity <= 0) {
        json_error('Nieprawidłowe dane koszyka: każdy element musi mieć id > 0 i quantity > 0.');
    }

    $normalizedItems[] = ['id' => $productId, 'quantity' => $quantity];
}

$conn   = db();
$userId = $authUser['id'];

ensure_order_tables($conn);
$conn->begin_transaction();

try {
    $products = [];

    foreach ($normalizedItems as $item) {
        $stmt = $conn->prepare('SELECT id, name, price, quantity FROM products WHERE id = ? FOR UPDATE');
        $stmt->bind_param('i', $item['id']);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$product) {
            throw new RuntimeException("Produkt o id {$item['id']} nie istnieje.");
        }

        if ((int) $product['quantity'] < $item['quantity']) {
            throw new RuntimeException(
                "Produkt \"{$product['name']}\" nie ma wystarczającego stanu (dostępne: {$product['quantity']})."
            );
        }

        $products[] = [
            'id'       => (int)   $product['id'],
            'name'     => (string)$product['name'],
            'price'    => (float) $product['price'],
            'quantity' => $item['quantity'],
        ];
    }

    $stmt = $conn->prepare(
        'INSERT INTO orders (user_id, first_name, last_name, address, city, postal_code)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('isssss', $userId, $firstName, $lastName, $address, $city, $postalCode);
    $stmt->execute();
    $orderId = $stmt->insert_id;
    $stmt->close();

    $itemStmt  = $conn->prepare(
        'INSERT INTO order_items (order_id, product_id, product_name, price, quantity)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stockStmt = $conn->prepare('UPDATE products SET quantity = quantity - ? WHERE id = ?');

    foreach ($products as $p) {
        $itemStmt->bind_param('iisdi', $orderId, $p['id'], $p['name'], $p['price'], $p['quantity']);
        $itemStmt->execute();

        $stockStmt->bind_param('ii', $p['quantity'], $p['id']);
        $stockStmt->execute();
    }

    $itemStmt->close();
    $stockStmt->close();

    $conn->commit();

    json_response([
        'success'  => true,
        'message'  => 'Zamówienie złożone pomyślnie.',
        'order_id' => $orderId,
    ], 201);

} catch (Throwable $e) {
    $conn->rollback();
    json_error($e->getMessage(), 400);
}
