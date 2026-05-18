<?php
// CORS Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


function getDB() {
    static $db = null;
    
    if ($db !== null) return $db;
    
    $host = 'localhost';
    $dbname = 'flashpoint';
    $user = 'root';
    $pass = 'root';               // XAMPP default: NO password || mamp root pass
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;port=8889;dbname=$dbname;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $db = new PDO($dsn, $user, $pass, $options);
        return $db;
    } catch (PDOException $e) {
        error('Database connection failed: ' . $e->getMessage(), 500);
    }
}


function body() {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}


function error(string $message, int $code = 400) {
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit();
}


function respond(array $data, int $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit();
}


define('TOKEN_EXPIRY', 3600);
function generateToken(array $payload) {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload['iat'] = time();
    $payload['exp'] = time() + TOKEN_EXPIRY;
    
    $b64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $b64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));
    
    //REPLACE THIS IN PRODUCTION with a real secret from environment variables
    $secret = 'flashpoint-secret-key-2026';
    
    $signature = hash_hmac('sha256', "$b64Header.$b64Payload", $secret, true);
    $b64Sig = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
    return "$b64Header.$b64Payload.$b64Sig";
}


function requireAuth(): array {
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
        error('Authorization header required', 401);
    }

    $token = substr($auth, 7);
    //error('DEBUG token: ' . substr($token, 0, 20) . ' parts: ' . count(explode('.', $token)), 400);
    $parts = explode('.', $token);
    if (count($parts) !== 3) error('Invalid token format', 401);

    [$b64Header, $b64Payload, $b64Sig] = $parts;

    $secret = 'flashpoint-secret-key-2026';

    // Must match exactly how generateToken() creates the signature
    $expectedSig = hash_hmac('sha256', "$b64Header.$b64Payload", $secret, true);
    $expectedB64 = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($expectedSig));

    if (!hash_equals($expectedB64, $b64Sig)) {
        error('Invalid token signature', 401);
    }

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