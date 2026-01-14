<?php
// partials/sidebar.php
$active = $active ?? '';
?>
<nav class="sidebar">
  <div class="logo-small">CNC<span>SHARE</span></div>
  <ul>
    <li><a href="index.php" class="<?= $active==='dashboard'?'active':'' ?>">Tableau de bord</a></li>
    <li><a href="transfert.php" class="<?= $active==='transfert'?'active':'' ?>">enregistrer fichier</a></li>
    <li><a href="consultation.php" class="<?= $active==='consultation'?'active':'' ?>">Consultation Fichiers</a></li>
    <li><a href="logs.php" class="<?= $active==='logs'?'active':'' ?>">Logs de connexion</a></li>
    <li><a href="gestion_comptes.php" class="<?= $active==='comptes'?'active':'' ?>">Gestion des Comptes</a></li>
    <li><a href="index.php?logout=true" class="logout">Quitter la session</a></li>
  </ul>
</nav>
