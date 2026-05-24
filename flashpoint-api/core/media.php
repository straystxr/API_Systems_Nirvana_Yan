<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

require_once dirname(__DIR__) . '/config.php';

function handleMedia(string $method, string $action) {
    match ($action) {
        'videos' => mediaVideos($method),
        default  => error('Media endpoint not found', 404),
    };
}

// GET /core/media.php?action=videos
// Returns all verified videos with article info joined
function mediaVideos(string $method) {
    if ($method !== 'GET') error('Method not allowed', 405);

    $db = getDB();

    $stmt = $db->prepare("
        SELECT 
            m.id,
            m.url,
            m.media_type,
            m.status,
            m.uploaded_at,
            m.article_id,
            a.title        as article_title,
            a.content      as article_body,
            a.category     as article_category,
            a.verification_status,
            u.display_name as uploader_name,
            u.username     as uploader_username
        FROM media m
        LEFT JOIN articles a  ON m.article_id  = a.id
        LEFT JOIN users    u  ON m.uploaded_by = u.id
        WHERE m.media_type = 'video'
        ORDER BY m.uploaded_at DESC
    ");
    $stmt->execute();
    $videos = $stmt->fetchAll();

    respond([
        'count'  => count($videos),
        'videos' => $videos,
    ]);
}

$action = $_GET['action'] ?? '';
handleMedia($_SERVER['REQUEST_METHOD'], $action);



