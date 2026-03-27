<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Metoda niedozwolona. Użyj POST.', 405);
}

$token = get_bearer_token();
if (!$token) {
    json_error('Brak tokena autoryzacji.', 401);
}

$conn = db();
$stmt = $conn->prepare('UPDATE tokens SET revoked = 1 WHERE token = ?');
$stmt->bind_param('s', $token);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();

if ($affected === 0) {
    json_error('Token nie istnieje lub już unieważniony.', 404);
}

json_response([
    'success' => true,
    'message' => 'Wylogowano pomyślnie. Token unieważniony.',
]);
