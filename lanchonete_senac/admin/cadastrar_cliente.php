<?php
header('Content-Type: application/json');
require_once('../includes/conexao.php');

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $endereco = $_POST['endereco'] ?? '';
    
    try {
        // Inserir cliente
        $stmt = $conexao->prepare("INSERT INTO tb_clientes (nome) VALUES (?)");
        $stmt->bind_param("s", $nome);
        $stmt->execute();
        $cliente_id = $stmt->insert_id;
        
        // Inserir telefone
        $stmt = $conexao->prepare("INSERT INTO tb_telefones (cliente_id, numero) VALUES (?, ?)");
        $stmt->bind_param("is", $cliente_id, $telefone);
        $stmt->execute();
        
        // Inserir endereço se fornecido
        if (!empty($endereco)) {
            $stmt = $conexao->prepare("INSERT INTO tb_enderecos (cliente_id, endereco) VALUES (?, ?)");
            $stmt->bind_param("is", $cliente_id, $endereco);
            $stmt->execute();
        }
        
        $response = [
            'success' => true,
            'cliente' => [
                'id' => $cliente_id,
                'nome' => $nome
            ]
        ];
    } catch (Exception $e) {
        $response['message'] = 'Erro no banco de dados: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Método não permitido';
}

echo json_encode($response);
?>