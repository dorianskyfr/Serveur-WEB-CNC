<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/lib/auth.php";

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Méthode non autorisée.");
}

$csrf = (string)($_POST['csrf_token'] ?? '');
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
    http_response_code(403);
    exit("CSRF invalide.");
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    exit("ID invalide.");
}

// Récupération du fichier
$stmt = $pdo->prepare("SELECT id, owner_user_id, original_name, storage_path FROM files WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$f = $stmt->fetch();

if (!$f) {
    http_response_code(404);
    exit("Fichier introuvable.");
}

// Autorisation: owner ou admin
$ownerId = (int)($f['owner_user_id'] ?? 0);
if (!is_admin() && $ownerId !== (int)$_SESSION['user_id']) {
    http_response_code(403);
    exit("Suppression interdite.");
}

$fullPath = __DIR__ . "/" . $f['storage_path'];

try {
    $pdo->beginTransaction();

    // Supprime d’abord le fichier sur disque si présent
    if (is_file($fullPath)) {
        if (!unlink($fullPath)) {
            throw new RuntimeException("Impossible de supprimer le fichier sur le disque.");
        }
    }

    // Puis supprime en base
    // Note: file_transfers est en ON DELETE CASCADE -> supprimé automatiquement quand files est supprimé. [web:150]
    $stmt = $pdo->prepare("DELETE FROM files WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);

    $pdo->commit();

    header("Location: consultation.php?msg=" . urlencode("Fichier supprimé: " . $f['original_name']));
    exit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    exit("Erreur suppression: " . $e->getMessage());
}
