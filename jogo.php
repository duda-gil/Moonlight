<?php
require_once 'include/conexao.php';
if (session_status() !== PHP_SESSION_ACTIVE)
    session_start();

require_once __DIR__ . '/include/biblioteca.php';

// Normaliza id de sessão
$uid = (int) (
    $_SESSION['id']
    ?? $_SESSION['user_id']
    ?? ($_SESSION['user']['id'] ?? 0)
    ?? ($_SESSION['usuario']['id'] ?? 0)
);

// Retorna dizendo se o usuário possui ou não o jogo em sua biblioteca, alterando o botão
$owned = ($uid && isset($_GET['id'])) ? library_has($conn, $uid, (int) $_GET['id']) : false;
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="css/styles2.css">
    <link rel="stylesheet" href="css/stylesNav.css">
    <title>Moonlight</title>

    <style>
        :root {
            --stage-w: 100%;
            --stage-h: 337px;
            --gap-cols: 24px;
            --info-gap: 12px;
            --thumb-gap: 8px;
            --gallery-bg: rgba(255, 255, 255, .035);
            --gallery-border: rgba(255, 255, 255, .07);
        }

        .game-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 50px 40px;
            gap: 30px;
        }

        .game-header {
            display: flex;
            align-items: flex-start;
            justify-content: center;
            gap: var(--gap-cols);
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .left-col {
            flex: 1;
            min-width: 0;
        }

        .gallery {
            width: 100%;
            max-width: var(--stage-w);
            margin: 0 auto;
            background: var(--gallery-bg);
            border: 1px solid var(--gallery-border);
            border-radius: 12px;
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .stage {
            width: 100%;
            height: var(--stage-h);
            border-radius: 10px;
            background: #0d1324;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .stage img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .nav-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            transition: background 0.15s ease;
            width: 38px;
            height: 38px;
            border-radius: 8px;
            background: transparent;
            border: none;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 2;
        }

        .nav-btn:active {
            background: transparent;
        }

        .nav-btn:hover,
        .nav-btn:focus-visible {
            background: rgba(0, 0, 0, 0.35);
            outline: none;
            box-shadow: none;
        }

        .nav-prev {
            left: 8px;
        }

        .nav-next {
            right: 8px;
        }

        .thumb-strip {
            width: 100%;
            display: flex;
            gap: var(--thumb-gap);
        }

        .thumb {
            box-sizing: border-box;
            flex: 0 0 auto;
            width: calc((100% - (var(--thumbs-count) - 1)*var(--thumb-gap))/var(--thumbs-count));
            aspect-ratio: 120/67;
            border-radius: 8px;
            background: #0d1324;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            cursor: pointer;
            opacity: .9;
            border: 1px solid transparent;
            transition: .15s;
        }


        .thumb-strip.single {
            justify-content: center;
        }

        .thumb-strip.single .thumb {
            width: min(260px, 50%);
        }

        .thumb img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .thumb:hover {
            opacity: 1;
            transform: scale(1.02);
        }

        .thumb.active {
            border-color: #7aa3ef;
            opacity: 1;
        }

        .banner-wrapper {
            margin-top: 30px;
            width: 100%;
            max-width: var(--stage-w);
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            justify-content: center;
            margin-left: auto;
            margin-right: auto;
        }

        .banner-wrapper img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        .game-info {
            flex: 1;
            color: #fff;
            display: flex;
            flex-direction: column;
            gap: var(--info-gap);
            max-width: var(--stage-w);
            padding-top: 14px;
        }

        .game-info h1 {
            font-size: 28px;
            margin: 0 0 10px;
        }

        .game-info p {
            font-size: 15px;
            line-height: 1.5;
            margin: 0;
        }

        .price-box {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 8px 0 4px
        }

        .price-old {
            color: #a9b5d4;
            text-decoration: line-through;
            font-size: 15px
        }

        .price-new {
            color: #fff;
            font-size: 17px
        }

        .price-badge {
            background: #4ca169;
            color: #fff;
            padding: 6px 10px;
            border-radius: 8px
        }

        .game-cats {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 6px
        }

        .cat-badge {
            display: inline-flex;
            align-items: center;
            background: #1e2438;
            border: 1px solid #2b3358;
            color: #dbe6ff;
            font-size: 13px;
            padding: 4px 8px;
            border-radius: 8px;
            text-decoration: none
        }

        .cat-badge:hover {
            background: #252d4a;
            border-color: #3b4a7a;
            color: #fff
        }

        .compra {
            background: #7aa3ef;
            border-radius: 6px;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            color: #fff;
            font-weight: bold;
            font-size: 15px;
            align-self: flex-start;
            margin-top: 10px
        }

        .compra:hover {
            background: #345aa1;
            transition: background .25s
        }

        .rating-card {
            margin-top: 12px;
            display: grid;
            grid-template-columns: 74px 1fr;
            gap: 12px;
            align-items: center;
            background: var(--gallery-bg);
            border: 1px solid var(--gallery-border);
            border-radius: 10px;
            padding: 12px
        }

        .rating-left img {
            width: 74px;
            height: 74px;
            object-fit: contain;
            background: #0d1320;
            border-radius: 6px;
            display: block
        }

        .rating-right {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 0
        }

        .rating-desc {
            color: #cbd5f1;
            font-size: 14px
        }

        .rating-sub {
            margin-top: 6px;
            color: #9fb3d9;
            font-size: 13px
        }

        .rating-tags {
            margin: 6px 0 0;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            list-style: none;
            padding: 0
        }

        .rating-tags li {
            background: #1e2438;
            border: 1px solid #2b3358;
            color: #dbe6ff;
            font-size: 12.5px;
            padding: 4px 8px;
            border-radius: 8px;
            white-space: nowrap
        }

        .req-card {
            margin-top: 12px;
            background: var(--gallery-bg);
            border: 1px solid var(--gallery-border);
            border-radius: 10px;
            padding: 12px 14px
        }

        .req-title {
            font-size: 13px;
            font-weight: 700;
            margin: 0 0 6px
        }

        .req-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            column-gap: 18px;
            row-gap: 4px;
            font-size: 12px
        }

        .req-item {
            padding: 2px 0;
            color: #dbe6ff
        }

        .req-item b {
            color: #7aa3ef;
            margin-right: 6px
        }


        @media (max-width:992px) {
            .game-header {
                flex-direction: column
            }

            .stage {
                height: auto;
                aspect-ratio: 600/337
            }

            .game-info {
                max-width: 100%
            }
        }

        @media (max-width:480px) {
            .rating-card {
                grid-template-columns: 56px 1fr;
                padding: 10px
            }

            .rating-left img {
                width: 56px;
                height: 56px
            }

            .req-grid {
                grid-template-columns: 1fr
            }

            .req-title {
                font-size: 14px
            }
        }

        #multiKeysModal .modal-content {
            background: #141a27;
            color: #eaf1ff;
        }

        #multiKeysModal .modal-header,
        #multiKeysModal .modal-footer {
            border: 0;
        }

        #mkList {
            display: grid;
            gap: 14px;
        }

        .mk-row {
            display: grid;
            grid-template-columns: 90px 1fr auto;
            align-items: center;
            gap: 10px;
        }

        .mk-badge {
            color: #cbd5f1;
            font-size: 14px;
            white-space: nowrap;
        }

        .mk-code {
            background: #0f1724;
            border: 1px solid rgba(255, 255, 255, .08);
            color: #eaf1ff;
            border-radius: 8px;
            padding: 10px 12px;
            font-weight: 800;
            letter-spacing: 2px;
            text-align: center;
            word-break: break-all;
        }

        @media (max-width: 520px) {
            .mk-row {
                grid-template-columns: 1fr;
            }

            .mk-badge {
                order: -1;
            }
        }

        footer {
            margin-top: auto;
            text-align: center;
            font-size: .9rem;
            color: #fff;
            padding: 20px 0
        }
    </style>
</head>

<body>
    <div class="content">
        <?php require_once 'include/navbar.php' ?>

        <main class="game-container">
            <?php

            // Formatador de data + preenche com "em breve" quando vazia
            if (!function_exists('dataPtAbrev')) {
                function dataPtAbrev(?string $data, string $tz = 'America/Sao_Paulo'): string
                {
                    if ($data === null)
                        return 'Em breve';
                    $data = trim($data);
                    if ($data === '' || $data === '0000-00-00')
                        return 'Em breve';
                    try {
                        $dt = new DateTime($data, new DateTimeZone($tz));
                    } catch (Throwable $e) {
                        return 'Em breve';
                    }
                    if (class_exists('IntlDateFormatter')) {
                        $fmt = new IntlDateFormatter('pt_BR', 0, 0, $tz, IntlDateFormatter::GREGORIAN, 'd/MMM/y');
                        return str_replace('.', '', $fmt->format($dt));
                    }
                    $meses = [1 => 'jan.', 'fev.', 'mar.', 'abr.', 'mai.', 'jun.', 'jul.', 'ago.', 'set.', 'out.', 'nov.', 'dez.'];
                    return $dt->format('d') . '/' . $meses[(int) $dt->format('n')] . '/' . $dt->format('Y');
                }
            }

            // Formatador para padrão R$ + 2 casas após a virgula
            if (!function_exists('fmtBR')) {
                function fmtBR(float $v): string
                {
                    return number_format($v, 2, ',', '.');
                }
            }

            if (isset($_GET['id'])) {
                $id = intval($_GET['id']);

                // Busca o jogo pelo ID e traz todas as informações da tabela, a classificação indicativa e os requisitos
                $stmt = $conn->prepare("
                    SELECT
                        j.id, j.nome, j.resumo, j.desenvolvedor, j.data_lancamento, j.preco, j.desconto,
                        j.url_banner, j.url_1, j.url_2, j.url_3, j.url_4, j.url_5,
                        j.classificacao_ind, j.conteudo,
                        ci.tipo AS ci_tipo, ci.descricao AS ci_desc, ci.url_imagem AS ci_img,
                        r.processador AS r_proc, r.memoria AS r_mem, r.placa_video AS r_gpu, 
                        r.sistema_op AS r_so, r.armazenamento AS r_store, r.directx AS r_dx
                    FROM jogos j
                    LEFT JOIN classificacao_ind ci
                        ON ((j.classificacao_ind REGEXP '^[0-9]+$' AND ci.id = CAST(j.classificacao_ind AS UNSIGNED))
                        OR (NOT j.classificacao_ind REGEXP '^[0-9]+$' AND ci.tipo = j.classificacao_ind))
                    LEFT JOIN requisitos r ON r.jogo_id = j.id
                    WHERE j.id = ?
                ");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $resultado = $stmt->get_result();
                $jogo = $resultado->fetch_assoc();

                
                // Garante uma segunda consulta caso a classificação indicativa não venha 
                if ($jogo && empty($jogo['ci_tipo']) && !empty($jogo['classificacao_ind'])) {
                    $val = trim((string) $jogo['classificacao_ind']);
                    if (ctype_digit($val)) {
                        $q = $conn->prepare("SELECT tipo,descricao,url_imagem FROM classificacao_ind WHERE id=?");
                        $idval = (int) $val;
                        $q->bind_param('i', $idval);
                    } else {
                        $q = $conn->prepare("SELECT tipo,descricao,url_imagem FROM classificacao_ind WHERE tipo=?");
                        $q->bind_param('s', $val);
                    }
                    $q->execute();
                    if ($r = $q->get_result()->fetch_assoc()) {
                        $jogo['ci_tipo'] = $r['tipo'];
                        $jogo['ci_desc'] = $r['descricao'];
                        $jogo['ci_img'] = $r['url_imagem'];
                    }
                }

                if ($jogo) {

                    // Classifica um jogo sem data definida como estando na pré-venda
                    $rawDate = isset($jogo['data_lancamento'])
                        ? trim((string)$jogo['data_lancamento'])
                        : '';
                    $isPreOrder = false;

                    // Caso data de lançamento não definida
                    if($rawDate ===  '' || $rawDate === '0000-00-00'){
                        $isPreOrder = true; 
                    } else {
                        try{
                            $tz = new DateTimeZone('America/Sao_Paulo');
                            $dtLanc = new DateTime($rawDate , $tz);
                            $hoje = new DateTime('today', $tz);

                            // Caso data de lançamento futura
                            if($dtLanc > $hoje) {
                                $isPreOrder = true;
                            }
                        }
                        catch(Throwable $e){
                                $isPreOrder = true;
                            }
                    }


                    // Remove imagens duplicadas + sempre busca a próxima imagem caso campo vazio
                    $urls = [];
                    for ($i = 1; $i <= 5; $i++) {
                        $u = isset($jogo["url_$i"]) ? trim((string) $jogo["url_$i"]) : '';
                        if ($u !== '')
                            $urls[] = $u;
                    }
                    $urls = array_values(array_unique($urls));
                    $banner = trim((string) ($jogo['url_banner'] ?? ''));
                    if ($banner === '' && !empty($urls))
                        $banner = $urls[0];

                    echo "<div class='game-header'>";

                    
                    // Lado esquerdo: mostra a imagem principal com a primeira imagem, cria as thumbs e os botões de navegação
                    echo "<div class='left-col'>";
                    echo "<div class='gallery'>";
                    if (!empty($urls)) {
                        $first = htmlspecialchars($urls[0], ENT_QUOTES, 'UTF-8');
                        echo "<div class='stage'>
                            <button class='nav-btn nav-prev' id='cPrev' aria-label='Anterior'>&#10094;</button>
                            <img id='stageImage' src='{$first}' alt='Imagem do jogo'>
                            <button class='nav-btn nav-next' id='cNext' aria-label='Próximo'>&#10095;</button>
                        </div>";

                        $thumbCount = count($urls);
                        $thumbClass = ($thumbCount === 1) ? ' single' : '';
                        echo "<div class='thumb-strip{$thumbClass}' id='thumbStrip' style='--thumbs-count: {$thumbCount};'>";

                        foreach ($urls as $idx => $u) {
                            $safe = htmlspecialchars($u, ENT_QUOTES, 'UTF-8');
                            $active = ($idx === 0) ? ' active' : '';
                            echo "<div class='thumb{$active}' data-index='{$idx}' data-src='{$safe}'><img src='{$safe}' alt='Miniatura'></div>";
                        }
                        echo "</div>";
                    } 
                    
                    else {
                        echo "<div class='stage'><img src='placeholder.png' alt='Imagem indisponível'></div>";
                    }

                    echo "</div>";
                    $safeBanner = ($banner !== '') ? htmlspecialchars($banner, ENT_QUOTES, 'UTF-8') : 'placeholder.png';

                    echo "<div class='banner-wrapper'><img src='{$safeBanner}' alt='Banner do jogo'></div>";
                    echo "</div>";

                    // Lado direito: mostra as informações básicas do jogo
                    echo "<div class='game-info'>";
                    echo "<h1>" . htmlspecialchars($jogo['nome'] ?? '', ENT_QUOTES, 'UTF-8') . "</h1>";
                    echo "<p>" . htmlspecialchars($jogo['resumo'] ?? '', ENT_QUOTES, 'UTF-8') . "</p>";
                    echo "<p><strong>Desenvolvedor:</strong> " . htmlspecialchars($jogo['desenvolvedor'] ?? '', ENT_QUOTES, 'UTF-8') . "</p>";
                    echo "<p><strong>Data de lançamento:</strong> " . dataPtAbrev($jogo['data_lancamento'] ?? null) . "</p>";

                    // Mostra o preço
                    $precoBruto = max(0.0, (float) ($jogo['preco'] ?? 0));
                    $desc = max(0.0, min(100.0, (float) ($jogo['desconto'] ?? 0)));

                    // Caso desconto, preço antigo riscado + preço novo
                    if ($precoBruto > 0 && $desc > 0) {
                        $final = $precoBruto * (1 - $desc / 100);
                        echo '<div class="price-box"><span class="price-old">R$ ' . fmtBR($precoBruto) . '</span><span class="price-new">R$ ' . fmtBR($final) . '</span><span class="price-badge">-' . (int) $desc . '%</span></div>';
                    } 
                    
                    // Caso sem desconto, preço bruto
                    else {
                        echo '<div class="price-box"><span class="price-new">R$ ' . fmtBR($precoBruto) . '</span></div>';
                    }


                    // Busca as categorias ligadas ao jogo em questão + clicáveis e redirecionáveis
                    $stCat = $conn->prepare("SELECT c.id,c.nome FROM categorias c JOIN jogos_categorias jc ON jc.categoria_id=c.id WHERE jc.jogo_id=? ORDER BY c.id");
                    $stCat->bind_param('i', $id);
                    $stCat->execute();
                    $cats = $stCat->get_result()->fetch_all(MYSQLI_ASSOC);
                    if (!empty($cats)) {
                        echo '<div class="game-cats">';
                        foreach ($cats as $c) {
                            $cid = (int) $c['id'];
                            $cname = htmlspecialchars($c['nome'], ENT_QUOTES, 'UTF-8');
                            echo '<a class="cat-badge" href="categorias.php?id=' . $cid . '">' . $cname . '</a>';
                        }
                        echo '</div>';
                    }

                    // Guarda o preço final para possível reuso futuro
                    $precoFinal = ($precoBruto > 0 && $desc > 0) ? $precoBruto * (1 - $desc / 100) : $precoBruto;
                    ?>

                    <!-- Botões de ação variando conforme o status do jogo -->
                    <div class="game-actions" style="display:flex; gap:10px; flex-wrap:wrap;">
                        <?php if ($owned): ?>

                            <!-- Caso o usuário já tenha o jogo, há os botões "Ver chave" e "Comprar novamente" -->
                            <button id="btnBuyAgain" class="m-btn" data-game="<?= (int) $id ?>" type="button">
                                Comprar novamente
                            </button>

                            <button id="btnViewKey" class="k-btn" type="button" data-id="<?= (int) $id ?>">
                                Ver chave
                            </button>

                        <?php else: ?>

                            <!-- Caso o usuário não tenha o jogo, há o botão "Adicionar ao carrinho" -->
                            <button id="btnAddCart" class="m-btn" data-game="<?= (int) $id ?>" type="button">
                                <?= $isPreOrder ? 'Comprar na pré-venda' : 'Adicionar ao carrinho' ?>
                            </button>

                        <?php endif; ?>
                    </div>

                    <?php

                    // Junta os requisitos + remove os vazios
                    $reqRaw = [
                        'Processador' => trim((string) ($jogo['r_proc'] ?? '')),
                        'Memória' => trim((string) ($jogo['r_mem'] ?? '')),
                        'Placa de vídeo' => trim((string) ($jogo['r_gpu'] ?? '')),
                        'S.O.' => trim((string) ($jogo['r_so'] ?? '')),
                        'Armazenamento' => trim((string) ($jogo['r_store'] ?? '')),
                        'DirectX' => trim((string) ($jogo['r_dx'] ?? '')),
                    ];

                    $reqFilled = array_filter($reqRaw, fn($v) => $v !== '');

                    // Devolve "A ser definido" se nada for preenchido
                    if (empty($reqFilled)) {
                        $reqFilled = ['S.O.' => 'A ser definido'];
                    }

                    echo '<div class="req-card" id="reqCard">';
                    echo '<div class="req-title">Requisitos Mínimos</div>';
                    echo '<div class="req-grid">';
                    foreach ($reqFilled as $k => $v) {
                        echo '<div class="req-item"><b>' . $k . ':</b> ' . htmlspecialchars($v, ENT_QUOTES, 'UTF-8') . '</div>';
                    }
                    echo '</div></div>';


                    // Transforma o "conteúdo" da classificação indicativa em chips separados 
                    $chips = [];
                    $conteudoBruto = trim((string) ($jogo['conteudo'] ?? ''));
                    if ($conteudoBruto !== '') {
                        foreach (preg_split('/[;,|]+/u', $conteudoBruto) as $p) {
                            $p = trim($p);
                            if ($p !== '')
                                $chips[] = htmlspecialchars($p, ENT_QUOTES, 'UTF-8');
                        }
                    }

                    // Mostra a imagem, a descrição e a lista de conteúdos dentro de um card
                    $safeImg = ($jogo['ci_img'] ?? '') !== '' ? htmlspecialchars($jogo['ci_img'], ENT_QUOTES, 'UTF-8') : 'img/classificacao_placeholder.png';
                    echo '<div class="rating-card" id="ratingCard">';
                    echo '<div class="rating-left"><img src="' . $safeImg . '" alt="Classificação"></div>';
                    echo '<div class="rating-right">';
                    if (($jogo['ci_desc'] ?? '') !== '')
                        echo '<div class="rating-desc">' . htmlspecialchars($jogo['ci_desc'], ENT_QUOTES, 'UTF-8') . '</div>';
                    if (!empty($chips)) {
                        echo '<div class="rating-sub">Conteúdo presente:</div><ul class="rating-tags">';
                        foreach ($chips as $c)
                            echo '<li>' . $c . '</li>';
                        echo '</ul>';
                    }
                    echo '</div>';
                    echo '</div>';

                    echo "</div>";
                    echo "</div>";
                } else {
                    echo "<p>Jogo não encontrado.</p>";
                }
            }
            ?>
        </main>
    </div>

    <!-- Modal caso o usuário só possua uma chave do jogo -->
    <div class="modal fade" id="keyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content border-0" style="background:#141a27;">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-white">Sua chave</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <p class="text-white-50 mb-2">Use o código abaixo para ativar o jogo:</p>
                    <div class="mk-item">
                        <div id="gameKeyLabel" class="mk-label">Chave 1:</div>
                        <div class="mk-line">
                            <div id="gameKeyBox" class="mk-code">—</div>
                            <button type="button" id="btnCopyKey" class="k-btn">Copiar</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal caso o usuário possua mais de uma chave do jogo -->
    <div class="modal fade" id="multiKeysModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">

            <div class="modal-content border-0" style="background:#141a27;color:#eaf1ff;">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Suas chaves</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <p class="text-white-50 mb-3">Use os códigos abaixo para ativar os jogos:</p>
                    <div id="mkList" class="d-grid gap-4"></div>
                </div>
            </div>
        </div>
    </div>


    <script>

        // Busca as imagens e ativa o carrossel
        (() => {
            const stageImg = document.getElementById('stageImage');
            const thumbs = document.querySelectorAll('.thumb');
            const btnPrev = document.getElementById('cPrev');
            const btnNext = document.getElementById('cNext');
            if (!stageImg || thumbs.length === 0 || !btnPrev || !btnNext) return;
            let index = 0;
            const sources = Array.from(thumbs).map(t => t.dataset.src);
            const setActive = i => { index = (i + sources.length) % sources.length; stageImg.src = sources[index]; thumbs.forEach((t, k) => t.classList.toggle('active', k === index)); };
            btnPrev.addEventListener('click', () => setActive(index - 1));
            btnNext.addEventListener('click', () => setActive(index + 1));
            thumbs.forEach((t, k) => t.addEventListener('click', () => setActive(k)));
            window.addEventListener('keydown', e => { if (e.key === 'ArrowLeft') setActive(index - 1); if (e.key === 'ArrowRight') setActive(index + 1); });
        })();

        // Responsável pelo alinhamento dos itens em telas maiores e menores
        (() => {
            const info = document.querySelector('.game-info');
            const banner = document.querySelector('.banner-wrapper');
            const req = document.getElementById('reqCard');
            const rating = document.getElementById('ratingCard');

            function getBaseMargin(el) {
                const base = parseFloat(el?.dataset.baseMargin ?? getComputedStyle(el).marginTop) || 0;
                if (el && !el.dataset.baseMargin) el.dataset.baseMargin = String(base);
                return base;
            }
            function resetToBase(el) {
                if (!el) return;
                const base = getBaseMargin(el);
                el.style.marginTop = base + 'px';
            }

            function align() {
                if (!info || !banner) return;
                const stacked = window.matchMedia('(max-width: 992px)').matches;
                if (stacked) { resetToBase(req); resetToBase(rating); return; }

                const infoTop = info.getBoundingClientRect().top + window.scrollY;
                const bannerRect = banner.getBoundingClientRect();
                const bannerTop = bannerRect.top + window.scrollY - infoTop;
                const bannerBottom = bannerRect.bottom + window.scrollY - infoTop;

                // Garante o início de "requisitos" alinhados ao topo do
                if (req) {
                    resetToBase(req);
                    const baseReq = getBaseMargin(req);
                    const reqTop = req.getBoundingClientRect().top + window.scrollY - infoTop;
                    const deltaReqTop = Math.max(0, Math.round(bannerTop - reqTop));
                    req.style.marginTop = (baseReq + deltaReqTop) + 'px';
                }

            }

            window.addEventListener('load', align);
            window.addEventListener('resize', () => requestAnimationFrame(align));
        })();

    </script>

    <!-- Script de chaves + cópia de chaves -->
    <script>
        (() => {
            const btn = document.getElementById('btnViewKey');
            if (!btn) return;

            const modal1El = document.getElementById('keyModal');
            const modal1 = new bootstrap.Modal(modal1El);
            const box = document.getElementById('gameKeyBox');
            const label1 = document.getElementById('gameKeyLabel');
            const copy1 = document.getElementById('btnCopyKey');

            const modalN = new bootstrap.Modal(document.getElementById('multiKeysModal'));
            const list = document.getElementById('mkList');

            const ok = (m) => (window.mlToast ? mlToast('ok', m) : alert(m));
            const err = (m) => (window.mlToast ? mlToast('err', m) : alert(m));

            copy1?.addEventListener('click', async () => {
                const key = (box?.textContent || '').trim();
                if (!key || key === '—' || /^carregando/i.test(key)) return;
                try { await navigator.clipboard.writeText(key); ok('Chave copiada para a área de transferência!'); }
                catch { err('Não foi possível copiar.'); }
            });

            btn.addEventListener('click', async () => {
                box.textContent = 'Carregando...';
                label1.style.display = 'none';
                try {
                    const r = await fetch('carrinhoAcao.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
                        body: new URLSearchParams({ action: 'mykey', id: btn.dataset.id })
                    });
                    const j = await r.json();
                    if (!j || j.ok === false) throw new Error(j?.msg || 'Falha ao obter chave.');

                    const keys = Array.isArray(j.keys) ? j.keys : [j.key];

                    if (keys.length <= 1) {
                        label1.style.display = 'none';
                        box.textContent = keys[0] || '—';
                        modal1.show();
                        return;
                    }

                    list.innerHTML = '';
                    keys.forEach((code, i) => {
                        const item = document.createElement('div');
                        item.className = 'mk-item';

                        const lab = document.createElement('div');
                        lab.className = 'mk-label';
                        lab.textContent = `Chave ${i + 1}:`;

                        const line = document.createElement('div');
                        line.className = 'mk-line';

                        const codeBox = document.createElement('div');
                        codeBox.className = 'mk-code';
                        codeBox.textContent = code || '—';

                        const copyBtn = document.createElement('button');
                        copyBtn.type = 'button';
                        copyBtn.className = 'k-btn';
                        copyBtn.textContent = 'Copiar';
                        copyBtn.addEventListener('click', async () => {
                            try { await navigator.clipboard.writeText(code); ok('Chave copiada para a área de transferência!'); }
                            catch { err('Não foi possível copiar.'); }
                        });

                        line.appendChild(codeBox);
                        line.appendChild(copyBtn);
                        item.appendChild(lab);
                        item.appendChild(line);
                        list.appendChild(item);
                    });

                    modalN.show();
                } catch (e) {
                    err(e?.message || 'Falha ao obter chave.');
                }
            });
        })();
    </script>

    <!-- Script para adição de jogos ao carrinho + garante mensagem de sucesso -->
    <script>
        (function () {
            function hook(btnId) {
                const btn = document.getElementById(btnId);
                if (!btn) return;

                const gid = Number(btn.dataset.game);
                if (!gid) return;

                btn.addEventListener('click', async () => {
                    try {
                        const res = await fetch(`carrinhoAcao.php?action=add&id=${gid}`, {
                            method: 'POST',
                            credentials: 'same-origin'
                        });

                        const j = await res.json().catch(() => null);

                        if (j && j.ok) {
                            window.mlToast && mlToast('ok', 'Jogo adicionado ao carrinho!');
                            window.dispatchEvent(new Event('cart:sync'));
                        } else {
                            window.mlToast && mlToast('err', j?.msg || 'Não foi possível adicionar ao carrinho.');
                        }
                    } catch {
                        window.mlToast && mlToast('err', 'Falha na requisição.');
                    }
                });
            }

            hook('btnAddCart');
            hook('btnBuyAgain');
        })();
    </script>

    <footer>
        <p>&copy; 2025 Moonlight. Todos os direitos reservados.</p>
    </footer>

</body>
</html>