<?php
declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ALL);
ini_set('zlib.output_compression', '0');

session_start();
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/lib/auth.php";
require_login();

try {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) { http_response_code(400); exit("ID invalide"); }

    $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $f = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$f) { http_response_code(404); exit("Introuvable"); }

    // Noms de colonnes possibles selon ton schéma
    $ext = strtolower((string)($f['file_ext'] ?? $f['fileext'] ?? ''));
    $path = (string)($f['storage_path'] ?? $f['storagepath'] ?? $f['storagepath'] ?? $f['storage_path'] ?? '');

    if ($path === '') { http_response_code(404); exit("Chemin manquant en BDD"); }

    // Chemin absolu (commence par /) ou relatif (ex: uploads/xxx.stl)
    $full = ($path !== '' && $path[0] === '/')
        ? $path
        : (__DIR__ . '/' . ltrim($path, '/'));

    if (!is_file($full)) { http_response_code(404); exit("Fichier manquant sur le serveur"); }

    // IMPORTANT: ne rien avoir envoyé avant les headers (sinon le binaire est corrompu)
    while (ob_get_level() > 0) { ob_end_clean(); }

    $contentType = "application/octet-stream";
    if ($ext === "stl") $contentType = "model/stl";
    if (in_array($ext, ['gcode','nc','ngc','tap','iso','txt','cammgl'], true)) {
        $contentType = "text/plain; charset=utf-8";
    }

    header("Content-Type: $contentType");
    header("X-Content-Type-Options: nosniff");
    header("Content-Length: " . filesize($full));

    $ok = readfile($full);
    if ($ok === false) {
        http_response_code(500);
        exit("Erreur lecture fichier");
    }
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    header("Content-Type: text/plain; charset=utf-8");
    exit("Erreur file_raw.php: " . $e->getMessage());
}
