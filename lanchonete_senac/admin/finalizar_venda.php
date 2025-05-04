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
        
        // Verificar duplicidade
        $check = $conexao->prepare("SELECT id FROM tb_vendas WHERE pedido_id = ?");
        $check->bind_param("i", $pedido_id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            header("Location: dashboard.php?erro=Venda já registrada");
            exit();
        }

        // Calcular total com adicionais
        $stmt_total = $conexao->prepare("
            SELECT 
                SUM(ip.preco_unitario * ip.quantidade) + 
                COALESCE(SUM(ipa.quantidade * a.preco), 0) AS total
            FROM tb_itens_pedido ip
            LEFT JOIN tb_itens_pedido_adicionais ipa ON ip.id = ipa.item_pedido_id
            LEFT JOIN tb_adicionais a ON ipa.adicional_id = a.id
            WHERE ip.pedido_id = ?
        ");
        $stmt_total->bind_param("i", $pedido_id);
        $stmt_total->execute();
        $total = $stmt_total->get_result()->fetch_assoc()['total'] ?? 0;

        // Inserir venda
        $stmt = $conexao->prepare("
            INSERT INTO tb_vendas 
            (pedido_id, data_venda, valor_total) 
            VALUES (?, NOW(), ?)
        ");
        $stmt->bind_param("id", $pedido_id, $total);
        $stmt->execute();

        header("Location: dashboard.php?sucesso=Venda registrada!");
        exit();

    } catch (Exception $e) {
        header("Location: nota_fiscal.php?id=$pedido_id&erro=" . urlencode($e->getMessage()));
        exit();
    }
}
?>