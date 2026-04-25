<?php

// Garante que a sessão existe
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Limpa a sessão
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destrói a sessão
session_destroy();

// Redireciona para a página princial
header('Location: ../index.php');
exit;