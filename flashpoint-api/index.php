<?php

// ─── CORS + JSON HEADERS ─────────────────────────────────────
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

//require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/config.php';

// ─── ROUTER ──────────────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'];

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$uri = preg_replace('#^.*/api#', '/api', $uri);

$uri = rtrim($uri, '/');

$seg = explode('/', trim($uri, '/'));

// Example:
// /api/users/1
// /api/memberships/1

if (($seg[0] ?? '') !== 'api') {

    http_response_code(404);

    echo json_encode([
        'error' => 'Not found'
    ]);

    exit;
}

$resource = $seg[1] ?? '';
$subA     = $seg[2] ?? '';
$subB     = $seg[3] ?? '';

switch ($resource) {

    // AUTH
    case 'auth':

        require_once __DIR__ . '/api/auth/auth.php';

        handleAuth($method, $subA);

        break;


    // USERS
    case 'users':

        require_once __DIR__ . '/core/users.php';

        handleUsers($method, $subA, $subB);

        break;


    // MEMBERSHIPS
    case 'memberships':

        require_once __DIR__ . '/core/memberships.php';

       handleMemberships($method, $subA, $subB);

        break;


    // EVENTS
    case 'events':

        require_once __DIR__ . '/api/events/events.php';

        handleEvents($method, $subA);

        break;


    // JOURNALISM
    case 'journalism':

        require_once __DIR__ . '/api/journalism/journalism.php';

        handleJournalism($method, $subA);

        break;


    default:

        http_response_code(404);

        echo json_encode([
            'error' => 'Endpoint not found'
        ]);
}