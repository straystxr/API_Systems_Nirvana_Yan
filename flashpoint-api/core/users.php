<?php
// GET    /api/users/{id}               – get profile
// GET    /api/users/{id}/membership    – get membership tier details
// PATCH  /api/users/{id}/membership    – upgrade membership
// PATCH  /api/users/{id}/password      – change password
// POST   /api/users/{id}/photo         – upload profile photo
// POST   /api/users/{userId}/bookmarks – add bookmark
// GET    /api/users/{userId}/bookmarks – get bookmarks

function handleUsers(string $method, string $userId, string $action) {
    if (!$userId) error('User ID required', 400);

    if ($action === '' && $method === 'GET')             { userGet($userId); return; }
    if ($action === 'password' && $method === 'PATCH')   { userPassword($method, $userId); return; }
    if ($action === 'membership' && $method === 'GET')   { userMembershipGet($userId); return; }
    if ($action === 'membership' && $method === 'PATCH') { userMembershipUpgrade($userId); return; }
    if ($action === 'photo' && $method === 'POST')       { userPhotoUpload($userId); return; }
    if ($action === 'bookmarks' && $method === 'POST')   { bookmarkAdd($userId); return; }
    if ($action === 'bookmarks' && $method === 'GET')    { bookmarkGet($userId); return; }

    error('Endpoint not found', 404);
}

// ─── GET /api/users/{id} ──────────────────────────────────────────────────────
function userGet(string $userId) {
    $user = verifyToken();
    if ((string)$user['sub'] !== $userId) error('Forbidden', 403);

    $db   = getDB();
    $stmt = $db->prepare("
        SELECT u.id, u.username, u.display_name, u.email, u.user_type,
               u.profile_photo_url, u.is_verified, u.created_at,
               mt.name as membership_name, mt.price_eur
        FROM users u
        LEFT JOIN memberships m ON u.membership_id = m.id
        LEFT JOIN membershipTiers mt ON m.tier_id = mt.id
        WHERE u.id = ?
    ");
    $stmt->execute([$userId]);
    $userData = $stmt->fetch();

    if (!$userData) error('User not found', 404);

    respond([
        'user' => [
            'id'               => $userData['id'],
            'username'         => $userData['username'],
            'name'             => $userData['display_name'],
            'email'            => $userData['email'],
            'role'             => $userData['user_type'],
            'profile_photo'    => $userData['profile_photo_url'],
            'is_verified'      => (bool)$userData['is_verified'],
            'membership'       => $userData['membership_name'] ?? 'Basic',
            'membership_price' => $userData['price_eur'] ?? '0.00',
            'joined'           => $userData['created_at'],
        ]
    ]);
}

// ─── GET /api/users/{id}/membership ───────────────────────────────────────────
function userMembershipGet(string $userId) {
    $user = verifyToken();
    if ((string)$user['sub'] !== $userId) error('Forbidden', 403);

    $db   = getDB();
    $stmt = $db->prepare("
        SELECT mt.*, m.id as membership_id,
               p.purchase_date, p.expiry_date, p.status as purchase_status
        FROM users u
        LEFT JOIN memberships m ON u.membership_id = m.id
        LEFT JOIN membershipTiers mt ON m.tier_id = mt.id
        LEFT JOIN in_app_purchases p ON p.user_id = u.id AND p.status = 'active'
        WHERE u.id = ?
        ORDER BY p.purchase_date DESC
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $data = $stmt->fetch();

    if (!$data) error('User not found', 404);

    respond([
        'membership' => [
            'tier_id'                      => $data['id'],
            'name'                         => $data['name'] ?? 'Basic',
            'price_eur'                    => $data['price_eur'] ?? '0.00',
            'description'                  => $data['description'],
            'can_remove_ads'               => (bool)$data['can_remove_ads'],
            'can_upload_media'             => (bool)$data['can_upload_media'],
            'can_post_news'                => (bool)$data['can_post_news'],
            'can_bookmark'                 => (bool)$data['can_bookmark'],
            'can_comment'                  => (bool)$data['can_comment'],
            'can_react'                    => (bool)$data['can_react'],
            'can_get_discounts'            => (bool)$data['can_get_discounts'],
            'can_access_vacancies'         => (bool)$data['can_access_vacancies'],
            'can_receive_fast_notifications'=> (bool)$data['can_receive_fast_notifications'],
            'can_view_videos_early'        => (bool)$data['can_view_videos_early'],
            'purchase_date'                => $data['purchase_date'],
            'expiry_date'                  => $data['expiry_date'],
            'purchase_status'              => $data['purchase_status'],
        ]
    ]);
}

// ─── PATCH /api/users/{id}/membership ─────────────────────────────────────────
function userMembershipUpgrade(string $userId) {
    $user = verifyToken();
    if ((string)$user['sub'] !== $userId) error('Forbidden', 403);

    $b = body();
    if (empty($b['tier_id'])) error('tier_id is required', 400);

    $db = getDB();

    // Get tier details
    $tier = $db->prepare("SELECT * FROM membershipTiers WHERE id = ?");
    $tier->execute([$b['tier_id']]);
    $tierData = $tier->fetch();
    if (!$tierData) error('Membership tier not found', 404);

    // Get user current membership
    $memStmt = $db->prepare("SELECT id, tier_id FROM memberships WHERE user_id = ?");
    $memStmt->execute([$userId]);
    $mem = $memStmt->fetch();

    // Check if already on this tier
    if ($mem && (int)$mem['tier_id'] === (int)$b['tier_id']) {
        error('You are already on this plan', 409);
    }

    $isCancellation = (int)$b['tier_id'] === 1;

    try {
        $db->beginTransaction();

        if ($mem) {
            $db->prepare("UPDATE memberships SET tier_id = ? WHERE user_id = ?")
               ->execute([$b['tier_id'], $userId]);
            $membershipId = $mem['id'];
        } else {
            $db->prepare("INSERT INTO memberships (user_id, tier_id) VALUES (?, ?)")
               ->execute([$userId, $b['tier_id']]);
            $membershipId = (int)$db->lastInsertId();
            $db->prepare("UPDATE users SET membership_id = ? WHERE id = ?")
               ->execute([$membershipId, $userId]);
        }

        // Cancel old active purchases
        $db->prepare("
            UPDATE in_app_purchases SET status = 'cancelled'
            WHERE user_id = ? AND status = 'active'
        ")->execute([$userId]);

        // Log new purchase (free tier = no purchase record needed, just log as cancelled)
        if (!$isCancellation) {
            $expiry = date('Y-m-d H:i:s', strtotime('+1 month'));
            $db->prepare("
                INSERT INTO in_app_purchases (user_id, membership_id, purchase_date, expiry_date, amount, status)
                VALUES (?, ?, NOW(), ?, ?, 'active')
            ")->execute([$userId, $membershipId, $expiry, $tierData['price_eur']]);
        } else {
            $expiry = null;
        }

        $db->commit();

    } catch (Exception $e) {
        $db->rollBack();
        error('Failed: ' . $e->getMessage(), 500);
    }

    respond([
        'message'     => $isCancellation ? 'Subscription cancelled. You are now on the Basic plan.' : 'Plan updated successfully',
        'tier'        => $tierData['name'],
        'price'       => $tierData['price_eur'],
        'expiry_date' => $expiry ?? null,
    ]);
}

// ─── PATCH /api/users/{id}/password ───────────────────────────────────────────
function userPassword(string $method, string $userId) {
    if ($method !== 'PATCH') error('Method not allowed', 405);
    $user = verifyToken();
    if ((string)$user['sub'] !== $userId) error('Forbidden', 403);

    $b = body();
    if (empty($b['currentPass']) || empty($b['newPass'])) error('currentPass and newPass are required', 400);
    if (strlen($b['newPass']) < 6) error('New password must be at least 6 characters', 400);

    $db   = getDB();
    $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $userData = $stmt->fetch();

    if (!$userData || !password_verify($b['currentPass'], $userData['password_hash'])) {
        error('Current password is incorrect', 401);
    }

    $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?")
       ->execute([password_hash($b['newPass'], PASSWORD_BCRYPT), $userId]);

    respond(['message' => 'Password updated successfully'], 204);
}

// ─── POST /api/users/{id}/photo ───────────────────────────────────────────────
function userPhotoUpload(string $userId) {
    $user = verifyToken();
    if ((string)$user['sub'] !== $userId) error('Forbidden', 403);

    if (empty($_FILES['photo'])) error('No photo uploaded', 400);

    $file     = $_FILES['photo'];
    $allowed  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize  = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowed)) error('Invalid file type. Use JPG, PNG, GIF or WEBP', 400);
    if ($file['size'] > $maxSize) error('File too large. Max size is 5MB', 400);

    // Create uploads folder if it doesn't exist
    $uploadDir = __DIR__ . '/../../uploads/profiles/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    // Generate unique filename
    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'user_' . $userId . '_' . time() . '.' . $ext;
    $filepath = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        error('Failed to save photo', 500);
    }

    // Build public URL
    $photoUrl = 'http://localhost/API_Systems_Nirvana_Vella/flashpoint-api/uploads/profiles/' . $filename;

    // Save URL to database
    $db = getDB();
    $db->prepare("UPDATE users SET profile_photo_url = ? WHERE id = ?")
       ->execute([$photoUrl, $userId]);

    respond([
        'message'   => 'Profile photo updated',
        'photo_url' => $photoUrl,
    ]);
}

// ─── POST /api/users/{userId}/bookmarks ──────────────────────────────────────
function bookmarkAdd(string $userId) {
    $user = verifyToken();
    if ((string)$user['sub'] !== $userId) error('Forbidden', 403);

    $b = body();
    if (empty($b['articleId'])) error('articleId is required', 400);

    $db = getDB();

    $stmt = $db->prepare("SELECT id FROM articles WHERE id = ?");
    $stmt->execute([$b['articleId']]);
    if (!$stmt->fetch()) error('Article not found', 404);

    $stmt = $db->prepare("SELECT id FROM bookmarks WHERE user_id = ? AND article_id = ?");
    $stmt->execute([$userId, $b['articleId']]);
    if ($stmt->fetch()) error('Already bookmarked', 409);

    $db->prepare("INSERT INTO bookmarks (user_id, article_id, created_at) VALUES (?, ?, NOW())")
       ->execute([$userId, $b['articleId']]);

    $bookmarkId = (int)$db->lastInsertId();

    respond(['message' => 'Article bookmarked', 'bookmark_id' => $bookmarkId], 201);
}

// ─── GET /api/users/{userId}/bookmarks ───────────────────────────────────────
function bookmarkGet(string $userId) {
    $user = verifyToken();
    if ((string)$user['sub'] !== $userId) error('Forbidden', 403);

    $db   = getDB();
    $stmt = $db->prepare("
        SELECT b.id as bookmark_id, b.created_at as bookmarked_at,
               a.id as article_id, a.title, a.summary, a.source, a.published_at,
               u.display_name as author
        FROM bookmarks b
        JOIN articles a ON b.article_id = a.id
        LEFT JOIN users u ON a.created_by = u.id
        WHERE b.user_id = ?
        ORDER BY b.created_at DESC
    ");
    $stmt->execute([$userId]);
    $bookmarks = $stmt->fetchAll();

    respond(['count' => count($bookmarks), 'bookmarks' => $bookmarks]);
}