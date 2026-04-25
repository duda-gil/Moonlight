<?php
require_once __DIR__ . '/include/conexao.php';
require_once __DIR__ . '/include/carrinhoFunc.php';
require_once __DIR__ . '/include/biblioteca.php';

if (session_status() !== PHP_SESSION_ACTIVE)
  session_start();


// Normaliza id de sessão
$userId = $_SESSION['id']
  ?? $_SESSION['usuario_id']
  ?? ($_SESSION['user']['id'] ?? null)
  ?? ($_SESSION['usuario']['id'] ?? null);
if ($userId && !isset($_SESSION['id']))
  $_SESSION['id'] = (int) $userId;


// Define o código HTTP, manda o JSON e fecha o script
function json_out(int $status, array $payload)
{
  if (ob_get_length())
    ob_clean();
  http_response_code($status);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($payload, JSON_UNESCAPED_UNICODE);
  exit;
}


// Garante que as mensganes de erros técnicos apareçam para o usuário + descobre qual operação o carrinho deve usar
ini_set('display_errors', '0');
while (ob_get_level())
  ob_end_clean();

$action = $_POST['action'] ?? $_GET['action'] ?? 'add';


// Usuário deslogado não pode adicionar itens ao carrinho
if ($action === 'add' && empty($_SESSION['id'])) {
  json_out(401, ['ok' => false, 'code' => 'auth', 'msg' => 'Faça login ou cadastre-se para adicionar itens ao carrinho.']);
}


// Garante novamente que o carrinho exista
if (!function_exists('cart_store')) {
  function &cart_store(): array
  {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart']))
      $_SESSION['cart'] = [];
    return $_SESSION['cart'];
  }
}


// Impede valores menores que 1 e maiores que 99 
function qty_sane($q): int
{
  $q = (int) $q;
  return max(1, min(99, $q));
}


// Formatador para padrão R$ + 2 casas após a virgula
function fmt($v)
{
  return 'R$ ' . number_format((float) $v, 2, ',', '.');
}


// Busca as informações de preços no banco e calcula o total
function totals(mysqli $conn, array $cart): array
{
  $ids = array_keys($cart);
  if (!$ids)
    return ['total' => 0.0, 'total_fmt' => fmt(0), 'subs' => []];
  $place = implode(',', array_fill(0, count($ids), '?'));
  $types = str_repeat('i', count($ids));
  $st = $conn->prepare("SELECT id,preco,desconto FROM jogos WHERE id IN ($place)");
  $st->bind_param($types, ...$ids);
  $st->execute();
  $rs = $st->get_result();
  $total = 0.0;
  $subs = [];
  while ($r = $rs->fetch_assoc()) {
    $id = (int) $r['id'];
    $q = (int) ($cart[$id] ?? 0);
    $p = (float) $r['preco'];
    $d = (int) $r['desconto'];
    $pn = $d > 0 ? $p * (1 - $d / 100) : $p;
    $subs[$id] = $pn * $q;
    $total += $subs[$id];
  }
  return ['total' => $total, 'total_fmt' => fmt($total), 'subs' => $subs];
}


// Pagamento com cartão de crédito e parcelas + juros
function calc_valor_final(float $baseTotal, string $forma, int $parcelas): float
{
  $baseTotal = max(0.0, $baseTotal);
  $forma = strtolower(trim($forma));

  // Aplicação de juros
  if ($forma !== 'credito') {
    return $baseTotal;
  }

  $n = max(1, min(10, $parcelas));
  $i = 0.0;
  if ($n >= 4 && $n <= 6) {
    $i = 0.005;        // 0,5% a.m.
  } elseif ($n >= 7 && $n <= 10) {
    $i = 0.01;         // 1% a.m.
  }

  if ($i <= 0.0) {
    return $baseTotal;
  }

  // Calculo do parcelamento com juros 
  $pow = pow(1 + $i, $n);
  if ($pow <= 1.0) {
    return $baseTotal;
  }

  $parcela = $baseTotal * ($i * $pow) / ($pow - 1);
  $total   = $parcela * $n;

  return max(0.0, $total);
}


// Início da lógica do carrinho
$id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
$cart = &cart_store();

switch ($action) {

  // Soma o item adicionado a quantidade no carrinho
  case 'add':
    if ($id <= 0)
      json_out(400, ['ok' => false, 'msg' => 'ID inválido']);
    $qty = qty_sane($_POST['qty'] ?? 1);
    $cart[$id] = ($cart[$id] ?? 0) + $qty;
    break;


  // Altera a quantidade de um item no carrinho
  case 'update':
    if ($id <= 0)
      json_out(400, ['ok' => false, 'msg' => 'ID inválido']);
    $qty = qty_sane($_POST['qty'] ?? 1);
    $cart[$id] = $qty;
    break;


  // Valida os IDs e quantidades, recalcula os valores e atualiza o carrinho diretamente na tela
  case 'setqty': {
    // id e qty vindos do carrinho.php
    $jid = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $qty = isset($_POST['qty']) ? (int) $_POST['qty'] : 1;
    if ($jid <= 0)
      json_out(400, ['ok' => false, 'msg' => 'Jogo inválido.']);

    // Estipula quantidade minima e maxima
    if ($qty < 1)
      $qty = 1;
    if ($qty > 99)
      $qty = 99;

    // Se não existir o item no carrinho, cria; senão, atualiza
    if (!isset($_SESSION['cart']))
      $_SESSION['cart'] = [];
    $_SESSION['cart'][$jid] = $qty;

    // Recalcula totais para devolver ao front
    require_once __DIR__ . '/include/carrinhoFunc.php';
    $items = cart_items($conn);      // usa $_SESSION['cart']
    $total = cart_total($conn);

    // Acha o subtotal do item atualizado
    $subtotal = 0.0;
    foreach ($items as $it) {
      if ((int) $it['id'] === $jid) {
        $subtotal = (float) $it['subtotal'];
        break;
      }
    }

    json_out(200, [
      'ok' => true,
      'count' => cart_count(),
      'subtotal' => $subtotal,
      'subtotal_fmt' => brl_cart($subtotal),
      'total' => $total,
      'total_fmt' => brl_cart($total),
    ]);
  }
    break;


  // Remove o item do carrinho
  case 'remove':
    if ($id > 0)
      unset($cart[$id]);
    break;


  // Esvazia o carrinho
  case 'clear':
    $cart = [];
    break;


  // Informa quantos itens tem no carrinho e o valor atual
  case 'status':
    $ts = totals($conn, $cart);
    json_out(200, ['ok' => true, 'count' => array_sum(array_map('intval', $cart)), 'total' => $ts['total'], 'total_fmt' => $ts['total_fmt']]);
    break;


    // Finaliza a compra, lê a forma de pagamento, limpa o carrinho, insere os jogos dentro de biblioteca e gera as chaves
    case 'checkout':
    try {
      if (empty($_SESSION['id'])) {
        json_out(401, ['ok' => false, 'code' => 'auth', 'msg' => 'Faça login para finalizar a compra.']);
      }
      if (empty($cart)) {
        json_out(400, ['ok' => false, 'msg' => 'Seu carrinho está vazio.']);
      }

      $uid = (int) $_SESSION['id'];

      // Forma de pagamento e parcelas
      $forma = strtolower(trim((string) ($_POST['form_pag'] ?? '')));
      if (!in_array($forma, ['pix', 'debito', 'credito'], true)) {
        $forma = 'pix';
      }

      $parcelas = (int) ($_POST['parcelas'] ?? 1);
      if ($parcelas < 1) $parcelas = 1;
      if ($parcelas > 10) $parcelas = 10;
      if ($forma !== 'credito') {
        $parcelas = 1;
      }

      // Monta as linhas da compra a partir do carrinho
      $items = cart_items($conn);
      if (!$items) {
        json_out(400, ['ok' => false, 'msg' => 'Não há itens válidos no carrinho.']);
      }

      $linhas   = [];
      $subtotal = 0.0;

      foreach ($items as $it) {
        $jid   = (int) $it['id'];
        $qtd   = max(1, (int) $it['qty']);
        $punit = (float) $it['preco_n'];
        $vTot  = (float) $it['subtotal'];

        $subtotal += $vTot;

        $linhas[] = [
          'jogo_id'    => $jid,
          'qtd_chave'  => $qtd,
          'preco_unit' => $punit,
          'valor_total'=> $vTot,
        ];
      }

      if ($subtotal <= 0) {
        json_out(400, ['ok' => false, 'msg' => 'Total da compra inválido.']);
      }

      // Aplica juros conforme forma + parcelas
      $valor_final = calc_valor_final($subtotal, $forma, $parcelas);

      $conn->begin_transaction();

      // Registra compra
      $stCompra = $conn->prepare("
          INSERT INTO compras (user_id, data_compra, form_pag, parcelas, valor_final, status)
          VALUES (?, NOW(), ?, ?, ?, 'pago')
      ");
      if (!$stCompra) {
        throw new RuntimeException('Erro ao preparar INSERT de compra: ' . $conn->error);
      }
      $stCompra->bind_param('isid', $uid, $forma, $parcelas, $valor_final);
      if (!$stCompra->execute()) {
        throw new RuntimeException('Erro ao salvar compra: ' . $stCompra->error);
      }
      $compraId = $conn->insert_id;

      // Registra itens e gera chaves na biblioteca
      $stItem = $conn->prepare("
          INSERT INTO compras_itens (compra_id, jogo_id, qtd_chave, preco_unit, valor_total)
          VALUES (?, ?, ?, ?, ?)
      ");
      if (!$stItem) {
        throw new RuntimeException('Erro ao preparar INSERT de itens: ' . $conn->error);
      }

      foreach ($linhas as $ln) {
        $jid   = $ln['jogo_id'];
        $qtd   = $ln['qtd_chave'];
        $punit = $ln['preco_unit'];
        $vTot  = $ln['valor_total'];

        $stItem->bind_param('iiidd', $compraId, $jid, $qtd, $punit, $vTot);
        if (!$stItem->execute()) {
          throw new RuntimeException('Erro ao salvar item de compra: ' . $stItem->error);
        }

        for ($n = 0; $n < $qtd; $n++) {
          library_add($conn, $uid, $jid);
        }
      }

      // Limpa carrinho
      $_SESSION['cart'] = [];

      $conn->commit();

      json_out(200, [
        'ok'        => true,
        'msg'       => 'Compra finalizada com sucesso!',
        'compra_id' => $compraId,
        'count'     => 0,
        'total'     => 0.0,
        'total_fmt' => 'R$ 0,00',
      ]);

    } catch (Throwable $e) {
      if ($conn) {
        try {
          $conn->rollback();
        } catch (Throwable $__) {
        }
      }
      json_out(500, ['ok' => false, 'msg' => 'Erro no checkout: ' . $e->getMessage()]);
    }
    break;


  // Busca as chaves que o usuário tem para aquele jogo  
  case 'mykey':
    if (empty($_SESSION['id']))
      json_out(401, ['ok' => false, 'code' => 'auth', 'msg' => 'Faça login para ver suas chaves.']);
    $uid = (int) $_SESSION['id'];
    $jid = (int) ($_POST['id'] ?? 0);
    if ($jid <= 0)
      json_out(400, ['ok' => false, 'msg' => 'Jogo inválido.']);

    $st = $conn->prepare('SELECT chave FROM biblioteca WHERE user_id=? AND jogo_id=? ORDER BY compra_data DESC, id DESC');
    $st->bind_param('ii', $uid, $jid);
    $st->execute();
    $keys = array_column($st->get_result()->fetch_all(MYSQLI_ASSOC), 'chave');

    if (!$keys) { // se não tiver, cria uma (backfill)
      $keys[] = library_add($conn, $uid, $jid);
    }

    json_out(200, ['ok' => true, 'keys' => $keys, 'key' => $keys[0]]); // 'key' p/ compatibilidade
    break;


  // Se não cair em nenhum caso, entra no caso padrão, que recalcula os totais e subtotais
  default:

}

$ts = totals($conn, $cart);
$out = ['ok' => true, 'count' => array_sum(array_map('intval', $cart)), 'total' => $ts['total'], 'total_fmt' => $ts['total_fmt']];
if ($id && isset($ts['subs'][$id])) {
  $out['item_subtotal'] = $ts['subs'][$id];
  $out['item_subtotal_fmt'] = fmt($ts['subs'][$id]);
}
json_out(200, $out);
