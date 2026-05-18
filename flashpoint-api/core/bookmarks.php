<?php
require_once __DIR__ . '/../../config.php';

function handleBookmarks(string $method, string $action) {
    match ($action) {
        'list'   => bookmarksList($method),
        'add'    => bookmarksAdd($method),
        'remove' => bookmarksRemove($method),
        default  => error('Bookmarks endpoint not found', 404),
    };
}

function bookmarksList(string $method) {
    if ($method !== 'GET') error('Method not allowed', 405);
    
    $user = requireAuth();

    $db = getDB();
    $stmt = $db->prepare("
        SELECT a.*, b.created_at as bookmarked_at 
        FROM bookmarks b
        JOIN articles a ON b.article_id = a.id
        WHERE b.user_id = ?
        ORDER BY b.created_at DESC
    ");
    $stmt->execute([$user['id']]);
    
    respond([
        'count' => $stmt->rowCount(),
        'bookmarks' => $stmt->fetchAll()
    ]);
}

function bookmarksAdd(string $method) {
    if ($method !== 'POST') error('Method not allowed', 405);
    
    $user = requireAuth();
    $b = body();
    
    if (empty($b['article_id'])) error('article_id required');

    $db = getDB();

    // Check article exists
    $check = $db->prepare("SELECT id FROM articles WHERE id = ?");
    $check->execute([$b['article_id']]);
    if (!$check->fetch()) error('Article not found', 404);

    try {
        $db->prepare("
            INSERT INTO bookmarks (user_id, article_id)
            VALUES (?, ?)
        ")->execute([$user['id'], $b['article_id']]);

        respond(['message' => 'Article bookmarked'], 201);

    } catch (Exception $e) {
        if (str_contains($e->getMessage(), 'Duplicate')) {
            error('Already bookmarked', 409);
        }
        error('Failed to bookmark', 500);
    }
}

function bookmarksRemove(string $method) {
    if ($method !== 'DELETE') error('Method not allowed', 405);
    
    $user = requireAuth();
    $b = body();
    
    if (empty($b['article_id'])) error('article_id required');

    $db = getDB();
    $stmt = $db->prepare("
        DELETE FROM bookmarks 
        WHERE user_id = ? AND article_id = ?
    ");
    $stmt->execute([$user['id'], $b['article_id']]);

    if ($stmt->rowCount() === 0) {
        error('Bookmark not found', 404);
    }

    respond(['message' => 'Bookmark removed']);
}

// ─── ROUTE ───
$action = $_GET['action'] ?? '';
handleBookmarks($_SERVER['REQUEST_METHOD'], $action);