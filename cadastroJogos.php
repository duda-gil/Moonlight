<?php
require_once 'include/verifica.php';
require_once 'include/conexao.php';

if (session_status() !== PHP_SESSION_ACTIVE)
    session_start();

// Entende os separadores e estipula como casas decimais + garante que o valor nunca seja negativo
function parse_money_ptbr(?string $s): float
{
    $s = trim((string) $s);
    if ($s === '')
        return 0.0;

    // Mantém apenas dígitos, vírgulas e pontos
    $s = preg_replace('/[^\d.,]/', '', $s);

    $posComma = strrpos($s, ',');
    $posDot = strrpos($s, '.');

    if ($posComma !== false && $posDot !== false) {
        $last = max($posComma, $posDot);
        $int = preg_replace('/\D/', '', substr($s, 0, $last));
        $dec = preg_replace('/\D/', '', substr($s, $last + 1));
        $s = $int . '.' . $dec;
    } else {
        $s = str_replace(',', '.', $s);
        if (substr_count($s, '.') > 1) {
            $first = strpos($s, '.');
            $s = str_replace('.', '', substr($s, 0, $first)) . substr($s, $first);
        }
    }

    $v = (float) $s;
    return max(0.0, $v);
}

// Limpa e valida a URL da imagem
function normalize_image_url(string $u): string
{
    $u = trim($u);
    if ($u === '')
        return '';

    // Se vier sem protocolo, assume https
    if (!preg_match('~^https?://~i', $u)) {
        $u = 'https://' . ltrim($u, '/');
    }

    // Espaços internos viram "%20" (caractere de espaço em branco)
    $u = preg_replace('/\s+/', '%20', $u);

    // Aceita qualquer http/https válido
    return filter_var($u, FILTER_VALIDATE_URL) ? $u : '';
}

// Busca as classificações indicativas, com L vindo primeiro e em ordem numérica
$ratingSql = "
  SELECT id, tipo, descricao
  FROM classificacao_ind
  ORDER BY CASE WHEN tipo='L' THEN 0 ELSE CAST(tipo AS UNSIGNED) END
";
$ratings = mysqli_query($conn, $ratingSql);


// Reaproveita os dados e as categorias preenchidos no formulário 
$jogo = [];
$categoriasDoJogo = [];

if (isset($_SESSION['formdata'])) {
    $jogo = $_SESSION['formdata'];
    $categoriasDoJogo = array_values(array_map('intval', $jogo['categorias'] ?? []));
    unset($_SESSION['formdata']);
}

// Pega a classificação que o usuário selecionou
$selCI = (isset($jogo['classificacao_ind']) && is_numeric($jogo['classificacao_ind']))
    ? (int) $jogo['classificacao_ind']
    : null;


// Lê os dados e os valida
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $status = trim($_POST['status'] ?? '');
    $nome = trim($_POST['nome'] ?? '');
    $resumo = trim($_POST['resumo'] ?? '');
    $desenvolvedor = trim($_POST['desenvolvedor'] ?? '');
    $data_lanc = trim($_POST['data_lancamento'] ?? '');

    // Impossibilita um preço menor que 1 centavo
    $preco = parse_money_ptbr($_POST['preco'] ?? '0');
    if ($preco < 0.01) {
        $_SESSION['flash'] = ['tipo' => 'danger', 'msg' => 'Informe um preço válido!'];
        $_SESSION['formdata'] = $_POST;
        header('Location: cadastroJogos.php');
        exit;
    }

    $desconto = (int) ($_POST['desconto'] ?? 0);
    $url_banner = normalize_image_url($_POST['url_banner'] ?? '');

    // Lê cadastro de requisitos
    $req_processador = trim($_POST['req_processador'] ?? '');
    $req_memoria = trim($_POST['req_memoria'] ?? '');
    $req_placa_video = trim($_POST['req_placa_video'] ?? '');
    $req_sistema_op = trim($_POST['req_sistema_op'] ?? '');
    $req_armazenamento = trim($_POST['req_armazenamento'] ?? '');
    $req_directx = trim($_POST['req_directx'] ?? '');

    // Nomarliza URLs e garante primeira imagem obrigatória
    $url_1 = normalize_image_url($_POST['url_1'] ?? '');
    $url_2 = normalize_image_url($_POST['url_2'] ?? '');
    $url_3 = normalize_image_url($_POST['url_3'] ?? '');
    $url_4 = normalize_image_url($_POST['url_4'] ?? '');
    $url_5 = normalize_image_url($_POST['url_5'] ?? '');

    if ($url_1 === '') {
        $_SESSION['flash'] = ['tipo' => 'danger', 'msg' => 'A primeira imagem é obrigatória e precisa ser um URL válido.'];
        $_SESSION['formdata'] = $_POST;
        header('Location: cadastroJogos.php');
        exit;
    }

    // Seleção das categorias atribuídas
    $cats = array_map('intval', $_POST['categorias'] ?? []);
    $cats = array_values(array_unique(array_filter($cats)));

    // Seleção de classificações indicativas + campo de conteúdo
    $classificacao_ind = filter_input(INPUT_POST, 'classificacao_ind', FILTER_VALIDATE_INT);
    $conteudo = trim($_POST['conteudo'] ?? '');

    // Inserção dos dados no banco
    $conn->begin_transaction();
    try {
        $sql = 'INSERT INTO jogos (
          status, nome, resumo, desenvolvedor, data_lancamento,
          classificacao_ind, conteudo, preco, desconto,
          url_banner, url_1, url_2, url_3, url_4, url_5
        ) VALUES (?,?,?,?,?, ?,?, ?,?, ?, ?, ?, ?, ?, ?)';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'sssssisdissssss',
            $status,
            $nome,
            $resumo,
            $desenvolvedor,
            $data_lanc,
            $classificacao_ind,
            $conteudo,
            $preco,
            $desconto,
            $url_banner,
            $url_1,
            $url_2,
            $url_3,
            $url_4,
            $url_5
        );

        $ok = $stmt->execute();
        if (!$ok) {
            throw new Exception($stmt->error ?: 'Falha ao inserir jogo');
        }

        $jogoId = $conn->insert_id;

        if (!empty($cats)) {
            $ins = $conn->prepare('INSERT INTO jogos_categorias (jogo_id, categoria_id) VALUES (?, ?)');
            foreach ($cats as $cid) {
                $ins->bind_param('ii', $jogoId, $cid);
                if (!$ins->execute()) {
                    throw new Exception($ins->error ?: 'Falha ao vincular categoria');
                }
            }
        }

        $temReq = (
            $req_processador !== '' ||
            $req_memoria !== '' ||
            $req_placa_video !== '' ||
            $req_sistema_op !== '' ||
            $req_armazenamento !== '' ||
            $req_directx !== ''
        );

        if ($temReq) {
            $sqlReq = 'INSERT INTO requisitos
                       (jogo_id, processador, memoria, placa_video, sistema_op, armazenamento, directx)
                       VALUES (?,?,?,?,?,?,?)';
            $stmReq = $conn->prepare($sqlReq);
            $stmReq->bind_param(
                'issssss',
                $jogoId,
                $req_processador,
                $req_memoria,
                $req_placa_video,
                $req_sistema_op,
                $req_armazenamento,
                $req_directx
            );
            if (!$stmReq->execute()) {
                throw new Exception($stmReq->error ?: 'Falha ao salvar requisitos mínimos');
            }
        }

        $conn->commit();

        $_SESSION['flash'] = ['tipo' => 'success', 'msg' => 'Jogo cadastrado com sucesso!'];
        header('Location: adm.php');
        exit;

    } catch (Throwable $e) {
        $conn->rollback();
        $_SESSION['flash'] = ['tipo' => 'danger', 'msg' => 'Erro ao cadastrar: ' . $e->getMessage()];
        $_SESSION['formdata'] = $_POST; // <--- repopula depois
        header('Location: cadastroJogos.php');
        exit;

    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/stylesNav.css">

    <title>Cadastro Jogos</title>

</head>

<body>

    <?php $NAV_CONTEXT = 'admin';
    require_once 'include/navbar.php'; ?>

    <div class="content">

        <section class="cadastroJogos">

            <div class="main-content">
                <div class="form-container">

                    <!-- Formulário de cadastro de jogos -->
                    <form method="post">

                        <h1>Cadastro de Jogos</h1>
                        <br>

                        <div class="form-group form-inline">
                            <label for="status">Status:</label>
                            <div class="radio-group">
                                <label class="radio-option">
                                    <input type="radio" name="status" value="Ativo" <?= (($jogo['status'] ?? '') === 'Ativo') ? 'checked' : '' ?> required>
                                    Ativo
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="status" value="Inativo" <?= (($jogo['status'] ?? '') === 'Inativo') ? 'checked' : '' ?> required>
                                    Inativo
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Informe o nome do jogo:</label>
                            <input type="text" name="nome" placeholder="Digite o nome do jogo"
                                value="<?= htmlspecialchars($jogo['nome'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Disponibilize um resumo do jogo:</label>
                            <textarea name="resumo" rows="3" placeholder="Digite o resumo"
                                required><?= htmlspecialchars($jogo['resumo'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Informe a empresa ou desenvolvedor produtor do jogo:</label>
                            <input type="text" name="desenvolvedor" placeholder="Digite o nome da empresa"
                                value="<?= htmlspecialchars($jogo['desenvolvedor'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Informe a data de lançamento do jogo:</label>
                            <input type="date" name="data_lancamento" class="date-custom"
                                value="<?= htmlspecialchars($jogo['data_lancamento'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label>Informe o preço do jogo:</label>
                            <input type="text" name="preco" inputmode="decimal" class="price-input"
                                placeholder="Digite o preço" pattern="^\d+(\.\d{0,2})?$"
                                value="<?= htmlspecialchars($jogo['preco'] ?? '') ?>"
                                oninput="this.value=this.value.replace(/[^0-9.]/g,'').replace(/(\..*)\./g,'$1');"
                                required>
                        </div>

                        <div class="form-group">
                            <label>Informe o desconto a ser aplicado (%):</label>
                            <input type="number" name="desconto" min="0" max="100" class="no-spinners"
                                placeholder="Porcentagem do desconto"
                                value="<?= htmlspecialchars($jogo['desconto'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Categorias do jogo:</label>

                            <div id="catControl" class="cat-control" tabindex="0" aria-haspopup="listbox"
                                aria-expanded="false">

                                <!-- Chips de categorias -->
                                <div class="cat-chips" id="catChips">
                                    <span id="catPlaceholder" class="cat-placeholder">Busque e selecione...</span>
                                </div>
                                <button type="button" class="cat-caret" aria-label="Abrir categorias">
                                    <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
                                        <path fill="currentColor" d="M7 10l5 5 5-5z" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Dropdown para escolha -->
                            <div id="catDropdown" class="cat-dropdown" role="listbox" aria-label="Categorias">
                                <div class="cat-search">
                                    <input id="catSearch" type="text" placeholder="Buscar">
                                </div>

                                <div id="catGrid" class="cat-grid">
                                    <?php

                                    // Garante a lista de todas as categorias do banco
                                    $res = mysqli_query($conn, "SELECT id, nome FROM categorias ORDER BY nome ASC");
                                    while ($r = mysqli_fetch_assoc($res)):
                                        $id = (int) $r['id'];
                                        $nm = htmlspecialchars($r['nome'], ENT_QUOTES, 'UTF-8');
                                        ?>
                                        <button type="button" class="cat-option" data-id="<?= $id ?>" data-name="<?= $nm ?>"
                                            aria-selected="false">
                                            <?= $nm ?>
                                        </button>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                            <div id="catHiddenInputs"></div>
                        </div>





                        <div class="form-group">
                            <label for="classificacao_ind">Classificação indicativa:</label>

                            <!-- Dropdown para escolha -->
                            <div class="rating-control">
                                <select id="classificacao_ind" name="classificacao_ind" required>
                                    <option value="" disabled <?= $selCI ? '' : 'selected' ?>>Selecione a classificação
                                    </option>
                                    <?php while ($ci = mysqli_fetch_assoc($ratings)): ?>
                                        <?php $id = (int) $ci['id']; ?>
                                        <option value="<?= $id ?>" <?= ($selCI === $id) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($ci['tipo']) ?> — <?= htmlspecialchars($ci['descricao']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>

                                <span class="select-caret" aria-hidden="true">
                                    <svg width="18" height="18" viewBox="0 0 24 24">
                                        <path d="M7 10l5 5 5-5z" fill="#d0d7e6" />
                                    </svg>
                                </span>
                            </div>
                        </div>

                        <!-- Campo para disponilizar conteúdos da classificação -->
                        <div class="form-group">
                            <label>Conteúdo que justifica a classificação:</label>
                            <textarea name="conteudo" rows="3"
                                placeholder="Ex.: Violência moderada; Linguagem; Compras no jogo"><?= htmlspecialchars($jogo['conteudo'] ?? '') ?></textarea>

                        </div>

                        <!-- Imagem obrigatória para o banner -->
                        <div class="form-group">
                            <label>Informe a URL de uma imagem para o banner:</label>
                            <input type="text" name="url_banner" placeholder="Digite a url do banner"
                                value="<?= htmlspecialchars($jogo['url_banner'] ?? '') ?>" required>
                        </div>

                        <!-- Cadastro de requisitos mínimos -->
                        <div class="form-group">
                            <label>Requisitos mínimos:</label>
                            <button type="button" class="req-btn" data-bs-toggle="modal" data-bs-target="#reqModal">
                                <span>Definir requisitos mínimos</span>
                            </button>
                        </div>

                        <!-- Modal de requisitos (dentro do formulário) -->
                        <div class="modal fade" id="reqModal" tabindex="-1" aria-labelledby="reqModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content req-modal">
                                    <div class="modal-header border-0">
                                        <h5 class="modal-title" id="reqModalLabel">Requisitos Mínimos</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                            aria-label="Fechar"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="req-modal-grid">
                                            <div class="req-modal-item">
                                                <label class="req-modal-label">Processador:</label>
                                                <input type="text" name="req_processador"
                                                    value="<?= htmlspecialchars($jogo['req_processador'] ?? '') ?>"
                                                    placeholder="Modelo mínimo recomendado">
                                            </div>
                                            <div class="req-modal-item">
                                                <label class="req-modal-label">Memória:</label>
                                                <input type="text" name="req_memoria"
                                                    value="<?= htmlspecialchars($jogo['req_memoria'] ?? '') ?>"
                                                    placeholder="Memória mínima recomendada">
                                            </div>
                                            <div class="req-modal-item">
                                                <label class="req-modal-label">Placa de vídeo:</label>
                                                <input type="text" name="req_placa_video"
                                                    value="<?= htmlspecialchars($jogo['req_placa_video'] ?? '') ?>"
                                                    placeholder="Modelo mínimo recomendado">
                                            </div>
                                            <div class="req-modal-item">
                                                <label class="req-modal-label">Sistema operacional.:</label>
                                                <input type="text" name="req_sistema_op"
                                                    value="<?= htmlspecialchars($jogo['req_sistema_op'] ?? '') ?>"
                                                    placeholder="Versão mínima recomendada">
                                            </div>
                                            <div class="req-modal-item">
                                                <label class="req-modal-label">Armazenamento:</label>
                                                <input type="text" name="req_armazenamento"
                                                    value="<?= htmlspecialchars($jogo['req_armazenamento'] ?? '') ?>"
                                                    placeholder="Espaço mínimo recomendado">
                                            </div>
                                            <div class="req-modal-item">
                                                <label class="req-modal-label">DirectX:</label>
                                                <input type="text" name="req_directx"
                                                    value="<?= htmlspecialchars($jogo['req_directx'] ?? '') ?>"
                                                    placeholder="Versão mínima recomendada">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0">
                                        <button type="button" class="btn-detail" data-bs-dismiss="modal">
                                            Salvar requisitos
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="divider">

                        <h1>Adicionar Imagens</h1>
                        <br>

                        <!-- Cadastro de imagens do jogo (primeira sendo obrigatória) -->
                        <div class="form-group">
                            <label>URL da primeira imagem (obrigatório):</label>
                            <input type="text" name="url_1" placeholder="Digite a url da imagem"
                                value="<?= htmlspecialchars($jogo['url_1'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label>URL da segunda imagem (opcional):</label>
                            <input type="text" name="url_2" placeholder="Digite a url da imagem"
                                value="<?= htmlspecialchars($jogo['url_2'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label>URL da terceira imagem (opcional):</label>
                            <input type="text" name="url_3" placeholder="Digite a url da imagem"
                                value="<?= htmlspecialchars($jogo['url_3'] ?? '') ?>">

                        </div>

                        <div class="form-group">
                            <label>URL da quarta imagem (opcional):</label>
                            <input type="text" name="url_4" placeholder="Digite a url da imagem"
                                value="<?= htmlspecialchars($jogo['url_4'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label>URL da quinta imagem (opcional):</label>
                            <input type="text" name="url_5" placeholder="Digite a url da imagem"
                                value="<?= htmlspecialchars($jogo['url_5'] ?? '') ?>">
                        </div>

                        <button type="submit" class="btn-detail">Cadastrar Jogo</button>
                        <button type="reset" class="btn-detail">Limpar</button>

                    </form>
                </div>
            </div>

        </section>

        <footer>
            <p>&copy; 2025 Moonlight. Todos os direitos reservados.</p>
        </footer>
    </div>

    <style>
        .main-content {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            flex: 1;
            padding: 20px 0 10px 0;
        }

        h1 {
            width: 100%;
            max-width: 1600px;
            color: #fff;
            font-size: 2rem;
            font-weight: bold;
        }

        .form-container {
            background: linear-gradient(to bottom, #202330ff 0%, #272b3fff 50%);
            padding: 30px;
            border-radius: 10px;
            width: 700px;
            height: auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        .form-container form {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .form-container .form-inline {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 7px;
        }

        textarea {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            border: none;
            width: 100%;
            height: 100px;
            color: #fff;
            padding: 10px;
            outline: none;
            resize: none !important;
        }

        textarea::placeholder {
            color: rgba(206, 196, 196, 0.56);
            font-size: 14px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .form-group label {
            font-size: 14px;
            margin-bottom: 6px;
            color: #fff;
        }

        .form-group input {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 4px;
            padding: 8px 10px;
            outline: none;
            width: 100%;
            height: 45px;
            color: #fff;
        }

        .form-group input::placeholder {
            color: rgba(206, 196, 196, 0.56);
            font-size: 14px;
        }

        .radio-group {
            display: flex;
            align-items: center;
            gap: 20px;
            border-radius: 4px;
            padding: 8px 10px;
            width: 100%;
            height: 35px;
        }

        .radio-group input[type="radio"] {
            margin-right: 6px;
        }

        .radio-group label {
            display: flex;
            align-items: center;
            font-size: 15px;
            color: #fff;
        }

        .cat-control {
            width: 100%;
            min-height: 40px;
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            outline: none;
            border-radius: 4px;
            padding: 8px 12px;
            position: relative;
        }

        .cat-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            flex: 1 1 auto;
        }

        .cat-placeholder {
            color: rgba(206, 196, 196, 0.56);
            font-size: 14px;
        }

        .cat-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #31405cff;
            color: #e8f0ff;
            border-radius: 6px;
            padding: 5px 8px;
            font-size: 14px;
        }

        .cat-chip button {
            all: unset;
            cursor: pointer;
            opacity: .8;
        }

        .cat-chip button:hover {
            opacity: 1;
        }

        .cat-caret {
            all: unset;
            cursor: pointer;
            display: flex;
            align-items: center;
            padding: 6px;
            border-radius: 6px;
        }

        .cat-caret:hover {
            background: #334056;
        }

        .cat-dropdown {
            position: absolute;
            left: 0;
            right: 0;
            top: calc(100% + 12px);
            background: #121826;
            border-radius: 10px;
            box-shadow: 0 12px 32px rgba(0, 0, 0, .45);
            border: 1px solid rgba(255, 255, 255, .06);
            z-index: 50;
            display: none;
        }

        .cat-dropdown.open {
            display: block;
        }

        .cat-search {
            padding: 10px 12px;
            border-bottom: 1px solid rgba(255, 255, 255, .06);
        }

        .cat-search input {
            width: 100%;
            height: 38px;
            border: none;
            outline: none;
            border-radius: 8px;
            padding: 0 12px;
            color: #eaeefb;
            background: #1a2233;
        }

        .cat-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
            padding: 12px;
            max-height: 320px;
            overflow: auto;
        }

        @media (max-width:900px) {
            .cat-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width:640px) {
            .cat-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .cat-option {
            all: unset;
            cursor: pointer;
            text-align: left;
            padding: 10px 12px;
            border-radius: 8px;
            color: #e3e8f7;
            background: transparent;
        }

        .cat-option:hover {
            background: #1b2740;
        }

        .cat-option[aria-selected="true"] {
            background: #263b6b;
        }

        .form-group {
            position: relative;
        }

        .form-group input,
        .form-group select {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 4px;
            padding: 8px 10px;
            outline: none;
            width: 100%;
            height: 45px;
            color: #fff;
            box-shadow: none;
        }

        .rating-control {
            position: relative;
            width: 100%;
            height: 45px;
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 4px;
            padding: 0;
        }

        .rating-control select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background: transparent !important;
            border: none !important;
            outline: none;
            color: #fff;
            width: 100%;
            height: 100%;
            padding: 0 38px 0 10px;
            font: inherit;
        }

        .rating-control select:invalid {
            color: rgba(206, 196, 196, .56);
            font-size: 14px;
        }

        .select-caret {
            position: absolute;
            right: 6px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 6px;
            border-radius: 6px;
            transition: background .15s ease;
            pointer-events: none;
        }

        .rating-control:hover .select-caret {
            background: #334056;
        }

        .rating-control select {
            color-scheme: dark;
        }

        .rating-control select option {
            background: #121826;
            color: #e8edf9;
        }

        .btn-detail {
            background: #7aa3ef;
            border-radius: 4px;
            border: none;
            padding: 6px 10px;
            cursor: pointer;
            color: #fff;
            font-size: 14px;
            width: 200px;
            align-self: center;
        }

        .btn-detail:hover {
            background: #345aa1ff;
            color: rgb(126, 171, 255);
            transition: color 0.3s;
        }

        .form-container .btn-detail+.btn-detail {
            margin-top: 9px;
        }

        .no-spinners::-webkit-outer-spin-button,
        .no-spinners::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .date-custom::-webkit-datetime-edit {
            color: rgba(255, 255, 255, 1);
            font-size: 15px;
        }

        .req-btn {
            width: 100%;
            height: 45px;
            border-radius: 4px;
            border: none;
            background: rgba(255, 255, 255, 0.1);
            color: rgba(206, 196, 196, 0.56);
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 10px;
            cursor: pointer;
        }

        .req-btn:hover {
            background: rgba(255, 255, 255, 0.18);
        }

        .req-modal {
            background: linear-gradient(to bottom, #202330ff 0%, #272b3fff 50%);
            color: #fff;
            border-radius: 12px;
        }

        .req-modal-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            column-gap: 18px;
            row-gap: 10px;
            font-size: 14px;
            text-align: left;
        }

        .req-modal-item input {
            width: 100%;
            height: 40px;
            border-radius: 6px;
            border: none;
            outline: none;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            padding: 8px 10px;
        }

        .req-modal-item input::placeholder {
            color: rgba(206, 196, 196, 0.56);
            font-size: 14px;
        }

        @media (max-width: 576px) {
            .req-modal-grid {
                grid-template-columns: 1fr;
            }
        }

        #reqModal .modal-header .btn-close {
            all: unset;
            cursor: pointer;
            position: relative;
            margin-left: auto;
            width: 22px;
            height: 22px;
        }

        #reqModal .modal-header .btn-close::before {
            content: '×';
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 700;
            line-height: 1;
            color: #fff;
        }

        #reqModal .modal-header .btn-close:hover::before {
            color: #7aa3ef;
        }

        #reqModal .modal-header .btn-close:focus {
            outline: none;
            box-shadow: none;
        }
    </style>

    <!-- Script do campo de categorias -->
    <script>
        (() => {
            const control = document.getElementById('catControl');
            const dropdown = document.getElementById('catDropdown');
            const searchInp = document.getElementById('catSearch');
            const grid = document.getElementById('catGrid');
            const chipsBox = document.getElementById('catChips');
            const placeholder = document.getElementById('catPlaceholder');
            const hiddenBox = document.getElementById('catHiddenInputs');

            const selected = new Map(); // De ID para nome cadastrado

            function openDD() {
                dropdown.classList.add('open');
                control.setAttribute('aria-expanded', 'true');
                // foca na busca sem “pular” a página
                setTimeout(() => searchInp?.focus({ preventScroll: true }), 0);
            }
            function closeDD() {
                dropdown.classList.remove('open');
                control.setAttribute('aria-expanded', 'false');
            }

            // Abre/fecha ao clicar no campo
            control.addEventListener('click', (e) => {
                if (dropdown.classList.contains('open')) closeDD(); else openDD();
            });

            // Fecha ao clicar fora
            document.addEventListener('click', (e) => {
                if (!control.contains(e.target) && !dropdown.contains(e.target)) closeDD();
            });

            // Filtro de opções
            searchInp.addEventListener('input', () => {
                const q = searchInp.value.trim().toLowerCase();
                grid.querySelectorAll('.cat-option').forEach(btn => {
                    const hit = btn.dataset.name.toLowerCase().includes(q);
                    btn.style.display = hit ? '' : 'none';
                });
            });

            // Ativa/desativa a seleção de uma categoria
            grid.addEventListener('click', (e) => {
                const btn = e.target.closest('.cat-option');
                if (!btn) return;
                const id = btn.dataset.id, name = btn.dataset.name;
                if (selected.has(id)) { deselect(id); }
                else { select(id, name); }
            });

            function select(id, name) {
                selected.set(id, name);

                // Marca o selecionado no dropdown
                const opt = grid.querySelector(`.cat-option[data-id="${CSS.escape(id)}"]`);
                if (opt) opt.setAttribute('aria-selected', 'true');

                // Chips
                const chip = document.createElement('span');
                chip.className = 'cat-chip'; chip.dataset.id = id;
                chip.innerHTML = `${name} <button title="Remover" aria-label="Remover">×</button>`;
                chip.querySelector('button').addEventListener('click', () => deselect(id));
                chipsBox.appendChild(chip);

                const inp = document.createElement('input');
                inp.type = 'hidden'; inp.name = 'categorias[]'; inp.value = id; inp.id = `cat-hidden-${id}`;
                hiddenBox.appendChild(inp);
                updatePlaceholder();
            }

            function deselect(id) {
                selected.delete(id);
                const opt = grid.querySelector(`.cat-option[data-id="${CSS.escape(id)}"]`);
                if (opt) opt.setAttribute('aria-selected', 'false');
                const chip = chipsBox.querySelector(`.cat-chip[data-id="${CSS.escape(id)}"]`);
                if (chip) chip.remove();
                const inp = document.getElementById(`cat-hidden-${id}`);
                if (inp) inp.remove();
                updatePlaceholder();
            }

            function updatePlaceholder() {
                placeholder.style.display = selected.size ? 'none' : 'inline';
            }

            // Pré-seleciona categorias ao reabrir após erro
            const pre = <?= json_encode($categoriasDoJogo, JSON_UNESCAPED_UNICODE) ?>;
            pre.forEach(id => {
                const btn = grid.querySelector(`.cat-option[data-id="${id}"]`);
                if (btn) select(String(id), btn.dataset.name);
            });

        })();
    </script>

</body>
</html>