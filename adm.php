<?php
require_once 'include/verifica.php';

// Proteção da página 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
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

    <title>Moonlight - Painel ADM</title>
</head>

<body>
        <!-- Modal de detalhes dos jogos -->
    <div class="modal fade" id="modalJogo" tabindex="-1" aria-labelledby="modalJogoLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalJogoLabel">Detalhes do Jogo</h5>
                </div>

                <div class="modal-body">
                    <div class="game-detail-top">

                        <!-- Conteúdo e organização dentro do modal -->
                        <div class="game-detail-meta">
                            <div class="game-meta-list">
                                <div class="game-meta-row">
                                    <div class="game-meta-label">Lançamento</div>
                                    <div class="game-meta-value" id="m-lancamento">—</div>
                                </div>
                                <div class="game-meta-row">
                                    <div class="game-meta-label">Desconto</div>
                                    <div class="game-meta-value" id="m-desconto">—</div>
                                </div>
                                <div class="game-meta-row">
                                    <div class="game-meta-label">Classificação Ind.</div>
                                    <div class="game-meta-value" id="m-classificacao">—</div>
                                </div>
                                <div class="game-meta-row">
                                    <div class="game-meta-label">Conteúdo</div>
                                    <div class="game-meta-value" id="m-conteudo">—</div>
                                </div>
                                <div class="game-meta-row">
                                    <div class="game-meta-label">Categoria</div>
                                    <div class="game-meta-value" id="m-categoria">—</div>
                                </div>
                            </div>
                        </div>

                        <div class="banner-box">
                            <img id="m-banner" alt="Banner do jogo">
                        </div>
                    </div>

                    <hr class="game-detail-separator">

                    <div class="game-detail-resumo">
                        <div class="game-detail-resumo-header">Resumo</div>
                        <div class="game-detail-resumo-text" id="m-resumo">—</div>
                    </div>
                </div>

                <div class="modal-footer justify-content-end gap-2">
                    <form id="form-delete" action="excluirJogos.php" method="post" class="m-0 d-inline">
                        <input type="hidden" name="id" id="m-id-hidden">
                        <button type="submit" class="btn btn-danger">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-trash3" viewBox="0 0 16 16">
                                <path
                                    d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5" />
                            </svg>
                        </button>
                    </form>

                    <a id="m-editar" href="#" class="btn btn-warning">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="bi bi-pencil-square" viewBox="0 0 16 16">
                            <path
                                d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                            <path fill-rule="evenodd"
                                d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z" />
                        </svg>
                    </a>

                    <button type="button" class="btn-detail" data-bs-dismiss="modal">Cancelar</button>
                </div>

            </div>
        </div>
    </div>


    <!-- Modal de detalhes das categorias -->
    <div class="modal fade" id="modalCat" tabindex="-1" aria-labelledby="modalCatLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" style="border-bottom:1px solid #4e5c72;">
                    <h5 class="modal-title" id="modalCatLabel">Detalhes da Categoria</h5>
                </div>

                <!-- Conteúdo e organização dentro do modal -->
                <div class="modal-body">
                    <div class="cat-detail-wrapper">
                        <dl class="cat-detail-list">
                            <dt>Descrição</dt>
                            <dd id="m-descricao">—</dd>
                        </dl>
                    </div>
                </div>

                <div class="modal-footer justify-content-end gap-2" style="border-top:1px solid #4e5c72;">

                    <form id="form-delete-cat" action="excluirCat.php" method="post" class="m-0 d-inline">
                        <input type="hidden" name="id" id="c-id-hidden">
                        <button type="submit" class="btn btn-danger">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3" viewBox="0 0 16 16">
                                <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5" />
                            </svg>
                        </button>
                    </form>

                    <a id="c-editar" href="#" class="btn btn-warning">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                            <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                            <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z" />
                        </svg>
                    </a>

                    <button type="button" class="btn-detail" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <?php

    // Para possíveis erros em desenvolvimento
    error_reporting(E_ALL);
    ini_set('display_errors', 1)
    ?>

    <?php $NAV_CONTEXT = 'admin'; require_once 'include/navbar.php'; ?>

    <?php if (!empty($_SESSION['flash'])):
        $tipo = ($_SESSION['flash']['tipo'] ?? 'success');
        $msg = trim($_SESSION['flash']['msg'] ?? '');
        unset($_SESSION['flash']);
        ?>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
            if (!window.mlToast) return;

            const kind = <?= json_encode($tipo === 'success' ? 'ok' : 'err') ?>;
            const text = <?= json_encode($msg, JSON_UNESCAPED_UNICODE) ?>;

            window.mlToast(kind, text);
        });
        </script>

    <?php endif; ?>

    <!-- Recepção do adm na tela de controle -->
    <h1>Bem-vindo, <?php echo htmlspecialchars($_SESSION['username']) ?> (Admin) </h1>

    <div class="content">

        <section class="consultas">

            <div class="jogosC">
                <div class="container">
                    <h2>Jogos Cadastrados</h2>
                    <br>

                    <?php

                    // Formatador de data + retorna "em breve" se null
                    $fmtData = function (?string $d): string {
                        if (!$d)
                            return 'Em breve';
                        $ts = strtotime($d);
                        return $ts ? date('d/m/Y', $ts) : 'Em breve';
                    };

                    // Ordenação em ordem alfabética
                    $ord = $_GET['ord'] ?? 'az';
                    $direcao = ($ord === 'za') ? 'DESC' : 'ASC';

                    // Puxa as informações dos jogos no banco 
                   $sql = "
                    SELECT 
                        j.*,
                        ci.tipo      AS ci_tipo,
                        ci.descricao AS ci_desc,
                        GROUP_CONCAT(DISTINCT cat.nome ORDER BY cat.nome SEPARATOR ', ') AS categorias
                        FROM jogos j
                        LEFT JOIN classificacao_ind ci
                            ON ci.id = j.classificacao_ind
                        -- TABELA-PONTE jogo <-> categorias 
                        LEFT JOIN jogos_categorias jc
                            ON jc.jogo_id = j.id
                        LEFT JOIN categorias cat
                            ON cat.id = jc.categoria_id
                        GROUP BY j.id
                        ORDER BY j.nome $direcao
                    ";
                    $resultado = mysqli_query($conn, $sql);
                    ?>

                    <!-- Formulário da order bar; troca a ordem e recarrega a página -->
                    <form method="get" class="orderbar">
                        <label for="ord">Ordenar:</label>
                        <select id="ord" name="ord" onchange="this.form.submit()">
                            <option value="az" <?= $ord === 'az' ? 'selected' : '' ?>>Nome (A → Z)</option>
                            <option value="za" <?= $ord === 'za' ? 'selected' : '' ?>>Nome (Z → A)</option>
                        </select>
                    </form>

                    <?php if (mysqli_num_rows($resultado) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Status</th>
                                    <th>Nome</th>
                                    <th>Desenvolvedor</th>
                                    <th>Preço</th>
                                    <th>Detalhes</th>
                                </tr>
                            </thead>
                            <tbody>

                                <!-- Monta a tabela e o modal detalhando o jogo -->
                                <?php while ($row = mysqli_fetch_assoc($resultado)):
                                $id      = (int) $row['id'];
                                $st      = htmlspecialchars($row['status'] ?? '', ENT_QUOTES, 'UTF-8');
                                $nm      = htmlspecialchars($row['nome'] ?? '', ENT_QUOTES, 'UTF-8');
                                $dev     = htmlspecialchars($row['desenvolvedor'] ?? '', ENT_QUOTES, 'UTF-8');
                                $preco   = number_format(max(0,(float)$row['preco']), 2, ',', '.');
                                $res     = htmlspecialchars($row['resumo'] ?? '', ENT_QUOTES, 'UTF-8');
                                $dataB   = htmlspecialchars($row['data_lancamento'] ?? '', ENT_QUOTES, 'UTF-8');
                                $clasTipo= trim((string)($row['ci_tipo'] ?? ''));      // L, 10, 12, 14, 16, 18
                                $cont    = htmlspecialchars($row['conteudo'] ?? '', ENT_QUOTES, 'UTF-8');
                                $cat = htmlspecialchars($row['categorias'] ?? '', ENT_QUOTES, 'UTF-8');
                                $desc    = (int) ($row['desconto'] ?? 0);
                                $banner  = htmlspecialchars($row['url_banner'] ?? '', ENT_QUOTES, 'UTF-8');
                                ?>
                                <tr>
                                    <td><?= $id ?></td>
                                    <td><?= $st ?></td>
                                    <td><?= $nm ?></td>
                                    <td><?= $dev ?></td>
                                    <td>R$ <?= $preco ?></td>
                                    <td>
                                        <button type="button" class="btn-detail"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalJogo"
                                            data-id="<?= $id ?>"
                                            data-nome="<?= $nm ?>"
                                            data-resumo="<?= $res ?>"
                                            data-lancamento="<?= $dataB ?>"
                                            data-desconto="<?= $desc ?>%"
                                            data-banner="<?= $banner ?>"
                                            data-classificacao="<?= $clasTipo !== '' ? $clasTipo : '—' ?>"
                                            data-conteudo="<?= $cont ?>"
                                            data-categoria="<?= $cat ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                                                <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>

                    <?php else: ?>
                        <div class='no-data'>Nenhum jogo cadastrado/encontrado.</div>
                    <?php endif; ?>

                </div>
            </div>

            <br><br><br>

            <div class="categoriasC">
                <div class="container">
                    <h2>Categorias Cadastradas</h2>
                    <br>

                    <?php
                    // Ordenação em ordem alfabética
                    $ordCat   = $_GET['ord_cat'] ?? 'az';
                    $direcao  = ($ordCat === 'za') ? 'DESC' : 'ASC';
                    $sql      = "SELECT id, nome, descricao, status FROM categorias ORDER BY nome $direcao";
                    $resultado = mysqli_query($conn, $sql);
                    ?>

                    <!-- Formulário da order bar; troca a ordem e recarrega a página -->
                    <form method="get" class="orderbar" style="margin-bottom:12px;">
                        <label for="ord_cat">Ordenar:</label>
                        <select id="ord_cat" name="ord_cat" onchange="this.form.submit()">
                            <option value="az" <?= $ordCat === 'az' ? 'selected' : '' ?>>Nome (A → Z)</option>
                            <option value="za" <?= $ordCat === 'za' ? 'selected' : '' ?>>Nome (Z → A)</option>
                        </select>
                    </form>

                    <?php if (mysqli_num_rows($resultado) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Status</th>
                                    <th>Nome</th>
                                    <th>Detalhes</th>
                                </tr>
                            </thead>
                            <tbody>

                                <!-- Monta a tavela e o detalhamento de categoria -->
                                <?php while ($row = mysqli_fetch_assoc($resultado)):
                                    $id = (int) $row['id'];
                                    $st = htmlspecialchars($row['status'] ?? '', ENT_QUOTES, 'UTF-8');
                                    $nm = htmlspecialchars($row['nome'] ?? '', ENT_QUOTES, 'UTF-8');
                                    $desc = htmlspecialchars($row['descricao'] ?? '', ENT_QUOTES, 'UTF-8');
                                    $clasTipo = htmlspecialchars($row['ci_tipo'] ?? '', ENT_QUOTES, 'UTF-8');   // L, 10, 12, 14...
                                    ?>
                                    <tr>
                                        <td><?= $id ?></td>
                                        <td><?= $st ?></td>
                                        <td><?= $nm ?></td>
                                        <td>
                                            <button type="button" class="btn-detail" data-bs-toggle="modal"
                                                data-bs-target="#modalCat" data-id="<?= $id ?>" 
                                                data-nome="<?= $nm ?>" data-status="<?= $st ?>" 
                                                data-descricao="<?= htmlspecialchars($row['descricao'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                                    <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z" />
                                                    <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>

                    <?php else: ?>
                        <div class='no-data'>Nenhuma categoria cadastrada/encontrada.</div>
                    <?php endif; ?>

                </div>
            </div>

        </section>

    <footer>
        <p>&copy; 2025 Moonlight. Todos os direitos reservados.</p>
    </footer>
    </div>

    <style>
        .content {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .container {
            background: linear-gradient(to bottom, #202330ff 0%, #272b3fff 50%);
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: left;
            min-height: 100vh;
            background: #202330ff;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 1600px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            font-size: 15px;
        }

        .jogosC .container,
        .categoriasC .container {
            min-height: auto;
        }

        h1 {
            width: 100%;
            max-width: 1600px;
            margin: 40px auto auto;
            color: #fff;
            font-size: 2rem;
            font-weight: bold;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #202330ff;
            border-radius: 10px;
            overflow: hidden;
        }

        th,
        td {
            padding: 12px 16px;
            text-align: center;
            border-bottom: 1px solid #4e5c72ff;
        }

        th {
            background-color: #202330ff;
            color: #fff;
        }

        tr:nth-child(even) {
            background-color: #272b3fff;
        }

        tr:hover {
            background-color: #272b3fff;
        }

        .no-data {
            text-align: center;
            padding: 1rem;
            background-color: #272b3fff;
            color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }

        .orderbar select {
            background: #2a2f35;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 6px 10px;
        }

        .orderbar label {
            color: #fff;
            font-weight: 600;
        }

        #modalJogo .modal-dialog {
            max-width: min(900px, calc(100vw - 2rem));
        }

        #modalJogo .modal-content {
            background: #202330;
            border: 1px solid #4e5c72;
            color: #fff;
            border-radius: 20px;
            overflow: hidden;
        }

        #modalJogo .modal-header {
            border-bottom: 1px solid #4e5c72;
            padding: 14px 24px;
        }

        #modalJogo .modal-title {
            font-size: 1.1rem;
            font-weight: 700;
        }

        #modalJogo .modal-body {
            padding: 16px 24px 18px;
            overflow-x: clip;
        }

        #modalJogo .game-detail-top {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(260px, 1fr);
            gap: 24px;
            align-items: flex-start;
        }

        #modalJogo .game-meta-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        #modalJogo .game-meta-row {
            display: grid;
            grid-template-columns: 140px minmax(0, 1fr);
            column-gap: 14px;
            align-items: baseline;
        }

        #modalJogo .game-meta-label {
            font-weight: 600;
        }

        #modalJogo .game-meta-value {
            font-size: 0.95rem;
        }

        #modalJogo .banner-box {
            border-radius: 12px;
            border: 1px solid #3b465e;
            background: #141827;
            overflow: hidden;
            min-height: 150px;
        }

        #modalJogo .banner-box img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        #modalJogo .game-detail-separator {
            border: 0;
            border-top: 1px solid #3b465e;
            margin: 18px 0 14px;
        }

        #modalJogo .game-detail-resumo-header {
            font-weight: 600;
            margin-bottom: 6px;
        }

        #modalJogo .game-detail-resumo-text {
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 0.95rem;
            white-space: pre-line;
            min-height: 80px;
        }

        #modalJogo .modal-footer {
            border-top: 1px solid #4e5c72;
            padding: 10px 24px 14px;
            gap: 10px;
        }

        @media (max-width: 768px) {
            #modalJogo .game-detail-top {
                grid-template-columns: 1fr;
            }

            #modalJogo .banner-box {
                min-height: 130px;
            }
        }

        #modalCat .modal-dialog {
            max-width: min(700px, calc(100vw - 2rem));
            margin: 1.5rem auto;
        }

        #modalCat .modal-content {
            background: #202330ff;
            border: 1px solid #4e5c72;
            color: #fff;
            border-radius: 20px;
            overflow-x: hidden;
        }

        #modalCat .modal-header {
            border-bottom: 1px solid #4e5c72;
            padding: 14px 24px;
        }

        #modalCat .modal-title {
            font-size: 1.1rem;
            font-weight: 700;
            }

        #modalCat .modal-body {
            padding: 16px 24px 18px;
        }

        #modalCat .modal-footer {
            border-top: 1px solid #4e5c72;
            padding: 10px 24px 14px;
            gap: 10px;
        }

        #modalCat #m-descricao {
            white-space: normal;
            word-wrap: break-word; 
        }

        .btn-detail {
            background: #7aa3ef;
            border-radius: 4px;
            border: none;
            padding: 6px 10px;
            cursor: pointer;
            color: #fff;
            font-size: 14px;
        }

        .btn-detail:hover {
            background: #345aa1ff;
            color: rgb(126, 171, 255);
            transition: color 0.3s;
        }

        .orderbar {
            display: flex;
            gap: 8px;
            align-items: center;
            justify-content: flex-start;
            margin: 0 0 12px 0;
        }

        .orderbar label {
            color: #fff;
            font-weight: bold;
        }

        .orderbar select {
            background: #2a2f35 !important;
            color: #fff !important;
            outline: none;
            border-radius: 6px;
            padding: 6px 10px;
            appearance: auto;
        }

        footer {
            margin-top: auto;
            text-align: center;
            color: #fff;
            padding: 15px 0;
            font-size: 0.9rem;
            background: transparent;
        }
    </style>

    <!-- Script dos modais -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalEl = document.getElementById('modalJogo');
            const formDelete = document.getElementById('form-delete');
            const hiddenId = document.getElementById('m-id-hidden');
            const btnEdit = document.getElementById('m-editar');

            modalEl.addEventListener('show.bs.modal', function (event) {
                const btn = event.relatedTarget;
                const pick = (name) => (btn?.getAttribute(name) || '—');

                // Detalhamento do jogo
                this.querySelector('#m-lancamento').textContent = pick('data-lancamento');
                this.querySelector('#m-desconto').textContent = pick('data-desconto');
                this.querySelector('#m-classificacao').textContent = pick('data-classificacao');
                this.querySelector('#m-conteudo').textContent = pick('data-conteudo');
                this.querySelector('#m-categoria').textContent = pick('data-categoria');
                this.querySelector('#m-resumo').textContent = pick('data-resumo');

                // Banner
                const banner = pick('data-banner');
                const img = this.querySelector('#m-banner');
                if (banner && banner !== '—') { img.src = banner; img.alt = 'Banner do jogo'; }
                else { img.removeAttribute('src'); img.alt = 'Sem banner'; }

                // Ações
                const id = pick('data-id');
                const nome = pick('data-nome');
                hiddenId.value = /^\d+$/.test(id) ? id : '';
                btnEdit.href = hiddenId.value ? ('editarJogos.php?id=' + encodeURIComponent(id)) : '#';

                // Guarda o nome pora usar no confirm
                formDelete.dataset.nome = nome;

                // Desabilita o botões se ID for inválido
                formDelete.querySelector('button[type="submit"]').disabled = !hiddenId.value;
                btnEdit.classList.toggle('disabled', !hiddenId.value);
            });

            // Confirm na hora de excluir
            formDelete.addEventListener('submit', function (e) {
                const id = hiddenId.value;
                if (!id) { e.preventDefault(); alert('ID inválido para exclusão.'); return; }
                const nome = (formDelete.dataset.nome || 'este jogo').trim();
                const ok = confirm(`Tem certeza que deseja excluir "${nome}"? Esta ação não pode ser desfeita.`);
                if (!ok) e.preventDefault();
            });
        });


        document.addEventListener('DOMContentLoaded', function () {
            const modalCat = document.getElementById('modalCat');
            if (modalCat) {
                modalCat.addEventListener('show.bs.modal', function (event) {
                    const btn   = event.relatedTarget;
                    const id    = btn?.getAttribute('data-id') || '';
                    const nome  = btn?.getAttribute('data-nome') || '—';
                    const desc  = btn?.getAttribute('data-descricao') || '—';

                    // Detalhamento da categoria
                    const elDesc = modalCat.querySelector('#m-descricao');
                    if (elDesc) elDesc.textContent = desc;

                    const title = modalCat.querySelector('#modalCatLabel');
                    if (title) title.textContent = `Detalhes da Categoria`;

                    // Ações
                    const hidden = modalCat.querySelector('#c-id-hidden');
                    if (hidden) hidden.value = id;

                    const editar = modalCat.querySelector('#c-editar');
                    if (editar) editar.href = id ? ('editarCat.php?id=' + encodeURIComponent(id)) : '#';

                    const btnDel = modalCat.querySelector('#form-delete-cat button[type="submit"]');
                    if (btnDel) btnDel.disabled = !id;
                });
            }

            // Confirm na hora de excluir
            const formDelCat = document.getElementById('form-delete-cat');
            if (formDelCat) {
                formDelCat.addEventListener('submit', function (e) {
                    const id = document.getElementById('c-id-hidden')?.value || '';
                    if (!id || !confirm('Tem certeza que deseja excluir esta categoria?')) e.preventDefault();
                });
            }
        });
    </script>

</body>
</html>