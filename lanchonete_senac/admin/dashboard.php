<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include('../includes/conexao.php');
include('../includes/header.php');

if (isset($_GET['sucesso'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_GET['sucesso']) . '</div>';
}
if (isset($_GET['erro'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_GET['erro']) . '</div>';
}
?>

<div class="container mt-4">
    <h2><i class="fas fa-tachometer-alt"></i> Dashboard</h2>
    <hr>
    
    <div class="row">
        <!-- Card de Clientes -->
        <div class="col-md-4 mb-4">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-users"></i> Clientes</h5>
                    <?php
                    $sql = "SELECT COUNT(*) as total FROM tb_clientes";
                    $result = $conexao->query($sql);
                    $total = $result->fetch_assoc();
                    ?>
                    <p class="card-text display-4"><?php echo $total['total']; ?></p>
                    <a href="clientes.php" class="text-white">Ver detalhes <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>
        
        <!-- Card de Produtos -->
        <div class="col-md-4 mb-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-hamburger"></i> Produtos</h5>
                    <?php
                    $sql = "SELECT COUNT(*) as total FROM tb_produtos WHERE ativo = TRUE";
                    $result = $conexao->query($sql);
                    $total = $result->fetch_assoc();
                    ?>
                    <p class="card-text display-4"><?php echo $total['total']; ?></p>
                    <a href="produtos.php" class="text-white">Ver detalhes <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>
        
        <!-- Card de Vendas Mensais -->
        <div class="col-md-4 mb-4">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-chart-line"></i> Vendas (Mês)</h5>
                    <?php
                    $sql = "SELECT SUM(valor_total) as total FROM tb_vendas 
                            WHERE MONTH(data_venda) = MONTH(CURRENT_DATE()) 
                            AND YEAR(data_venda) = YEAR(CURRENT_DATE())";
                    $result = $conexao->query($sql);
                    $total = $result->fetch_assoc();
                    $valor = $total['total'] ? number_format($total['total'], 2, ',', '.') : '0,00';
                    ?>
                    <p class="card-text display-4">R$ <?php echo $valor; ?></p>
                    <a href="vendas.php" class="text-white">Ver detalhes <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>