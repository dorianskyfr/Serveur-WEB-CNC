<?php
// partials/layout_top.php
?>
<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($title ?? "CNC SHARE") ?></title>
  <link rel="stylesheet" href="accueil.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
  <script>
    // Thème stocké (dark/light) ou préférence système
    (function() {
      const stored = localStorage.getItem('theme');
      const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
      const theme = stored || (prefersDark ? 'dark' : 'light');
      document.documentElement.setAttribute('data-theme', theme);
    })();
  </script>

  <div class="background-grid"></div>

  <div class="theme-toggle">
    <button id="theme-toggle-btn" type="button">
      <span id="theme-toggle-label">Mode clair</span>
    </button>
  </div>

  <script>
    (function(){
      const btn = document.getElementById('theme-toggle-btn');
      const label = document.getElementById('theme-toggle-label');

      function syncLabel() {
        const current = document.documentElement.getAttribute('data-theme') || 'dark';
        label.textContent = current === 'dark' ? 'Mode clair' : 'Mode sombre';
      }

      syncLabel();

      btn.addEventListener('click', function() {
        const current = document.documentElement.getAttribute('data-theme') || 'dark';
        const next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('theme', next);
        syncLabel();
      });
    })();
  </script>
