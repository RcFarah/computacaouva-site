<?php
session_set_cookie_params([
    'lifetime' => 0,        // Cookie expira ao fechar o navegador
    'secure'   => true,     // Só trafega via HTTPS
    'httponly' => true,     // Inacessível via JavaScript
    'samesite' => 'Strict'  // Não é enviado em requisições cross-site
]);

session_start();

$timeout = 3600; // 1 hora em segundos

if (isset($_SESSION['ultimo_acesso']) && (time() - $_SESSION['ultimo_acesso']) > $timeout) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$_SESSION['ultimo_acesso'] = time();
// 1. O Segurança na porta
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: login.php");
    exit;
}

require_once 'config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

// --- SISTEMA DE MENSAGENS ANTI-F5 ---
$mensagem = '';
if (isset($_SESSION['mensagem'])) {
    $mensagem = $_SESSION['mensagem'];
    unset($_SESSION['mensagem']); // Limpa a mensagem para não ficar a aparecer para sempre
}

// =======================================================
// LÓGICA DOS CONFRONTOS (ESPORTES)
// =======================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'salvar') {
    $id = $_POST['id'] ?? '';
    $modalidade = $_POST['modalidade'];
    $adversario = $_POST['adversario'];
    $data_jogo = $_POST['data_jogo'];
    $hora_jogo = $_POST['hora_jogo'];
    $local_jogo = $_POST['local_jogo'];
    $tipo = $_POST['tipo'];
    $status = $_POST['status'];
    $resultado = $_POST['resultado'] ?? 'Aguardando'; // <-- NOVO: Recebe o resultado do form

    if (empty($id)) {
        // <-- NOVO: INSERE COM O RESULTADO
        $stmt = $pdo->prepare("INSERT INTO confrontos (modalidade, adversario, data_jogo, hora_jogo, local_jogo, tipo, status, resultado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$modalidade, $adversario, $data_jogo, $hora_jogo, $local_jogo, $tipo, $status, $resultado]);
        $_SESSION['mensagem'] = "<div class='msg-sucesso'>Confronto cadastrado com sucesso!</div>";
    } else {
        // <-- NOVO: ATUALIZA COM O RESULTADO
        $stmt = $pdo->prepare("UPDATE confrontos SET modalidade=?, adversario=?, data_jogo=?, hora_jogo=?, local_jogo=?, tipo=?, status=?, resultado=? WHERE id=?");
        $stmt->execute([$modalidade, $adversario, $data_jogo, $hora_jogo, $local_jogo, $tipo, $status, $resultado, $id]);
        $_SESSION['mensagem'] = "<div class='msg-sucesso'>Confronto atualizado com sucesso!</div>";
    }
    header("Location: painel.php"); // A mágica que resolve o F5
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['acao'] ?? '') == 'deletar_confronto') {
    $stmt = $pdo->prepare("DELETE FROM confrontos WHERE id=?");
    $stmt->execute([$_POST['id']]);
    header("Location: painel.php");
    exit;
}

// =======================================================
// LÓGICA DOS PRODUTOS (LOJA)
// =======================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'salvar_produto') {
    $id_produto = $_POST['id_produto'] ?? '';
    $nome = $_POST['nome'];
    $preco = str_replace(',', '.', $_POST['preco']);
    $link_venda = $_POST['link_venda'];
    $imagem = $_POST['imagem'];
    $categoria = $_POST['categoria'];

    if (empty($id_produto)) {
        $stmt = $pdo->prepare("INSERT INTO produtos (nome, preco, link_venda, imagem, categoria) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nome, $preco, $link_venda, $imagem, $categoria]);
        $_SESSION['mensagem'] = "<div class='msg-sucesso'>Produto adicionado à loja com sucesso!</div>";
    } else {
        $stmt = $pdo->prepare("UPDATE produtos SET nome=?, preco=?, link_venda=?, imagem=?, categoria=? WHERE id=?");
        $stmt->execute([$nome, $preco, $link_venda, $imagem, $categoria, $id_produto]);
        $_SESSION['mensagem'] = "<div class='msg-sucesso'>Produto atualizado com sucesso!</div>";
    }
    header("Location: painel.php"); // A mágica que resolve o F5
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['acao'] ?? '') == 'deletar_produto') {
    $stmt = $pdo->prepare("DELETE FROM produtos WHERE id=?");
    $stmt->execute([$_POST['id']]);
    header("Location: painel.php");
    exit;
}

// =======================================================
// LÓGICA DE EVENTOS GERAIS (FESTAS, CALOURADAS)
// =======================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'salvar_evento') {
    $id_evento = $_POST['id_evento'] ?? '';
    $nome = $_POST['nome_evento'];
    $data_evento = $_POST['data_evento'];
    $hora_evento = $_POST['hora_evento'];
    $local_evento = $_POST['local_evento'];
    $status_evento = $_POST['status_evento'];
    $link_detalhes = $_POST['link_detalhes'];

    if (empty($id_evento)) {
        $stmt = $pdo->prepare("INSERT INTO eventos_gerais (nome, data_evento, hora_evento, local_evento, status_evento, link_detalhes) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nome, $data_evento, $hora_evento, $local_evento, $status_evento, $link_detalhes]);
        $_SESSION['mensagem'] = "<div class='msg-sucesso'>Evento criado com sucesso!</div>";
    } else {
        $stmt = $pdo->prepare("UPDATE eventos_gerais SET nome=?, data_evento=?, hora_evento=?, local_evento=?, status_evento=?, link_detalhes=? WHERE id=?");
        $stmt->execute([$nome, $data_evento, $hora_evento, $local_evento, $status_evento, $link_detalhes, $id_evento]);
        $_SESSION['mensagem'] = "<div class='msg-sucesso'>Evento atualizado com sucesso!</div>";
    }
    header("Location: painel.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['acao'] ?? '') == 'deletar_evento') {
    $stmt = $pdo->prepare("DELETE FROM eventos_gerais WHERE id=?");
    $stmt->execute([$_POST['id']]);
    header("Location: painel.php");
    exit;
}

// Busca Eventos para a Tabela
$stmt_eventos = $pdo->query("SELECT * FROM eventos_gerais ORDER BY data_evento ASC");
$eventos_gerais = $stmt_eventos->fetchAll(PDO::FETCH_ASSOC);

// =======================================================
// LÓGICA DE MEMBROS (EQUIPA)
// =======================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'salvar_membro') {
    $id_membro = $_POST['id_membro'] ?? '';
    $nome = $_POST['nome_membro'];
    $cargo = $_POST['cargo_membro'];
    $detalhe = $_POST['detalhe_membro'];
    $descricao = $_POST['desc_membro'];
    $imagem = $_POST['img_membro'];
    $instagram = $_POST['insta_membro'];
    $linkedin = $_POST['in_membro'];
    $categoria = $_POST['cat_membro'];

    if (empty($id_membro)) {
        $stmt = $pdo->prepare("INSERT INTO membros (nome, cargo, detalhe, descricao, imagem, instagram, linkedin, categoria) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nome, $cargo, $detalhe, $descricao, $imagem, $instagram, $linkedin, $categoria]);
        $_SESSION['mensagem'] = "<div class='msg-sucesso'>Membro adicionado à equipa com sucesso!</div>";
    } else {
        $stmt = $pdo->prepare("UPDATE membros SET nome=?, cargo=?, detalhe=?, descricao=?, imagem=?, instagram=?, linkedin=?, categoria=? WHERE id=?");
        $stmt->execute([$nome, $cargo, $detalhe, $descricao, $imagem, $instagram, $linkedin, $categoria, $id_membro]);
        $_SESSION['mensagem'] = "<div class='msg-sucesso'>Membro atualizado com sucesso!</div>";
    }
    header("Location: painel.php"); // A mágica que resolve o F5
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['acao'] ?? '') == 'deletar_membro') {
    $stmt = $pdo->prepare("DELETE FROM membros WHERE id=?");
    $stmt->execute([$_POST['id']]);
    header("Location: painel.php");
    exit;
}

// 3. Busca tudo para preencher as tabelas
$stmt_jogos = $pdo->query("SELECT * FROM confrontos ORDER BY data_jogo ASC");
$jogos = $stmt_jogos->fetchAll(PDO::FETCH_ASSOC);

$stmt_prod = $pdo->query("SELECT * FROM produtos ORDER BY id DESC");
$produtos = $stmt_prod->fetchAll(PDO::FETCH_ASSOC);

$stmt_membros = $pdo->query("SELECT * FROM membros ORDER BY categoria ASC, id ASC");
$membros = $stmt_membros->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Painel - Dextemidos</title>
    <link rel="icon" type="image/svg+xml" href="assets/img/ui/mascote_favicon.png">
    <link rel="stylesheet" href="assets/css/style.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Barlow+Semi+Condensed:wght@700;900&family=Poppins:wght@400;600;700;900&display=swap"
        rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .painel-container {
            max-width: 1200px;
            margin: 50px auto;
            background-color: rgba(0, 0, 0, 0.8);
            border: 1px solid var(--cor-ciano-neon);
            border-radius: 20px;
            padding: 40px;
            color: white;
        }

        .header-painel {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .btn-sair {
            background: transparent;
            color: #ff4444;
            border: 1px solid #ff4444;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-sair:hover {
            background: #ff4444;
            color: black;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .input-neon {
            width: 100%;
            padding: 12px;
            background: rgba(0, 0, 0, 0.6);
            border: 1px solid rgba(38, 228, 227, 0.3);
            color: white;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
        }

        .input-neon:focus {
            outline: none;
            border-color: var(--cor-ciano-neon);
            box-shadow: 0 0 10px rgba(38, 228, 227, 0.3);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-bottom: 40px;
            font-size: 0.9rem;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        th {
            color: var(--cor-ciano-neon);
            text-transform: uppercase;
        }

        tr:hover {
            background-color: rgba(38, 228, 227, 0.05);
        }

        .btn-acao {
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.8rem;
            border: none;
            cursor: pointer;
            color: black;
        }

        .btn-editar {
            background: #ffaa00;
        }

        .btn-deletar {
            background: #ff4444;
            color: white;
        }

        .msg-sucesso {
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid #00ff88;
            color: #00ff88;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }

        .divisoria-setor {
            border: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--cor-ciano-neon), transparent);
            margin: 60px 0;
            opacity: 0.5;
        }
    </style>
</head>

<body style="background-color: var(--cor-fundo-página);">

    <div class="painel-container">

        <div class="header-painel">
            <h2><span style="color: var(--cor-ciano-neon);">ADMIN</span> DEXTEMIDOS</h2>
            <div>
                <span style="margin-right: 15px;">Olá, <strong><?= strtoupper($_SESSION['usuario']) ?></strong></span>
                <a href="logout.php" class="btn-sair">Sair</a>
            </div>
        </div>

        <?= $mensagem ?>

        <h2 class="subtitulo-branco-glow" style="font-size: 1.8rem; text-align: left; margin-top:0;">1. GESTÃO DE
            CONFRONTOS</h2>

        <div class="bloco-esporte" style="flex-direction: column; padding: 20px;">
            <h3 style="color: var(--cor-ciano-neon); margin-top: 0;" id="titulo-form">CADASTRAR NOVO CONFRONTO</h3>

            <form method="POST" action="painel.php">
                <input type="hidden" name="acao" value="salvar">
                <input type="hidden" name="id" id="form-id" value="">

                <div class="form-grid">
                    <div>
                        <label>Modalidade</label>
                        <select name="modalidade" id="form-modalidade" class="input-neon" required>
                            <option value="BASQUETE">Basquete</option>
                            <option value="VÔLEI">Vôlei</option>
                            <option value="FUTSAL">Futsal</option>
                            <option value="JIU JITSU">Jiu Jitsu</option>
                            <option value="ESPORT">eSports</option>
                        </select>
                    </div>
                    <div>
                        <label>Adversário / Faculdade</label>
                        <input type="text" name="adversario" id="form-adversario" class="input-neon" required>
                    </div>
                    <div>
                        <label>Data do Jogo</label>
                        <input type="date" name="data_jogo" id="form-data" class="input-neon" required>
                    </div>
                    <div>
                        <label>Horário</label>
                        <input type="text" name="hora_jogo" id="form-hora-jogo" class="input-neon"
                            placeholder="Ex: 19:00h" required>
                    </div>
                    <div>
                        <label>Local (Ex: Quadra 1)</label>
                        <input type="text" name="local_jogo" id="form-local" class="input-neon" required>
                    </div>
                    <div>
                        <label>Tipo (Ex: Amistoso)</label>
                        <input type="text" name="tipo" id="form-tipo" class="input-neon" required>
                    </div>
                    <div>
                        <label>Status</label>
                        <select name="status" id="form-status" class="input-neon" required>
                            <option value="CONFIRMADO">Confirmado</option>
                            <option value="CANCELADO">Cancelado</option>
                            <option value="FINALIZADO">Finalizado</option>
                        </select>
                    </div>

                    <div>
                        <label>Resultado Final</label>
                        <select name="resultado" id="form-resultado" class="input-neon" required>
                            <option value="Aguardando">Aguardando Jogo...</option>
                            <option value="VITÓRIA">Vitória</option>
                            <option value="DERROTA">Derrota</option>
                            <option value="EMPATE">Empate</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn-participar" id="btn-submit"
                    style="width: 100%; cursor: pointer;">SALVAR CONFRONTO</button>
                <button type="button" class="btn-acao btn-deletar" id="btn-cancelar"
                    style="display: none; width: 100%; margin-top: 10px;" onclick="cancelarEdicao()">Cancelar
                    Edição</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Modalidade</th>
                    <th>Adversário</th>
                    <th>Status</th>
                    <th>Resultado</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jogos as $jogo): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($jogo['data_jogo'])) ?></td>
                        <td><strong><?= $jogo['modalidade'] ?></strong></td>
                        <td><?= $jogo['adversario'] ?></td>
                        <td
                            style="color: <?= $jogo['status'] == 'CANCELADO' ? '#ff4444' : '#00ff88' ?>; font-weight: bold;">
                            <?= $jogo['status'] ?>
                        </td>
                        <td style="color: #aaa; font-style: italic;">
                            <?= htmlspecialchars($jogo['resultado'] ?? 'Aguardando') ?>
                        </td>
                        <td>
                            <button class="btn-acao btn-editar"
                                onclick="editarJogo(<?= htmlspecialchars(json_encode($jogo)) ?>)">Editar</button>
                            <form method="POST" action="painel.php" style="display:inline;"
                                onsubmit="return confirm('Apagar este confronto?')">
                                <input type="hidden" name="acao" value="deletar_confronto">
                                <input type="hidden" name="id" value="<?= $jogo['id'] ?>">
                                <button type="submit" class="btn-acao btn-deletar">Apagar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <hr class="divisoria-setor">

        <h2 class="subtitulo-branco-glow" style="font-size: 1.8rem; text-align: left;">2. GESTÃO DA LOJA</h2>

        <div class="bloco-esporte" style="flex-direction: column; padding: 20px;">
            <h3 style="color: var(--cor-ciano-neon); margin-top: 0;" id="titulo-form-prod">CADASTRAR NOVO PRODUTO</h3>

            <form method="POST" action="painel.php">
                <input type="hidden" name="acao" value="salvar_produto">
                <input type="hidden" name="id_produto" id="form-id-prod" value="">

                <div class="form-grid">
                    <div>
                        <label>Nome do Produto</label>
                        <input type="text" name="nome" id="form-nome-prod" class="input-neon"
                            placeholder="Ex: Camisa Oficial" required>
                    </div>
                    <div>
                        <label>Preço (R$)</label>
                        <input type="text" name="preco" id="form-preco-prod" class="input-neon" placeholder="Ex: 65,00"
                            required>
                    </div>
                    <div>
                        <label>Categoria</label>
                        <select name="categoria" id="form-cat-prod" class="input-neon" required>
                            <option value="Vestuário">Vestuário</option>
                            <option value="Acessórios">Acessórios</option>
                            <option value="Eventos">Eventos (Tirantes, etc)</option>
                        </select>
                    </div>

                    <div style="grid-column: span 3;">
                        <label>Link de Venda Direta (URL do Site de Vendas)</label>
                        <input type="url" name="link_venda" id="form-link-prod" class="input-neon"
                            placeholder="Ex: https://sua-loja.com.br/produto" required>
                    </div>

                    <div style="grid-column: span 3;">
                        <label>Caminho das Imagens (SEPARE POR VÍRGULA para criar Galeria)</label>
                        <input type="text" name="imagem" id="form-img-prod" class="input-neon"
                            placeholder="Ex: assets/img/produtos/frente.png, assets/img/produtos/costas.png" required>
                        <small style="color: var(--cor-ciano-neon);">* A primeira imagem será a capa. As seguintes serão
                            as miniaturas clicáveis.</small>
                    </div>
                </div>

                <button type="submit" class="btn-participar" id="btn-submit-prod"
                    style="width: 100%; cursor: pointer;">SALVAR PRODUTO</button>
                <button type="button" class="btn-acao btn-deletar" id="btn-cancelar-prod"
                    style="display: none; width: 100%; margin-top: 10px;" onclick="cancelarEdicaoProduto()">Cancelar
                    Edição</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Capa</th>
                    <th>Produto</th>
                    <th>Preço</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $prod):
                    // Pega só a primeira imagem para mostrar na tabela miniatura
                    $primeira_img = explode(',', $prod['imagem'])[0];
                    ?>
                    <tr>
                        <td><img src="<?= trim($primeira_img) ?>" alt="Thumb"
                                style="width: 40px; border-radius: 5px; background: #111;"></td>
                        <td><strong><?= $prod['nome'] ?></strong></td>
                        <td style="color: var(--cor-ciano-neon); font-weight: bold;">R$
                            <?= number_format($prod['preco'], 2, ',', '.') ?>
                        </td>
                        <td>
                            <button class="btn-acao btn-editar"
                                onclick="editarProduto(<?= htmlspecialchars(json_encode($prod)) ?>)">Editar</button>
                            <form method="POST" action="painel.php" style="display:inline;"
                                onsubmit="return confirm('Apagar este produto da loja?')">
                                <input type="hidden" name="acao" value="deletar_produto">
                                <input type="hidden" name="id" value="<?= $prod['id'] ?>">
                                <button type="submit" class="btn-acao btn-deletar">Apagar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <hr class="divisoria-setor">

        <h2 class="subtitulo-branco-glow" style="font-size: 1.8rem; text-align: left;">3. GESTÃO DA EQUIPA</h2>

        <div class="bloco-esporte" style="flex-direction: column; padding: 20px;">
            <h3 style="color: var(--cor-ciano-neon); margin-top: 0;" id="titulo-form-membro">CADASTRAR NOVO MEMBRO</h3>

            <form method="POST" action="painel.php">
                <input type="hidden" name="acao" value="salvar_membro">
                <input type="hidden" name="id_membro" id="form-id-membro" value="">

                <div class="form-grid">
                    <div>
                        <label>Nome do Membro</label>
                        <input type="text" name="nome_membro" id="form-nome-membro" class="input-neon"
                            placeholder="Ex: Rodrigo Farah" required>
                    </div>
                    <div>
                        <label>Cargo / Função</label>
                        <input type="text" name="cargo_membro" id="form-cargo-membro" class="input-neon"
                            placeholder="Ex: Presidente" required>
                    </div>
                    <div>
                        <label>Curso / Detalhe / Área</label>
                        <input type="text" name="detalhe_membro" id="form-detalhe-membro" class="input-neon"
                            placeholder="Ex: Ciência da Computação" required>
                    </div>

                    <div>
                        <label>Pertence à Categoria:</label>
                        <select name="cat_membro" id="form-cat-membro" class="input-neon" required>
                            <option value="Presidência">Presidência</option>
                            <option value="Direção">Direção Executiva</option>
                            <option value="Coordenação">Coordenação</option>
                            <option value="Técnicos">Técnicos</option>
                        </select>
                    </div>
                    <div>
                        <label>Link do Instagram (Opcional)</label>
                        <input type="url" name="insta_membro" id="form-insta-membro" class="input-neon"
                            placeholder="https://instagram.com/...">
                    </div>
                    <div>
                        <label>Link do LinkedIn (Opcional)</label>
                        <input type="url" name="in_membro" id="form-in-membro" class="input-neon"
                            placeholder="https://linkedin.com/...">
                    </div>

                    <div style="grid-column: span 3;">
                        <label>Descrição / Sobre (Uma frase curta)</label>
                        <input type="text" name="desc_membro" id="form-desc-membro" class="input-neon"
                            placeholder="Ex: Responsável pela gestão estratégica e foco..." required>
                    </div>

                    <div style="grid-column: span 3;">
                        <label>Caminho da Foto</label>
                        <input type="text" name="img_membro" id="form-img-membro" class="input-neon"
                            placeholder="Ex: assets/img/membros/rodrigo.jpg" required>
                    </div>
                </div>

                <button type="submit" class="btn-participar" id="btn-submit-membro"
                    style="width: 100%; cursor: pointer;">SALVAR MEMBRO</button>
                <button type="button" class="btn-acao btn-deletar" id="btn-cancelar-membro"
                    style="display: none; width: 100%; margin-top: 10px;" onclick="cancelarEdicaoMembro()">Cancelar
                    Edição</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Nome</th>
                    <th>Cargo</th>
                    <th>Categoria</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($membros as $m): ?>
                    <tr>
                        <td><img src="<?= htmlspecialchars($m['imagem']) ?>" alt="Foto"
                                style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; background: #111;">
                        </td>
                        <td><strong><?= htmlspecialchars($m['nome']) ?></strong></td>
                        <td><?= htmlspecialchars($m['cargo']) ?></td>
                        <td style="color: var(--cor-ciano-neon); font-weight: bold;">
                            <?= htmlspecialchars($m['categoria']) ?>
                        </td>
                        <td>
                            <button class="btn-acao btn-editar"
                                onclick="editarMembro(<?= htmlspecialchars(json_encode($m)) ?>)">Editar</button>
                            <form method="POST" action="painel.php" style="display:inline;"
                                onsubmit="return confirm('Apagar este membro da equipa?')">
                                <input type="hidden" name="acao" value="deletar_membro">
                                <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                <button type="submit" class="btn-acao btn-deletar">Apagar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <hr class="divisoria-setor">

        <h2 class="subtitulo-branco-glow" style="font-size: 1.8rem; text-align: left;">4. GESTÃO DE EVENTOS (FESTAS E
            ENCONTROS)</h2>

        <div class="bloco-esporte" style="flex-direction: column; padding: 20px;">
            <h3 style="color: var(--cor-ciano-neon); margin-top: 0;" id="titulo-form-evento">CADASTRAR NOVO EVENTO</h3>

            <form method="POST" action="painel.php">
                <input type="hidden" name="acao" value="salvar_evento">
                <input type="hidden" name="id_evento" id="form-id-evento" value="">

                <div class="form-grid">
                    <div>
                        <label>Nome do Evento</label>
                        <input type="text" name="nome_evento" id="form-nome-evento" class="input-neon"
                            placeholder="Ex: Intersala, Calourada..." required>
                    </div>
                    <div>
                        <label>Data</label>
                        <input type="date" name="data_evento" id="form-data-evento" class="input-neon" required>
                    </div>
                    <div>
                        <label>Horário</label>
                        <input type="text" name="hora_evento" id="form-hora-evento" class="input-neon"
                            placeholder="Ex: 18:00h" required>
                    </div>
                    <div>
                        <label>Local</label>
                        <input type="text" name="local_evento" id="form-local-evento" class="input-neon"
                            placeholder="Ex: Quadra Principal" required>
                    </div>
                    <div>
                        <label>Status do Evento</label>
                        <select name="status_evento" id="form-status-evento" class="input-neon" required>
                            <option value="Anunciado">Anunciado</option>
                            <option value="Vendas Abertas">Vendas Abertas</option>
                            <option value="Gratuito">Gratuito</option>
                            <option value="Vendas Esgotadas">Vendas Esgotadas</option>
                            <option value="Finalizado">Finalizado</option>
                        </select>
                    </div>
                    <div style="grid-column: span 3;">
                        <label>Link para Compra / Detalhes</label>
                        <input type="url" name="link_detalhes" id="form-link-evento" class="input-neon"
                            placeholder="https://...">
                    </div>
                </div>

                <button type="submit" class="btn-participar" id="btn-submit-evento"
                    style="width: 100%; cursor: pointer;">SALVAR EVENTO</button>
                <button type="button" class="btn-acao btn-deletar" id="btn-cancelar-evento"
                    style="display: none; width: 100%; margin-top: 10px;" onclick="cancelarEdicaoEvento()">Cancelar
                    Edição</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Evento</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($eventos_gerais as $ev): ?>
                    <tr>
                        <td>
                            <?= date('d/m/Y', strtotime($ev['data_evento'])) ?>
                        </td>
                        <td><strong>
                                <?= htmlspecialchars($ev['nome']) ?>
                            </strong></td>
                        <td style="color: var(--cor-ciano-neon); font-weight: bold;">
                            <?= htmlspecialchars($ev['status_evento']) ?>
                        </td>
                        <td>
                            <button class="btn-acao btn-editar"
                                onclick="editarEventoGeral(<?= htmlspecialchars(json_encode($ev)) ?>)">Editar</button>
                            <form method="POST" action="painel.php" style="display:inline;"
                                onsubmit="return confirm('Apagar este evento?')">
                                <input type="hidden" name="acao" value="deletar_evento">
                                <input type="hidden" name="id" value="<?= $ev['id'] ?>">
                                <button type="submit" class="btn-acao btn-deletar">Apagar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        // --- FUNÇÕES DE EDIÇÃO DE CONFRONTOS ---
        function editarJogo(jogo) {
            document.getElementById('titulo-form').innerText = 'EDITAR CONFRONTO';
            document.getElementById('btn-submit').innerText = 'ATUALIZAR CONFRONTO';
            document.getElementById('btn-cancelar').style.display = 'block';

            document.getElementById('form-id').value = jogo.id;
            document.getElementById('form-modalidade').value = jogo.modalidade;
            document.getElementById('form-adversario').value = jogo.adversario;
            document.getElementById('form-data').value = jogo.data_jogo;
            document.getElementById('form-hora-jogo').value = jogo.hora_jogo;
            document.getElementById('form-local').value = jogo.local_jogo;
            document.getElementById('form-tipo').value = jogo.tipo;
            document.getElementById('form-status').value = jogo.status;

            // <-- NOVO: PREENCHE O RESULTADO NO FORMULÁRIO AO EDITAR
            document.getElementById('form-resultado').value = jogo.resultado || 'Aguardando';

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function cancelarEdicao() {
            document.getElementById('titulo-form').innerText = 'CADASTRAR NOVO CONFRONTO';
            document.getElementById('btn-submit').innerText = 'SALVAR CONFRONTO';
            document.getElementById('btn-cancelar').style.display = 'none';

            document.getElementById('form-id').value = '';
            document.querySelectorAll('form')[0].reset();
        }

        // --- FUNÇÕES DE EDIÇÃO DE PRODUTOS ---
        function editarProduto(prod) {
            document.getElementById('titulo-form-prod').innerText = 'EDITAR PRODUTO';
            document.getElementById('btn-submit-prod').innerText = 'ATUALIZAR PRODUTO';
            document.getElementById('btn-cancelar-prod').style.display = 'block';

            document.getElementById('form-id-prod').value = prod.id;
            document.getElementById('form-nome-prod').value = prod.nome;
            document.getElementById('form-preco-prod').value = prod.preco.replace('.', ',');
            document.getElementById('form-cat-prod').value = prod.categoria;
            document.getElementById('form-link-prod').value = prod.link_venda;
            document.getElementById('form-img-prod').value = prod.imagem;

            document.getElementById('titulo-form-prod').scrollIntoView({ behavior: 'smooth' });
        }

        function cancelarEdicaoProduto() {
            document.getElementById('titulo-form-prod').innerText = 'CADASTRAR NOVO PRODUTO';
            document.getElementById('btn-submit-prod').innerText = 'SALVAR PRODUTO';
            document.getElementById('btn-cancelar-prod').style.display = 'none';

            document.getElementById('form-id-prod').value = '';
            document.querySelectorAll('form')[1].reset();
        }

        // --- FUNÇÕES DE EDIÇÃO DE MEMBROS ---
        function editarMembro(m) {
            document.getElementById('titulo-form-membro').innerText = 'EDITAR MEMBRO';
            document.getElementById('btn-submit-membro').innerText = 'ATUALIZAR MEMBRO';
            document.getElementById('btn-cancelar-membro').style.display = 'block';

            document.getElementById('form-id-membro').value = m.id;
            document.getElementById('form-nome-membro').value = m.nome;
            document.getElementById('form-cargo-membro').value = m.cargo;
            document.getElementById('form-detalhe-membro').value = m.detalhe;
            document.getElementById('form-cat-membro').value = m.categoria;
            document.getElementById('form-insta-membro').value = m.instagram;
            document.getElementById('form-in-membro').value = m.linkedin;
            document.getElementById('form-desc-membro').value = m.descricao;
            document.getElementById('form-img-membro').value = m.imagem;

            document.getElementById('titulo-form-membro').scrollIntoView({ behavior: 'smooth' });
        }

        function cancelarEdicaoMembro() {
            document.getElementById('titulo-form-membro').innerText = 'CADASTRAR NOVO MEMBRO';
            document.getElementById('btn-submit-membro').innerText = 'SALVAR MEMBRO';
            document.getElementById('btn-cancelar-membro').style.display = 'none';

            document.getElementById('form-id-membro').value = '';
            document.querySelectorAll('form')[2].reset();
        }

        function editarEventoGeral(ev) {
            document.getElementById('titulo-form-evento').innerText = 'EDITAR EVENTO';
            document.getElementById('btn-submit-evento').innerText = 'ATUALIZAR EVENTO';
            document.getElementById('btn-cancelar-evento').style.display = 'block';

            document.getElementById('form-id-evento').value = ev.id;
            document.getElementById('form-nome-evento').value = ev.nome;
            document.getElementById('form-data-evento').value = ev.data_evento;
            document.getElementById('form-hora-evento').value = ev.hora_evento;
            document.getElementById('form-local-evento').value = ev.local_evento;
            document.getElementById('form-status-evento').value = ev.status_evento;
            document.getElementById('form-link-evento').value = ev.link_detalhes;

            document.getElementById('titulo-form-evento').scrollIntoView({ behavior: 'smooth' });
        }

        function cancelarEdicaoEvento() {
            document.getElementById('titulo-form-evento').innerText = 'CADASTRAR NOVO EVENTO';
            document.getElementById('btn-submit-evento').innerText = 'SALVAR EVENTO';
            document.getElementById('btn-cancelar-evento').style.display = 'none';

            document.getElementById('form-id-evento').value = '';
            document.querySelectorAll('form')[3].reset(); // Assume que é o 4º formulário da página
        }
    </script>
</body>

</html>