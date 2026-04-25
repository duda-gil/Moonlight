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

// Formatador para padrão R$ + 2 casas após a virgula
function fmtBR(float $v): string
{
    return 'R$ ' . number_format($v, 2, ',', '.');
}

// Formata o nome do usuário para o relatório
function usuarioRelatorio(?string $nome, ?string $status): string
{
    $nome   = trim((string) ($nome ?? ''));
    $status = strtolower(trim((string) ($status ?? '')));

    if ($nome === '') {
        return '—';
    }

    if ($status === 'inativo') {
        return $nome . ' (inativo)';
    }

    return $nome;
}


// Formatador de número do mês para escrita por extenso 
function nomeMesPt(int $m): string
{
    $nomes = [
        1 => 'janeiro',
        2 => 'fevereiro',
        3 => 'março',
        4 => 'abril',
        5 => 'maio',
        6 => 'junho',
        7 => 'julho',
        8 => 'agosto',
        9 => 'setembro',
        10 => 'outubro',
        11 => 'novembro',
        12 => 'dezembro'
    ];
    return $nomes[$m] ?? '';
}

// Filtro de mês
$mesParam = $_GET['mes'] ?? date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $mesParam)) {
    $mesParam = date('Y-m');
}
[$anoSel, $mesSel] = array_map('intval', explode('-', $mesParam));

// Monta o intervalo de tempo selecionado
$inicio = sprintf('%04d-%02d-01 00:00:00', $anoSel, $mesSel);
$dtInicio = new DateTime($inicio);
$dtFim = clone $dtInicio;
$dtFim->modify('first day of next month');
$fim = $dtFim->format('Y-m-d H:i:s');

$mesLegivel = nomeMesPt($mesSel) . ' de ' . $anoSel;

// Busca as comprar do período selecionado
$sql = "
    SELECT
        c.id AS compra_id,
        c.data_compra,
        c.form_pag,
        c.parcelas,
        c.valor_final,
        u.usuario AS user_nome,
        u.status AS user_status,
        j.nome AS jogo_nome,

        ci.qtd_chave,
        ci.preco_unit,
        ci.valor_total,
        t.base_total AS compra_base_total
    FROM compras c
    JOIN usuarios u ON u.id = c.user_id
    JOIN compras_itens ci ON ci.compra_id = c.id
    JOIN jogos j ON j.id = ci.jogo_id
    JOIN (
        SELECT compra_id, SUM(valor_total) AS base_total
        FROM compras_itens
        GROUP BY compra_id
    ) t ON t.compra_id = c.id
    WHERE c.data_compra >= ? 
      AND c.data_compra < ?
    ORDER BY c.data_compra ASC, j.nome ASC
";

$stm = $conn->prepare($sql);
$stm->bind_param('ss', $inicio, $fim);
$stm->execute();
$res = $stm->get_result();

$linhas = [];
$totalSemJuros = 0.0;
$totalComprasFinal = 0.0;
$comprasVistas = [];
$qtdItensTabela = 0;

while ($r = $res->fetch_assoc()) {
    $cid = (int) $r['compra_id'];

    // Formatação da data
    $dataFmt = '';
    if (!empty($r['data_compra'])) {
        $dt = new DateTime($r['data_compra']);
        $dataFmt = $dt->format('d/m/Y H:i');
    }

    $qtd = (int) $r['qtd_chave'];
    $precoUnit = (float) $r['preco_unit'];
    $valorItemSemJuros = (float) $r['valor_total'];
    $baseTotalCompra = (float) $r['compra_base_total'];
    $valorFinalCompra = (float) $r['valor_final'];

    // Mostra o valor do item ja considerando os juros 
    if ($baseTotalCompra > 0.0) {
        $fator = $valorFinalCompra / $baseTotalCompra;
        $valorItemComJuros = $valorItemSemJuros * $fator;
    } else {
        $valorItemComJuros = $valorItemSemJuros;
    }

    $usuarioVisivel = usuarioRelatorio($r['user_nome'] ?? '', $r['user_status'] ?? null);

    // Monta a linha de cada item da tabela 
    $linhas[] = [
        'data'            => $dataFmt,
        'jogo'            => $r['jogo_nome'] ?? '',
        'usuario'         => $usuarioVisivel,
        'qtd'             => $qtd,
        'preco_unit'      => $precoUnit,
        'total_sem_juros' => $valorItemSemJuros,
        'total_com_juros' => $valorItemComJuros,
    ];

    // Conta o total de itens a tabela tem
    $qtdItensTabela++;
    $totalSemJuros += $valorItemSemJuros;

    if (!isset($comprasVistas[$cid])) {
        $comprasVistas[$cid] = true;
        $totalComprasFinal += $valorFinalCompra;
    }
}

// Informa o número e compras distintas
$comprasConcluidas = count($comprasVistas);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>Relatório - Vendas por mês</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>

    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/stylesNav.css">

    <style>
        .content {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h1 {
            width: 100%;
            max-width: 1600px;
            margin: 40px auto 0;
            color: #fff;
            font-size: 2rem;
            font-weight: bold;
            text-align: center;
        }

        .relVen .container {
            background: linear-gradient(to bottom, #202330ff 0%, #272b3fff 50%);
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 1600px;
            font-size: 15px;
            margin: 0 auto 40px;
        }

        .relVen table {
            width: 100%;
            border-collapse: collapse;
            background-color: #202330ff;
            border-radius: 10px;
            overflow: hidden;
        }

        .relVen th,
        .relVen td {
            padding: 16px 18px;
            text-align: center;
            border-bottom: 1px solid #4e5c72ff;
            font-size: 15px;
        }

        .relVen th {
            background-color: #202330ff;
            color: #fff;
            font-weight: 600;
        }

        .relVen tbody tr:nth-child(even) {
            background-color: #272b3fff;
        }

        .relVen tbody tr:hover {
            background-color: #272b3fff;
        }

        .relVen .no-data {
            text-align: center;
            padding: 1rem;
            background-color: #272b3fff;
            color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            font-size: 15px;
            width: 100%;
        }

        .relVen .filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            margin: 0 0 16px 0;
            font-size: 15px;
        }

        .relVen .filter-bar label {
            color: #fff;
            font-weight: 600;
        }

        .relVen .filter-bar input[type="month"] {
            background: #1b2133;
            color: #fff;
            border-radius: 6px;
            border: 1px solid #3b4255;
            padding: 6px 10px;
            font-size: 15px;
        }

        .relVen .filter-bar button {
            background: #7aa3ef;
            border-radius: 6px;
            border: none;
            padding: 6px 12px;
            color: #fff;
            font-weight: 600;
            font-size: 15px;
        }

        .relVen .filter-bar button:hover {
            background: #345aa1;
            color: #d5e3ff;
        }

        .relVen .resume-line {
            margin-top: 12px;
            font-size: 18px;
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
</head>

<body>

    <?php $NAV_CONTEXT = 'admin';
    require_once 'include/navbar.php'; ?>

    <div class="content">
        <h1>Vendas por mês</h1>

        <section class="relVen">
            <div class="vendas">
                <div class="container">

                    <p class="mb-3">
                        Selecione um mês para listar todas as vendas realizadas.
                        Cada linha representa um jogo comprado por um usuário no período.
                    </p>

                    <!-- Filtro de mês para seleção -->
                    <form method="get" class="filter-bar">
                        <label for="mes">Mês:</label>
                        <input type="month" id="mes" name="mes" value="<?= htmlspecialchars($mesParam) ?>">
                        <button type="submit">Filtrar</button>
                    </form>

                    <!-- Linhas de informações de cada compra do período -->
                    <?php if (!empty($linhas)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Data da compra</th>
                                    <th>Usuário</th>
                                    <th>Jogo</th>
                                    <th>Qtd. chaves</th>
                                    <th>Valor unitário</th>
                                    <th>Valor total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($linhas as $linha): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($linha['data']) ?></td>
                                        <td><?= htmlspecialchars($linha['usuario']) ?></td>
                                        <td><?= htmlspecialchars($linha['jogo']) ?></td>
                                        <td><?= (int) $linha['qtd'] ?></td>
                                        <td><?= fmtBR($linha['preco_unit']) ?></td>
                                        <td><?= fmtBR($linha['total_com_juros']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="no-data">
                            Nenhuma venda encontrada para o período selecionado.
                        </div>
                    <?php endif; ?>

                    <br>

                    <!-- Totais ao fim do container -->
                    <div class="resume-line">
                        Compras concluídas:
                        <strong><?= $comprasConcluidas ?></strong>
                        &nbsp;·&nbsp;
                        Itens vendidos:
                        <strong><?= $qtdItensTabela ?></strong>
                    </div>

                </div>
            </div>
        </section>
    </div>

    <footer>
        <p>&copy; 2025 Moonlight. Todos os direitos reservados.</p>
    </footer>

</body>
</html>
