<?php
require_once 'include/conexao.php';
session_start();

// Formatador para padrão R$ + 2 casas após a virgula
function brl($p)
{
    return 'R$ ' . number_format((float) $p, 2, ',', '.');
}

// Verifica se a tabela existe no banco
function tableExists(mysqli $conn, string $name): bool
{
    $q = "SHOW TABLES LIKE '" . $conn->real_escape_string($name) . "'";
    $st = $conn->query($q);
    return $st && $st->num_rows > 0;
}

// Verifica se a coluna existe no banco
function columnExists(mysqli $conn, string $table, string $column): bool
{
    $q = "SHOW COLUMNS FROM `" . $conn->real_escape_string($table) . "` LIKE '" . $conn->real_escape_string($column) . "'";
    $st = $conn->query($q);
    return $st && $st->num_rows > 0;
}

// Verifica a tabela de ligação
$hasLinkTable = tableExists($conn, 'jogos_categorias');


// Busca todas as categorias no banco
$cats = [];
$allCats = [];

$sqlCats = "SELECT id, nome, descricao, status FROM categorias ORDER BY id ASC";

$res = $conn->query($sqlCats);

if (!$res) {

    // Mensagem de debug caso a busca falhe
    die('<pre style="color:#ffb3b3;background:#2b1a1a;padding:10px;border-radius:8px;"
            ERRO SQL ao carregar categorias: ' . htmlspecialchars($conn->error) . '</pre>');
}

while ($r = $res->fetch_assoc()) {
    $allCats[] = $r;
}


// Filtro de categorias ativas
$cats = array_values(array_filter($allCats, function ($c) {
    $s = strtolower(trim($c['status'] ?? ''));
    return in_array($s, ['ativa', 'ativo']);
}));

// Se não houver categorias ativas mostra todas mesmo assim
$showInactiveBanner = false;
if (empty($cats)) {
    $cats = $allCats;

    // Para identificar categorias inativas
    $showInactiveBanner = true;
}


// Descobre qual categoria foi pedida na URL e guarda os dados
$selectedId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$ids = array_map('intval', array_column($cats, 'id'));
if (!$selectedId || !in_array((int) $selectedId, $ids, true)) {
    $selectedId = $cats[0]['id'] ?? null;
}
$catSel = null;
if ($selectedId) {
    foreach ($cats as $c)
        if ((int) $c['id'] === (int) $selectedId) {
            $catSel = $c;
            break;
        }
}

// Pega os jogos ativos ligados a categoria e os ordena por nome
$jogos = [];
if ($selectedId) {
    if ($hasLinkTable) {
        $sql = "SELECT j.id, j.nome, j.preco, j.desconto, j.url_banner, j.status FROM jogos j
                INNER JOIN jogos_categorias jc ON jc.jogo_id = j.id WHERE jc.categoria_id = ? 
                AND LOWER(j.status)='ativo' ORDER BY j.nome ASC";
        $st = $conn->prepare($sql);
        $st->bind_param('i', $selectedId);
        $st->execute();
        $jogos = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
        $jogos = [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <link rel="stylesheet" href="css/styles2.css">
    <link rel="stylesheet" href="css/stylesNav.css">

    <title>Categorias - Moonlight</title>
</head>

<body>
    <div class="content">
        <?php require_once 'include/navbar.php' ?>

        <br><br><br>

        <main class="cats-shell">

            <!-- Sidebar de lista de categorias + barra de pesquisa -->
            <aside class="cats-sidebar">
                <h4 class="cats-title">Categorias</h4>
                <input id="cat-search" type="search" placeholder="Buscar categoria" aria-label="Buscar categoria">
                <ul id="cats-list">
                    <?php foreach ($cats as $c): ?>
                        <li>
                            <a class="cat-link <?= ((int) $c['id'] === (int) $selectedId) ? 'active' : '' ?>"
                                href="categorias.php?id=<?= (int) $c['id'] ?>"
                                data-name="<?= htmlspecialchars(mb_strtolower($c['nome'])) ?>">
                                <?= htmlspecialchars($c['nome']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </aside>

            <!-- Conteúdo dentro da categoria selecionada -->
            <section class="cats-main">
                <?php if ($catSel): ?>
                    
                    <!-- Categoria e descrição -->
                    <header class="cats-header">
                        <h4 class="cats-tittle"><?= htmlspecialchars($catSel['nome']) ?></h4>
                        <?php if (trim((string) $catSel['descricao']) !== ''): ?>
                            <p class="cats-desc"><?= htmlspecialchars($catSel['descricao']) ?></p>
                        <?php endif; ?>
                    </header>

                    <!-- Jogos e informações -->
                    <div class="game-grid">
                        <?php if (count($jogos) > 0): ?>
                            <?php foreach ($jogos as $j):
                                $id = (int) $j['id'];
                                $nome = htmlspecialchars($j['nome']);
                                $url = htmlspecialchars($j['url_banner'] ?? '');
                                $p = (float) ($j['preco'] ?? 0);
                                $d = (int) ($j['desconto'] ?? 0);
                                $pFinal = $d > 0 ? $p * (1 - $d / 100) : $p;
                                ?>

                                <a class="game-card" href="jogo.php?id=<?= $id ?>">
                                    <div class="game-thumb">
                                        <?php if ($url): ?>
                                            <img src="<?= $url ?>" alt="<?= $nome ?>">
                                        <?php else: ?>
                                            <div class="thumb-fallback">Sem imagem</div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="game-info">
                                        <h4><?= $nome ?></h4>
                                        <div class="precos">
                                            <?php if ($d > 0): ?>
                                                <span class="desconto"><?= $d ?>%</span>
                                                <span class="preco0"><?= brl($p) ?></span>
                                                <span class="preco1"><?= brl($pFinal) ?></span>
                                            <?php else: ?>
                                                <span class="new"><?= brl($pFinal) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>

                        <?php else: ?>
                            <div class="no-data">Nenhum jogo ativo nesta categoria no momento.</div>
                        <?php endif; ?>
                    </div>

                <!-- Caso não haja nenhuma categoria selecionável -->
                <?php else: ?>
                    <div class="no-data">Nenhuma categoria ativa encontrada.</div>
                <?php endif; ?>
            </section>
        </main>

        <footer>
            <p>&copy; 2025 Moonlight. Todos os direitos reservados.</p>
        </footer>
    </div>


    <!-- Script de busca da sidebar -->
    <script>
        document.getElementById('cat-search')?.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            document.querySelectorAll('#cats-list .cat-link').forEach(a => {
                const name = a.dataset.name || '';
                a.parentElement.style.display = name.includes(q) ? '' : 'none';
            });
        });
    </script>

    <!-- Script de autocomplete -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('mlSearch');
            const input = document.getElementById('mlSearchInput');
            const drop = document.getElementById('mlSuggest');
            if (!form || !input || !drop) return; // se a navbar não tiver a busca, não faz nada

            const fetchSuggest = (q) =>
                fetch('buscar.php?ajax=1&q=' + encodeURIComponent(q))
                    .then(r => r.json());

            let timer, items = [], active = -1;

            function render(results, q) {
                drop.innerHTML = '';
                if (!results || !results.length) { drop.hidden = true; return; }

                results.forEach((g) => {
                    const a = document.createElement('a');
                    a.className = 'ml-s-item';
                    a.href = 'jogo.php?id=' + g.id;
                    a.innerHTML = `
                        <img class="ml-s-thumb" src="${g.banner}" alt="${g.nome}">
                        <div class="ml-s-meta">
                            <div class="ml-s-title">${g.nome}</div>
                                <div class="ml-s-prices">
                                ${g.desconto > 0
                                ? `<span class="ml-s-badge">${g.desconto}%</span>
                                <span class="old">${g.preco}</span>
                                <span class="new">${g.preco_novo}</span>`
                                : `<span class="new">${g.preco}</span>`}
                            </div>
                        </div>`;
                    drop.appendChild(a);
                });

                const hr = document.createElement('hr');
                hr.className = 'ml-s-divider';
                drop.appendChild(hr);

                const foot = document.createElement('div');
                foot.className = 'ml-s-footer';
                foot.innerHTML = `<a href="buscar.php?q=${encodeURIComponent(q)}">ver todos os resultados</a>`;
                drop.appendChild(foot);

                drop.hidden = false;
                items = Array.from(drop.querySelectorAll('.ml-s-item'));
                active = -1;
            }

            input.addEventListener('input', () => {
                const q = input.value.trim();
                clearTimeout(timer);
                if (!q) { drop.hidden = true; return; }
                timer = setTimeout(async () => {
                    try {
                        const data = await fetchSuggest(q);
                        render(data.results || [], q);
                    } catch (_) { }
                }, 180);
            });

            input.addEventListener('focus', () => { if (drop.innerHTML) drop.hidden = false; });
            input.addEventListener('blur', () => setTimeout(() => drop.hidden = true, 120));
        });
    </script>

</body>
</html>