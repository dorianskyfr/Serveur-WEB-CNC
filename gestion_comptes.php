<?php
session_start();
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/lib/auth.php";
require_login();

if (!is_admin()) {
    http_response_code(403);
    exit("Accès interdit (admin uniquement).");
}

$title = "Gestion des comptes";
$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $role = (string)($_POST['role'] ?? 'user');

    if ($username !== '' && $password !== '') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $pdo->prepare("INSERT INTO users (username, password_hash, role, is_active) VALUES (?, ?, ?, 1)")
                ->execute([$username, $hash, $role]);
            $msg = "Utilisateur créé.";
        } catch (Throwable $e) {
            $msg = "Erreur création (username déjà pris ?).";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $id = (int)($_POST['id'] ?? 0);
    $role = (string)($_POST['role'] ?? 'user');
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    $pdo->prepare("UPDATE users SET role = ?, is_active = ? WHERE id = ?")->execute([$role, $isActive, $id]);
    $msg = "Utilisateur mis à jour.";
}

$users = $pdo->query("SELECT id, username, role, is_active, created_at, last_login_at FROM users ORDER BY id DESC")->fetchAll();

include __DIR__ . "/partials/layout_top.php";
?>
<div class="dashboard-wrapper">
  <?php $active='comptes'; include __DIR__ . "/partials/sidebar.php"; ?>
  <main class="content">
    <h1>Gestion des comptes</h1>
    <?php if ($msg): ?><div class="alert success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

    <div class="card">
      <h3>Créer un utilisateur</h3>
      <form method="POST">
        <input type="text" name="username" placeholder="username" required>
        <input type="password" name="password" placeholder="password" required>
        <select name="role">
          <option value="user">user</option>
          <option value="admin">admin</option>
        </select>
        <button class="btn btn-primary" type="submit" name="create_user">Créer</button>
      </form>
    </div>

    <div class="card">
      <h3>Utilisateurs</h3>
      <table class="table">
        <thead><tr><th>ID</th><th>Username</th><th>Role</th><th>Actif</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach ($users as $u): ?>
          <tr>
            <form method="POST">
              <td><?= (int)$u['id'] ?><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"></td>
              <td><?= htmlspecialchars($u['username']) ?></td>
              <td>
                <select name="role">
                  <option value="user" <?= $u['role']==='user'?'selected':'' ?>>user</option>
                  <option value="admin" <?= $u['role']==='admin'?'selected':'' ?>>admin</option>
                </select>
              </td>
              <td><input type="checkbox" name="is_active" <?= ((int)$u['is_active']===1)?'checked':'' ?>></td>
              <td><button class="btn btn-primary" type="submit" name="update_user">Mettre à jour</button></td>
            </form>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
<?php include __DIR__ . "/partials/layout_bottom.php"; ?>
