<?php
session_start();
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/lib/auth.php";
require_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT original_name, storage_path FROM files WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$f = $stmt->fetch();

if (!$f) {
    http_response_code(404);
    exit("Fichier introuvable.");
}

$full = __DIR__ . "/" . $f['storage_path'];
if (!is_file($full)) {
    http_response_code(404);
    exit("Fichier manquant sur le serveur.");
}

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($f['original_name']) . '"');
header('Content-Length: ' . filesize($full));
readfile($full);
exit();
