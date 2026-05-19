<?php
require_once 'helpers.php';

if (empty($_SESSION['token'])) {
    header('Location: login.php');
    exit;
}

$userId  = $_SESSION['user']['id'];
$token   = $_SESSION['token'];
$success = null;
$error   = null;
$patchResult = null;

// Handle upgrade form — PATCH /api/users/{id}/membership
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['tier_id'])) {
    $patchResult = apiRequest('PATCH', '/memberships/' . $userId . '/membership', [
        'tier_id' => (int)$_POST['tier_id']
    ], $token);

    if ($patchResult['code'] === 200) {
        $success = $patchResult['data']['message'] ?? 'Membership updated!';
    } else {
        $error = $patchResult['data']['error'] ?? 'Update failed.';
    }
}

// Call API using cURL — GET /api/users/{id}/membership
$result     = apiRequest('GET', '/memberships/' . $userId . '/membership', [], $token);
$membership = $result['data']['membership'] ?? null;

pageHeader('Membership', 'membership');
?>

<div class="page-title">Membership</div>
<div class="page-sub">
  Calls <span class="code-tag">GET /api/memberships/<?= $userId ?>/membership</span> and
  <span class="code-tag">PATCH /api/memberships/<?= $userId ?>/membership</span> using cURL
</div>

<?php if ($success): ?>
  <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
  <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">

  <!-- Current Membership -->
  <div class="card">
    <div class="card-title">
      Current Plan
      <span class="badge badge-<?= $result['code'] ?>" style="margin-left:8px"><?= $result['code'] ?> OK</span>
    </div>

    <?php if ($membership): ?>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
        <div>
          <div style="font-size:22px;font-weight:900;color:#0d1b3e"><?= htmlspecialchars($membership['name']) ?></div>
          <div style="font-size:13px;color:#4a5068"><?= htmlspecialchars($membership['description'] ?? '') ?></div>
        </div>
        <div style="font-size:22px;font-weight:900;color:#f5a623">
          <?= $membership['price_eur'] == '0.00' ? 'Free' : '€' . $membership['price_eur'] . '/mo' ?>
        </div>
      </div>

      <div class="feature-grid">
        <?php
        $features = [
          'can_remove_ads'      => 'No Ads',
          'can_post_news'       => 'Post Articles',
          'can_upload_media'    => 'Upload Media',
          'can_bookmark'        => 'Bookmarks',
          'can_comment'         => 'Comments',
          'can_get_discounts'   => 'Discounts',
          'can_react'           => 'Reactions',
          'can_view_videos_early' => 'Early Videos',
        ];
        foreach ($features as $key => $label):
          $active = !empty($membership[$key]);
        ?>
          <div class="feature-item">
            <span class="<?= $active ? 'tick' : 'cross' ?>"><?= $active ? '✓' : '✗' ?></span>
            <span style="color:<?= $active ? '#0d1b3e' : '#9aa0b4' ?>"><?= $label ?></span>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if (!empty($membership['expiry_date'])): ?>
        <div style="margin-top:14px;font-size:12px;color:#4a5068">
          Renews: <strong><?= date('d M Y', strtotime($membership['expiry_date'])) ?></strong>
        </div>
      <?php endif; ?>

    <?php else: ?>
      <div class="alert alert-error">Could not load membership data.</div>
    <?php endif; ?>
  </div>

  <!-- Upgrade Form -->
  <div class="card">
    <div class="card-title">Change Plan — PATCH Request</div>
    <form method="POST">
      <div class="form-group">
        <label class="form-label">Select New Plan</label>
        <select name="tier_id" class="form-input" style="border-radius:12px;padding:10px 16px">
          <option value="1" <?= ($membership['tier_id']??0)==1?'selected':'' ?>>Basic — Free</option>
          <option value="2" <?= ($membership['tier_id']??0)==2?'selected':'' ?>>Premium — €5.99/mo</option>
          <option value="3" <?= ($membership['tier_id']??0)==3?'selected':'' ?>>Journalism — €12.99/mo</option>
        </select>
      </div>
      <button type="submit" class="btn btn-orange" style="margin-bottom:10px;width:100%">Update via cURL PATCH</button>
      <?php if ($membership['tier_id'] > 1): ?>
        <button type="submit" name="tier_id" value="1" class="btn btn-danger" style="width:100%"
          onclick="return confirm('Cancel subscription and return to Basic?')">
          Cancel Subscription
        </button>
      <?php endif; ?>
    </form>

    <?php if ($patchResult): ?>
      <div style="margin-top:14px;padding-top:14px;border-top:1px solid #E2E8F0">
        <div style="font-size:11px;font-weight:700;color:#4a5068;margin-bottom:6px;text-transform:uppercase">PATCH Response</div>
        <div class="result-box"><?= htmlspecialchars(json_encode($patchResult['data'], JSON_PRETTY_PRINT)) ?></div>
      </div>
    <?php endif; ?>
  </div>

</div>

<!-- cURL Details -->
<div class="card">
  <div class="card-title">cURL Request Details</div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
    <div>
      <div style="font-size:11px;font-weight:700;color:#4a5068;margin-bottom:6px;text-transform:uppercase">GET Request</div>
      <div class="result-box">GET <?= API_BASE ?>/users/<?= $userId ?>/membership
Headers:
  Authorization: Bearer <?= substr($token,0,25) ?>...
HTTP Code: <?= $result['code'] ?></div>
    </div>
    <div>
      <div style="font-size:11px;font-weight:700;color:#4a5068;margin-bottom:6px;text-transform:uppercase">PATCH Request</div>
      <div class="result-box">PATCH <?= API_BASE ?>/users/<?= $userId ?>/membership
Headers:
  Content-Type: application/json
  Authorization: Bearer <?= substr($token,0,25) ?>...
Body: { "tier_id": 2 }
HTTP Code: <?= $patchResult['code'] ?? '—' ?></div>
    </div>
  </div>
</div>

<?php pageFooter(); ?>
