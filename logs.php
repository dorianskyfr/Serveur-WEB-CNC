<?php
declare(strict_types=1);

session_start();
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/lib/auth.php";

require_login();

$title = "Logs & Historique";
$msg = "";
$err = "";

// CSRF token (simple)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Récupère la liste des fichiers + dépôts pour les dropdowns
$filesList = $pdo->query("
  SELECT id, original_name
  FROM files
  ORDER BY id DESC
  LIMIT 200
")->fetchAll();

$depotsList = $pdo->query("
  SELECT id, name
  FROM depots
  ORDER BY name ASC
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_print'])) {
    $csrf = (string)($_POST['csrf_token'] ?? '');
    if (empty($csrf) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
        $err = "CSRF invalide.";
    } else {
        $fileId = (int)($_POST['file_id'] ?? 0);
        $depotIdRaw = (string)($_POST['depot_id'] ?? '');
        $status = (string)($_POST['status'] ?? 'done');

        $allowedStatus = ['done', 'failed', 'cancelled'];
        if (!in_array($status, $allowedStatus, true)) {
            $status = 'done';
        }

        // depot_id peut être vide => NULL
        $depotId = null;
        if ($depotIdRaw !== '') {
            $tmp = (int)$depotIdRaw;
            if ($tmp > 0) $depotId = $tmp;
        }

        if ($fileId <= 0) {
            $err = "Choisis un fichier.";
        } else {
            // Vérifie que le fichier existe
            $stmt = $pdo->prepare("SELECT id FROM files WHERE id = ? LIMIT 1");
            $stmt->execute([$fileId]);
            $exists = $stmt->fetch();

            if (!$exists) {
                $err = "Fichier introuvable en base.";
            } else {
                $stmt = $pdo->prepare("
                  INSERT INTO print_history (file_id, printed_by, depot_id, status, printed_at)
                  VALUES (?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $fileId,
                    (int)$_SESSION['user_id'],
                    $depotId,
                    $status
                ]);

                $msg = "Impression ajoutée.";
            }
        }
    }
}

// Logs connexions
$auth = $pdo->query("
  SELECT a.occurred_at, a.success, COALESCE(u.username, a.username_try) AS who, a.ip_address
  FROM auth_logs a
  LEFT JOIN users u ON u.id = a.user_id
  ORDER BY a.id DESC
  LIMIT 50
")->fetchAll();

// Historique impressions
$prints = $pdo->query("
  SELECT
    p.printed_at,
    p.status,
    f.original_name,
    COALESCE(u.username,'?') AS printed_by,
    d.name AS depot_name
  FROM print_history p
  JOIN files f ON f.id = p.file_id
  LEFT JOIN users u ON u.id = p.printed_by
  LEFT JOIN depots d ON d.id = p.depot_id
  ORDER BY p.id DESC
  LIMIT 50
")->fetchAll();

include __DIR__ . "/partials/layout_top.php";
?>
<div class="dashboard-wrapper">
  <?php $active='logs'; include __DIR__ . "/partials/sidebar.php"; ?>

  <main class="content">
    <h1>Logs & Historique</h1>

    <?php if ($msg): ?>
      <div class="alert success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <?php if ($err): ?>
      <div class="alert"><?= htmlspecialchars($err) ?></div>
    <?php endif; ?>

    <div class="card">
      <h3>Ajouter une impression</h3>

      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <label>Fichier</label>
        <select name="file_id" required>
          <option value="">-- Choisir un fichier --</option>
          <?php foreach ($filesList as $f): ?>
            <option value="<?= (int)$f['id'] ?>">
              <?= (int)$f['id'] ?> — <?= htmlspecialchars($f['original_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <label>Dépôt (optionnel)</label>
        <select name="depot_id">
          <option value="">-- Aucun --</option>
          <?php foreach ($depotsList as $d): ?>
            <option value="<?= (int)$d['id'] ?>">
              <?= htmlspecialchars($d['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <label>Statut</label>
        <select name="status" required>
          <option value="done">done</option>
          <option value="failed">failed</option>
          <option value="cancelled">cancelled</option>
        </select>

        <button class="btn btn-primary" type="submit" name="add_print">Ajouter</button>
      </form>
    </div>

    <div class="card">
      <h3>Logs de connexion</h3>
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

    <div class="card">
      <h3>Historique impressions</h3>
      <ul>
        <?php foreach ($prints as $p): ?>
          <li>
            <?= htmlspecialchars($p['printed_at']) ?>
            — <?= htmlspecialchars($p['status']) ?>
            — <?= htmlspecialchars($p['original_name']) ?>
            — <?= htmlspecialchars($p['printed_by']) ?>
            <?= $p['depot_name'] ? (' — ' . htmlspecialchars($p['depot_name'])) : '' ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

  </main>
</div>
<?php include __DIR__ . "/partials/layout_bottom.php"; ?>
