<?php
// index.php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/lib/auth.php";

$message = "";
$action = $_GET['action'] ?? 'login'; // login | register

// Déconnexion
if (isset($_GET['logout'])) {
    session_destroy();
    redirect("index.php?action=login");
}

// Login
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    $stmt = $pdo->prepare("SELECT id, username, password_hash, role, is_active FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $u = $stmt->fetch();

    if ($u && (int)$u['is_active'] === 1 && password_verify($password, $u['password_hash'])) {
        $_SESSION['user_id'] = (int)$u['id'];
        $_SESSION['username'] = $u['username'];
        $_SESSION['role'] = $u['role'];

        $pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?")->execute([$_SESSION['user_id']]);
        auth_log($pdo, $_SESSION['user_id'], $username, true);

        redirect("index.php");
    } else {
        auth_log($pdo, $u['id'] ?? null, $username, false);
        $message = "Accès refusé : identifiant ou mot de passe incorrect.";
        $action = 'login';
    }
}

// Register
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['register'])) {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $password2 = (string)($_POST['password2'] ?? '');

    if (strlen($username) < 3) {
        $message = "Identifiant trop court (min 3).";
        $action = 'register';
    } elseif ($password !== $password2) {
        $message = "Les mots de passe ne correspondent pas.";
        $action = 'register';
    } elseif (strlen($password) < 4) {
        $message = "Mot de passe trop court (min 4).";
        $action = 'register';
    } else {
        // Si aucun user => premier compte admin
        $count = (int)$pdo->query("SELECT COUNT(*) AS c FROM users")->fetch()['c'];
        $role = ($count === 0) ? 'admin' : 'user';

        $hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role, is_active) VALUES (?, ?, ?, 1)");
            $stmt->execute([$username, $hash, $role]);
            $message = "Compte créé. Tu peux te connecter.";
            $action = 'login';
        } catch (Throwable $e) {
            $message = "Impossible de créer le compte (username déjà pris ?).";
            $action = 'register';
        }
    }
}

// Routage
if (!empty($_SESSION['user_id'])) {
    include __DIR__ . "/menu.php";
} else {
    $page = $action; // utilisé par login.php pour activer l’onglet [file:17]
    if ($action === 'register') {
        include __DIR__ . "/register.php";
    } else {
        include __DIR__ . "/login.php";
    }
}
