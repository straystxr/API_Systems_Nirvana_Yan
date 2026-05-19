<?php
require_once __DIR__ . '/../config.php';

function handleArticles(string $method, string $action) {
    match ($action) {
        //will be added to the base url to specify the method used 
        'list'     => articlesList($method),
        'create'   => articlesCreate($method),
        'verify'   => articlesVerify($method),
        'edit'     => articleEdit($method), 
        'comments' => articlesComments($method),
        'remove'   => articleDelete($method),
        default    => error('Articles endpoint not found', 404),
    };
}

// GET /api/articles.php?action=list
function articlesList(string $method) {
    if ($method !== 'GET') error('Method not allowed', 405);

    $db = getDB();
    
    $status = $_GET['status'] ?? null;
    $category = $_GET['category'] ?? null;
    $verified = $_GET['verified'] ?? null;

    $sql = "SELECT a.*, u.username as author_name, u.display_name as author_display 
            FROM articles a 
            JOIN users u ON a.user_id = u.id 
            WHERE 1=1";
    $params = [];

    if ($status) {
        $sql .= " AND a.status = ?";
        $params[] = $status;
    }
    if ($category) {
        $sql .= " AND a.category = ?";
        $params[] = $category;
    }
    if ($verified !== null) {
        $sql .= " AND a.verification_status = ?";
        $params[] = $verified ? 'verified' : 'unverified';
    }

    $sql .= " ORDER BY a.created_at DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $articles = $stmt->fetchAll();

    respond([
        'count' => count($articles),
        'articles' => $articles
    ]);
}

// POST /api/articles.php?action=create
// Requires: journalist or admin role
function articlesCreate(string $method) {
    if ($method !== 'POST') error('Method not allowed', 405);

    $user = requireAuth();
    if (!in_array($user['role'], ['journalist', 'admin'])) {
        error('Only journalists can post articles', 403);
    }

    $b = body();
    $required = ['title', 'body', 'category'];
    foreach ($required as $f) {
        if (empty($b[$f])) error("Missing field: $f");
    }

    $db = getDB();

    try {
        $db->prepare("
            INSERT INTO articles (user_id, title, body, category, lat, lng, url, source, status, verification_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'unverified')
        ")->execute([
            $user['id'],
            $b['title'],
            $b['body'],
            $b['category'],
            $b['lat'] ?? null,
            $b['lng'] ?? null,
            $b['url'] ?? '',
            $b['source'] ?? ''
        ]);

        $articleId = (int)$db->lastInsertId();

        respond([
            'message' => 'Article submitted for verification',
            'article_id' => $articleId,
            'status' => 'pending',
            'verification_status' => 'unverified'
        ], 201);

    } catch (Exception $e) {
        error('Failed to create article: ' . $e->getMessage(), 500);
    }
}

// PATCH /api/articles.php?action=verify
// Requires: verifier or admin role
function articlesVerify(string $method) {
    if ($method !== 'PATCH') error('Method not allowed', 405);

    $user = requireAuth();
    if (!in_array($user['role'], ['verifier', 'admin'])) {
        error('Only verifiers can verify articles', 403);
    }

    $b = body();
    if (empty($b['article_id'])) error('article_id required');
    if (empty($b['status']) || !in_array($b['status'], ['unverified', 'in_progress', 'verified'])) {
        error('Invalid status. Must be: unverified, in_progress, verified');
    }

    $db = getDB();

    $stmt = $db->prepare("SELECT id FROM articles WHERE id = ?");
    $stmt->execute([$b['article_id']]);
    if (!$stmt->fetch()) error('Article not found', 404);

    $db->prepare("
        UPDATE articles 
        SET verification_status = ?, verifier_id = ?, status = ? 
        WHERE id = ?
    ")->execute([
        $b['status'],
        $user['id'],
        $b['status'] === 'verified' ? 'published' : 'pending',
        $b['article_id']
    ]);

    respond([
        'message' => 'Article verification updated',
        'article_id' => $b['article_id'],
        'verification_status' => $b['status']
    ]);
}

// GET/POST /api/articles.php?action=comments
function articlesComments(string $method) {
    $db = getDB();

    if ($method === 'GET') {
        $articleId = $_GET['article_id'] ?? null;
        if (!$articleId) error('article_id required');

        $stmt = $db->prepare("
            SELECT c.*, u.username, u.display_name 
            FROM comments c 
            JOIN users u ON c.user_id = u.id 
            WHERE c.article_id = ? 
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$articleId]);
        respond(['comments' => $stmt->fetchAll()]);
    }

    if ($method === 'POST') {
        $user = requireAuth();
        $b = body();
        
        if (empty($b['article_id'])) error('article_id required');
        if (empty($b['body'])) error('body required');

        // Verify article exists
        $check = $db->prepare("SELECT id FROM articles WHERE id = ?");
        $check->execute([$b['article_id']]);
        if (!$check->fetch()) error('Article not found', 404);

        $db->prepare("
            INSERT INTO comments (article_id, user_id, body)
            VALUES (?, ?, ?)
        ")->execute([$b['article_id'], $user['id'], $b['body']]);

        respond(['message' => 'Comment added'], 201);
    }

    error('Method not allowed', 405);
}

function articleEdit(string $method){
    // PATCH /api/articles.php?action=edit || only the author of the article can edit it 
    // & only the verifier can change the verification status
    if ($method !== 'PATCH') error('Method not allowed', 405);

    $user = requireAuth();
    
    $b = body();
    if (empty($b['article_id'])) error('article_id required');

    $db = getDB();

    // Check article exists
    $stmt = $db->prepare("SELECT user_id, status FROM articles WHERE id = ?");
    $stmt->execute([$b['article_id']]);
    $article = $stmt->fetch();
    
    if (!$article) error('Article not found', 404);
    
    // ONLY the author or admin can edit article content
    if ($article['user_id'] !== $user['id'] && $user['role'] !== 'admin') {
        error('Only the author can edit this article', 403);
    }

    // Build update dynamically
    $fields = [];
    $params = [];
    
    if (isset($b['title'])) {
        $fields[] = "title = ?";
        $params[] = $b['title'];
    }
    if (isset($b['body'])) {
        $fields[] = "body = ?";
        $params[] = $b['body'];
    }
    if (isset($b['category'])) {
        $fields[] = "category = ?";
        $params[] = $b['category'];
    }
    if (isset($b['lat'])) {
        $fields[] = "lat = ?";
        $params[] = $b['lat'];
    }
    if (isset($b['lng'])) {
        $fields[] = "lng = ?";
        $params[] = $b['lng'];
    }
    if (isset($b['source'])) {
        $fields[] = "source = ?";
        $params[] = $b['source'];
    }

    if (empty($fields)) error('No fields to update');

    $params[] = $b['article_id'];
    
    $sql = "UPDATE articles SET " . implode(', ', $fields) . " WHERE id = ?";
    $db->prepare($sql)->execute($params);

    respond([
        'message' => 'Article updated',
        'article_id' => $b['article_id']
    ]);
}

function articleDelete(string $method) {
    if ($method !== 'DELETE') error('Method not allowed', 405);

    $user = requireAuth();
    $b = body();

    if (empty($b['article_id'])) error('article_id required');

    $db = getDB();

    // First check if article exists and get owner
    $stmt = $db->prepare("SELECT user_id FROM articles WHERE id = ?");
    $stmt->execute([$b['article_id']]);
    $article = $stmt->fetch();

    if (!$article) {
        error('Article not found', 404);
    }

    // Only owner or admin can delete
    if ($article['user_id'] !== $user['id'] && $user['role'] !== 'admin') {
        error('Only the author or admin can delete this article', 403);
    }

    // Delete the article
    $db->prepare("DELETE FROM articles WHERE id = ?")->execute([$b['article_id']]);

    respond(['message' => 'Article deleted']);
}

// Method override for servers that block DELETE/PATCH
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_GET['_method'])) {
    $_SERVER['REQUEST_METHOD'] = strtoupper($_GET['_method']);
}

//route
$action = $_GET['action'] ?? '';
handleArticles($_SERVER['REQUEST_METHOD'], $action);