<?php

// Impossibilita um usuário deslogado de tentar acessar uma página restrita
include('conexao.php');
session_start();

if(!isset($_SESSION['id'])){
    header("Location: ./login.php");
    exit();
}
?>