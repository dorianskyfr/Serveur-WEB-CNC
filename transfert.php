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

// Stockage local dans le projet
$uploadDir = __DIR__ . "/uploads";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}

$maxBytes = 100 * 1024 * 1024;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $f = $_FILES['file'];

    if ($f['error'] !== UPLOAD_ERR_OK) {
        $err = "Erreur upload (code: " . (int)$f['error'] . ").";
    } elseif ((int)$f['size'] > $maxBytes) {
        $err = "Fichier trop gros (max 100 Mo).";
    } else {
        $original = (string)$f['name'];
        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));

        $allowed = ['nc','gcode','tap','ngc','iso','txt','stl','3mf','zip','pdf'];

        if ($ext === '' || !in_array($ext, $allowed, true)) {
            $err = "Extension non autorisée: " . htmlspecialchars($ext);
        } else {
            $stored = uniqid("job_", true) . "." . $ext;

            // Chemin relatif (BDD) et absolu (disque)
            $relPath  = "uploads/" . $stored;
            $fullPath = $uploadDir . "/" . $stored;

            if (!move_uploaded_file($f['tmp_name'], $fullPath)) {
                $err = "Impossible de sauvegarder le fichier (droits sur uploads/).";
            } else {
                $sha = hash_file('sha256', $fullPath);
                $size = filesize($fullPath);
                if ($size === false) { $size = 0; }

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
                    $relPath,   // stocké RELATIF
                    $ext,
                    (int)$size,
                    $sha
                ]);

                $fileId = (int)$pdo->lastInsertId();

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

    <?php if ($ok): ?><div class="alert success"><?= htmlspecialchars($ok) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert"><?= htmlspecialchars($err) ?></div><?php endif; ?>

    <div class="card">
      <h3>Ajouter un fichier</h3>
      <form method="POST" enctype="multipart/form-data">
        <input type="file" name="file" required>
        <button class="btn btn-primary" type="submit">Enregistrer</button>
      </form>
      <p style="color:var(--text-muted);margin-top:10px;">
        Extensions : nc, gcode, tap, ngc, iso, txt, stl, 3mf, zip, pdf
      </p>
    </div>
  </main>
</div>
<?php include __DIR__ . "/partials/layout_bottom.php"; ?>
