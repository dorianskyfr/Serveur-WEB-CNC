<?php
// register.php
$title = "CNC Share - Inscription";
include __DIR__ . "/partials/layout_top.php";
?>
<div class="container">
  <header>
    <div class="logo">CNC<span>SHARE</span></div>
    <p>Création de compte</p>
  </header>

  <div class="auth-panel">
    <div class="tabs">
      <a href="index.php?action=login" class="<?= ($page ?? '')==='login' ? 'active' : '' ?>">Connexion</a>
      <a href="index.php?action=register" class="<?= ($page ?? '')==='register' ? 'active' : '' ?>">S'enregistrer</a>
    </div>

    <?php if (!empty($message)): ?>
      <div class="alert"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php?action=register">
      <div class="form-group">
        <label>Identifiant</label>
        <input type="text" name="username" required>
      </div>
      <div class="form-group">
        <label>Clé de sécurité</label>
        <input type="password" name="password" required>
      </div>
      <div class="form-group">
        <label>Confirmer la clé</label>
        <input type="password" name="password2" required>
      </div>
      <button type="submit" name="register" class="btn btn-primary">Créer le compte</button>
    </form>
  </div>
</div>
<?php include __DIR__ . "/partials/layout_bottom.php"; ?>
