<?php
require_once 'include/verifica.php';
require_once 'include/conexao.php';

// Somente adm tem acesso
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
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

// Deleta no banco
$stmt = $conn->prepare('DELETE FROM categorias WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$ok = $stmt->execute();

// Mensagem de sucesso/erro 
$_SESSION['flash'] = [
    'tipo' => $ok ? 'success' : 'danger',
    'msg' => $ok ? 'Categoria excluída com sucesso.' : 'Falha ao excluir a categoria.'
];

header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'adm.php'));
exit;