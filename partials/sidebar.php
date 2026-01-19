<?php
// partials/sidebar.php
$active = $active ?? '';
?>
<nav class="sidebar">
  <div class="sidebar-top">
    <div class="logo-small">CNC <span>SHARE</span></div>

    <button id="theme-toggle-btn" type="button" class="theme-btn">
      <span id="theme-toggle-label">Mode clair</span>
    </button>
  </div>

  <ul>
    <li><a href="menu.php" class="<?= ($active === 'dashboard') ? 'active' : '' ?>">Tableau de bord</a></li>
    <li><a href="transfert.php" class="<?= ($active === 'transfert') ? 'active' : '' ?>">Enregistrer fichier</a></li>
    <li><a href="consultation.php" class="<?= ($active === 'consultation') ? 'active' : '' ?>">Consultation fichiers</a></li>
    <li><a href="logs.php" class="<?= ($active === 'logs') ? 'active' : '' ?>">Logs de connexion</a></li>
    <li><a href="gestioncomptes.php" class="<?= ($active === 'comptes') ? 'active' : '' ?>">Gestion des comptes</a></li>
    <li><a href="index.php?logout=true" class="logout">Quitter la session</a></li>
  </ul>
</nav>
