<?php
session_start();

// Destrói todas as variáveis de sessão
$_SESSION = [];

// Remove o cookie de sessão (por segurança extra)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroi a sessão de fato
session_destroy();

// Redireciona para a página principal
header("Location: index.php");
exit;
?>
