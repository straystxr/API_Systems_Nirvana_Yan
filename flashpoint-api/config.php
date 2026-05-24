<?php
// ─── CORS ────────────────────────────────────────────────────────────────────
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ─── DATABASE — auto detects XAMPP vs MAMP ───────────────────────────────────
function getDB(): PDO {
    static $db = null;
    if ($db !== null) return $db;

    // MAMP uses port 8889 and password root
    // XAMPP uses port 3306 and no password
    $isMamp = file_exists('/Applications/MAMP/htdocs');

    $host    = '127.0.0.1';
    $port    = $isMamp ? '8889' : '3306';
    $dbname  = 'flashpoint';
    $user    = 'root';
    $pass    = $isMamp ? 'root' : '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";

    try {
        $db = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        return $db;
    } catch (PDOException $e) {
        error('Database connection failed: ' . $e->getMessage(), 500);
    }
}

// ─── HELPERS ──────────────────────────────────────────────────────────────────
function body(): array {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function error(string $message, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit();
}

function respond(array $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data);
    exit();
}

// ─── TOKEN ────────────────────────────────────────────────────────────────────
define('TOKEN_EXPIRY', 3600);
define('TOKEN_SECRET', 'flashpoint-secret-key-2026');

function generateToken(array $payload): string {
    $header     = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
    $payload['iat'] = time();
    $payload['exp'] = time() + TOKEN_EXPIRY;
    $b64Payload = base64_encode(json_encode($payload));

    $header     = str_replace(['+','/',  '='], ['-', '_', ''], $header);
    $b64Payload = str_replace(['+','/',  '='], ['-', '_', ''], $b64Payload);

    $signature  = hash_hmac('sha256', "$header.$b64Payload", TOKEN_SECRET);
    $b64Sig     = str_replace(['+','/', '='], ['-', '_', ''], base64_encode($signature));

    return "$header.$b64Payload.$b64Sig";
}

// ─── AUTH —
// Called requireAuth() in Nirvana's files
// Called verifyToken() in Yan's files
// Both names work — they call the same function underneath

function requireAuth(): array {
    return _verifyJWT();
}

function verifyToken(): array {
    return _verifyJWT();
}

function _verifyJWT(): array {
    // Check all possible header locations (XAMPP and MAMP handle this differently)
    $auth = '';
    if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        $auth = $_SERVER['HTTP_AUTHORIZATION'];
    } elseif (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $auth = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    } elseif (function_exists('getallheaders')) {
        $headers = getallheaders();
        $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    }

    if (empty($auth) || !str_starts_with($auth, 'Bearer ')) {
        error('No token provided', 401);
    }

    $token  = substr($auth, 7);
    $parts  = explode('.', $token);

    if (count($parts) !== 3) error('Invalid token format', 401);

    [$b64Header, $b64Payload, $b64Sig] = $parts;

    // Verify signature
    $expectedSig = hash_hmac('sha256', "$b64Header.$b64Payload", TOKEN_SECRET);
    $expectedB64 = str_replace(['+','/', '='], ['-', '_', ''], base64_encode($expectedSig));

    if (!hash_equals($expectedB64, $b64Sig)) {
        error('Invalid token signature', 401);
    }

    // Decode payload
    $payload = json_decode(
        base64_decode(str_replace(['-', '_'], ['+', '/'], $b64Payload)),
        true
    );

    if (!$payload || ($payload['exp'] ?? 0) < time()) {
        error('Token expired or invalid', 401);
    }

    return [
        'id'   => $payload['sub'],
        'name' => $payload['name'] ?? '',
        'role' => $payload['role'] ?? 'general',
    ];
}