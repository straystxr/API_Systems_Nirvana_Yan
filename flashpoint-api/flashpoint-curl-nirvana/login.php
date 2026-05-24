<?php
require_once 'helpers.php';

$result = null;
$error  = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = apiRequest('POST', AUTH_BASE . '?action=login', [
        'email'    => $_POST['email'] ?? '',
        'password' => $_POST['password'] ?? '',
    ]);

    if ($result['code'] === 200 && isset($result['data']['access_token'])) {
        $_SESSION['token'] = $result['data']['access_token'];
        $_SESSION['user']  = $result['data']['user'];
        header('Location: articles.php');
        exit;
    } else {
        $error = $result['data']['error'] ?? 'Login failed';
    }
}

header_layout('Login', 'login');
?>

<div class="page-title">Login</div>
<div class="page-sub"><span class="method-badge post">POST</span> auth/auth.php?action=login</div>

<?php if ($error): ?>
  <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="two-col">
  <div class="card card-orange">
    <div class="card-title">Login Form</div>
    <form method="POST">
      <div class="form-group">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-input" placeholder="nirvana@flashpoint.mt" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-input" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn btn-navy btn-block">Login via cURL</button>
    </form>
  </div>

  <div class="card">
    <div class="card-title">How it works</div>
    <p style="font-size:13px;color:var(--muted);line-height:1.8;margin-bottom:12px">
      PHP sends a cURL POST request to the FlashPoint login endpoint. On success the Bearer token is stored in a PHP session and used automatically for all authenticated requests.
    </p>
    <div class="code-label">cURL Request</div>
    <div class="result-box">POST <?= AUTH_BASE ?>?action=login
Headers: Content-Type: application/json
Body: {
  "email": "...",
  "password": "..."
}

Response:
{
  "access_token": "eyJ0eXAi...",
  "token_type": "Bearer",
  "expires_in": 3600
}</div>
  </div>
</div>

<?php footer_layout(); ?>
