<?php
// menu.php
require_once __DIR__ . "/lib/auth.php";
require_login();

$title = "CNC SHARE - Tableau de bord";
include __DIR__ . "/partials/layout_top.php";
?>
<div class="dashboard-wrapper">
  <?php $active = 'dashboard'; include __DIR__ . "/partials/sidebar.php"; ?>

  <main class="content">
    <header class="content-header">
      <h1>Bienvenue, <?= htmlspecialchars($_SESSION['username']) ?></h1>
      <div class="system-status"><span class="pulse"></span> Système Opérationnel</div>
    </header>

    <div class="menu-grid">
      <div class="menu-card">
        <h3>📤 Enregistrer un fichier</h3>
        <p>Enregistrer des fichier.</p>
        <a href="transfert.php" class="btn btn-primary">Accéder</a>
      </div>

      <div class="menu-card">
        <h3>📁 Consultation Local</h3>
        <p>Consulter et trier les fichiers d'usinage.</p>
        <a href="consultation.php" class="btn btn-primary">Consulter</a>
      </div>

      <div class="menu-card">
        <h3>📜 Logs & Historique</h3>
        <p>Afficher les logs de connexion et l'historique.</p>
        <a href="logs.php" class="btn btn-primary">Voir les logs</a>
      </div>

      <div class="menu-card">
        <h3>👥 Gestion Comptes</h3>
        <p>Administration des accès et des droits.</p>
        <a href="gestion_comptes.php" class="btn btn-primary">Gérer</a>
      </div>
    </div>
  </main>
</div>
<?php include __DIR__ . "/partials/layout_bottom.php"; ?>
