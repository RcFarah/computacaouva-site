<?php
session_set_cookie_params([
    'lifetime' => 0,        // Cookie expira ao fechar o navegador
    'secure'   => true,     // Só trafega via HTTPS
    'httponly' => true,     // Inacessível via JavaScript
    'samesite' => 'Strict'  // Não é enviado em requisições cross-site
]);
session_start();

// Se a pessoa já estiver logada, joga ela direto pro painel
if (isset($_SESSION['logado']) && $_SESSION['logado'] === true) {
    header("Location: painel.php");
    exit;
}

$erro = '';

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuarioDigitado = $_POST['usuario'] ?? '';
    $senhaDigitada = $_POST['senha'] ?? '';

    require_once 'config.php';
    
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);

        // Busca o usuário no banco
        $stmt = $pdo->prepare("SELECT * FROM administradores WHERE usuario = ?");
        $stmt->execute([$usuarioDigitado]);
        $admin = $stmt->fetch();

        // O password_verify cruza a senha digitada com o Hash bagunçado do banco
        if ($admin && password_verify($senhaDigitada, $admin['senha_hash'])) {
            // Sucesso! Entrega a "Pulseira VIP" (Sessão)
            $_SESSION['logado'] = true;
            $_SESSION['usuario'] = $admin['usuario'];
            $_SESSION['ultimo_acesso'] = time();

            header("Location: painel.php"); // Manda pro painel secreto
            exit;
        } else {
            $erro = 'Usuário ou senha incorretos!';
        }
    } catch (Exception $e) {
        $erro = 'Erro no servidor: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Login - Diretoria Dextemidos</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/svg+xml" href="assets/img/ui/mascote_favicon.png">
    <link
        href="https://fonts.googleapis.com/css2?family=Barlow+Semi+Condensed:wght@700;900&family=Poppins:wght@400;600;700;900&display=swap"
        rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        /* CSS específico para os inputs do formulário */
        .input-neon {
            width: 100%;
            padding: 15px;
            margin-bottom: 20px;
            background: rgba(0, 0, 0, 0.6);
            border: 1px solid rgba(38, 228, 227, 0.2);
            color: white;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .input-neon:focus {
            border-color: var(--cor-ciano-neon);
            outline: none;
            box-shadow: 0 0 15px rgba(38, 228, 227, 0.4);
        }

        .container-login {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
    </style>
</head>

<body>

    <div class="container-login">
        <div class="bloco-esporte"
            style="flex-direction: column; width: 100%; max-width: 400px; text-align: center; gap: 20px;">

            <img src="assets/img/ui/logo_atletica.webp" alt="Logo" style="width: 120px; margin: 0 auto;">

            <h2 class="subtitulo-branco-glow" style="margin: 0; font-size: 2rem;">SISTEMA ADMIN</h2>

            <?php if ($erro): ?>
                <div
                    style="background: rgba(255,0,0,0.1); border: 1px solid red; color: #ff4444; padding: 10px; border-radius: 8px;">
                    <i class="ph-bold ph-warning"></i> <?= $erro ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" style="margin-top: 20px;">
                <input type="password" name="usuario" placeholder="Usuário" class="input-neon" required autocomplete="off">
                <input type="password" name="senha" placeholder="Senha" class="input-neon" required>

                <button type="submit" class="btn-participar" style="width: 100%; cursor: pointer;">ACESSAR
                    PAINEL</button>
            </form>

            <a href="index.html"
                style="color: rgba(255,255,255,0.5); text-decoration: none; font-size: 0.8rem; margin-top: 10px;">&larr;
                Voltar para o site</a>
        </div>
    </div>

</body>

</html>