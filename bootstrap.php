<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


function db(): mysqli
{
    static $conn = null;
    if ($conn instanceof mysqli) return $conn;
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');
    return $conn;
}



function json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function json_error(string $message, int $status = 400): void
{
    json_response(['success' => false, 'message' => $message], $status);
}



function read_json(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') return [];
    $data = json_decode($raw, true);
    if (!is_array($data)) json_error('Nieprawidłowe dane JSON.', 400);
    return $data;
}



function get_bearer_token(): ?string
{
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/^Bearer\s+(.+)$/i', $auth, $m)) return $m[1];
    return null;
}

function require_auth(): array
{
    $token = get_bearer_token();
    if (!$token) {
        json_error('Brak tokena autoryzacji. Zaloguj się.', 401);
    }

    $conn = db();
    $stmt = $conn->prepare(
        'SELECT t.user_id, t.expires_at, u.login
         FROM tokens t
         JOIN users u ON u.id = t.user_id
         WHERE t.token = ? AND t.revoked = 0'
    );
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        json_error('Nieprawidłowy lub unieważniony token.', 401);
    }

    if (strtotime($row['expires_at']) < time()) {
   
        $conn->query("UPDATE tokens SET revoked = 1 WHERE token = '$token'");
        json_error('Token wygasł. Zaloguj się ponownie.', 401);
    }

    return ['id' => (int)$row['user_id'], 'login' => $row['login']];
}



function request_base_url(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir    = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/.');
    return $scheme . '://' . $host . ($dir ?: '');
}

function image_url(?string $path): string
{
    $path = trim((string) $path);
    if ($path === '') return request_base_url() . '/uploads/placeholder.png';
    if (preg_match('~^https?://~i', $path)) return $path;
    $filename = basename(str_replace('\\', '/', $path));
    foreach ([__DIR__ . '/uploads/' . $filename, __DIR__ . '/img/' . $filename] as $c) {
        if (is_file($c)) {
            $sub = str_contains($c, '/uploads/') ? '/uploads/' : '/img/';
            return request_base_url() . $sub . rawurlencode($filename);
        }
    }
    return request_base_url() . '/uploads/placeholder.png';
}

function normalize_product(array $row): array
{
    return [
        'id'          => (int)    $row['id'],
        'name'        => (string) $row['name'],
        'description' => (string) ($row['description'] ?? ''),
        'price'       => (float)  $row['price'],
        'img'         => (string) $row['img'],
        'image_url'   => image_url((string) $row['img']),
        'type'        => (string) $row['type'],
        'quantity'    => (int)    $row['quantity'],
    ];
}

function ensure_upload_dir(): string
{
    $dir = __DIR__ . '/uploads';
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    return $dir;
}

function ensure_order_tables(mysqli $conn): void
{
    $conn->query("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        first_name VARCHAR(100) NOT NULL,
        last_name  VARCHAR(100) NOT NULL,
        address    VARCHAR(255) NOT NULL,
        city       VARCHAR(100) NOT NULL,
        postal_code VARCHAR(20) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id)
            ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $conn->query("CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id     INT NOT NULL,
        product_id   INT NOT NULL,
        product_name VARCHAR(255) NOT NULL,
        price        DECIMAL(10,2) NOT NULL,
        quantity     INT NOT NULL,
        CONSTRAINT fk_oi_order   FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT fk_oi_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}
