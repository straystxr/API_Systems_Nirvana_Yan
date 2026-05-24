<?php
require_once 'helpers.php';
pageHeader('Home', 'home');
?>

<div class="page-title">FlashPoint cURL Application</div>
<div class="page-sub">A PHP web app consuming the FlashPoint REST API using cURL — Part 3 of API Systems Assignment</div>

<div class="card">
  <div class="card-title">About This App</div>
  <p style="font-size:13px;color:#4a5068;line-height:1.8;margin-bottom:16px">
    This application demonstrates consuming the FlashPoint RESTful API using PHP cURL.
    It covers all three HTTP request types required by the assignment.
  </p>
  <table class="data-table">
    <thead>
      <tr><th>Page</th><th>Endpoint</th><th>Method</th><th>Description</th></tr>
    </thead>
    <tbody>
      <tr>
        <td><a href="register.php" style="color:#f5a623;font-weight:600">Register</a></td>
        <td><span class="code-tag">/api/auth/register</span></td>
        <td><span class="badge badge-201">POST</span></td>
        <td>Create a new user account</td>
      </tr>
      <tr>
        <td><a href="login.php" style="color:#f5a623;font-weight:600">Login</a></td>
        <td><span class="code-tag">/api/auth/login</span></td>
        <td><span class="badge badge-201">POST</span></td>
        <td>Authenticate and get Bearer token</td>
      </tr>
      <tr>
        <td><a href="profile.php" style="color:#f5a623;font-weight:600">Profile</a></td>
        <td><span class="code-tag">/api/users/{id}</span></td>
        <td><span class="badge badge-200">GET</span></td>
        <td>View user profile data</td>
      </tr>
      <tr>
        <td><a href="membership.php" style="color:#f5a623;font-weight:600">Membership</a></td>
        <td><span class="code-tag">/api/users/{id}/membership</span></td>
        <td><span class="badge badge-200">GET</span></td>
        <td>View current membership tier and features</td>
      </tr>
      <tr>
        <td><a href="membership.php" style="color:#f5a623;font-weight:600">Upgrade</a></td>
        <td><span class="code-tag">/api/users/{id}/membership</span></td>
        <td><span class="badge badge-200">PATCH</span></td>
        <td>Upgrade or change membership plan</td>
      </tr>
    </tbody>
  </table>
</div>

<div class="card">
  <div class="card-title">API Base URL</div>
  <span class="code-tag" style="font-size:13px"><?= API_BASE ?></span>
</div>

<?php pageFooter(); ?>
