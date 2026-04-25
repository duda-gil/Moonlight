<?php
require_once 'include/verifica.php';
require_once 'include/conexao.php';

// Somente adm tem acesso
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: adm.php');
    exit;
}

// Não permite que nenhum usuário acesse a página pela URL
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Método não permitido.';
    exit;
}

// Lê e valida o ID, caso inexistente, redireciona para a página de origem
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['flash'] = ['tipo' => 'danger', 'msg' => 'ID inválido.'];
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'adm.php'));
    exit;
}

$ok = false;
$errMsg = '';

try {
    // Inicia transação para exclusão em cascata
    $conn->begin_transaction();

    // Apaga requisitos mínimos do jogo
    $stmtReq = $conn->prepare('DELETE FROM requisitos WHERE jogo_id = ?');
    $stmtReq->bind_param('i', $id);
    $stmtReq->execute();

    // Apaga vínculos com categorias
    $stmtCat = $conn->prepare('DELETE FROM jogos_categorias WHERE jogo_id = ?');
    $stmtCat->bind_param('i', $id);
    $stmtCat->execute();

    // Apaga o jogo
    $stmtJogo = $conn->prepare('DELETE FROM jogos WHERE id = ? LIMIT 1');
    $stmtJogo->bind_param('i', $id);
    $stmtJogo->execute();

    $conn->commit();
    $ok = true;
} 

// Caso haja qualquer erro, a ação volta atrás e nada é excluído
catch (Throwable $e) {
    $conn->rollback();
    $ok = false;
    $errMsg = $e->getMessage();
}

// Mensagem de sucesso/erro 
$_SESSION['flash'] = [
    'tipo' => $ok ? 'success' : 'danger',
    'msg'  => $ok ? 'Jogo excluído com sucesso.' : ('Falha ao excluir o jogo.' /* . ' Detalhe: ' . $errMsg */)
];

header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'adm.php'));
exit;