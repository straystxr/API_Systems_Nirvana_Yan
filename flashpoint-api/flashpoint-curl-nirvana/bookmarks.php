<?php
require_once 'helpers.php';

if (empty($_SESSION['token'])) {
    header('Location: login.php');
    exit;
}

$success = $error = null;
$addResult = $removeResult = null;

// Add bookmark from article page shortcut
if (!empty($_GET['add'])) {
    $addResult = apiRequest('POST', API_BASE . '/bookmarks.php?action=add',
        ['article_id' => (int)$_GET['add']], $_SESSION['token']);
    if ($addResult['code'] === 201) $success = 'Article bookmarked!';
    else $error = $addResult['data']['error'] ?? 'Failed to bookmark';
}

// Handle add form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_article_id'])) {
    $addResult = apiRequest('POST', API_BASE . '/bookmarks.php?action=add',
        ['article_id' => (int)$_POST['add_article_id']], $_SESSION['token']);
    if ($addResult['code'] === 201) $success = 'Article bookmarked!';
    else $error = $addResult['data']['error'] ?? 'Failed to bookmark';
}

// Handle remove
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_article_id'])) {
    $removeResult = apiRequest('DELETE', API_BASE . '/bookmarks.php?action=remove',
        ['article_id' => (int)$_POST['remove_article_id']], $_SESSION['token']);
    if ($removeResult['code'] === 200) $success = 'Bookmark removed!';
    else $error = $removeResult['data']['error'] ?? 'Failed to remove';
}

// GET bookmarks
$result    = apiRequest('GET', API_BASE . '/bookmarks.php?action=list', [], $_SESSION['token']);
$bookmarks = $result['data']['bookmarks'] ?? [];

header_layout('Bookmarks', 'bookmarks');
?>

<div class="page-title">Bookmarks</div>
<div class="page-sub">
  <span class="method-badge get">GET</span> bookmarks.php?action=list &nbsp;|&nbsp;
  <span class="method-badge post">POST</span> bookmarks.php?action=add &nbsp;|&nbsp;
  <span class="method-badge delete">DELETE</span> bookmarks.php?action=remove
</div>

<?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="two-col">

  <!-- Bookmarks List -->
  <div class="card">
    <div class="card-title">
      My Bookmarks <?= code_badge($result['code']) ?>
      <span style="font-size:12px;font-weight:400;color:var(--muted);margin-left:4px"><?= count($bookmarks) ?> saved</span>
    </div>

    <?php if (empty($bookmarks)): ?>
      <div class="empty">No bookmarks yet</div>
    <?php else: foreach ($bookmarks as $b): ?>
      <div class="article-card">
        <div class="article-title"><?= htmlspecialchars($b['title'] ?? '—') ?></div>
        <div class="article-meta">
          <span class="badge badge-navy"><?= htmlspecialchars($b['category'] ?? '—') ?></span>
          <span style="font-size:11px;color:var(--muted)">Saved: <?= date('d M Y', strtotime($b['bookmarked_at'] ?? 'now')) ?></span>
        </div>
        <div class="article-body"><?= htmlspecialchars(substr($b['body'] ?? '', 0, 80)) ?>...</div>
        <form method="POST" style="margin-top:10px" onsubmit="return confirm('Remove this bookmark?')">
          <input type="hidden" name="remove_article_id" value="<?= $b['id'] ?>">
          <button type="submit" class="btn btn-danger" style="padding:5px 12px;font-size:11px">Remove Bookmark</button>
        </form>
      </div>
    <?php endforeach; endif; ?>

    <div class="code-label">GET cURL Request</div>
    <div class="result-box">GET <?= API_BASE ?>/bookmarks.php?action=list
Headers: Authorization: Bearer [token]
HTTP Code: <?= $result['code'] ?></div>
  </div>

  <!-- Add Bookmark -->
  <div>
    <div class="card card-orange">
      <div class="card-title"><span class="method-badge post">POST</span> Add Bookmark</div>
      <form method="POST">
        <div class="form-group">
          <label class="form-label">Article ID</label>
          <input type="number" name="add_article_id" class="form-input" placeholder="Enter article ID e.g. 1" required>
        </div>
        <button type="submit" class="btn btn-navy btn-block">Bookmark via cURL POST</button>
      </form>
      <?php if ($addResult): ?>
        <div class="code-label">Response <?= code_badge($addResult['code']) ?></div>
        <div class="result-box"><?= htmlspecialchars(json_encode($addResult['data'], JSON_PRETTY_PRINT)) ?></div>
        <div class="code-label">cURL Request Sent</div>
        <div class="result-box">POST <?= API_BASE ?>/bookmarks.php?action=add
Headers: Authorization: Bearer [token]
Body: { "article_id": <?= (int)($_POST['add_article_id'] ?? 0) ?> }</div>
      <?php endif; ?>
    </div>

    <?php if ($removeResult): ?>
    <div class="card">
      <div class="card-title"><span class="method-badge delete">DELETE</span> Remove Response <?= code_badge($removeResult['code']) ?></div>
      <div class="result-box"><?= htmlspecialchars(json_encode($removeResult['data'], JSON_PRETTY_PRINT)) ?></div>
      <div class="code-label">cURL Request Sent</div>
      <div class="result-box">DELETE <?= API_BASE ?>/bookmarks.php?action=remove
Headers: Authorization: Bearer [token]
Body: { "article_id": <?= (int)($_POST['remove_article_id'] ?? 0) ?> }</div>
    </div>
    <?php endif; ?>
  </div>

</div>

<?php footer_layout(); ?>
