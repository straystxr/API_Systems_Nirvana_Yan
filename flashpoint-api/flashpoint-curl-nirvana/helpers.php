<?php
session_start();

define('API_BASE', 'http://localhost:8888/API_Systems_Nirvana_Yan/flashpoint-api/core');
define('AUTH_BASE', 'http://localhost:8888/API_Systems_Nirvana_Yan/flashpoint-api/api/auth/auth.php');

function apiRequest(string $method, string $url, array $data = [], string $token = ''): array {
    $ch = curl_init($url);
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
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error    = curl_error($ch);
    curl_close($ch);

    if ($error) return ['data' => ['error' => 'cURL Error: ' . $error], 'code' => 0];
    return ['data' => json_decode($response, true), 'code' => $code];
}

function header_layout(string $title, string $active = '') {
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FlashPoint — <?= htmlspecialchars($title) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;900&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{
  --navy:#1a2d5a;
  --navy2:#0f1e3d;
  --orange:#f5a623;
  --orange2:#e09010;
  --bg:#f0f2f8;
  --white:#ffffff;
  --muted:#6b7a99;
  --border:#e2e8f0;
  --success:#1a7a4a;
  --danger:#c0392b;
}
body{font-family:'Nunito',sans-serif;background:var(--bg);min-height:100vh;color:var(--navy)}

/* TOPBAR */
.topbar{background:var(--navy2);padding:0 24px;height:60px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100}
.logo{display:flex;align-items:center;gap:10px}
.logo-icon{font-size:28px;font-weight:900;color:var(--white);letter-spacing:-1px}
.logo-icon span{color:var(--orange)}
.logo-pin{width:12px;height:12px;background:var(--orange);border-radius:50% 50% 50% 0;transform:rotate(-45deg);margin-left:-2px;margin-bottom:8px}
.logo-sub{font-size:10px;color:rgba(255,255,255,0.4);letter-spacing:1px;margin-top:-4px}
.nav{display:flex;gap:4px}
.nav a{padding:7px 14px;border-radius:20px;font-size:12px;font-weight:700;color:rgba(255,255,255,0.6);text-decoration:none;transition:all 0.15s}
.nav a:hover,.nav a.active{background:var(--orange);color:#fff}
.nav-user{font-size:12px;color:rgba(255,255,255,0.5);display:flex;align-items:center;gap:10px}
.btn-logout{background:transparent;border:1px solid rgba(255,255,255,0.2);color:rgba(255,255,255,0.5);border-radius:12px;padding:5px 12px;font-size:11px;font-weight:700;cursor:pointer;text-decoration:none;transition:all 0.15s;font-family:'Nunito',sans-serif}
.btn-logout:hover{background:#c0392b;border-color:#c0392b;color:#fff}

/* CONTAINER */
.container{max-width:900px;margin:0 auto;padding:28px 20px}
.page-title{font-size:22px;font-weight:900;color:var(--navy);margin-bottom:4px}
.page-sub{font-size:13px;color:var(--muted);margin-bottom:24px;display:flex;align-items:center;gap:6px}
.method-badge{display:inline-block;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:700}
.get{background:#e8f5e9;color:#2e7d32}
.post{background:#fff3e0;color:#e65100}
.patch{background:#e3f2fd;color:#1565c0}
.delete{background:#fdecea;color:#c0392b}

/* CARDS */
.card{background:var(--white);border-radius:16px;border:2px solid var(--border);padding:22px;margin-bottom:20px}
.card-orange{border-color:var(--orange)}
.card-title{font-size:14px;font-weight:700;color:var(--navy);margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:8px}
.two-col{display:grid;grid-template-columns:1fr 1fr;gap:20px}

/* FORM */
.form-group{margin-bottom:12px}
.form-label{display:block;font-size:11px;font-weight:700;color:var(--navy);margin-bottom:5px;text-transform:uppercase;letter-spacing:0.3px}
.form-input{width:100%;padding:10px 16px;border:2px solid var(--border);border-radius:50px;font-size:13px;font-family:'Nunito',sans-serif;color:var(--navy);outline:none;transition:border-color 0.2s;background:#fff}
.form-input:focus{border-color:var(--orange)}
textarea.form-input{border-radius:12px;resize:vertical;min-height:80px}
select.form-input{border-radius:50px;cursor:pointer}
.btn{padding:11px 24px;border-radius:50px;font-size:13px;font-weight:700;border:none;cursor:pointer;font-family:'Nunito',sans-serif;transition:all 0.2s;display:inline-flex;align-items:center;gap:6px}
.btn-navy{background:var(--navy);color:#fff}
.btn-navy:hover{background:var(--navy2)}
.btn-orange{background:var(--orange);color:#fff}
.btn-orange:hover{background:var(--orange2)}
.btn-danger{background:#fdecea;color:var(--danger);border:1px solid #f5c6c4}
.btn-danger:hover{background:var(--danger);color:#fff}
.btn-block{width:100%;justify-content:center}

/* RESULT */
.result-box{background:#f8f9fc;border:1px solid var(--border);border-radius:12px;padding:14px;font-family:'Courier New',monospace;font-size:12px;white-space:pre-wrap;overflow-x:auto;margin-top:12px;line-height:1.7;color:var(--navy)}
.code-label{font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px;margin-top:12px}
.http-code{display:inline-block;padding:3px 10px;border-radius:8px;font-size:12px;font-weight:700;margin-left:8px}
.code-2xx{background:#e8f5e9;color:#2e7d32}
.code-4xx,.code-5xx{background:#fdecea;color:#c0392b}

/* ALERTS */
.alert{padding:12px 16px;border-radius:12px;font-size:13px;font-weight:600;margin-bottom:16px}
.alert-success{background:#e8f5e9;color:var(--success);border:1px solid #b7e4cb}
.alert-error{background:#fdecea;color:var(--danger);border:1px solid #f5c6c4}

/* ARTICLE CARDS */
.article-card{background:#fff;border-radius:14px;border:2px solid var(--border);padding:16px;margin-bottom:12px;transition:border-color 0.15s}
.article-card:hover{border-color:var(--orange)}
.article-title{font-size:15px;font-weight:700;color:var(--navy);margin-bottom:6px}
.article-meta{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:8px}
.article-body{font-size:13px;color:var(--muted);line-height:1.6}
.badge{display:inline-block;padding:2px 10px;border-radius:10px;font-size:11px;font-weight:700}
.badge-navy{background:var(--navy);color:#fff}
.badge-orange{background:var(--orange);color:#fff}
.badge-green{background:#e8f5e9;color:#2e7d32}
.badge-red{background:#fdecea;color:#c0392b}
.badge-blue{background:#e3f2fd;color:#1565c0}
.empty{text-align:center;padding:40px;color:var(--muted);font-size:14px}
</style>
</head>
<body>
<div class="topbar">
  <div class="logo">
    <div>
      <div class="logo-icon">FLASH<span>●</span></div>
      <div class="logo-sub">CURL APP</div>
    </div>
  </div>
  <div class="nav">
    <a href="index.php" class="<?= $active==='home'?'active':'' ?>">Home</a>
    <a href="login.php" class="<?= $active==='login'?'active':'' ?>">Login</a>
    <a href="articles.php" class="<?= $active==='articles'?'active':'' ?>">Articles</a>
    <a href="bookmarks.php" class="<?= $active==='bookmarks'?'active':'' ?>">Bookmarks</a>
    <a href="comments.php" class="<?= $active==='comments'?'active':'' ?>">Comments</a>
  </div>
  <div class="nav-user">
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

function footer_layout() { ?>
</div>
</body>
</html>
<?php }

function code_badge(int $code): string {
    $class = $code >= 200 && $code < 300 ? 'code-2xx' : 'code-4xx';
    return "<span class='http-code $class'>$code</span>";
}
