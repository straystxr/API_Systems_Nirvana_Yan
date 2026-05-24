<?php
require_once 'helpers.php';

$success = $error = null;
$postResult = null;
$articleId = (int)($_GET['article_id'] ?? $_POST['article_id'] ?? 1);

// Post comment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SESSION['token'])) {
    $postResult = apiRequest('POST', API_BASE . '/articles.php?action=comments', [
        'article_id' => (int)$_POST['article_id'],
        'body'       => $_POST['body'] ?? '',
    ], $_SESSION['token']);

    if ($postResult['code'] === 201) {
        $success = 'Comment posted!';
        $articleId = (int)$_POST['article_id'];
    } else {
        $error = $postResult['data']['error'] ?? 'Failed to post comment';
    }
}

// GET comments
$commentsUrl = API_BASE . '/articles.php?action=comments&article_id=' . $articleId;
$result   = $articleId ? apiRequest('GET', $commentsUrl) : ['data' => [], 'code' => 0];
$comments = $result['data']['comments'] ?? [];

header_layout('Comments', 'comments');
?>

<div class="page-title">Comments</div>
<div class="page-sub">
  <span class="method-badge get">GET</span> articles.php?action=comments&article_id={id} &nbsp;|&nbsp;
  <span class="method-badge post">POST</span> articles.php?action=comments
</div>

<?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="two-col">

  <!-- Comments List -->
  <div class="card">
    <!-- Article selector -->
    <form method="GET" style="display:flex;gap:8px;margin-bottom:16px">
      <input type="number" name="article_id" class="form-input" placeholder="Article ID" value="<?= $articleId ?>" style="flex:1" required>
      <button type="submit" class="btn btn-orange" style="padding:8px 18px">Load Comments</button>
    </form>

    <div class="card-title">
      Comments for Article #<?= $articleId ?> <?= $articleId ? code_badge($result['code']) : '' ?>
      <span style="font-size:12px;font-weight:400;color:var(--muted);margin-left:4px"><?= count($comments) ?> comments</span>
    </div>

    <?php if (!$articleId): ?>
      <div class="empty">Enter an article ID above to load comments</div>
    <?php elseif (empty($comments)): ?>
      <div class="empty">No comments yet on this article</div>
    <?php else: foreach ($comments as $c): ?>
      <div class="article-card">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
          <div style="width:32px;height:32px;border-radius:50%;background:var(--navy);display:flex;align-items:center;justify-content:center;color:#fff;font-size:12px;font-weight:700;flex-shrink:0">
            <?= strtoupper(substr($c['display_name'] ?? $c['username'] ?? '?', 0, 1)) ?>
          </div>
          <div>
            <div style="font-size:13px;font-weight:700;color:var(--navy)"><?= htmlspecialchars($c['display_name'] ?? $c['username'] ?? '—') ?></div>
            <div style="font-size:11px;color:var(--muted)">@<?= htmlspecialchars($c['username'] ?? '—') ?> · <?= isset($c['created_at']) ? date('d M Y H:i', strtotime($c['created_at'])) : '—' ?></div>
          </div>
        </div>
        <div class="article-body"><?= htmlspecialchars($c['body'] ?? '') ?></div>
      </div>
    <?php endforeach; endif; ?>

    <?php if ($articleId): ?>
      <div class="code-label">GET cURL Request</div>
      <div class="result-box">GET <?= htmlspecialchars($commentsUrl) ?>
HTTP Code: <?= $result['code'] ?>
Count: <?= count($comments) ?></div>
    <?php endif; ?>
  </div>

  <!-- Post Comment -->
  <div>
    <div class="card card-orange">
      <div class="card-title"><span class="method-badge post">POST</span> Add a Comment</div>

      <?php if (!empty($_SESSION['token'])): ?>
      <form method="POST">
        <div class="form-group">
          <label class="form-label">Article ID</label>
          <input type="number" name="article_id" class="form-input" placeholder="Article ID" value="<?= $articleId ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Comment</label>
          <textarea name="body" class="form-input" placeholder="Write your comment..." required style="min-height:100px"></textarea>
        </div>
        <button type="submit" class="btn btn-navy btn-block">Post Comment via cURL</button>
      </form>
      <?php else: ?>
        <div class="empty"><a href="login.php" class="btn btn-orange">Login to comment</a></div>
      <?php endif; ?>

      <?php if ($postResult): ?>
        <div class="code-label">Response <?= code_badge($postResult['code']) ?></div>
        <div class="result-box"><?= htmlspecialchars(json_encode($postResult['data'], JSON_PRETTY_PRINT)) ?></div>
        <div class="code-label">cURL Request Sent</div>
        <div class="result-box">POST <?= API_BASE ?>/articles.php?action=comments
Headers:
  Content-Type: application/json
  Authorization: Bearer [token]
Body: {
  "article_id": <?= (int)($_POST['article_id'] ?? 0) ?>,
  "body": "<?= htmlspecialchars(substr($_POST['body'] ?? '', 0, 40)) ?>..."
}</div>
      <?php endif; ?>
    </div>

    <div class="card">
      <div class="card-title">About Comments</div>
      <p style="font-size:13px;color:var(--muted);line-height:1.8">
        Comments are linked to articles. Any logged-in user can post a comment. The GET endpoint is public — no token needed to read comments.
      </p>
      <div style="margin-top:12px;display:flex;flex-direction:column;gap:6px">
        <div style="font-size:12px;padding:8px 12px;background:#f8f9fc;border-radius:8px"><span class="method-badge get">GET</span> &nbsp;No auth required</div>
        <div style="font-size:12px;padding:8px 12px;background:#f8f9fc;border-radius:8px"><span class="method-badge post">POST</span> &nbsp;Bearer token required</div>
      </div>
    </div>
  </div>

</div>

<?php footer_layout(); ?>
