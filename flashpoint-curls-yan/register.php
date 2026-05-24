<?php
require_once 'helpers.php';

$result   = null;
$error    = null;
$success  = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name'     => trim($_POST['name'] ?? ''),
        'username' => trim($_POST['username'] ?? ''),
        'email'    => trim($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'role'     => $_POST['role'] ?? 'general',
    ];

    // Call API using cURL — POST /api/auth/register
    $result = apiRequest('POST', '/auth/register', $data);

    if ($result['code'] === 201) {
        $success = 'User registered successfully! You can now log in.';
    } else {
        $error = $result['data']['error'] ?? 'Registration failed.';
    }
}

pageHeader('Register', 'register');
?>

<div class="page-title">Register a New User</div>
<div class="page-sub">
  Calls <span class="code-tag">POST /api/auth/register</span> using cURL
</div>

<?php if ($success): ?>
  <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
  <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

  <!-- Form -->
  <div class="card">
    <div class="card-title">Registration Form</div>
    <form method="POST">
      <div class="form-group">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-input" placeholder="Yan Cesare" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-input" placeholder="yan_cesare" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-input" placeholder="yan@flashpoint.mt" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-input" placeholder="Min 6 characters" required>
      </div>
      <div class="form-group">
        <label class="form-label">Role</label>
        <select name="role" class="form-input" style="border-radius:12px;padding:10px 16px">
          <option value="general" <?= ($_POST['role']??'general')==='general'?'selected':'' ?>>General User</option>
          <option value="journalist" <?= ($_POST['role']??'')==='journalist'?'selected':'' ?>>Journalist</option>
          <option value="verifier" <?= ($_POST['role']??'')==='verifier'?'selected':'' ?>>Verifier</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Register via cURL</button>
    </form>
  </div>

  <!-- Result -->
  <div class="card">
    <div class="card-title">
      API Response
      <?php if ($result): ?>
        <span class="badge badge-<?= $result['code'] ?>" style="margin-left:8px"><?= $result['code'] ?></span>
      <?php endif; ?>
    </div>

    <?php if ($result && $result['code'] === 201 && isset($result['data']['user'])): ?>
      <table class="data-table">
        <tr><th>Field</th><th>Value</th></tr>
        <tr><td>ID</td><td><?= $result['data']['user']['id'] ?></td></tr>
        <tr><td>Name</td><td><?= htmlspecialchars($result['data']['user']['name']) ?></td></tr>
        <tr><td>Username</td><td>@<?= htmlspecialchars($result['data']['user']['username']) ?></td></tr>
        <tr><td>Email</td><td><?= htmlspecialchars($result['data']['user']['email']) ?></td></tr>
        <tr><td>Role</td><td><?= htmlspecialchars($result['data']['user']['role']) ?></td></tr>
        <tr><td>Membership</td><td><?= htmlspecialchars($result['data']['user']['membership']) ?></td></tr>
      </table>
    <?php elseif ($result): ?>
      <div class="result-box"><?= htmlspecialchars(json_encode($result['data'], JSON_PRETTY_PRINT)) ?></div>
    <?php else: ?>
      <p style="font-size:13px;color:#4a5068">Submit the form to see the API response here.</p>
    <?php endif; ?>

    <div style="margin-top:16px;padding-top:14px;border-top:1px solid #E2E8F0">
      <div style="font-size:11px;font-weight:700;color:#4a5068;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px">cURL Request Sent</div>
      <div class="result-box">POST <?= API_BASE ?>/auth/register
Headers: Content-Type: application/json
Body: {
  "name": "...",
  "username": "...",
  "email": "...",
  "password": "...",
  "role": "general"
}</div>
    </div>
  </div>

</div>

<?php pageFooter(); ?>
