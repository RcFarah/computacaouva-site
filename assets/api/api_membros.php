<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Busca os membros organizados por categoria
    $stmt = $pdo->query("SELECT * FROM membros ORDER BY categoria ASC, id ASC");
    $membros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($membros);

} catch(PDOException $e) {
    echo json_encode(['erro' => $e->getMessage()]);
}
?>