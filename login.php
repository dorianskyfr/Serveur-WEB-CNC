<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CNC Share - Connexion</title>
    <link rel="stylesheet" href="accueil.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="background-grid"></div>
    <div class="container">
        <header>
            <div class="logo">CNC<span>SHARE</span></div>
            <p>Portail Opérateur</p>
        </header>

        <div class="auth-panel">
            <div class="tabs">
                <a href="index.php?action=login" class="<?php echo $page == 'login' ? 'active' : ''; ?>">Connexion</a>
                <a href="index.php?action=register" class="<?php echo $page == 'register' ? 'active' : ''; ?>">S'enregistrer</a>
            </div>

            <?php if ($message): ?>
                <div class="alert"><?php echo $message; ?></div>
            <?php endif; ?>

            <form method="POST" action="index.php">
                <div class="form-group">
                    <label>Identifiant</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Clé de sécurité</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" name="login" class="btn btn-primary">Initialiser Connexion</button>
            </form>
        </div>
    </div>
</body>
</html>