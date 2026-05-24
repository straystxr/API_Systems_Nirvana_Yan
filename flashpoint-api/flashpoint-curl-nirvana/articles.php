<?php
require_once 'helpers.php';

$success = $error = null;
$createResult = null;

//Handle create article form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SESSION['token'])) {
    $createResult = apiRequest('POST', API_BASE . '/articles.php?action=create', [
        'title'    => $_POST['title'] ?? '',
        'body'     => $_POST['body'] ?? '',
        'category' => $_POST['category'] ?? 'news',
        'lat'      => $_POST['lat'] ? (float)$_POST['lat'] : null,
        'lng'      => $_POST['lng'] ? (float)$_POST['lng'] : null,
        'source'   => $_POST['source'] ?? '',
    ], $_SESSION['token']);

    if ($createResult['code'] === 201) {
        $success = 'Article submitted for verification! Article ID: ' . $createResult['data']['article_id'];
    } else {
        $error = $createResult['data']['error'] ?? 'Failed to create article';
    }
}

// Filter params
$filter = $_GET['filter'] ?? '';
$category = $_GET['category'] ?? '';
$url = API_BASE . '/articles.php?action=list';
if ($filter === 'pending') $url .= '&status=pending';
if ($filter === 'unverified') $url .= '&verified=0';
if ($category) $url .= '&category=' . urlencode($category);

// GET articles
$result   = apiRequest('GET', $url);
$articles = $result['data']['articles'] ?? [];

header_layout('Articles', 'articles');
?>

<div class="page-title">Articles</div>
<div class="page-sub"><span class="method-badge get">GET</span> articles.php?action=list</div>

<?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="two-col">

  <!--Article List -->
  <div>
    <!-- Filters -->
    <div class="card" style="padding:14px 18px;margin-bottom:14px">
      <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
        <a href="articles.php" class="btn <?= !$filter && !$category ? 'btn-navy' : 'btn-orange' ?>" style="padding:6px 14px;font-size:12px">All</a>
        <a href="articles.php?filter=pending" class="btn <?= $filter==='pending' ? 'btn-navy' : 'btn-orange' ?>" style="padding:6px 14px;font-size:12px">Pending</a>
        <a href="articles.php?filter=unverified" class="btn <?= $filter==='unverified' ? 'btn-navy' : 'btn-orange' ?>" style="padding:6px 14px;font-size:12px">Unverified</a>
        <form method="GET" style="display:flex;gap:6px;align-items:center">
          <input type="text" name="category" class="form-input" placeholder="Filter by category..." value="<?= htmlspecialchars($category) ?>" style="width:160px;padding:6px 14px;font-size:12px">
          <button type="submit" class="btn btn-navy" style="padding:6px 14px;font-size:12px">Go</button>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-title">
        Articles <?= code_badge($result['code']) ?>
        <span style="font-size:12px;font-weight:400;color:var(--muted);margin-left:4px"><?= count($articles) ?> results</span>
      </div>

      <?php if (empty($articles)): ?>
        <div class="empty">No articles found</div>
      <?php else: foreach ($articles as $a): ?>
        <div class="article-card">
          <div class="article-title"><?= htmlspecialchars($a['title'] ?? '—') ?></div>
          <div class="article-meta">
            <span class="badge badge-navy"><?= htmlspecialchars($a['category'] ?? '—') ?></span>
            <span class="badge <?= ($a['verification_status'] ?? '') === 'verified' ? 'badge-green' : (($a['verification_status'] ?? '') === 'in_progress' ? 'badge-blue' : 'badge-red') ?>">
              <?= htmlspecialchars($a['verification_status'] ?? 'unverified') ?>
            </span>
            <span style="font-size:11px;color:var(--muted)">by <?= htmlspecialchars($a['author_display'] ?? $a['author_name'] ?? '—') ?></span>
          </div>
          <div class="article-body"><?= htmlspecialchars(substr($a['body'] ?? '', 0, 100)) ?>...</div>
          <div style="margin-top:10px;display:flex;gap:6px">
            <a href="comments.php?article_id=<?= $a['id'] ?>" class="btn btn-orange" style="padding:5px 12px;font-size:11px">Comments</a>
            <a href="bookmarks.php?add=<?= $a['id'] ?>" class="btn btn-navy" style="padding:5px 12px;font-size:11px">Bookmark</a>
          </div>
        </div>
      <?php endforeach; endif; ?>

      <div class="code-label">cURL Request Sent</div>
      <div class="result-box">GET <?= htmlspecialchars($url) ?>

HTTP Code: <?= $result['code'] ?>
Count: <?= count($articles) ?></div>
    </div>
  </div>

  <!--Create Article -->
  <div>
    <?php if (!empty($_SESSION['token']) && ($_SESSION['user']['role'] ?? '') === 'journalist'): ?>
    <div class="card card-orange">
      <div class="card-title"><span class="method-badge post">POST</span> Create Article</div>
      <form method="POST">
        <div class="form-group">
          <label class="form-label">Title</label>
          <input type="text" name="title" class="form-input" placeholder="Article title" required>
        </div>
        <div class="form-group">
          <label class="form-label">Body</label>
          <textarea name="body" class="form-input" placeholder="Article content..." required></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Category</label>
          <select name="category" class="form-input">
            <option value="news">News</option>
            <option value="politics">Politics</option>
            <option value="sport">Sport</option>
            <option value="crime">Crime</option>
            <option value="environment">Environment</option>
          </select>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
          <div class="form-group">
            <label class="form-label">Latitude</label>
            <input type="number" name="lat" class="form-input" placeholder="35.8997" step="any">
          </div>
          <div class="form-group">
            <label class="form-label">Longitude</label>
            <input type="number" name="lng" class="form-input" placeholder="14.5147" step="any">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Source</label>
          <input type="text" name="source" class="form-input" placeholder="FlashPoint Reporter">
        </div>
        <button type="submit" class="btn btn-navy btn-block">Submit Article via cURL</button>
      </form>

      <?php if ($createResult): ?>
        <div class="code-label">Response <?= code_badge($createResult['code']) ?></div>
        <div class="result-box"><?= htmlspecialchars(json_encode($createResult['data'], JSON_PRETTY_PRINT)) ?></div>
      <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="card">
      <div class="card-title">Create Article</div>
      <div class="empty">
        <?php if (empty($_SESSION['token'])): ?>
          <a href="login.php" class="btn btn-orange">Login to create articles</a>
        <?php else: ?>
          <p style="color:var(--muted)">Only journalists can create articles</p>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

</div>

<?php footer_layout(); ?>
