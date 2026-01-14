<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/lib/auth.php";
require_login();

$title = "Consultation Fichiers";

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$msg = (string)($_GET['msg'] ?? '');
$q = trim((string)($_GET['q'] ?? ''));

$sql = "SELECT id, original_name, file_ext, size_bytes, created_at, owner_user_id FROM files";
$params = [];
if ($q !== '') {
    $sql .= " WHERE original_name LIKE ?";
    $params[] = "%" . $q . "%";
}
$sql .= " ORDER BY id DESC LIMIT 200";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$files = $stmt->fetchAll();

include __DIR__ . "/partials/layout_top.php";
?>
<div class="dashboard-wrapper">
  <?php $active='consultation'; include __DIR__ . "/partials/sidebar.php"; ?>
  <main class="content">
    <h1>Consultation des fichiers</h1>

    <?php if ($msg): ?>
      <div class="alert success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <form method="GET" class="searchbar">
      <input type="text" name="q" placeholder="Rechercher..." value="<?= htmlspecialchars($q) ?>">
      <button class="btn btn-primary" type="submit">Chercher</button>
    </form>

    <div class="card">
      <table class="table">
        <thead>
          <tr>
            <th>Nom</th><th>Ext</th><th>Taille</th><th>Date</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($files as $f): ?>
          <tr>
            <td><?= htmlspecialchars($f['original_name']) ?></td>
            <td><?= htmlspecialchars($f['file_ext'] ?? '') ?></td>
            <td><?= htmlspecialchars((string)$f['size_bytes']) ?></td>
            <td><?= htmlspecialchars($f['created_at']) ?></td>
            <td style="display:flex;gap:10px;align-items:center;">
              <a class="btn btn-primary" href="download.php?id=<?= (int)$f['id'] ?>">Télécharger</a>

              <?php
                $ownerId = (int)($f['owner_user_id'] ?? 0);
                $canDelete = is_admin() || $ownerId === (int)$_SESSION['user_id'];
              ?>
              <?php if ($canDelete): ?>
                <form method="POST" action="delete_file.php" onsubmit="return confirm('Supprimer ce fichier ?');" style="margin:0;">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                  <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
                  <button class="btn btn-danger" type="submit">Supprimer</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
<?php include __DIR__ . "/partials/layout_bottom.php"; ?>
