<?php
declare(strict_types=1);

session_start();
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/lib/auth.php";

require_login();

$title = "Logs de connexion";

$auth = $pdo->query("
  SELECT a.occurred_at, a.success, COALESCE(u.username, a.username_try) AS who, a.ip_address
  FROM auth_logs a
  LEFT JOIN users u ON u.id = a.user_id
  ORDER BY a.id DESC
  LIMIT 100
")->fetchAll();

include __DIR__ . "/partials/layout_top.php";
?>
<div class="dashboard-wrapper">
  <?php $active='logs'; include __DIR__ . "/partials/sidebar.php"; ?>

  <main class="content">
    <h1>Logs de connexion</h1>

    <div class="card">
      <ul>
        <?php foreach ($auth as $a): ?>
          <li>
            <?= htmlspecialchars($a['occurred_at']) ?>
            — <?= ((int)$a['success'] === 1) ? 'OK' : 'KO' ?>
            — <?= htmlspecialchars((string)$a['who']) ?>
            — <?= htmlspecialchars((string)($a['ip_address'] ?? '')) ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </main>
</div>
<?php include __DIR__ . "/partials/layout_bottom.php"; ?>
