<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

require_once dirname(__DIR__) . '/config.php';

function handleUpload(string $method) {
    if ($method !== 'POST') error('Method not allowed', 405);

    $user = requireAuth();

    // Check file was sent
    if (empty($_FILES['media'])) error('No file uploaded', 400);

    $file     = $_FILES['media'];
    $maxSize  = 1024 * 1024 * 1024; // 1GB
    $allowedTypes = ['video/mp4', 'video/webm', 'video/ogg', 'image/jpeg', 'image/png', 'image/webp'];

    // Validate size
    if ($file['size'] > $maxSize) error('File too large. Maximum size is 50MB', 400);

    // Validate type
    if (!in_array($file['type'], $allowedTypes)) {
        error('Invalid file type. Allowed: mp4, webm, ogg, jpg, png, webp', 400);
    }

    // Determine media type
    $mediaType = str_starts_with($file['type'], 'video/') ? 'video' : 'image';

    // Create upload folder if needed
    $uploadDir = __DIR__ . '/../uploads/' . $mediaType . 's/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    // Generate unique filename
    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $mediaType . '_' . $user['id'] . '_' . time() . '.' . $ext;
    $filepath = $uploadDir . $filename;

    // Move file to uploads folder
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        error('Failed to save file', 500);
    }

    // Build public URL
    $baseUrl  = 'http://localhost/API_Systems_Nirvana_Yan/flashpoint-api/uploads/' . $mediaType . 's/';
    $publicUrl = $baseUrl . $filename;

    // Get article_id if provided
    $articleId = !empty($_POST['article_id']) ? (int)$_POST['article_id'] : null;

    // Save to media table
    $db = getDB();
    $db->prepare("
        INSERT INTO media (article_id, uploaded_by, media_type, url, status)
        VALUES (?, ?, ?, ?, 'pending')
    ")->execute([$articleId, $user['id'], $mediaType, $publicUrl]);

    $mediaId = (int)$db->lastInsertId();

    respond([
        'message'    => ucfirst($mediaType) . ' uploaded successfully',
        'media_id'   => $mediaId,
        'url'        => $publicUrl,
        'media_type' => $mediaType,
        'status'     => 'pending',
    ], 201);
}

handleUpload($_SERVER['REQUEST_METHOD']);