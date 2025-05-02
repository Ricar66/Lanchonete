<?php
session_start();
require_once('../includes/conexao.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pedido_id'])) {
    try {
        $pedido_id = $_POST['pedido_id'];
        
        // Buscar total do pedido
        $stmt_total = $conexao->prepare("
            SELECT SUM(ip.preco_unitario * ip.quantidade) as total 
            FROM tb_itens_pedido ip 
            WHERE ip.pedido_id = ?
        ");
        $stmt_total->bind_param("i", $pedido_id);
        $stmt_total->execute();
        $total_result = $stmt_total->get_result()->fetch_assoc();
        $total_pedido = $total_result['total'] ?? 0;
        
        // Buscar cliente_id do pedido
        $stmt_cliente = $conexao->prepare("SELECT cliente_id FROM tb_pedidos WHERE id = ?");
        $stmt_cliente->bind_param("i", $pedido_id);
        $stmt_cliente->execute();
        $cliente_result = $stmt_cliente->get_result()->fetch_assoc();
        $cliente_id = $cliente_result['cliente_id'] ?? null;
        
        // Inserir na tabela de vendas
        $stmt_venda = $conexao->prepare("
            INSERT INTO tb_vendas 
            (pedido_id, data_venda, cliente_id, valor_total) 
            VALUES (?, NOW(), ?, ?)
        ");
        $stmt_venda->bind_param("iid", $pedido_id, $cliente_id, $total_pedido);
        $stmt_venda->execute();
        
        // Redirecionar para a dashboard
        header("Location: dashboard.php?sucesso=Venda registrada!");
        exit();

    } catch (Exception $e) {
        header("Location: nota_fiscal.php?id=$pedido_id&erro=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>