<?php
require_once 'helpers.php';
header_layout('Home', 'home');
?>

<div class="page-title">FlashPoint cURL App</div>
<div class="page-sub">Consuming the FlashPoint API using PHP cURL — Nirvana Vella</div>

<div class="card card-orange">
  <div class="card-title">API Endpoints</div>
  <table style="width:100%;border-collapse:collapse;font-size:13px">
    <thead>
      <tr style="background:#f8f9fc">
        <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid var(--border)">Method</th>
        <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid var(--border)">Endpoint</th>
        <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid var(--border)">Description</th>
        <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid var(--border)">Auth</th>
      </tr>
    </thead>
    <tbody>
      <?php $rows = [
        ['GET','articles.php?action=list','Get all articles','No','get'],
        ['GET','articles.php?action=list&status=pending','Get pending articles','No','get'],
        ['GET','articles.php?action=list&category=news','Get by category','No','get'],
        ['POST','articles.php?action=create','Create article','Journalist','post'],
        ['PATCH','articles.php?action=verify','Verify article','Verifier','patch'],
        ['GET','articles.php?action=comments&article_id=1','Get comments','No','get'],
        ['POST','articles.php?action=comments','Add comment','Any user','post'],
        ['GET','bookmarks.php?action=list','Get my bookmarks','Required','get'],
        ['POST','bookmarks.php?action=add','Add bookmark','Required','post'],
        ['DELETE','bookmarks.php?action=remove','Remove bookmark','Required','delete'],
      ];
      foreach($rows as $r): ?>
      <tr style="border-bottom:1px solid var(--border)">
        <td style="padding:10px 14px"><span class="method-badge <?= $r[4] ?>"><?= $r[0] ?></span></td>
        <td style="padding:10px 14px;font-family:'Courier New',monospace;font-size:11px;color:var(--navy)"><?= $r[1] ?></td>
        <td style="padding:10px 14px;font-size:13px;color:var(--muted)"><?= $r[2] ?></td>
        <td style="padding:10px 14px;font-size:12px;color:var(--muted)"><?= $r[3] ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="card">
  <div class="card-title">Base URL</div>
  <div style="font-family:'Courier New',monospace;font-size:13px;background:#f8f9fc;padding:12px 16px;border-radius:12px;color:var(--navy)"><?= API_BASE ?></div>
</div>

<?php footer_layout(); ?>
