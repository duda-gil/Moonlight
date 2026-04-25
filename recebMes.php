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

// Busca os recebimentos do período selecionado
$sql = "
    SELECT
        c.id AS compra_id,
        c.data_compra,
        c.form_pag,
        c.parcelas,
        c.valor_final,
        u.usuario AS user_nome,
        u.status AS user_status,
        SUM(ci.valor_total) AS base_total
    FROM compras c
    JOIN usuarios u ON u.id = c.user_id
    JOIN compras_itens ci ON ci.compra_id = c.id
    WHERE c.data_compra >= ?
      AND c.data_compra < ?
    GROUP BY
        c.id,
        c.data_compra,
        c.form_pag,
        c.parcelas,
        c.valor_final,
        u.usuario,
        u.status
    ORDER BY c.data_compra ASC
";

$stm = $conn->prepare($sql);
$stm->bind_param('ss', $inicio, $fim);
$stm->execute();
$res = $stm->get_result();

$linhas = [];
$totalBase   = 0.0;
$totalJuros  = 0.0;
$totalFinal  = 0.0;

while ($r = $res->fetch_assoc()) {

    // Formatação da data
    $dataFmt = '';
    if (!empty($r['data_compra'])) {
        $dt = new DateTime($r['data_compra']);
        $dataFmt = $dt->format('d/m/Y H:i');
    }

    // Pega os valores e garante que o valor FINAL NÃO FIQUE NEGATIVO
    $base = (float) $r['base_total'];
    $final = (float) $r['valor_final'];
    $juros = max(0.0, $final - $base);

    $usuarioVisivel = usuarioRelatorio($r['user_nome'] ?? '', $r['user_status'] ?? null);

    // Monta a entrada das informações na linha
    $linhas[] = [
        'data'      => $dataFmt,
        'usuario'   => $usuarioVisivel,
        'form_pag'  => $r['form_pag'] ?? '',
        'parcelas'  => (int) ($r['parcelas'] ?? 1),
        'base'      => $base,
        'juros'     => $juros,
        'final'     => $final,
    ];

    // Atualiza os totais gerais
    $totalBase  += $base;
    $totalJuros += $juros;
    $totalFinal += $final;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>Relatório - Recebimentos por mês</title>

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

        .relRec .container {
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

        .relRec table {
            width: 100%;
            border-collapse: collapse;
            background-color: #202330ff;
            border-radius: 10px;
            overflow: hidden;
        }

        .relRec th,
        .relRec td {
            padding: 16px 18px;
            text-align: center;
            border-bottom: 1px solid #4e5c72ff;
            font-size: 15px;
        }

        .relRec th {
            background-color: #202330ff;
            color: #fff;
            font-weight: 600;
        }

        .relRec tbody tr:nth-child(even) {
            background-color: #272b3fff;
        }

        .relRec tbody tr:hover {
            background-color: #272b3fff;
        }

        .relRec .no-data {
            text-align: center;
            padding: 1rem;
            background-color: #272b3fff;
            color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            font-size: 15px;
            width: 100%;
        }

        .relRec .filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            margin: 0 0 16px 0;
            font-size: 15px;
        }

        .relRec .filter-bar label {
            color: #fff;
            font-weight: 600;
        }

        .relRec .filter-bar input[type="month"] {
            background: #1b2133;
            color: #fff;
            border-radius: 6px;
            border: 1px solid #3b4255;
            padding: 6px 10px;
            font-size: 15px;
        }

        .relRec .filter-bar button {
            background: #7aa3ef;
            border-radius: 6px;
            border: none;
            padding: 6px 12px;
            color: #fff;
            font-weight: 600;
            font-size: 15px;
        }

        .relRec .filter-bar button:hover {
            background: #345aa1;
            color: #d5e3ff;
        }

        .relRec .resume-line {
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
        <h1>Recebimentos por mês</h1>

        <section class="relRec">
            <div class="receb">
                <div class="container">

                    <p class="mb-3">
                        Selecione um mês para listar todos os recebimentos da loja.
                        Cada linha representa uma compra concluída no período.
                    </p>

                    <!-- Filtro de mês para seleção -->
                    <form method="get" class="filter-bar">
                        <label for="mes">Mês:</label>
                        <input type="month" id="mes" name="mes" value="<?= htmlspecialchars($mesParam) ?>">
                        <button type="submit">Filtrar</button>
                    </form>

                    <!-- Linha de informações de cada recebimento do período -->
                    <?php if (!empty($linhas)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Data da compra</th>
                                    <th>Usuário</th>
                                    <th>Forma de pagamento</th>
                                    <th>Parcelas</th>
                                    <th>Valor base</th>
                                    <th>Juros</th>
                                    <th>Total recebido</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($linhas as $linha): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($linha['data']) ?></td>
                                        <td><?= htmlspecialchars($linha['usuario']) ?></td>
                                        <td><?= htmlspecialchars($linha['form_pag']) ?></td>
                                        <td><?= (int) $linha['parcelas'] ?></td>
                                        <td><?= fmtBR($linha['base']) ?></td>
                                        <td><?= fmtBR($linha['juros']) ?></td>
                                        <td><?= fmtBR($linha['final']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <br>

                        <div class="resume-line">
                            Valor base total:
                            <strong><?= fmtBR($totalBase) ?></strong>
                            &nbsp;·&nbsp;
                            Juros totais:
                            <strong><?= fmtBR($totalJuros) ?></strong>
                            &nbsp;·&nbsp;
                            Total recebido:
                            <strong><?= fmtBR($totalFinal) ?></strong>
                        </div>

                    <?php else: ?>
                        <div class="no-data">
                            Nenhum recebimento encontrado para o período selecionado.
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </section>
    </div>

    <footer>
        <p>&copy; 2025 Moonlight. Todos os direitos reservados.</p>
    </footer>

</body>
</html>