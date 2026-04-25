<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['sucesso' => false, 'erro' => 'Acesso negado.']);
    exit;
}

$nome     = htmlspecialchars(strip_tags($_POST['nome']));
$email    = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$assunto  = htmlspecialchars(strip_tags($_POST['assunto']));
$mensagem = htmlspecialchars(strip_tags($_POST['mensagem']));

// Monta os dados para o Formspree
$dados = http_build_query([
    'nome'     => $nome,
    'email'    => $email,
    'assunto'  => $assunto,
    'mensagem' => $mensagem,
]);

// Envia para o Formspree via cURL (backend — URL nunca exposta ao HTML)
$ch = curl_init(FORMSPREE_URL);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $dados);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/x-www-form-urlencoded'
]);

$resposta   = curl_exec($ch);
$httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($httpStatus === 200) {
    echo json_encode(['sucesso' => true]);
} else {
    echo json_encode(['sucesso' => false, 'erro' => 'Falha ao encaminhar a mensagem.']);
}
?>