<?php
require_once __DIR__ . '/include/conexao.php';


// Vem com as sugestões dos jogos que correspondem ao que foi informado na barra de pesquisa (se limita 8)
if (isset($_GET['mode']) && $_GET['mode'] === 'suggest') {
    header('Content-Type: application/json; charset=utf-8');

    $q = trim($_GET['q'] ?? '');
    if ($q === '' || mb_strlen($q) < 1) {
        echo json_encode(['items' => []], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Informações mostradas nos cards de sugestão
    $sql = "SELECT id, nome, preco, desconto, url_banner
            FROM jogos
            WHERE nome LIKE CONCAT('%', ?, '%')
            ORDER BY nome ASC
            LIMIT 8";
    $st = $conn->prepare($sql);
    $st->bind_param('s', $q);
    $st->execute();
    $rs = $st->get_result();

    $items = [];
    while ($r = $rs->fetch_assoc()) {
        $preco  = (float)($r['preco'] ?? 0);
        $desc   = (int)($r['desconto'] ?? 0);
        $precoN = $desc > 0 ? $preco * (1 - $desc/100) : $preco;

        $items[] = [
            'id'       => (int)$r['id'],
            'nome'     => (string)$r['nome'],
            'thumb'    => (string)($r['url_banner'] ?? ''),   // ajuste se for outra coluna
            'precoFmt' => 'R$ ' . number_format($precoN, 2, ',', '.'),
        ];
    }
    echo json_encode(['items' => $items], JSON_UNESCAPED_UNICODE);
    exit;
}


// Formatador para padrão R$ + 2 casas após a virgula
function brl($v)
{
    return 'R$ ' . number_format((float) $v, 2, ',', '.');
}

// Texto digitado na busca e dropdown de sugestão
$q = trim($_GET['q'] ?? '');
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';

// Garante as sugestões com as ordens da informações ideais
if ($isAjax) {
    header('Content-Type: application/json; charset=utf-8');

    if ($q === '') {
        echo json_encode(['ok' => true, 'results' => []], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $terms = array_values(array_filter(preg_split('/\s+/', $q)));
    $where = " WHERE status='ativo' ";
    $types = '';
    $params = [];

    foreach ($terms as $t) {
        $where .= " AND nome LIKE ? ";
        $types .= 's';
        $params[] = "%{$t}%";
    }

    $sql = "SELECT id, nome, preco, desconto, url_banner
          FROM jogos
          $where
          ORDER BY (desconto>0) DESC, desconto DESC, nome ASC
          LIMIT 8";
    $stmt = $conn->prepare($sql);
    if ($types)
        $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();

    $out = [];
    while ($r = $res->fetch_assoc()) {
        $p = (float) $r['preco'];
        $d = (int) $r['desconto'];
        $n = $d > 0 ? $p * (1 - $d / 100) : $p;

        $out[] = [
            'id' => (int) $r['id'],
            'nome' => $r['nome'],
            'banner' => $r['url_banner'],
            'desconto' => $d,
            'preco' => brl($p),
            'preco_novo' => brl($n),
        ];
    }
    echo json_encode(['ok' => true, 'results' => $out], JSON_UNESCAPED_UNICODE);
    exit;
}
?>

<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>Buscar: <?= htmlspecialchars($q ?: '') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/stylesNav.css">

<body>

    <div class="content">
        <?php require __DIR__ . '/include/navbar.php'; ?>

        <br>

        <!-- Página de resultados para o que foi pesquisado -->
        <main class="container-xxl px-3 px-md-4" style="padding:40px 0">
            <section class="busca">
                <h2>Resultados para "<?= htmlspecialchars($q) ?>"</h2>

                <div class="jogos">
                    <?php
                    $terms = array_values(array_filter(preg_split('/\s+/', $q)));
                    $where = " WHERE status='ativo' ";
                    $types = '';
                    $params = [];
                    foreach ($terms as $t) {
                        $where .= " AND nome LIKE ? ";
                        $types .= 's';
                        $params[] = "%{$t}%";
                    }

                    $sql = "SELECT id, nome, preco, desconto, url_banner
            FROM jogos
            $where
            ORDER BY (desconto>0) DESC, desconto DESC, nome ASC
            LIMIT 48";
                    $stmt = $conn->prepare($sql);
                    if ($types)
                        $stmt->bind_param($types, ...$params);
                    $stmt->execute();
                    $res = $stmt->get_result();

                    if ($res->num_rows === 0) {
                        echo '<p>Nenhum jogo encontrado.</p>';
                    }

                    while ($r = $res->fetch_assoc()):
                        $p = (float) $r['preco'];
                        $d = (int) $r['desconto'];
                        $n = $d > 0 ? $p * (1 - $d / 100) : $p;
                        ?>

                        <!-- Monta um card para cada resultado (limitado a 48) -->
                        <a href="jogo.php?id=<?= (int) $r['id'] ?>" class="jogo-card">
                            <div class="jogo">
                                <img src="<?= htmlspecialchars($r['url_banner']) ?>"
                                    alt="<?= htmlspecialchars($r['nome']) ?>">
                                <h3><?= htmlspecialchars($r['nome']) ?></h3>

                                <div class="precos">
                                    <?php if ($d > 0): ?>
                                        <span class="desconto"><?= $d ?>%</span>
                                        <span class="preco0"><?= brl($p) ?></span>
                                        <span class="preco1"><?= brl($n) ?></span>
                                    <?php else: ?>
                                        <p><?= brl($p) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>

                    <?php endwhile; ?>
                </div>
            </section>
        </main>

        <footer>
            <p>&copy; 2025 Moonlight. Todos os direitos reservados.</p>
        </footer>

    </div>

    <!-- Script do autocomplete -->
    <script>
        (() => {
            const form = document.getElementById('mlSearch');
            if (!form) return; // navbar sem busca nessa página

            const input = document.getElementById('mlSearchInput');
            const box = document.getElementById('mlSuggest');
            let ctrl = null;
            let last = '';

            function prices(it) {
                return (it.desconto && it.desconto > 0)
                    ? `<span class="ml-s-badge">${it.desconto}%</span>
         <span class="old">${it.preco}</span>
         <span class="new">${it.preco_novo}</span>`
                    : `<span class="new">${it.preco_novo}</span>`;
            }

            async function fetchResults(q) {
                if (ctrl) ctrl.abort();
                ctrl = new AbortController();
                const r = await fetch('buscar.php?ajax=1&q=' + encodeURIComponent(q), { signal: ctrl.signal });
                const j = await r.json();
                return j.results || [];
            }

            function render(list) {
                if (!list.length) {
                    box.hidden = true;
                    box.innerHTML = '';
                    return;
                }
                box.innerHTML =
                    list.map(it => `
        <a class="ml-s-item" href="jogo.php?id=${it.id}">
          <img class="ml-s-thumb" src="${it.banner}" alt="${it.nome}">
          <div class="ml-s-meta">
            <div class="ml-s-title">${it.nome}</div>
            <div class="ml-s-prices">${prices(it)}</div>
          </div>
        </a>
      `).join('') +
                    `<hr class="ml-s-divider">
       <div class="ml-s-footer">
         <a href="buscar.php?q=${encodeURIComponent(input.value.trim())}">ver todos os resultados</a>
       </div>`;
                box.hidden = false;
            }

            let t = null;
            input.addEventListener('input', () => {
                const q = input.value.trim();
                if (q === last) return;
                last = q;

                clearTimeout(t);
                if (!q) {
                    box.hidden = true; box.innerHTML = '';
                    return;
                }
                t = setTimeout(async () => render(await fetchResults(q)), 140);
            });

            document.addEventListener('click', (ev) => {
                if (!box.contains(ev.target) && ev.target !== input) box.hidden = true;
            });
        })();
    </script>

</body>
</html>