<?php
session_start();
header('Content-Type: application/json');
require_once('../includes/conexao.php');

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['admin_id'])) {
    try {
        $conexao->begin_transaction();

        // Inserir cliente
        $stmt = $conexao->prepare("INSERT INTO tb_clientes (nome) VALUES (?)");
        $stmt->bind_param("s", $_POST['nome']);
        $stmt->execute();
        $cliente_id = $stmt->insert_id;

        // Inserir telefone
        $stmt = $conexao->prepare("INSERT INTO tb_telefones (cliente_id, numero) VALUES (?, ?)");
        $stmt->bind_param("is", $cliente_id, $_POST['telefone']);
        $stmt->execute();

        // Inserir endereço se existir
        if (!empty($_POST['endereco'])) {
            $stmt = $conexao->prepare("INSERT INTO tb_enderecos (cliente_id, logradouro) VALUES (?, ?)");
            $stmt->bind_param("is", $cliente_id, $_POST['endereco']);
            $stmt->execute();
        }

        $conexao->commit();

        $response = [
            'success' => true,
            'cliente' => [
                'id' => $cliente_id,
                'nome' => $_POST['nome']
            ]
        ];
    } catch (Exception $e) {
        $conexao->rollback();
        $response['message'] = "Erro: " . $e->getMessage();
    }
} else {
    $response['message'] = "Requisição inválida";
}

echo json_encode($response);
?>