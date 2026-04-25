<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT * FROM produtos ORDER BY id DESC");
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($produtos);

} catch(PDOException $e) {
    echo json_encode(['erro' => $e->getMessage()]);
}
?>