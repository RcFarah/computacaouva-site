<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Puxa todos os eventos da nova tabela
    $query = "SELECT * FROM eventos_gerais ORDER BY data_evento ASC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($eventos);

} catch(PDOException $e) {
    echo json_encode(['erro' => 'Falha na conexão: ' . $e->getMessage()]);
}
?>