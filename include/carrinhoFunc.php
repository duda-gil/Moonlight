<?php
if (session_status() !== PHP_SESSION_ACTIVE)
    session_start();

// Formatador para padrão R$ + 2 casas após a virgula
function brl_cart(float $v): string
{
    return 'R$ ' . number_format($v, 2, ',', '.');
}


// Função que inicializa o carrinho na sessão
function &cart_store(): array
{
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];                   // [ id => qty ]
    }
    return $_SESSION['cart'];
}


// Soma da quantidade de itens no carrinho para atualização do bagde
function cart_count(): int
{
    $cart = cart_store();
    return array_sum(array_map('intval', $cart));
}

// Mantém somente os IDs maiores que zero, caso contrário, retorna carrinho vazio
function cart_items(mysqli $conn): array
{
    $cart = cart_store();
    $ids = array_keys($cart);
    $ids = array_values(array_filter(array_map('intval', $ids), fn($v) => $v > 0));
    if (!$ids)
        return [];


    // Lista de jogos que estão no carrinho
    $place = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

    $sql = "SELECT id, nome, preco, desconto, url_banner AS banner
          FROM jogos
          WHERE id IN ($place)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $res = $stmt->get_result();


    // Não permite que um valor menor que 1 ou maior que 99 + disponibiliza as informações para a tabela do carrinho 
    $items = [];
    while ($r = $res->fetch_assoc()) {
        $id = (int) $r['id'];
        $qty = max(1, min(99, (int) ($cart[$id] ?? 1)));

        $p = (float) $r['preco'];
        $d = (int) $r['desconto'];
        $pn = $d > 0 ? $p * (1 - $d / 100) : $p;

        $items[] = [
            'id' => $id,
            'nome' => (string) $r['nome'],
            'thumb' => (string) $r['banner'],
            'preco' => $p,
            'desconto' => $d,
            'preco_n' => $pn,
            'qty' => $qty,
            'subtotal' => $pn * $qty,
        ];
    }

    // Ordena os itens do carrinho por ordem alfabética
    usort($items, fn($a, $b) => strcmp($a['nome'], $b['nome']));
    return $items;
}

// Calcula o valor total do carrinho
function cart_total(mysqli $conn): float
{
    $sum = 0.0;
    foreach (cart_items($conn) as $it)
        $sum += $it['subtotal'];
    return $sum;
}