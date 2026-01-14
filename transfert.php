<?php
declare(strict_types=1);

session_start();
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/lib/auth.php";

require_login();

$title = "Enregistrer un fichier";

$ok = "";
$err = "";

// Dossier d'upload
$uploadDir = __DIR__ . "/uploads";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $f = $_FILES['file'];

    if ($f['error'] !== UPLOAD_ERR_OK) {
        $err = "Erreur upload (code: " . (int)$f['error'] . ").";
    } else {
        $original = (string)$f['name'];
        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));

        // Extensions autorisées (change si besoin)
        $allowed = ['nc', 'gcode', 'txt', 'tap', 'ngc', 'iso', 'pdf', 'zip'];

        if ($ext === '' || !in_array($ext, $allowed, true)) {
            $err = "Extension non autorisée.";
        } else {
            $stored = uniqid("job_", true) . "." . $ext;
            $relPath = "uploads/" . $stored;
            $fullPath = $uploadDir . "/" . $stored;

            if (!move_uploaded_file($f['tmp_name'], $fullPath)) {
                $err = "Impossible de sauvegarder le fichier (droits uploads ?).";
            } else {
                $sha = hash_file('sha256', $fullPath);
                $size = filesize($fullPath);
                if ($size === false) { $size = 0; }

                // 1) Enregistrer le fichier
                $stmt = $pdo->prepare("
                    INSERT INTO files
                      (owner_user_id, original_name, stored_name, storage_path, file_ext, size_bytes, sha256)
                    VALUES
                      (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    (int)$_SESSION['user_id'],
                    $original,
                    $stored,
                    $relPath,
                    $ext,
                    (int)$size,
                    $sha
                ]);

                $fileId = (int)$pdo->lastInsertId();

                // 2) Trace de l'action (stocké, pas CNC)
                $stmt = $pdo->prepare("
                    INSERT INTO file_transfers
                      (file_id, requested_by, source, destination, status, started_at, finished_at)
                    VALUES
                      (?, ?, 'designer', 'db', 'stored', NOW(), NOW())
                ");
                $stmt->execute([$fileId, (int)$_SESSION['user_id']]);

                $ok = "Fichier enregistré : " . $original;
            }
        }
    }
}

include __DIR__ . "/partials/layout_top.php";
?>
<div class="dashboard-wrapper">
  <?php $active = 'transfert'; include __DIR__ . "/partials/sidebar.php"; ?>

  <main class="content">
    <h1>Enregistrer un fichier</h1>

    <?php if ($ok): ?>
      <div class="alert success"><?= htmlspecialchars($ok) ?></div>
    <?php endif; ?>

    <?php if ($err): ?>
      <div class="alert"><?= htmlspecialchars($err) ?></div>
    <?php endif; ?>

    <div class="card">
      <h3>Ajouter un fichier d'usinage</h3>
      <form method="POST" enctype="multipart/form-data">
        <input type="file" name="file" required>
        <button class="btn btn-primary" type="submit">Enregistrer</button>
      </form>
      <p style="color:#aaa;margin-top:10px;">
        Extensions autorisées : nc, gcode, txt, tap, ngc, iso, pdf, zip
      </p>
    </div>

    <div class="card">
      <h3>Derniers enregistrements</h3>
      <?php
      $rows = $pdo->query("
          SELECT t.created_at, t.status, f.original_name
          FROM file_transfers t
          JOIN files f ON f.id = t.file_id
          ORDER BY t.id DESC
          LIMIT 10
      ")->fetchAll();
      ?>
      <ul>
        <?php foreach ($rows as $r): ?>
          <li><?= htmlspecialchars($r['created_at']) ?> — <?= htmlspecialchars($r['status']) ?> — <?= htmlspecialchars($r['original_name']) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </main>
</div>
<?php include __DIR__ . "/partials/layout_bottom.php"; ?>
