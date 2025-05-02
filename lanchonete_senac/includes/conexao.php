<?php
// Arquivo de conexão com o banco de dados
$servidor = "localhost";
$usuario = "admin66";
$senha = "123";
$banco = "lanchonete_db";

// Criar conexão
$conexao = new mysqli($servidor, $usuario, $senha, $banco);

// Verificar conexão
if ($conexao->connect_error) {
    die("Conexão falhou: " . $conexao->connect_error);
}

// Definir charset para utf8
$conexao->set_charset("utf8");
?>