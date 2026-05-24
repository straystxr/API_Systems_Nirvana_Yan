<?php
require_once 'helpers.php';

$result  = null;
$error   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'username' => trim($_POST['username'] ?? ''),
        'password' => $_POST['password'] ?? '',
    ];

    // Call API using cURL — POST /api/auth/login
    $result = apiRequest('POST', '/auth/login', $data);

    if ($result['code'] === 200 && isset($result['data']['access_token'])) {
        // Store token and user in session
        $_SESSION['token'] = $result['data']['access_token'];
        $_SESSION['user']  = $result['data']['user'];
        header('Location: profile.php');
        exit;
    } else {
        $error = $result['data']['error'] ?? 'Login failed.';
    }
}

pageHeader('Login', 'login');
?>

<div class="page-title">Login</div>
<div class="page-sub">
  Calls <span class="code-tag">POST /api/auth/login</span> using cURL — stores Bearer token in session
</div>

<?php if ($error): ?>
  <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

  <div class="card">
    <div class="card-title">Login Form</div>
    <form method="POST">
      <div class="form-group">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-input" placeholder="yan_cesare" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-input" placeholder="Your password" required>
      </div>
      <button type="submit" class="btn btn-primary">Login via cURL</button>
      <a href="register.php" style="margin-left:12px;font-size:13px;color:#f5a623">Register instead</a>
    </form>
  </div>

  <div class="card">
    <div class="card-title">How it works</div>
    <p style="font-size:13px;color:#4a5068;line-height:1.8;margin-bottom:12px">
      When you submit this form, PHP sends a cURL request to the FlashPoint API.
      If successful, the Bearer token is stored in a PHP session and used for all
      subsequent authenticated requests.
    </p>
    <div class="result-box">POST <?= API_BASE ?>/auth/login
Headers: Content-Type: application/json
Body: {
  "username": "...",
  "password": "..."
}

Response:
{
  "access_token": "eyJzdWIi...",
  "token_type": "Bearer",
  "expires_in": 3600,
  "user": { ... }
}</div>
  </div>

</div>

<?php pageFooter(); ?>
