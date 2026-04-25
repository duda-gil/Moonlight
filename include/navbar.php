    <?php

if (session_status() !== PHP_SESSION_ACTIVE)
    session_start();
require_once __DIR__ . '/carrinhoFunc.php';
$forceGuest = isset($forceGuest) ? (bool) $forceGuest : false;
$cartCount = cart_count();

// Descobrindo qual o papel do usuário logado
$isLogged = isset($_SESSION['user_id']);
$role = $_SESSION['role'] ?? 'guest';
$isAdmin = ($role === 'admin');


// Disponibiliza o contexto da navbar
$ctx = $NAV_CONTEXT ?? 'store';
$showSearch = ($ctx !== 'admin');


// Disponibiliza as mensagens de sucesso/erro
if (session_status() !== PHP_SESSION_ACTIVE)
    session_start();
$__FLASH = $_SESSION['flash'] ?? null;
if ($__FLASH) {
    unset($_SESSION['flash']);
    $__kind = (in_array($__FLASH['tipo'] ?? '', ['success', 'ok', true], true) ? 'ok' : 'err');
    $__text = htmlspecialchars((string) ($__FLASH['msg'] ?? ''), ENT_QUOTES, 'UTF-8');
    echo "<script>
      (function(){
        function go(){ if(window.mlToast){ window.mlToast('{$__kind}','{$__text}'); } }
        if (document.readyState === 'loading') {
          document.addEventListener('DOMContentLoaded', go);
        } else { go(); }
      })();
    </script>";
}
?>

<!-- Muda o destino da logo -->
<nav class="navbar navbar-expand-lg detail">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= $ctx === 'admin' ? 'adm.php' : 'index.php' ?>">
            <img src="logomoon.png" alt="Moonlight" width="140" height="70">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain"
            aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">

            <!-- Se o contexto E o usuário for adm = NAVBAR DO ADM -->
            <?php if ($ctx === 'admin' && $isAdmin): ?>
                <ul class="navbar-nav nav-underline ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="index.php">LOJA</a></li>
                    <li class="nav-item"><a class="nav-link" href="categorias.php">CATEGORIAS</a></li>

                    <li class="nav-item dropdown">
                        <button class="navbtn" type="button">CADASTROS</button>
                        <ul class="dropdown-menu">
                            <li><a href="cadastrojogos.php">Cadastrar jogos</a></li>
                            <li><a href="cadastrocat.php">Cadastrar categorias</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <button class="navbtn" type="button">RELATÓRIOS</button>
                        <ul class="dropdown-menu">
                            <li><a href="relJogCat.php">Jogos por categoria</a></li>
                            <li><a href="vendaMes.php">Vendas por mês</a></li>
                            <li><a href="recebMes.php">Recebimentos por mês</a></li>
                        </ul>
                    </li>
                </ul>

            <!-- Qualquer outro caso = NAVBAR DA LOJA -->
            <?php else: ?>
                <ul class="navbar-nav nav-underline me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="index.php">LOJA</a></li>
                    <li class="nav-item"><a class="nav-link" href="categorias.php">CATEGORIAS</a></li>

                    <!-- Usuário NÃO está logado = INICIAR SESSÃO -->
                    <?php if (!$isLogged || $forceGuest): ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">INICIAR SESSÃO</a></li>

                    <!-- Usuário ADM logado = PERFIL COM DROPDOWN -->
                    <?php else: ?>
                        <?php if ($isAdmin): ?>
                            <li class="nav-item dropdown perfil-dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    PERFIL
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a href="perfil.php">Conta pessoal</a></li>
                                    <li><a href="adm.php">Área administrativa</a></li>
                                </ul>
                            </li>

                        <!-- Usuário comum logado = PERFIL SIMPLES -->
                        <?php else: ?>
                            <li class="nav-item"><a class="nav-link" href="perfil.php">PERFIL</a></li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <li class="nav-item"><a class="nav-link" href="suporte.php">SUPORTE</a></li>
                </ul>

                <!-- Barra de pesquisa só visivel fora do contexto ADM -->
                <?php if (($NAV_CONTEXT ?? 'store') !== 'admin'): ?>
                    <form class="ml-search" id="mlSearch" action="buscar.php" method="get" role="search" autocomplete="off">
                        <input id="mlSearchInput" name="q" type="search" placeholder="Buscar na loja"
                            aria-label="Buscar na loja" />
                        <button id="mlSearchBtn" type="submit" aria-label="Buscar">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-search" viewBox="0 0 16 16">
                                <path
                                    d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                            </svg>
                        </button>
                        <div class="ml-suggest" id="mlSuggest" hidden></div>
                    </form>
                <?php endif; ?>

                <!-- Quando a navbar for de loja, haverá o carrinho -->
                <a class="btn-cart position-relative" href="carrinho.php" aria-label="Abrir carrinho">
                    <svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" fill="currentColor"
                        class="bi bi-basket2-fill" viewBox="0 0 16 16">
                        <path
                            d="M5.929 1.757a.5.5 0 1 0-.858-.514L2.217 6H.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h.623l1.844 6.456A.75.75 0 0 0 3.69 15h8.622a.75.75 0 0 0 .722-.544L14.877 8h.623a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1.717L10.93 1.243a.5.5 0 1 0-.858.514L12.617 6H3.383zM4 10a1 1 0 0 1 2 0v2a1 1 0 1 1-2 0zm3 0a1 1 0 0 1 2 0v2a1 1 0 1 1-2 0zm4-1a1 1 0 0 1 1 1v2a1 1 0 1 1-2 0v-2a1 1 0 0 1 1-1" />
                    </svg>
                    <span class="cart-badge" data-cart-count hidden>0</span>
                </a>

            <?php endif; ?>

        </div>
    </div>
</nav>

<style>
    .ml-search {
        position: relative;
        display: flex;
        align-items: center;
        gap: .4rem
    }

    .ml-search #mlSearchInput {
        width: 420px;
        max-width: 52vw
    }

    .ml-suggest {
        position: absolute;
        left: 0;
        right: 0;
        top: calc(100% + 6px);
        background: #1b2133;
        border: 1px solid rgba(255, 255, 255, .08);
        border-radius: 10px;
        box-shadow: 0 10px 28px rgba(0, 0, 0, .45);
        max-height: 320px;
        overflow: auto;
        z-index: 1060;
    }

        .ml-s-item {
        display: flex;
        gap: 10px;
        padding: 10px 12px;
        text-decoration: none;
        color: #eaf1ff;
    }

    .ml-s-item:hover,
    .ml-s-item.is-active {
        background: #273148;
    }

    .ml-s-thumb {
        width: 72px;
        height: 40px;
        border-radius: 6px;
        object-fit: cover;
        background: #0f131c;
        flex: 0 0 auto;
    }

    .ml-s-meta {
        min-width: 0;
    }

    .ml-s-name,
    .ml-s-title {
        font-weight: 700;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .ml-s-price,
    .ml-s-prices {
        font-size: 13px;
        color: #cbe0ff;
        margin-top: 2px;
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .ml-s-badge {
        background: #4ca169;
        color: #fff;
        padding: 2px 6px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
    }

    .ml-s-prices .old {
        text-decoration: line-through;
        opacity: .8;
    }

    .ml-s-prices .new {
        font-weight: 600;
        color: #ffffff;
    }

    .ml-toast .progress {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 3px;
        background: rgba(255, 255, 255, 0.35);
        transform-origin: left center;
        transform: scaleX(0);
        animation: none;
    }

    /* Animação da barrinha de 0% a 100% */
    @keyframes mlToastBar {
        from { transform: scaleX(0); }
        to   { transform: scaleX(1); }
    }

</style>

<script>

    // Atualiza corretamente o badge do carrinho
    (() => {
        const badgeEl = document.querySelector('[data-cart-count]');
        if (!badgeEl) return;

        function setBadge(n) {
            n = parseInt(n || 0, 10);
            if (n > 0) { badgeEl.textContent = n; badgeEl.hidden = false; }
            else { badgeEl.textContent = 0; badgeEl.hidden = true; }
        }

        fetch('carrinhoAcao.php?action=status', { credentials: 'same-origin' })
            .then(r => r.json()).then(j => { if (j && j.ok && 'count' in j) setBadge(j.count); })
            .catch(() => { });

        const _fetch = window.fetch;
        window.fetch = async (...args) => {
            const res = await _fetch(...args);
            try {
                const url = typeof args[0] === 'string' ? args[0] : (args[0] && args[0].url) || '';
                if (url.includes('carrinhoAcao.php')) {
                    res.clone().json().then(j => {
                        if (j && typeof j.count !== 'undefined') setBadge(j.count);
                    }).catch(() => { });
                }
            } catch (e) { }
            return res;
        };

        window.addEventListener('cart:sync', () => {
            fetch('carrinhoAcao.php?action=status')
                .then(r => r.json()).then(j => { if (j && j.ok) setBadge(j.count); });
        });
    })();


    // Barra de pesquisa com autocomplete conforme o usuário digita
    (() => {
        const form = document.getElementById('mlSearch');
        const input = document.getElementById('mlSearchInput');
        const box = document.getElementById('mlSuggest');
        if (!form || !input || !box) return;

        const MIN_CHARS = 1;
        const ENDPOINT = 'buscar.php?ajax=1';
        let aborter = null, lastQ = '', cache = [];

        function goSearch() {
            const q = (input.value || '').trim();
            const url = 'buscar.php' + (q ? ('?q=' + encodeURIComponent(q)) : '');
            window.location.href = url;
        }

        form.addEventListener('submit', (e) => { e.preventDefault(); e.stopPropagation(); goSearch(); });

        function priceTpl(it) {
            if (it.desconto && it.desconto > 0) {
                return `<span class="ml-s-badge">${it.desconto}%</span>
              <span class="old">${it.preco}</span>
              <span class="new">${it.preco_novo}</span>`;
            }
            return `<span class="new">${it.preco_novo || it.preco}</span>`;
        }

        function render(list) {
            if (!list || !list.length) { box.hidden = true; box.innerHTML = ''; return; }
            box.innerHTML = list.map(it => `
      <a class="ml-s-item" href="jogo.php?id=${encodeURIComponent(it.id)}">
        <img class="ml-s-thumb" src="${(it.banner || '').replace(/"/g, '&quot;')}" alt="">
        <div class="ml-s-meta">
          <div class="ml-s-title">${(it.nome || '').replace(/</g, '&lt;')}</div>
          <div class="ml-s-prices">${priceTpl(it)}</div>
        </div>
      </a>`).join('') + `
      <hr class="ml-s-divider">
      <div class="ml-s-footer">
        <a href="buscar.php?q=${encodeURIComponent(input.value.trim())}">ver todos os resultados</a>
      </div>`;
            box.hidden = false;
        }

        const debounce = (fn, ms) => { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); } };

        const ask = debounce(async () => {
            const q = (input.value || '').trim();
            if (q.length < MIN_CHARS) { box.hidden = true; box.innerHTML = ''; return; }
            if (q === lastQ) { render(cache); return; }
            lastQ = q;

            try {
                if (aborter) aborter.abort();
                aborter = new AbortController();

                const r = await fetch(`${ENDPOINT}&q=${encodeURIComponent(q)}`, { signal: aborter.signal });
                const j = await r.json();
                cache = Array.isArray(j?.results) ? j.results : [];
                render(cache);
            } catch (e) { }
        }, 120);

        input.addEventListener('input', ask);
        input.addEventListener('focus', () => {
            const q = (input.value || '').trim();
            if (q.length >= MIN_CHARS && cache.length) { render(cache); }
            else { ask(); }
        });

        document.addEventListener('click', (e) => { if (!form.contains(e.target)) box.hidden = true; });

    })();
</script>


<!-- Script de animação da barrinha de mensagens -->
<script>
(() => {
    if (window.mlToast) return;

    window.mlToast = function (kind, text, timeout = 3200) {
        const ok = (kind === 'ok' || kind === 'success' || kind === true);

        const el = document.createElement('div');
        el.className = 'ml-toast ' + (ok ? 'is-success' : 'is-error') + ' anim-in';
        el.innerHTML =
            '<span class="ico">' + (ok ? '✔' : '✖') + '</span>' +
            '<span class="text">' + (text || '') + '</span>' +
            '<button class="close" aria-label="Fechar">×</button>' +
            '<div class="progress"></div>';

        document.body.appendChild(el);

        const bar = el.querySelector('.progress');
        if (bar) {
            const dur = Math.max(400, timeout - 400);

            bar.style.transformOrigin = 'left center';
            bar.style.transform = 'scaleX(0)';
            bar.style.transition = 'transform ' + dur + 'ms linear';

            void bar.offsetWidth;

            requestAnimationFrame(() => {
                bar.style.transform = 'scaleX(1)';
            });
        }

        function kill() {
            el.classList.remove('anim-in');
            el.classList.add('anim-out');
            setTimeout(() => el.remove(), 220);
        }

        const t = setTimeout(kill, timeout);
        el.querySelector('.close').onclick = () => {
            clearTimeout(t);
            kill();
        };
    };

    window.flash = function (kind, text, timeout) {
        window.mlToast(kind, text, timeout);
    };
})();
</script>
