<?php
session_start();

define('API_BASE', 'http://localhost/API_Systems_Nirvana_Vella/flashpoint-api/api');

// ─── CURL HELPER ─────────────────────────────────────────────────────────────
function apiRequest(string $method, string $endpoint, array $data = [], string $token = ''): array {
    $url = API_BASE . $endpoint;
    $ch  = curl_init($url);

    $headers = ['Content-Type: application/json', 'Accept: application/json'];
    if ($token) $headers[] = 'Authorization: Bearer ' . $token;

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } elseif ($method === 'PATCH') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error    = curl_error($ch);
    curl_close($ch);

    if ($error) return ['error' => 'cURL Error: ' . $error, 'code' => 0];

    $decoded = json_decode($response, true);
    return ['data' => $decoded, 'code' => $httpCode, 'raw' => $response];
}

// ─── LAYOUT HELPERS ──────────────────────────────────────────────────────────
function pageHeader(string $title, string $activePage = '') { ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FlashPoint — <?= htmlspecialchars($title) ?></title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Segoe UI',Arial,sans-serif;background:#f0f0f0;color:#0d1b3e;min-height:100vh}
  .topbar{background:#0d1b3e;padding:0 24px;height:56px;display:flex;align-items:center;justify-content:space-between}
  .topbar-logo{display:flex;align-items:center;gap:10px}
  .logo-box{width:32px;height:32px;background:#f5a623;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:900;color:#fff}
  .logo-text{font-size:16px;font-weight:900;color:#fff;letter-spacing:0.5px}
  .logo-text span{color:#f5a623}
  .topbar-nav{display:flex;gap:6px}
  .nav-link{padding:6px 14px;border-radius:20px;font-size:12px;font-weight:600;color:rgba(255,255,255,0.6);text-decoration:none;transition:all 0.15s}
  .nav-link:hover,.nav-link.active{background:#f5a623;color:#fff}
  .topbar-user{font-size:12px;color:rgba(255,255,255,0.5);display:flex;align-items:center;gap:10px}
  .btn-logout{background:transparent;border:1px solid rgba(255,255,255,0.3);color:rgba(255,255,255,0.6);border-radius:12px;padding:4px 12px;font-size:11px;cursor:pointer;text-decoration:none}
  .btn-logout:hover{background:#C0392B;border-color:#C0392B;color:#fff}
  .container{max-width:860px;margin:0 auto;padding:28px 20px}
  .page-title{font-size:22px;font-weight:900;color:#0d1b3e;margin-bottom:6px}
  .page-sub{font-size:13px;color:#4a5068;margin-bottom:24px}
  .card{background:#fff;border-radius:16px;border:2px solid #E2E8F0;padding:24px;margin-bottom:20px}
  .card-title{font-size:14px;font-weight:700;color:#0d1b3e;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid #E2E8F0}
  .form-group{margin-bottom:14px}
  .form-label{display:block;font-size:11px;font-weight:700;color:#0d1b3e;margin-bottom:5px;text-transform:uppercase;letter-spacing:0.3px}
  .form-input{width:100%;padding:10px 16px;border:2px solid #E2E8F0;border-radius:50px;font-size:13px;font-family:inherit;color:#0d1b3e;outline:none;transition:border-color 0.2s}
  .form-input:focus{border-color:#f5a623}
  .btn{padding:11px 24px;border-radius:50px;font-size:13px;font-weight:700;border:none;cursor:pointer;font-family:inherit;transition:all 0.2s}
  .btn-primary{background:#0d1b3e;color:#fff}
  .btn-primary:hover{background:#1a2d5a}
  .btn-orange{background:#f5a623;color:#fff}
  .btn-orange:hover{background:#e09010}
  .btn-danger{background:#C0392B;color:#fff}
  .result-box{background:#f8f9fc;border:1px solid #E2E8F0;border-radius:12px;padding:16px;font-family:'Courier New',monospace;font-size:12px;color:#0d1b3e;white-space:pre-wrap;overflow-x:auto;margin-top:12px;line-height:1.6}
  .badge{display:inline-block;padding:3px 10px;border-radius:10px;font-size:11px;font-weight:700}
  .badge-200,.badge-201{background:#E8F8F0;color:#1A7A4A}
  .badge-400,.badge-401,.badge-403,.badge-404,.badge-409,.badge-500{background:#FDECEA;color:#C0392B}
  .alert{padding:12px 16px;border-radius:10px;font-size:13px;font-weight:600;margin-bottom:16px}
  .alert-success{background:#E8F8F0;color:#1A7A4A;border:1px solid #b7e4cb}
  .alert-error{background:#FDECEA;color:#C0392B;border:1px solid #f5c6c4}
  .data-table{width:100%;border-collapse:collapse;font-size:13px}
  .data-table th{padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:#4a5068;text-transform:uppercase;letter-spacing:0.5px;background:#f8f9fc;border-bottom:1px solid #E2E8F0}
  .data-table td{padding:12px 14px;border-bottom:1px solid #E2E8F0;color:#0d1b3e}
  .data-table tr:last-child td{border-bottom:none}
  .feature-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:12px}
  .feature-item{display:flex;align-items:center;gap:8px;font-size:13px;padding:8px 12px;border-radius:8px;background:#f8f9fc}
  .tick{color:#1A7A4A;font-weight:700}
  .cross{color:#C0392B;font-weight:700}
  .code-tag{display:inline-block;background:#E8F0FE;color:#1565C0;font-family:'Courier New',monospace;font-size:11px;padding:2px 8px;border-radius:4px}
</style>
</head>
<body>
<div class="topbar">
  <div class="topbar-logo">
    <div class="logo-box">F</div>
    <div class="logo-text">Flash<span>Point</span> <span style="font-size:10px;opacity:0.5;font-weight:400">cURL App</span></div>
  </div>
  <div class="topbar-nav">
    <a href="index.php" class="nav-link <?= $activePage==='home'?'active':'' ?>">Home</a>
    <a href="register.php" class="nav-link <?= $activePage==='register'?'active':'' ?>">Register</a>
    <a href="login.php" class="nav-link <?= $activePage==='login'?'active':'' ?>">Login</a>
    <?php if(!empty($_SESSION['token'])): ?>
    <a href="profile.php" class="nav-link <?= $activePage==='profile'?'active':'' ?>">Profile</a>
    <a href="membership.php" class="nav-link <?= $activePage==='membership'?'active':'' ?>">Membership</a>
    <?php endif; ?>
  </div>
  <div class="topbar-user">
    <?php if(!empty($_SESSION['user'])): ?>
      Hi, <?= htmlspecialchars($_SESSION['user']['name']) ?>
      <a href="logout.php" class="btn-logout">Log out</a>
    <?php else: ?>
      <a href="login.php" class="btn-logout">Login</a>
    <?php endif; ?>
  </div>
</div>
<div class="container">
<?php }

function pageFooter() { ?>
</div>
</body>
</html>
<?php }
