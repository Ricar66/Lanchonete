<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include('../includes/conexao.php');
include('../includes/header.php');

// Parâmetros do filtro
$mes = isset($_GET['mes']) ? $_GET['mes'] : date('m');
$ano = isset($_GET['ano']) ? $_GET['ano'] : date('Y');

// Consulta principal corrigida
$sql = "SELECT 
            v.id,
            v.data_venda,
            COALESCE(c.nome, 'Cliente não cadastrado') AS cliente,
            GROUP_CONCAT(p.nome SEPARATOR ', ') AS itens,
            v.valor_total,
            v.pedido_id
        FROM tb_vendas v
        LEFT JOIN tb_clientes c ON v.cliente_id = c.id
        LEFT JOIN tb_pedidos ped ON v.pedido_id = ped.id
        LEFT JOIN tb_itens_pedido ip ON ped.id = ip.pedido_id
        LEFT JOIN tb_produtos p ON ip.produto_id = p.id
        WHERE MONTH(v.data_venda) = ? AND YEAR(v.data_venda) = ?
        GROUP BY v.id
        ORDER BY v.data_venda DESC";

$stmt = $conexao->prepare($sql);
$stmt->bind_param("ss", $mes, $ano);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container mt-4">
    <h2><i class="fas fa-cash-register"></i> Relatório de Vendas</h2>
    <hr>
    
    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-filter"></i> Filtrar Vendas</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-4">
                    <label for="mes">Mês</label>
                    <select id="mes" name="mes" class="form-control">
                        <?php
                        $meses = [
                            '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março',
                            '04' => 'Abril', '05' => 'Maio', '06' => 'Junho',
                            '07' => 'Julho', '08' => 'Agosto', '09' => 'Setembro',
                            '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'
                        ];
                        
                        foreach ($meses as $num => $nome) {
                            $selected = ($num == $mes) ? 'selected' : '';
                            echo "<option value='$num' $selected>$nome</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="ano">Ano</label>
                    <select id="ano" name="ano" class="form-control">
                        <?php
                        $ano_atual = date('Y');
                        for ($a = $ano_atual; $a >= ($ano_atual - 5); $a--) {
                            $selected = ($a == $ano) ? 'selected' : '';
                            echo "<option value='$a' $selected>$a</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumo mensal -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-chart-line"></i> Total de Vendas</h5>
                    <?php
                    $sql_total = "SELECT 
                                    COUNT(*) as total_vendas, 
                                    SUM(valor_total) as valor_total 
                                FROM tb_vendas 
                                WHERE MONTH(data_venda) = ? AND YEAR(data_venda) = ?";
                    $stmt_total = $conexao->prepare($sql_total);
                    $stmt_total->bind_param("ss", $mes, $ano);
                    $stmt_total->execute();
                    $resumo = $stmt_total->get_result()->fetch_assoc();
                    
                    $total_vendas = $resumo['total_vendas'] ?? 0;
                    $valor_total = $resumo['valor_total'] ?? 0;
                    ?>
                    <p class="card-text">
                        <span class="display-4"><?= $total_vendas ?></span> vendas realizadas
                    </p>
                    <p class="card-text">
                        Total: <span class="display-6">R$ <?= number_format($valor_total, 2, ',', '.') ?></span>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-utensils"></i> Produtos Mais Vendidos</h5>
                    <?php
                    $sql_produtos = "SELECT 
                                    p.nome, 
                                    SUM(ip.quantidade) as quantidade 
                                FROM tb_itens_pedido ip
                                JOIN tb_produtos p ON ip.produto_id = p.id
                                JOIN tb_pedidos ped ON ip.pedido_id = ped.id
                                JOIN tb_vendas v ON ped.id = v.pedido_id
                                WHERE MONTH(v.data_venda) = ? AND YEAR(v.data_venda) = ?
                                GROUP BY p.nome
                                ORDER BY quantidade DESC
                                LIMIT 3";
                    $stmt_produtos = $conexao->prepare($sql_produtos);
                    $stmt_produtos->bind_param("ss", $mes, $ano);
                    $stmt_produtos->execute();
                    $produtos_result = $stmt_produtos->get_result();
                    
                    if ($produtos_result->num_rows > 0) {
                        echo "<ul class='list-group list-group-flush'>";
                        while ($row = $produtos_result->fetch_assoc()) {
                            echo "<li class='list-group-item bg-transparent text-white'>
                                {$row['nome']} <span class='badge bg-primary float-end'>{$row['quantidade']}</span>
                            </li>";
                        }
                        echo "</ul>";
                    } else {
                        echo "<p>Nenhum dado disponível</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

   <!-- Lista de vendas -->
<div class="card">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0"><i class="fas fa-list"></i> Detalhes das Vendas</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Data/Hora</th>
                        <th>Cliente</th>
                        <th>Itens</th>
                        <th>Valor Total</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($venda = $result->fetch_assoc()) {
                            echo "<tr>
                                <td>{$venda['id']}</td>
                                <td>".date('d/m/Y H:i', strtotime($venda['data_venda']))."</td>
                                <td>".htmlspecialchars($venda['cliente'])."</td>
                                <td>".($venda['itens'] ?? 'Nenhum item registrado')."</td>
                                <td>R$ ".number_format($venda['valor_total'], 2, ',', '.')."</td>
                                <td>
                                    <div class='btn-group'>
                                        <a href='nota_fiscal.php?id={$venda['pedido_id']}' 
                                           class='btn btn-sm btn-primary'
                                           title='Ver Nota Fiscal'>
                                            <i class='fas fa-file-invoice'></i>
                                        </a>
                                        <a href='excluir_venda.php?id={$venda['id']}' 
                                           class='btn btn-sm btn-danger' 
                                           title='Excluir Venda'
                                           onclick='return confirm(\"Tem certeza que deseja excluir esta venda?\\nIsso não afetará o pedido relacionado.\")'>
                                            <i class='fas fa-trash'></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>Nenhuma venda encontrada para o período selecionado</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>