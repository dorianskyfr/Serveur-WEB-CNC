<?php
// partials/layout_top.php
?>
<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($title ?? 'CNC SHARE') ?></title>

  <link rel="stylesheet" href="accueil.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
<script>
  // Applique le thème le plus tôt possible (évite le flash)
  (function () {
    const stored = localStorage.getItem("theme");
    const prefersDark = window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches;
    const theme = stored ? stored : (prefersDark ? "dark" : "light");
    document.documentElement.setAttribute("data-theme", theme);
  })();
</script>

<div class="background-grid"></div>

<script>
  // Branche le bouton (où qu’il soit dans le HTML)
  window.addEventListener("DOMContentLoaded", function () {
    const btn = document.getElementById("theme-toggle-btn");
    const label = document.getElementById("theme-toggle-label");
    if (!btn || !label) return;

    function syncLabel() {
      const current = document.documentElement.getAttribute("data-theme") || "dark";
      label.textContent = (current === "dark") ? "Mode clair" : "Mode sombre";
    }

    syncLabel();

    btn.addEventListener("click", function () {
      const current = document.documentElement.getAttribute("data-theme") || "dark";
      const next = (current === "dark") ? "light" : "dark";
      document.documentElement.setAttribute("data-theme", next);
      localStorage.setItem("theme", next);
      syncLabel();
    });
  });
</script>
