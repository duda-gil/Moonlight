<?php

// Estabelece a conexão com o banco
$servidor = "localhost";
$usuario = "root";
$senha = "";
$bd = "bd";

$conn = new mysqli($servidor, $usuario, $senha, $bd);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
?>