<?php
require_once __DIR__ . '/../config.php';

function handleComments(string $method, string $action) {
    match ($action) {
        'list'      => commentsList($method),
        'single'    => commentSingle($method),
        'byArticle' => commentsByArticle($method),
        'create'    => commentCreate($method),
        'edit'      => commentEdit($method),
        'remove'    => commentDelete($method),
        default     => error('Comments endpoint not found', 404),
    };
}

function commentsList(string $method) {
    if ($method !== 'GET') error('Method not allowed', 405);

    $db = getDB();
    $stmt = $db->query("SELECT * FROM comments ORDER BY id ASC");

    respond([
        'count'    => $stmt->rowCount(),
        'comments' => $stmt->fetchAll()
    ]);
}

function commentSingle(string $method) {
    if ($method !== 'GET') error('Method not allowed', 405);

    $id = $_GET['id'] ?? null;
    if (empty($id)) error('id required');

    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM comments WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $comment = $stmt->fetch();

    if (!$comment) error('Comment not found', 404);

    respond($comment);
}

function commentsByArticle(string $method) {
    if ($method !== 'GET') error('Method not allowed', 405);

    $articleId = $_GET['article_id'] ?? null;
    if (empty($articleId)) error('article_id required');

    $db = getDB();
    $stmt = $db->prepare("
        SELECT 
            c.id,
            c.body,
            c.user_id,
            c.created_at,
            u.username,
            u.display_name
        FROM comments c
        LEFT JOIN users u ON c.user_id = u.id
        WHERE c.article_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$articleId]);

    respond([
        'count'    => $stmt->rowCount(),
        'comments' => $stmt->fetchAll()
    ]);
}

function commentCreate(string $method) {
    if ($method !== 'POST') error('Method not allowed', 405);

    $user = requireAuth();
    $b = body();

    if (empty($b['body']))    error('content required');
    if (empty($b['article_id'])) error('article_id required');

    $db = getDB();

    // Verify article exists
    $check = $db->prepare("SELECT id FROM articles WHERE id = ?");
    $check->execute([$b['article_id']]);
    if (!$check->fetch()) error('Article not found', 404);

    $stmt = $db->prepare("
        INSERT INTO comments (body, article_id, user_id, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([
        htmlspecialchars(strip_tags($b['body'])),
        $b['article_id'],
        $user['id']
    ]);

    respond([
        'message'   => 'Comment created',
        'comment_id' => $db->lastInsertId()
    ], 201);
}

function commentEdit(string $method) {
    if ($method !== 'PUT' && $method !== 'PATCH') error('Method not allowed', 405);

    $user = requireAuth();
    $b = body();

    if (empty($b['id']))   error('id required');
    if (empty($b['body'])) error('body required');  // changed from 'content'

    $db = getDB();

    // Verify ownership
    $owner = $db->prepare("SELECT user_id FROM comments WHERE id = ?");
    $owner->execute([$b['id']]);
    $comment = $owner->fetch();

    if (!$comment) error('Comment not found', 404);
    if ($comment['user_id'] != $user['id']) error('Unauthorized', 403);

    $stmt = $db->prepare("
        UPDATE comments
        SET body = ?          -- changed from 'content'
        WHERE id = ?
    ");
    $stmt->execute([
        htmlspecialchars(strip_tags($b['body'])),
        $b['id']
    ]);

    respond(['message' => 'Comment updated']);
}

function commentDelete(string $method) {
    if ($method !== 'DELETE') error('Method not allowed', 405);

    $user = requireAuth();
    $b = body();

    if (empty($b['id'])) error('id required');

    $db = getDB();

    // Fetch comment to check ownership
    $stmt = $db->prepare("SELECT user_id FROM comments WHERE id = ?");
    $stmt->execute([$b['id']]);
    $comment = $stmt->fetch();

    if (!$comment) error('Comment not found', 404);

    // Allow if: admin, verifier, or original author
    $role = $user['role'] ?? '';
    $isAuthor = $comment['user_id'] == $user['id'];
    $canDelete = $isAuthor || in_array($role, ['admin', 'verifier'], true);

    if (!$canDelete) error('Unauthorized', 403);

    $db->prepare("DELETE FROM comments WHERE id = ?")->execute([$b['id']]);

    respond(['message' => 'Comment deleted']);
}

// route
$action = $_GET['action'] ?? '';
handleComments($_SERVER['REQUEST_METHOD'], $action);