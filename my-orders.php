<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$authUser = require_auth();
$userId   = $authUser['id'];

$conn = db();

ensure_order_tables($conn);

$stmt = $conn->prepare(
    'SELECT id, first_name, last_name, address, city, postal_code, created_at
     FROM orders WHERE user_id = ? ORDER BY created_at DESC'
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$ordersRaw = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$orders = [];
foreach ($ordersRaw as $order) {
    $orderId = (int) $order['id'];

    $stmt = $conn->prepare(
        'SELECT product_id, product_name, price, quantity FROM order_items WHERE order_id = ?'
    );
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $total = array_sum(array_map(fn($i) => (float)$i['price'] * (int)$i['quantity'], $items));

    $orders[] = [
        'id'          => $orderId,
        'first_name'  => $order['first_name'],
        'last_name'   => $order['last_name'],
        'address'     => $order['address'],
        'city'        => $order['city'],
        'postal_code' => $order['postal_code'],
        'created_at'  => $order['created_at'],
        'total'       => round($total, 2),
        'items'       => array_map(fn($i) => [
            'product_id'   => (int)   $i['product_id'],
            'product_name' => (string)$i['product_name'],
            'price'        => (float) $i['price'],
            'quantity'     => (int)   $i['quantity'],
        ], $items),
    ];
}

json_response(['success' => true, 'orders' => $orders]);
