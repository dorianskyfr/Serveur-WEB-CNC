<?php
// config/db.php
declare(strict_types=1);

$DB_HOST = "localhost";
$DB_NAME = "cnc_files";
$DB_USER = "Projetcnc";
$DB_PASS = "CNC2026";

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (Throwable $e) {
    http_response_code(500);
    exit("Erreur DB: " . $e->getMessage());
}
