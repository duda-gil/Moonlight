<?php
require_once 'include/conexao.php';
session_start();

// Pega os jogos sem desconto, para "Em destaque"
$stmt = $conn->prepare("SELECT id, nome, preco, desconto, url_banner FROM jogos WHERE status = 'ativo' and desconto = 0");
$stmt->execute();
$resultado = $stmt->get_result();

// Pega os jogos com desconto, para "Promoções e Descontos"
$stmt1 = $conn->prepare("SELECT id, nome, preco, desconto, url_banner FROM jogos WHERE status = 'ativo' and desconto != 0");
$stmt1->execute();
$resultado1 = $stmt1->get_result();
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

    <title>Moonlight</title>
</head>

<body>
    <div class="content">

        <?php require_once 'include/navbar.php' ?>

        <!-- Carrossel -->
        <div class="borda">
            <section class="recomendados">
                <h3>Recomendados</h3>

                <div class="carrossel" id="carrossel">
                    <div class="carrossel-slide">
                        <a href="jogo.php?id=9" class="carrossel-link">
                            <img src="uploads/p3.jpg" alt="Persona 3 Reload">
                        </a>
                    </div>

                    <div class="carrossel-slide">
                        <a href="jogo.php?id=5" class="carrossel-link">
                            <img src="uploads/hks.jpg" alt="Hollow Knight: Silksong">
                        </a>
                    </div>
                    
                    <div class="carrossel-slide">
                        <a href="jogo.php?id=7" class="carrossel-link">
                            <img src="uploads/cyb.jpeg" alt="Cyberpunk 2077">
                        </a>
                    </div>

                    <button class="carrossel-btn ant">&#10094;</button>
                    <button class="carrossel-btn prox">&#10095;</button>

                    <div class="carrossel-indicadores">
                        <span class="dot ativo"></span>
                        <span class="dot"></span>
                        <span class="dot"></span>
                    </div>
                </div>
            </section>
        </div>

        <!-- Sessão "Em destaque" -->
        <section class="destaques">
            <div class="container-xxl px-3 px-md-4">
                <h2>Em Destaque</h2>
                <div class="jogos">
                    <?php

                    // Formatador para padrão R$ + 2 casas após a virgula
                    function brl($valor): string
                    {
                        return 'R$ ' . number_format((float) $valor, 2, ',', '.'); // sempre 2 casas, vírgula decimal
                    }

                    // Busca todos os jogos do banco sem desconto
                    while ($linha = $resultado->fetch_assoc()) {

                        // Garante que nenhum preço seja negativo
                        $preco = max(0.0, (float) $linha['preco']); ?>

                        <!-- Um card a cada jogo -->
                        <a href="jogo.php?id=<?= $linha['id'] ?>" class="jogo-card">
                            <div class="jogo">
                                <img src="<?= $linha['url_banner'] ?>" alt="<?= $linha['nome'] ?>">
                                <h3><?= $linha['nome'] ?></h3>
                                <p><?= brl($preco) ?></p>
                            </div>
                        </a>
                    <?php } ?>
                </div>
            </div>
        </section>


        <!-- Sessão "Promoções e Descontos -->
        <section class="promocoes">
            <div class="container-xxl px-3 px-md-4">
                <h2>Promoções e Descontos</h2>
                <div class="jogos">

                    <!-- Busca todos os jogo do banco com desconto -->
                    <?php while ($linha1 = $resultado1->fetch_assoc()) {

                        // Garante que nenhum preço seja negativo
                        $preco = max(0.0, (float) $linha1['preco']);

                        // Nenhum desconto poder menor que 0% e maior que 100%
                        $desconto = max(0, min(100, (float) $linha1['desconto']));

                        // Cálculo do desconto
                        $novoPreco = $preco * (1 - $desconto / 100);
                        ?>

                        <a href="jogo.php?id=<?= (int) $linha1['id'] ?>" class="jogo-card">
                            <div class="jogo">
                                <img src="<?= htmlspecialchars($linha1['url_banner']) ?>"
                                    alt="<?= htmlspecialchars($linha1['nome']) ?>">
                                <h3><?= htmlspecialchars($linha1['nome']) ?></h3>

                                <!-- Mostra o desconto, velho preço e novo preço -->
                                <span class="desconto"><?= (int) $desconto ?>%</span>
                                <span class="preco0"><?= brl($preco) ?></span>
                                <span class="preco1"><?= brl($novoPreco) ?></span>
                            </div>
                        </a>
                    <?php } ?>
                </div>
            </div>
        </section>


        <footer>
            <p>&copy; 2025 Moonlight. Todos os direitos reservados.</p>
        </footer>
    </div>

    <!-- Script do carrossel -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const slides = document.querySelectorAll(".carrossel-slide");
            const dots = document.querySelectorAll(".dot");
            const btnProx = document.querySelector(".carrossel-btn.prox");
            const btnAnt = document.querySelector(".carrossel-btn.ant");
            let index = 0;
            let intervalo;

            function mostrarSlide(i) {
                slides.forEach((slide, idx) => {
                    slide.classList.toggle("ativo", idx === i);
                    dots[idx].classList.toggle("ativo", idx === i);
                });
            }

            function proximoSlide() {
                index = (index + 1) % slides.length;
                mostrarSlide(index);
            }

            function anteriorSlide() {
                index = (index - 1 + slides.length) % slides.length;
                mostrarSlide(index);
            }

            function iniciarAutoTroca() {
                intervalo = setInterval(proximoSlide, 5000);
            }

            function pararAutoTroca() {
                clearInterval(intervalo);
            }

            btnProx.addEventListener("click", () => {
                pararAutoTroca();
                proximoSlide();
                iniciarAutoTroca();
            });

            btnAnt.addEventListener("click", () => {
                pararAutoTroca();
                anteriorSlide();
                iniciarAutoTroca();
            });

            dots.forEach((dot, i) => {
                dot.addEventListener("click", () => {
                    pararAutoTroca();
                    index = i;
                    mostrarSlide(index);
                    iniciarAutoTroca();
                });
            });

            mostrarSlide(index);
            iniciarAutoTroca();
        });
    </script>

    <!-- Script de busca com autocomplete -->
    <script>
        (() => {
            const form = document.getElementById('mlSearch');
            const input = document.getElementById('mlSearchInput');
            const box = document.getElementById('mlSuggest');

            const ENDPT = 'buscar.php?ajax=1&q=';   // relativo (funciona em subpasta)
            let timer = null, ctrl = null, items = [], pos = -1;

            function hide() { box.hidden = true; box.innerHTML = ''; items = []; pos = -1; }
            function show() { box.hidden = false; }

            function row(r) {
                return `
                    <a class="ml-s-item" role="option" href="jogo.php?id=${r.id}">
                        <img class="ml-s-thumb" src="${r.banner || ''}" alt="">
                            <div class="ml-s-meta">
                                <div class="ml-s-title">${r.nome}</div>
                                <div class="ml-s-prices">
                                    ${r.desconto > 0 ? `<span class="ml-s-badge">${r.desconto}%</span>` : ``}
                                    ${r.desconto > 0 ? `<span style="text-decoration:line-through;opacity:.9;">${r.preco}</span>` : ``}
                                    <strong>${r.preco_novo}</strong>
                                </div>
                            </div>
                    </a>`;
            }

            function render(list, q) {
                if (!list.length) { hide(); return; }
                const html = list.map(row).join('<hr class="ml-s-divider">')
                    + `<div class="ml-s-footer"><a href="buscar.php?q=${encodeURIComponent(q)}">Ver todos os resultados</a></div>`;
                box.innerHTML = html; show();
                items = Array.from(box.querySelectorAll('.ml-s-item')); pos = -1;
            }

            function fetchSuggest(q) {
                if (ctrl) ctrl.abort();
                ctrl = new AbortController();
                fetch(ENDPT + encodeURIComponent(q), { signal: ctrl.signal, headers: { 'Accept': 'application/json' } })
                    .then(r => r.ok ? r.json() : { results: [] })
                    .then(j => render(j.results || [], q))
                    .catch(() => { }); // abort/erro silencioso
            }

            input.addEventListener('input', () => {
                const q = input.value.trim();
                clearTimeout(timer);
                if (q.length < 1) { hide(); return; }
                timer = setTimeout(() => fetchSuggest(q), 160);
            });

            input.addEventListener('keydown', (e) => {
                if (box.hidden) return;
                if (e.key === 'ArrowDown') { e.preventDefault(); pos = (pos + 1) % items.length; }
                else if (e.key === 'ArrowUp') { e.preventDefault(); pos = (pos - 1 + items.length) % items.length; }
                else if (e.key === 'Enter') {
                    if (pos >= 0 && items[pos]) { e.preventDefault(); items[pos].click(); return; }
                } else if (e.key === 'Escape') { hide(); return; } else { return; }
                items.forEach(el => el.classList.remove('active'));
                if (items[pos]) items[pos].classList.add('active');
            });

            form.addEventListener('submit', (e) => {
                if (!input.value.trim()) { e.preventDefault(); hide(); }
            });
            document.addEventListener('click', (ev) => { if (!form.contains(ev.target)) hide(); });
        })();
    </script>

</body>
</html>