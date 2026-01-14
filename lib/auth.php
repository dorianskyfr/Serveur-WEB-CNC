<?php
// lib/auth.php
declare(strict_types=1);

function redirect(string $to): void {
    header("Location: " . $to);
    exit();
}

function require_login(): void {
    if (empty($_SESSION['user_id'])) {
        redirect("index.php?action=login");
    }
}

function is_admin(): bool {
    return (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin');
}

function auth_log(PDO $pdo, ?int $userId, ?string $usernameTry, bool $success): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;

    $stmt = $pdo->prepare("INSERT INTO auth_logs (user_id, username_try, success, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $usernameTry, $success ? 1 : 0, $ip, $ua]);
}
