<?php
require_once 'helpers.php';

// Redirect if not logged in
if (empty($_SESSION['token'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user']['id'];
$token  = $_SESSION['token'];

// Call API using cURL — GET /api/users/{id}
$result = apiRequest('GET', '/users/' . $userId, [], $token);
$user   = $result['data']['user'] ?? null;

pageHeader('Profile', 'profile');
?>

<div class="page-title">User Profile</div>
<div class="page-sub">
  Calls <span class="code-tag">GET /api/users/<?= $userId ?></span> using cURL with Bearer token
</div>

<?php if (!$user): ?>
  <div class="alert alert-error">
    Could not load profile. <?= htmlspecialchars($result['data']['error'] ?? '') ?>
    <span class="badge badge-<?= $result['code'] ?>" style="margin-left:8px"><?= $result['code'] ?></span>
  </div>
<?php else: ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

  <div class="card">
    <div class="card-title">
      Profile Data
      <span class="badge badge-<?= $result['code'] ?>" style="margin-left:8px"><?= $result['code'] ?> OK</span>
    </div>

    <div style="display:flex;align-items:center;gap:16px;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid #E2E8F0">
      <div style="width:60px;height:60px;border-radius:50%;background:#f5a623;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:900;color:#fff;flex-shrink:0">
        <?= strtoupper(substr($user['name'],0,1)) ?>
      </div>
      <div>
        <div style="font-size:18px;font-weight:900;color:#0d1b3e"><?= htmlspecialchars($user['name']) ?></div>
        <div style="font-size:13px;color:#4a5068">@<?= htmlspecialchars($user['username']) ?></div>
        <span style="background:#FFF3E0;color:#f5a623;font-size:10px;font-weight:700;padding:2px 10px;border-radius:10px;text-transform:capitalize"><?= htmlspecialchars($user['role']) ?></span>
      </div>
    </div>

    <table class="data-table">
      <tr><th>Field</th><th>Value</th></tr>
      <tr><td>ID</td><td><?= $user['id'] ?></td></tr>
      <tr><td>Email</td><td><?= htmlspecialchars($user['email']) ?></td></tr>
      <tr><td>Membership</td><td><?= htmlspecialchars($user['membership'] ?? 'Basic') ?></td></tr>
      <tr><td>Verified</td><td><?= $user['is_verified'] ? '✓ Verified' : '✗ Unverified' ?></td></tr>
      <tr><td>Joined</td><td><?= $user['joined'] ? date('d M Y', strtotime($user['joined'])) : '—' ?></td></tr>
    </table>

    <div style="margin-top:16px">
      <a href="membership.php" class="btn btn-orange">View Membership</a>
    </div>
  </div>

  <div class="card">
    <div class="card-title">cURL Request Details</div>
    <div class="result-box">GET <?= API_BASE ?>/users/<?= $userId ?>

Headers:
  Content-Type: application/json
  Authorization: Bearer <?= substr($token, 0, 30) ?>...

HTTP Code: <?= $result['code'] ?></div>

    <div style="margin-top:14px">
      <div style="font-size:11px;font-weight:700;color:#4a5068;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px">Raw JSON Response</div>
      <div class="result-box"><?= htmlspecialchars(json_encode($result['data'], JSON_PRETTY_PRINT)) ?></div>
    </div>
  </div>

</div>
<?php endif; ?>

<?php pageFooter(); ?>
