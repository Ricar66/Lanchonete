<?php
session_start();
require_once('../includes/conexao.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    try {
        $venda_id = $conexao->real_escape_string($_GET['id']);
        
        // Excluir a venda
        $stmt = $conexao->prepare("DELETE FROM tb_vendas WHERE id = ?");
        $stmt->bind_param("i", $venda_id);
        $stmt->execute();
        
        // Feedback
        if ($stmt->affected_rows > 0) {
            header("Location: vendas.php?sucesso=Venda excluída com sucesso!");
        } else {
            header("Location: vendas.php?erro=Venda não encontrada");
        }
        exit();

    } catch (Exception $e) {
        error_log("Erro ao excluir venda: " . $e->getMessage());
        header("Location: vendas.php?erro=Erro ao excluir venda");
        exit();
    }
} else {
    header("Location: vendas.php");
    exit();
}
?>