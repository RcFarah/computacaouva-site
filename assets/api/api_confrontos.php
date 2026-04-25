<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // MÁGICA ACONTECE AQUI: Tiramos o 'WHERE' para ele puxar TUDO (passado e futuro)
    $query = "SELECT * FROM confrontos ORDER BY data_jogo ASC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $jogos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($jogos);

} catch(PDOException $e) {
    echo json_encode(['erro' => 'Falha na conexão: ' . $e->getMessage()]);
}
?>