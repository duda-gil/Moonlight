<?php
require_once __DIR__ . '/include/verifica.php';
require_once __DIR__ . '/include/conexao.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Somente adm tem acesso
if (($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: index.php');
    exit;
}

// Consulta o banco e busca as categorias e os jogos nelas cadastradas
$sql = "
    SELECT
        c.id   AS cat_id,
        c.nome AS cat_nome,
        j.id   AS jogo_id,
        j.nome AS jogo_nome,
        j.desenvolvedor,
        j.status
    FROM categorias c
    LEFT JOIN jogos_categorias jc ON jc.categoria_id = c.id
    LEFT JOIN jogos j             ON j.id = jc.jogo_id
    ORDER BY c.nome ASC, j.nome ASC
";
$res = $conn->query($sql);

// Lista de quantos jogos há em cada categoria + lista dos jogos 
$cats = [];
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $cid = (int) $r['cat_id'];

        if (!isset($cats[$cid])) {
            $cats[$cid] = [
                'id' => $cid,
                'nome' => $r['cat_nome'],
                'total' => 0,
                'jogos' => [],
            ];
        }

        if (!empty($r['jogo_id'])) {
            $cats[$cid]['total']++;
            $cats[$cid]['jogos'][] = [
                'id' => (int) $r['jogo_id'],
                'nome' => $r['jogo_nome'],
                'dev' => $r['desenvolvedor'],
                'status' => $r['status'],
            ];
        }
    }
}

// Ordenação dos nomes por ordem alfabética
$ord = $_GET['ord'] ?? 'az';

if (!empty($cats)) {
    $cats = array_values($cats);

    usort($cats, function (array $a, array $b) use ($ord) {
        $cmp = strcasecmp($a['nome'] ?? '', $b['nome'] ?? '');
        return ($ord === 'za') ? -$cmp : $cmp;
    });
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>Relatório - Jogos por categoria</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/stylesNav.css">

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
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 1600px;
            font-size: 15px;
        }

        .jogosC .container {
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
            font-size: 15px;
        }

        th {
            background-color: #202330ff;
            color: #fff;
            font-weight: 600;
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

        .btn-detail {
            background: #7aa3ef;
            border-radius: 4px;
            border: none;
            text-decoration: none;
            padding: 6px 10px;
            cursor: pointer;
            color: #fff;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-detail:hover {
            background: #345aa1ff;
            color: rgb(126, 171, 255);
            transition: color 0.3s;
        }

        footer {
            margin-top: auto;
            text-align: center;
            color: #fff;
            padding: 15px 0;
            font-size: 0.9rem;
            background: transparent;
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
            background: #2a2f35;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 6px 10px;
            outline: none;
            appearance: auto;
        }

        .cat-modal .modal-content {
            background: #202330;
            border: 1px solid #4e5c72;
            color: #fff;
            border-radius: 16px;
            overflow: hidden;
        }

        .cat-modal .modal-header,
        .cat-modal .modal-footer {
            border-color: #4e5c72;
        }

        .cat-modal .modal-header,
        .cat-modal .modal-body {
            font-size: 15px;
        }

        .cat-modal .modal-title {
            font-weight: 700;
        }

        .cat-modal .table {
            width: 100%;
            border-collapse: collapse;
            background-color: #202330ff;
            border-radius: 0;
        }

        .cat-modal .table thead th {
            background-color: #202330ff !important;
            color: #fff;
            font-weight: 600;
            padding: 12px 16px;
            text-align: center;
            border-bottom: 1px solid #4e5c72ff;
            font-size: 15px;
        }

        .cat-modal .table tbody td {
            padding: 12px 16px;
            text-align: center;
            border-bottom: 1px solid #5f6b88;
            font-size: 15px;
        }

        .cat-modal .table tbody tr:nth-child(odd)>* {
            background-color: #202330ff;
        }

        .cat-modal .table tbody tr:nth-child(even)>* {
            background-color: #272b3fff;
        }

        .cat-modal .table tbody tr:hover>* {
            background-color: #272b3fff;
        }

        .cat-modal .btn-ver-jogo {
            white-space: nowrap;
            font-size: 14px;
            padding: 6px 12px;
        }
    </style>
</head>

<body>

    <?php $NAV_CONTEXT = 'admin';
    require_once 'include/navbar.php'; ?>

    <div class="content">

        <h1>Jogos por categoria</h1>

        <section class="consultas">
            <div class="jogosC">
                <div class="container">

                    <p class="mb-3">
                        Veja quantos jogos existem em cada categoria e use a coluna <strong>Jogos</strong> para listar
                        os títulos.
                    </p>

                    <!-- Informações das categorias dentro do container -->
                    <?php if (!empty($cats)): ?>

                        <form method="get" class="orderbar">
                            <label for="ord">Ordenar:</label>
                            <select id="ord" name="ord" onchange="this.form.submit()">
                                <option value="az" <?= $ord === 'az' ? 'selected' : '' ?>>Nome (A → Z)</option>
                                <option value="za" <?= $ord === 'za' ? 'selected' : '' ?>>Nome (Z → A)</option>
                            </select>
                        </form>

                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Categoria</th>
                                    <th>Quantidade de jogos</th>
                                    <th>Jogos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cats as $cat): ?>
                                    <tr>
                                        <td><?= (int) $cat['id'] ?></td>
                                        <td><?= htmlspecialchars($cat['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= (int) $cat['total'] ?></td>
                                        <td>
                                            <?php if ($cat['total'] > 0): ?>
                                                <button type="button" class="btn-detail" data-bs-toggle="modal"
                                                    data-bs-target="#catModal<?= (int) $cat['id'] ?>">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            <?php else: ?>
                                                <span class="text-white">Sem jogos</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class='no-data'>Nenhuma categoria cadastrada/encontrada.</div>
                    <?php endif; ?>

                </div>
            </div>
        </section>
    </div>

    <!-- Modal dos detalhes dos jogos de cada categoria -->
    <?php if (!empty($cats)): ?>
        <?php foreach ($cats as $cat): ?>
            <div class="modal fade cat-modal" id="catModal<?= (int) $cat['id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title mb-0">
                                Categoria: <?= htmlspecialchars($cat['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </h5>
                        </div>

                        <div class="modal-body">
                            <?php if (empty($cat['jogos'])): ?>
                                <p class="text-muted mb-0">Nenhum jogo cadastrado nesta categoria.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-dark table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Status</th>
                                                <th>Nome</th>
                                                <th>Desenvolvedor</th>
                                                <th>Detalhes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($cat['jogos'] as $j): ?>
                                                <?php
                                                $statusRaw = strtolower(trim((string) ($j['status'] ?? '')));
                                                $isAtivo = in_array($statusRaw, ['ativo', '1', 'true', 'on'], true);
                                                $statusClass = $isAtivo ? 'ativo' : 'inativo';
                                                ?>
                                                <tr>
                                                    <td><?= (int) $j['id'] ?></td>
                                                    <td>
                                                        <span class="status <?= $statusClass ?>">
                                                            <?= $isAtivo ? 'ativo' : 'inativo' ?>
                                                        </span>
                                                    </td>
                                                    <td><?= htmlspecialchars($j['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td><?= htmlspecialchars($j['dev'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td>
                                                        <a href="jogo.php?id=<?= (int) $j['id'] ?>" class="btn-detail btn-ver-jogo"
                                                            target="_blank">
                                                            Ver jogo
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn-detail" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <footer>
        <p>&copy; 2025 Moonlight. Todos os direitos reservados.</p>
    </footer>

</body>

</html>