<?php
// ─── DB CONNECTION ────────────────────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'flashpoint');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    }
    return $pdo;
}

// ─── RESPONSE HELPERS ─────────────────────────────────────────────────────────
function respond($data, int $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

function error($message, int $code = 400) {
    respond(['error' => $message], $code);
}

// ─── BODY PARSER ──────────────────────────────────────────────────────────────
function body(): array {
    $raw = file_get_contents('php://input');
    return json_decode($raw, true) ?? [];
}

// ─── AUTH / TOKEN ─────────────────────────────────────────────────────────────
define('TOKEN_SECRET', 'flashpoint_secret_key_2026');
define('TOKEN_EXPIRY', 3600); // 1 hour

function generateToken(array $payload): string {
    $payload['exp'] = time() + TOKEN_EXPIRY;
    $encoded = base64_encode(json_encode($payload));
    $sig = hash_hmac('sha256', $encoded, TOKEN_SECRET);
    return $encoded . '.' . $sig;
}

function verifyToken(): array {
    // XAMPP sometimes puts the token in different places
    $auth = '';
    if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        $auth = $_SERVER['HTTP_AUTHORIZATION'];
    } elseif (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $auth = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    } elseif (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    }

    if (empty($auth) || !str_starts_with($auth, 'Bearer ')) error('No token provided', 401);
    
    $token = substr($auth, 7);
    $parts = explode('.', $token, 2);
    $encoded = $parts[0] ?? null;
    $sig = $parts[1] ?? null;
    if (!$encoded || !$sig) error('Malformed token', 401);
    if (!hash_equals(hash_hmac('sha256', $encoded, TOKEN_SECRET), $sig)) error('Invalid token', 401);
    $payload = json_decode(base64_decode($encoded), true);
    if (!$payload || $payload['exp'] < time()) error('Token expired', 401);
    return $payload;
}

function requireRole(string ...$roles): array {
    $user = verifyToken();
    if (!in_array($user['role'] ?? '', $roles)) error('Forbidden', 403);
    return $user;
}