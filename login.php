<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Metoda niedozwolona. Użyj POST.', 405);
}

$data = read_json();

$login    = trim((string) ($data['login']    ?? ''));
$password = (string)       ($data['password'] ?? '');

if ($login === '' || $password === '') {
    json_error('Podaj login i hasło.');
}

$conn = db();


$conn->query("CREATE TABLE IF NOT EXISTS tokens (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    token      VARCHAR(128) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    revoked    TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tokens_user2 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$stmt = $conn->prepare('SELECT id, login, password FROM users WHERE login = ?');
$stmt->bind_param('s', $login);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    json_error('Nieprawidłowy login lub hasło.', 401);
}

$storedPwd = (string) $user['password'];
$valid = password_verify($password, $storedPwd) || hash_equals($storedPwd, $password);

if (!$valid) {
    json_error('Nieprawidłowy login lub hasło.', 401);
}

$userId = (int) $user['id'];


$stmt = $conn->prepare('UPDATE tokens SET revoked = 1 WHERE user_id = ? AND revoked = 0');
$stmt->bind_param('i', $userId);
$stmt->execute();
$stmt->close();

$token     = bin2hex(random_bytes(32)); 
$expiresAt = date('Y-m-d H:i:s', time() + TOKEN_LIFETIME);

$stmt = $conn->prepare('INSERT INTO tokens (user_id, token, expires_at) VALUES (?, ?, ?)');
$stmt->bind_param('iss', $userId, $token, $expiresAt);
$stmt->execute();
$stmt->close();

json_response([
    'success'    => true,
    'message'    => 'Zalogowano pomyślnie.',
    'token'      => $token,
    'expires_at' => $expiresAt,
    'user' => [
        'id'    => $userId,
        'login' => (string) $user['login'],
    ],
]);
