<?php
require_once __DIR__ . '/include/conexao.php';
require_once __DIR__ . '/include/carrinhoFunc.php';
if (session_status() !== PHP_SESSION_ACTIVE)
    session_start();


// Bloqueia as ações do carrinho para os usuários não logados
function cart_require_login_or_fail()
{
    if (empty($_SESSION['id'])) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => false,
            'code' => 'auth',
            'msg' => 'Faça login ou cadastre-se para adicionar itens ao carrinho.'
        ]);
        exit;
    }
}

// Exibe a lista de jogos e dá o total do carrinho 
$items = cart_items($conn);
$total = cart_total($conn);

// Dá o contexto de loja para a navbar, garantindo os devidos elementos
$NAV_CONTEXT = 'store';
?>

<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>Carrinho - Moonlight</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/stylesNav.css">

    <style>
        :root {
            --cart-row-h: 112px;
        }

        .carrinho-wrap {
            max-width: 1100px;
            margin: 24px auto;
            padding: 0 16px;
        }

        .carrinho-grid {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) 340px !important;
            width: 100% !important;
            gap: 20px !important;
            align-items: start !important;
        }

        @media (max-width:980px) {
            .carrinho-grid {
                grid-template-columns: 1fr;
            }
        }

        .carrinho-card {
            flex: none !important;
            background: #141821;
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 10px;
            overflow: hidden;
            width: 640px !important;
            box-sizing: border-box;
        }

        .carrinho-vazio {
            display: flex;
            align-items: center;
            justify-content: center;
            height: var(--cart-row-h);
            padding: 12px;
            text-align: center;
            color: #cfe2ff;
            width: 100%;
            box-sizing: border-box;
        }

        .carrinho-i {
            display: flex;
            gap: 14px;
            padding: 12px;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, .06);
        }

        .carrinho-thumb {
            width: 140px;
            height: 78px;
            border-radius: 8px;
            object-fit: cover;
            flex-shrink: 0;
            background: #0f131c;
        }

        .carrinho-meta {
            flex: 1;
            min-width: 0;
        }

        .carrinho-nome {
            font-weight: 700;
            margin: 0 0 6px 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .precos {
            display: flex;
            align-items: center;
            gap: 12px;
            line-height: 1;
        }

        .desconto {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 42px;
            height: 24px;
            padding: 0 10px;
            background: #4ca169;
            color: #fff;
            font-weight: 700;
            font-size: 13px;
            border-radius: 4px;
        }

        .preco0 {
            color: #cbd7ff;
            text-decoration: line-through;
            font-size: 13px;
            opacity: .95;
        }

        .preco1 {
            color: #fff;
            font-weight: 700;
            font-size: 14px;
        }

        .carrinho-qnt {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .carrinho-qnt input {
            width: 64px;
            height: 36px;
            text-align: center;
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, .12);
            background: rgba(255, 255, 255, .05);
            color: #fff;
        }

        .carrinho-sub {
            font-weight: 700;
        }

        .carrinho-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-min {
            background: #2a3246;
            border: none;
            color: #dbe6ff;
            padding: 8px 11px;
            border-radius: 6px;
        }

        .btn-min:hover {
            background: #3a4561;
        }

        .carrinho-side {
            flex: 0 0 340px;
            width: 340px;
            background: #141821;
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 10px;
            padding: 16px;
            height: fit-content;
        }

        .carrinho-total {
            display: flex;
            justify-content: space-between;
            font-size: 18px;
            font-weight: 700;
            margin-top: 8px;
        }

        .pay-wrap {
            margin-top: 14px;
        }

        .form-select.pag-select {
            --bs-form-select-bg-img: none;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            width: 100%;
            height: 44px;
            border-radius: 8px;
            border: none;
            background-color: #1b2133;
            color: #eaf1ff;
            padding: 8px 40px 8px 12px;
            transition: .15s;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="%23c9d6ff"><path d="M3.2 5.5 8 10.3l4.8-4.8.9.9L8 12.1 2.3 6.4l.9-.9z"/></svg>');
            background-repeat: no-repeat;
            background-position: right .8rem center;
            background-size: 12px;
        }

        .form-select.pag-select:hover {
            border: none;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="%237aa3ef"><path d="M3.2 5.5 8 10.3l4.8-4.8.9.9L8 12.1 2.3 6.4l.9-.9z"/></svg>');
        }

        .form-select.pag-select:focus {
            box-shadow: none !important;
        }

        .form-select.pag-select option {
            background: #0f1626;
            color: #eaf1ff;
        }

        .pay-actions {
            margin-top: 14px;
        }

        .btn-full {
            width: 100%;
            height: 44px;
            border: none;
            border-radius: 8px;
            background: #7aa9ef;
            color: #fff;
            font-weight: 700;
        }

        .btn-full:hover {
            background: #5f8fe1;
        }

        #cardSectionGrid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: auto auto auto;
            column-gap: 16px;
            row-gap: 12px;
        }

        #cardNumGroup {
            grid-column: 1;
            grid-row: 1;
        }

        #cardNameGroup {
            grid-column: 2;
            grid-row: 1;
        }

        #cardLine2 {
            grid-column: 1;
            grid-row: 2;
            display: grid;
            grid-template-columns: 1fr 1fr;
            column-gap: 24px;
            row-gap: 8px;
            margin-top: 2px;
        }

        #cardLine2 .form-label {
            margin-bottom: 6px;
        }

        #cardPreviewWrap {
            grid-column: 2;
            grid-row: 2 / span 2;
            align-self: start;
            padding-top: 8px;
        }

        #parcelasLeft {
            grid-column: 1;
            grid-row: 3;
            width: 100%;
        }

        @media (max-width:991.98px) {
            #cardSectionGrid {
                grid-template-columns: 1fr;
                grid-template-rows: auto auto auto auto auto;
            }

            #cardNumGroup,
            #cardNameGroup,
            #cardLine2,
            #parcelasLeft,
            #cardPreviewWrap {
                grid-column: 1;
            }

            #cardPreviewWrap {
                grid-row: auto;
                padding-top: 12px;
            }

            #cardLine2 {
                grid-template-columns: 1fr;
            }
        }

        #checkoutModal .modal-dialog {
            max-width: 1000px;
        }

        #checkoutModal .modal-body {
            padding-bottom: 8px;
        }

        #checkoutModal .modal-footer {
            padding-top: 8px;
        }

        #checkoutModal .form-control:focus,
        #checkoutModal .form-select:focus,
        #checkoutModal .form-check-input:focus {
            box-shadow: none !important;
            outline: 0 !important;
        }

        #checkoutModal .modal-header .btn-close {
            background: none !important;
            border: none !important;
            box-shadow: none !important;
            opacity: 1;
            width: 22px;
            height: 22px;
            padding: 0;
            position: relative;
        }

        #checkoutModal .modal-header .btn-close::before {
            content: '×';
            display: block;
            font-size: 20px;
            line-height: 1;
            color: #fff;
        }

        #checkoutModal .modal-header .btn-close:hover::before {
            color: #7aa3ef;
        }

        #checkoutModal .modal-header .btn-close:focus {
            box-shadow: none !important;
        }

        #checkoutModal .btn-finish {
            background: #43a36a;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            padding: .55rem 1rem;
        }

        #checkoutModal .btn-finish:hover {
            background: #3a8f5d;
        }
    </style>
</head>

<body>
    <div class="content">

        <?php

        // Monta a mensagem de sucesso/erro que será utilizada futuramente
        $flash_text = '';
        $flash_kind = 'ok';
        if (isset($_GET['ok'])) {
            $flash_kind = ((string) $_GET['ok'] === '1') ? 'ok' : 'err';
            $flash_text = $_GET['msg'] ?? ($flash_kind === 'ok' ? 'Compra finalizada com sucesso!' : 'Não foi possível finalizar a compra.');
        }
        ?>
        <?php if ($flash_text): ?>
            <span id="flashData" data-kind="<?= htmlspecialchars($flash_kind) ?>"
                data-text="<?= htmlspecialchars($flash_text) ?>" hidden></span>
        <?php endif; ?>

        <?php require __DIR__ . '/include/navbar.php'; ?>

        <br><br>

        <div class="carrinho-wrap">
            <h2>Seu carrinho</h2>

            <div class="carrinho-grid">

                <!-- Lista de itens + detalhes -->
                <div class="carrinho-card">
                    <?php if (!$items): ?>
                        <div class="carrinho-vazio">Seu carrinho está vazio.</div>
                    <?php else:
                        foreach ($items as $it): ?>
                            <div class="carrinho-i" data-id="<?= $it['id'] ?>">
                                <img class="carrinho-thumb" src="<?= htmlspecialchars($it['thumb']) ?>"
                                    alt="<?= htmlspecialchars($it['nome']) ?>">
                                
                                <!-- Cálculo com o menor preço caso em desconto -->
                                <div class="carrinho-meta">
                                    <div class="carrinho-nome"><?= htmlspecialchars($it['nome']) ?></div>
                                    <div class="precos">
                                        <?php if ($it['desconto'] > 0): ?>
                                            <span class="desconto"><?= (int) $it['desconto'] ?>%</span>
                                            <span class="preco0"><?= brl_cart($it['preco']) ?></span>
                                        <?php endif; ?>
                                        <span class="preco1"><?= brl_cart($it['preco_n']) ?></span>
                                    </div>
                                </div>

                                <!-- Adição ou subtração de itens -->
                                <div class="carrinho-qnt">
                                    <input type="number" min="1" max="99" value="<?= (int) $it['qty'] ?>" class="qnt-input">
                                    <span class="carrinho-sub"><?= brl_cart($it['subtotal']) ?></span>
                                </div>

                                <!-- Remoção de itens -->
                                <div class="carrinho-actions">
                                    <button class="btn-min js-remove">Remover</button>
                                </div>
                            </div>
                        <?php endforeach; endif; ?>
                </div>

                <!-- Exibição dos detalhes do carrinho + seleção da forma de pagamento + botão para esvaziar carrinho -->
                <aside class="carrinho-side">
                    <div class="d-flex justify-content-between">
                        <span>Itens</span> <strong id="counter"><?= cart_count() ?></strong>
                    </div>
                    <div class="carrinho-total">
                        <span>Total</span> <span id="total"><?= brl_cart($total) ?></span>
                    </div>

                    <div class="pay-wrap">
                        <label for="MetPag" class="form-label" style="font-weight:700;">Selecione uma forma de
                            pagamento:</label>
                        <select id="MetPag" class="form-select pag-select" aria-label="Forma de pagamento">
                            <option value="" selected disabled>Escolha...</option>
                            <option value="pix">Pix</option>
                            <option value="debito">Cartão de Débito</option>
                            <option value="credito">Cartão de Crédito</option>
                        </select>
                    </div>

                    <br>

                    <div class="pay-actions">
                        <button id="btnGoCheckout" type="button" class="btn-full">Continuar para o pagamento</button>
                    </div>

                    <?php if ($items): ?>
                        <div class="mt-2"><button class="btn-min w-100 js-clear">Esvaziar carrinho</button></div>
                    <?php endif; ?>
                </aside>
            </div>
        </div>

        <footer>
            <p>&copy; 2025 Moonlight. Todos os direitos reservados.</p>
        </footer>
    </div>

    <!-- Modal de pagamento -->
    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0" style="background:#141a27;">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-white" id="checkoutTitle">Pagamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body py-3">
                    <input type="hidden" name="payment_method" id="payment_method">
                    <div id="orderData" data-total="<?= (float) $total ?>"></div>

                    <div class="mx-auto" style="max-width:1100px;padding:0 16px;">
                        <div class="row g-3 align-items-start">
                            <div class="col-lg-7">

                                <!-- Informações do pagador (quem está pagando a compra) -->
                                <div class="rounded-3 p-3 mb-3" style="background:#0f1724;">
                                    <h6 class="text-white-50 mb-3">Dados do pagador</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label text-white-50">Nome completo</label>
                                            <input class="form-control bg-dark text-white border-0" id="payer_nome"
                                                required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label text-white-50">Email</label>
                                            <input type="email" class="form-control bg-dark text-white border-0"
                                                id="payer_email" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label text-white-50">CPF</label>
                                            <input class="form-control bg-dark text-white border-0" id="payer_cpf"
                                                inputmode="numeric" maxlength="14" placeholder="000.000.000-00"
                                                required>
                                            <div class="form-text text-white-50">Informe o CPF do titular que irá pagar.
                                            </div>
                                            <div id="cpfHelp" class="text-warning small mt-1 d-none">CPF inválido.</div>
                                            <div id="cpfOk" class="text-success small mt-1 d-none">✔ CPF válido.</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Dados de pagamento via cartão de crédito -->
                                <div id="cardSection" class="rounded-3 p-3 mb-3 d-none" style="background:#0f1724;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="text-white-50 mb-0">Dados do cartão</h6>
                                        <small class="text-white-50" id="semJurosMsg">Parcele em até 3x sem
                                            juros</small>
                                    </div>

                                    <div id="cardSectionGrid">

                                        <!-- Número do cartão e nome impresso -->
                                        <div id="cardNumGroup">
                                            <label class="form-label text-white-50">Número do cartão</label>
                                            <input class="form-control bg-dark text-white border-0" id="card_number"
                                                inputmode="numeric" maxlength="19" placeholder="0000 0000 0000 0000">
                                        </div>
                                        <div id="cardNameGroup">
                                            <label class="form-label text-white-50">Nome impresso</label>
                                            <input class="form-control bg-dark text-white border-0" id="card_name"
                                                maxlength="40" placeholder="Como no cartão">
                                        </div>

                                        <!-- Validade e CVV -->
                                        <div id="cardLine2">
                                            <div>
                                                <label class="form-label text-white-50">Validade</label>
                                                <input class="form-control bg-dark text-white border-0" id="card_expiry"
                                                    maxlength="5" placeholder="MM/AA">
                                            </div>
                                            <div>
                                                <label class="form-label text-white-50">CVV</label>
                                                <input type="password" class="form-control bg-dark text-white border-0"
                                                    id="card_cvv" maxlength="4" inputmode="numeric">
                                            </div>
                                        </div>

                                        <!-- Cartãozinho -->
                                        <div id="cardPreviewWrap">
                                            <div class="rounded-3 p-3" id="cardPreview"
                                                style="background:#1b2335;width:100%;">
                                                <div class="d-flex justify-content-between">
                                                    <div class="rounded-2"
                                                        style="width:34px;height:24px;background:#d6d6d6;"></div>
                                                    <div class="d-flex gap-2">
                                                        <div class="rounded-circle"
                                                            style="width:18px;height:18px;background:#e54b4b;opacity:.8;">
                                                        </div>
                                                        <div class="rounded-circle"
                                                            style="width:18px;height:18px;background:#f1c54b;opacity:.8;margin-left:-6px;">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 text-white fw-bold" id="cardPreviewNumber">•••• ••••
                                                    •••• ••••</div>
                                                <div class="d-flex justify-content-between mt-1 text-white-50">
                                                    <small id="cardPreviewName">NOME NO CARTÃO</small>
                                                    <small id="cardPreviewExp">MM/AA</small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Escolha de parcelas -->
                                        <div id="parcelasLeft" class="d-none">
                                            <label class="form-label text-white-50">Parcelas (crédito)</label>
                                            <select class="form-select pag-select" id="card_installments"></select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pagamento via pix -->
                                <div id="pixSection" class="rounded-3 p-3 mb-3 d-none">
                                    <div class="d-grid gap-2">
                                        <button type="button" id="btnGerarPix" class="btn btn-finish w-100">
                                            Gerar QR Code
                                        </button>
                                    </div>
                                </div>

                                <!-- Mensagem de compra bem sucedida -->
                                <div id="feedback" class="alert alert-success d-none mt-2 mb-0" role="alert">Compra
                                    finalizada com sucesso!</div>
                            </div>

                            <!-- Resumo do que foi solicitado na compra -->
                            <div class="col-lg-5">
                                <div class="rounded-3 p-3" style="background:#0f1724;">
                                    <h6 class="text-white-50 mb-3">Resumo do pedido</h6>

                                    <div class="mb-2">
                                        <div class="text-white-50">Itens do pedido</div>
                                        <div class="text-white">
                                            <?php if ($items):
                                                foreach ($items as $it): ?>
                                                    <div class="d-flex justify-content-between">
                                                        <span><?= htmlspecialchars($it['nome']) ?></span>
                                                        <span><?= brl_cart($it['subtotal']) ?></span>
                                                    </div>
                                                <?php endforeach; else: ?>
                                                <small class="text-white-50">Seu carrinho está vazio.</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <hr class="border-secondary">

                                    <div class="d-flex justify-content-between">
                                        <span class="text-white-50">Forma de pagamento</span>
                                        <span class="text-white" id="resumoMetodo">—</span>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <span class="text-white-50">Parcelas</span>
                                        <span class="text-white" id="resumoParcelas">—</span>
                                    </div>
                                    <div class="d-flex justify-content-between mt-3 fw-bold">
                                        <span class="text-white">Total a pagar</span>
                                        <span class="text-white" id="resumoTotal"><?= brl_cart($total) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" id="btnFinalizar" class="btn-finish">Finalizar compra</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mudança no modal de chaves caso o usuário compre mais de uma -->
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
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Caminho do QR code
    $pix_qr_url = 'uploads/qrcode.jpg';
    ?>

    <!-- Modal do QR code do pix -->
    <div class="modal fade" id="pixQrModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0" style="background:#141a27;color:#eaf1ff;">
                <div class="modal-header border-0">
                    <h5 class="modal-title">QR Code PIX</h5>
                </div>
                <div class="modal-body text-center">
                    <?php if ($pix_qr_url): ?>
                        <img src="<?= htmlspecialchars($pix_qr_url) ?>" alt="QR Code PIX" class="img-fluid rounded-3"
                            style="max-width:320px">
                    <?php else: ?>
                        <div class="text-white-50">QR Code não encontrado em /uploads.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>


    <!-- Script de atualização do carrinho pós compra -->
    <script>
        async function runCheckoutAndHandleKeys(showToast = true, extra = {}) {
            const params = new URLSearchParams({ action: 'checkout' });

            if (extra.form_pag) {
                params.set('form_pag', extra.form_pag);
            }
            if (extra.parcelas) {
                params.set('parcelas', String(extra.parcelas));
            }

            const r = await fetch('carrinhoAcao.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
                body: params
            });

            const j = await r.json().catch(() => null);
            if (!r.ok || !j || j.ok === false) {
                throw new Error(j?.msg || 'Falha ao finalizar a compra.');
            }

            const badge = document.querySelector('[data-cart-count]');
            if (badge) { badge.textContent = '0'; badge.hidden = true; }
            const counter = document.getElementById('counter');
            if (counter) counter.textContent = '0';
            const totalEl = document.getElementById('total');
            if (totalEl) totalEl.textContent = 'R$ 0,00';

            const listCard = document.querySelector('.carrinho-card');
            if (listCard) {
                listCard.querySelectorAll('.carrinho-i').forEach(n => n.remove());
                let empty = listCard.querySelector('.carrinho-vazio');
                if (!empty) {
                    empty = document.createElement('div');
                    empty.className = 'carrinho-vazio';
                    empty.textContent = 'Seu carrinho está vazio.';
                    listCard.appendChild(empty);
                } else {
                    empty.hidden = false;
                }
            }

            const clearWrap = document.querySelector('.js-clear')?.closest('.mt-2');
            if (clearWrap) clearWrap.remove();
            const sel = document.getElementById('MetPag');
            if (sel) sel.selectedIndex = 0;

            if (showToast) mlToast('ok', j.msg || 'Compra finalizada com sucesso!');
            return j;
        }
    </script>

    <!-- Script principal dos dados fornecidos -->
    <script>

        // Verifica se o usuário está logado para algumas atividades 
        (function () {
            const select = document.getElementById('MetPag');
            const goBtn = document.getElementById('btnGoCheckout');
            const modal = new bootstrap.Modal(document.getElementById('checkoutModal'), { backdrop: 'static' });

            const isLogged = <?= !empty($_SESSION['id']) ? 'true' : 'false' ?>;
            const loginMsg = 'Faça login ou cadastre-se para efetuar uma compra.';
            const emptyCartMsg = 'Selecione itens antes de finalizar a compra.';

            // Quantos itens há no carrinho
            const cartCounterEl = document.getElementById('counter');
            const getCartCount = () => parseInt(cartCounterEl?.textContent || '0', 10) || 0;

            const methodInput = document.getElementById('payment_method');
            const title = document.getElementById('checkoutTitle');
            const orderData = document.getElementById('orderData');

            const cardSection = document.getElementById('cardSection');
            const pixSection = document.getElementById('pixSection');
            const btnFinal = document.getElementById('btnFinalizar');
            const feedback = document.getElementById('feedback');

            // CPF
            const cpf = document.getElementById('payer_cpf');
            const cpfHelp = document.getElementById('cpfHelp');
            const cpfOk = document.getElementById('cpfOk');

            // Cartão
            const cardNum = document.getElementById('card_number');
            const cardExp = document.getElementById('card_expiry');
            const cardCVV = document.getElementById('card_cvv');
            const cardName = document.getElementById('card_name');
            const prevNum = document.getElementById('cardPreviewNumber');
            const prevName = document.getElementById('cardPreviewName');
            const prevExp = document.getElementById('cardPreviewExp');
            const semJurosMsg = document.getElementById('semJurosMsg');

            // Parcelas
            const parcelasWrap = document.getElementById('parcelasLeft');
            const parcelasSel = document.getElementById('card_installments');

            // Resumo
            const resumoMetodo = document.getElementById('resumoMetodo');
            const resumoParcelas = document.getElementById('resumoParcelas');
            const resumoTotal = document.getElementById('resumoTotal');

            // Pix
            const btnPix = document.getElementById('btnGerarPix');
            const qrEl = document.getElementById('pixQrModal');
            const pixQr = new bootstrap.Modal(qrEl, { backdrop: 'static', keyboard: false });

            // Formatação para padrão R$
            const formatBRL = v => v.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

            // Máscara de CPF
            function maskCPF(v) { v = v.replace(/\D/g, '').slice(0, 11); v = v.replace(/(\d{3})(\d)/, '$1.$2'); v = v.replace(/(\d{3})(\d)/, '$1.$2'); v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2'); return v; }

            // Máscara de número de cartão
            function maskCard(v) { return v.replace(/\D/g, '').slice(0, 16).replace(/(\d{4})(?=\d)/g, '$1 ').trim(); }

            // Máscara de validade do cartão
            function maskExp(v) { v = v.replace(/\D/g, '').slice(0, 4); return v.length >= 3 ? v.slice(0, 2) + '/' + v.slice(2) : v; }

            // Validação de CPF
            function cpfValido(c) {
                c = (c || '').replace(/\D/g, ''); if (c.length !== 11 || /^(\d)\1+$/.test(c)) return false;
                let s = 0; for (let i = 1; i <= 9; i++) s += parseInt(c.substring(i - 1, i)) * (11 - i);
                let r = (s * 10) % 11; if (r >= 10) r = 0; if (r !== parseInt(c[9])) return false;
                s = 0; for (let i = 1; i <= 10; i++) s += parseInt(c.substring(i - 1, i)) * (12 - i);
                r = (s * 10) % 11; if (r >= 10) r = 0; return r === parseInt(c[10]);
            }

            // Gera as opções de parcelamento
            function gerarOpcoesParcelas(total) {
                const ops = [];
                for (let n = 1; n <= 10; n++) {
                    let i = 0; if (n >= 4 && n <= 6) i = 0.005; else if (n >= 7 && n <= 10) i = 0.01;
                    let parcela, tot;
                    if (i === 0) { parcela = total / n; tot = total; }
                    else { parcela = total * (i * Math.pow(1 + i, n)) / (Math.pow(1 + i, n) - 1); tot = parcela * n; }
                    ops.push({ n, parcela, tot, i });
                }
                return ops;
            }

            // Máscaras do cartãozinho e de CPF
            cpf.addEventListener('input', e => {
                e.target.value = maskCPF(e.target.value);
                const raw = e.target.value.replace(/\D/g, '');
                const ok = raw.length === 11 && cpfValido(e.target.value);
                cpfHelp.classList.toggle('d-none', ok || raw.length < 11);
                cpfOk.classList.toggle('d-none', !ok);
            });
            cardNum.addEventListener('input', e => { e.target.value = maskCard(e.target.value); prevNum.textContent = e.target.value || '•••• •••• •••• ••••'; });
            cardExp.addEventListener('input', e => { e.target.value = maskExp(e.target.value); prevExp.textContent = e.target.value || 'MM/AA'; });
            cardName.addEventListener('input', e => { prevName.textContent = (e.target.value || 'NOME NO CARTÃO').toUpperCase(); });


            // Abrir modal de pagamento conforme o selecionado
            if (goBtn) {
                goBtn.addEventListener('click', () => {
                    const count = getCartCount();

                    // Bloqueia se NÃO estiver logado
                    if (!isLogged) {
                        window.mlToast && mlToast('err', loginMsg);
                        return;
                    }

                    // Bloqueia se o carrinho estiver vazio (mesmo logado)
                    if (count <= 0) {
                        window.mlToast && mlToast('err', emptyCartMsg);
                        return;
                    }

                    // Exige forma de pagamento selecionada
                    const method = select.value;
                    if (!method) {
                        window.mlToast && mlToast('err', 'Selecione uma forma de pagamento antes de continuar!');
                        select.focus();
                        return;
                    }

                    methodInput.value = method;

                    const isPix = method === 'pix',
                    isCredito = method === 'credito';

                    title.textContent = isPix
                        ? 'Pagamento via PIX'
                        : (isCredito ? 'Pagamento com Cartão de Crédito' : 'Pagamento com Cartão de Débito');

                    cardSection.classList.toggle('d-none', isPix);
                    pixSection.classList.toggle('d-none', !isPix);
                    btnFinal.classList.toggle('d-none', isPix);
                    parcelasWrap.classList.toggle('d-none', !isCredito);
                    semJurosMsg.classList.toggle('d-none', !isCredito);

                    resumoMetodo.textContent = isPix
                        ? 'PIX'
                        : (isCredito ? 'Cartão de Crédito' : 'Cartão de Débito');

                    cpfHelp.classList.add('d-none');
                    cpfOk.classList.add('d-none');
                    feedback.classList.add('d-none');

                    if (isCredito) {
                        const total = Number(orderData.dataset.total || '0');
                        const ops = gerarOpcoesParcelas(total);

                        parcelasSel.innerHTML = ops.map(o => {
                            const label = (o.i === 0)
                            ? `${o.n}x de ${formatBRL(o.parcela)} (sem juros)`
                            : `${o.n}x de ${formatBRL(o.parcela)} (${(o.i * 100).toFixed(1)}% a.m.)`;
                            return `<option value="${o.n}" data-total="${o.tot}">${label}</option>`;
                        }).join('');

                        resumoParcelas.textContent = parcelasSel.options[0].textContent;
                        resumoTotal.textContent = formatBRL(ops[0].tot);
                    } else {
                        resumoParcelas.textContent = '—';
                        resumoTotal.textContent = document.getElementById('total')?.textContent || resumoTotal.textContent;
                    }

                    modal.show();
                });
            }

            parcelasSel.addEventListener('change', () => {
                const opt = parcelasSel.options[parcelasSel.selectedIndex];
                resumoParcelas.textContent = opt.textContent;
                resumoTotal.textContent = formatBRL(Number(opt.dataset.total || '0'));
            });


            // Finalizar compra
            btnFinal.addEventListener('click', () => {
                const method = methodInput.value;
                if (method === 'pix') return;

                const req = ['payer_nome', 'payer_email', 'payer_cpf', 'card_number', 'card_expiry', 'card_cvv'];
                for (const id of req) {
                    const el = document.getElementById(id);
                    if (!el || !el.value.trim()) {
                        el?.focus();
                        return;
                    }
                }
                if (!cpfValido(cpf.value)) {
                    cpf.focus();
                    return;
                }

                // Parcelas escolhidas no pagamento (somente para crédito)
                let parcelas = 1;
                if (method === 'credito') {
                    const opt = parcelasSel.options[parcelasSel.selectedIndex];
                    parcelas = opt ? parseInt(opt.value || '1', 10) || 1 : 1;
                }

                bootstrap.Modal.getInstance(document.getElementById('checkoutModal')).hide();

                runCheckoutAndHandleKeys(true, {
                    form_pag: method || 'debito',
                    parcelas
                }).catch(e => mlToast('err', e?.message || 'Não foi possível finalizar.'));
            });


            // PIX
            btnPix.addEventListener('click', async () => {
                const req = ['payer_nome', 'payer_email', 'payer_cpf'];
                for (const id of req) {
                    const el = document.getElementById(id);
                    if (!el || !el.value.trim()) { el?.focus(); return; }
                }
                if (!cpfValido(document.getElementById('payer_cpf').value)) { document.getElementById('payer_cpf').focus(); return; }

                // Fecha o modal de pagamento e abre o QR code
                bootstrap.Modal.getInstance(document.getElementById('checkoutModal')).hide();
                pixQr.show();

                // Aguarda o fechamento do modal de QR code para o aparecimento da mensagem de sucesso
                let toastKind = 'ok';
                let toastMsg = 'Compra finalizada com sucesso!';
                const method = methodInput.value || 'pix';

                runCheckoutAndHandleKeys(false, {
                    form_pag: method,
                    parcelas: 1
                })
                    .then(j => { toastMsg = j?.msg || toastMsg; })
                    .catch(e => { toastKind = 'err'; toastMsg = e?.message || 'Não foi possível finalizar.'; });


                // Fecha o modal do QR code após 6 segundos na tela
                const QR_SHOW_MS = 6000;
                setTimeout(() => {
                    qrEl.addEventListener('hidden.bs.modal', () => {
                        mlToast(toastKind, toastMsg);
                    }, { once: true });
                    pixQr.hide();
                }, QR_SHOW_MS);
            });

        })();
    </script>

    <!-- Script de quantidade por item -->
    <script>
        (() => {
            const $total = document.getElementById('total');
            const $count = document.getElementById('counter');

            function clamp(n, min, max) { n = parseInt(n || 0, 10); return Math.min(max, Math.max(min, n)); }
            let t = null;
            function debounced(fn, ms = 300) { return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; }

            async function applyQty(row, qty) {
                const id = row?.dataset?.id; if (!id) return;
                try {
                    const r = await fetch('carrinhoAcao.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
                        body: new URLSearchParams({ action: 'setqty', id, qty })
                    });
                    const j = await r.json();
                    if (!r.ok || !j?.ok) throw new Error(j?.msg || 'Falha ao atualizar quantidade.');

                    const subEl = row.querySelector('.carrinho-sub');
                    if (subEl) subEl.textContent = j.subtotal_fmt || 'R$ 0,00';
                    if ($total) $total.textContent = j.total_fmt || 'R$ 0,00';
                    if ($count) $count.textContent = j.count ?? '0';

                    const badge = document.querySelector('[data-cart-count]');
                    if (badge) { badge.textContent = j.count ?? '0'; badge.hidden = (j.count == 0); }
                } catch (e) {
                    if (window.mlToast) mlToast('err', e.message || 'Não foi possível atualizar a quantidade.');
                }
            }

            document.querySelectorAll('.carrinho-i .qnt-input').forEach(input => {
                const row = input.closest('.carrinho-i');
                input.addEventListener('blur', () => { input.value = clamp(input.value, 1, 99); applyQty(row, input.value); });
                input.addEventListener('input', debounced(() => { input.value = clamp(input.value, 1, 99); applyQty(row, input.value); }, 300));
                input.addEventListener('change', () => { input.value = clamp(input.value, 1, 99); applyQty(row, input.value); });
            });
        })();
    </script>

    <!-- Script de remover item e esvaziar carrinho -->
    <script>
        (() => {
            const $list = document.querySelector('.carrinho-card');
            const $total = document.getElementById('total');
            const $count = document.getElementById('counter');

            function updateBadge(n) {
                const badge = document.querySelector('[data-cart-count]');
                if (badge) { badge.textContent = String(n); badge.hidden = (parseInt(n, 10) <= 0); }
            }
            function showEmptyState() {
                if (!$list) return;
                $list.querySelectorAll('.carrinho-i').forEach(n => n.remove());
                let empty = $list.querySelector('.carrinho-vazio');
                if (!empty) {
                    empty = document.createElement('div');
                    empty.className = 'carrinho-vazio';
                    empty.textContent = 'Seu carrinho está vazio.';
                    $list.appendChild(empty);
                } else {
                    empty.hidden = false;
                }

                const clearWrap = document.querySelector('.js-clear')?.closest('.mt-2'); if (clearWrap) clearWrap.remove();
            }

            document.addEventListener('click', async (ev) => {
                const btnRemove = ev.target.closest('.js-remove');
                const btnClear = ev.target.closest('.js-clear');

                if (btnRemove) {
                    const row = btnRemove.closest('.carrinho-i');
                    const id = row?.dataset?.id; if (!id) return;
                    try {
                        const r = await fetch('carrinhoAcao.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
                            body: new URLSearchParams({ action: 'remove', id })
                        });
                        const j = await r.json().catch(() => null);
                        if (!r.ok || !j?.ok) throw new Error(j?.msg || 'Não foi possível remover o item.');
                        row.remove();
                        if ($total) $total.textContent = j.total_fmt || 'R$ 0,00';
                        if ($count) $count.textContent = j.count ?? '0';
                        updateBadge(j.count ?? 0);
                        const restam = parseInt(j.count ?? 0, 10);
                        if (restam <= 0) showEmptyState();
                    } catch (e) {
                        mlToast && mlToast('err', e?.message || 'Falha ao remover.');
                    }
                    return;
                }

                if (btnClear) {
                    try {
                        const r = await fetch('carrinhoAcao.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
                            body: new URLSearchParams({ action: 'clear' })
                        });
                        const j = await r.json().catch(() => null);
                        if (!r.ok || !j?.ok) throw new Error(j?.msg || 'Não foi possível esvaziar o carrinho.');
                        if ($total) $total.textContent = j.total_fmt || 'R$ 0,00';
                        if ($count) $count.textContent = '0';
                        updateBadge(0);
                        showEmptyState();
                    } catch (e) {
                        mlToast && mlToast('err', e?.message || 'Falha ao esvaziar o carrinho.');
                    }
                }
            });
        })();
    </script>

</body>
</html>