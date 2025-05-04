<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include('../includes/conexao.php');
include('../includes/header.php');

// Parâmetros do filtro
$periodo = $_GET['periodo'] ?? 'mes';
$mes = $_GET['mes'] ?? date('m');
$ano = $_GET['ano'] ?? date('Y');

// Construir cláusula WHERE dinâmica
$where = "1=1";
switch ($periodo) {
    case 'hoje':
        $where = "DATE(v.data_venda) = CURDATE()";
        break;
    case 'semana':
        $where = "v.data_venda >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)";
        break;
    case 'mes':
        $where = "MONTH(v.data_venda) = '$mes' AND YEAR(v.data_venda) = '$ano'";
        break;
}

// Consulta principal otimizada
$sql = "SELECT 
            v.id,
            v.data_venda,
            COALESCE(c.nome, 'Cliente não cadastrado') AS cliente,
            GROUP_CONCAT(CONCAT(p.nome, ' (', ip.quantidade, 'x)')) AS itens, -- ✅ Parêntese adicionado
            v.valor_total,
            v.pedido_id
        FROM tb_vendas v
        LEFT JOIN tb_clientes c ON v.cliente_id = c.id
        LEFT JOIN tb_pedidos ped ON v.pedido_id = ped.id
        LEFT JOIN tb_itens_pedido ip ON ped.id = ip.pedido_id
        LEFT JOIN tb_produtos p ON ip.produto_id = p.id
        WHERE $where
        GROUP BY v.id
        ORDER BY v.data_venda DESC";

$result = $conexao->query($sql);
?>

<div class="container mt-4">
    <h2><i class="fas fa-cash-register"></i> Relatório de Vendas</h2>
    <hr>
    
    <!-- Filtros melhorados -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-filter"></i> Filtrar Vendas</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label>Período:</label>
                    <select class="form-select" name="periodo">
                        <option value="hoje" <?= $periodo == 'hoje' ? 'selected' : '' ?>>Hoje</option>
                        <option value="semana" <?= $periodo == 'semana' ? 'selected' : '' ?>>Esta Semana</option>
                        <option value="mes" <?= $periodo == 'mes' ? 'selected' : '' ?>>Este Mês</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Mês:</label>
                    <select class="form-select" name="mes" <?= $periodo != 'mes' ? 'disabled' : '' ?>>
                        <?php
                        $meses = [
                            '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março',
                            '04' => 'Abril', '05' => 'Maio', '06' => 'Junho',
                            '07' => 'Julho', '08' => 'Agosto', '09' => 'Setembro',
                            '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'
                        ];
                        foreach ($meses as $num => $nome) {
                            echo "<option value='$num'" . ($num == $mes ? ' selected' : '') . ">$nome</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Ano:</label>
                    <select class="form-select" name="ano" <?= $periodo != 'mes' ? 'disabled' : '' ?>>
                        <?php
                        $ano_atual = date('Y');
                        for ($a = $ano_atual; $a >= ($ano_atual - 5); $a--) {
                            echo "<option value='$a'" . ($a == $ano ? ' selected' : '') . ">$a</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Aplicar Filtros
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumo de vendas -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-coins"></i> Total Vendido</h5>
                    <?php
                    $sql_total = "SELECT SUM(v.valor_total) as total 
                    FROM tb_vendas v 
                    WHERE $where";
                    $total = $conexao->query($sql_total)->fetch_assoc()['total'] ?? 0;
                    ?>
                    <p class="display-4 mb-0">R$ <?= number_format($total, 2, ',', '.') ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-cube"></i> Itens Vendidos</h5>
                    <?php
                   $sql_itens = "SELECT SUM(ip.quantidade) as total 
                   FROM tb_itens_pedido ip
                   JOIN tb_vendas v ON ip.pedido_id = v.pedido_id 
                   WHERE $where";
                    $total_itens = $conexao->query($sql_itens)->fetch_assoc()['total'] ?? 0;
                    ?>
                    <p class="display-4 mb-0"><?= $total_itens ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-users"></i> Clientes Atendidos</h5>
                    <?php
                    $sql_clientes = "SELECT COUNT(DISTINCT v.cliente_id) as total 
                    FROM tb_vendas v 
                    WHERE $where";
                    $total_clientes = $conexao->query($sql_clientes)->fetch_assoc()['total'] ?? 0;
                    ?>
                    <p class="display-4 mb-0"><?= $total_clientes ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de vendas -->
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0"><i class="fas fa-list"></i> Detalhes das Transações</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>ID</th>
                            <th>Data/Hora</th>
                            <th>Cliente</th>
                            <th>Itens</th>
                            <th>Valor</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($venda = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?= $venda['id'] ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($venda['data_venda'])) ?></td>
                                    <td><?= htmlspecialchars($venda['cliente']) ?></td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <?= str_replace(',', '<br>', $venda['itens']) ?>
                                        </div>
                                    </td>
                                    <td class="text-nowrap">R$ <?= number_format($venda['valor_total'], 2, ',', '.') ?></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="nota_fiscal.php?id=<?= $venda['pedido_id'] ?>" 
                                               class="btn btn-sm btn-primary"
                                               title="Ver Nota Completa">
                                                <i class="fas fa-file-invoice"></i>
                                            </a>
                                            <a href="excluir_venda.php?id=<?= $venda['id'] ?>" 
                                               class="btn btn-sm btn-danger"
                                               title="Excluir Registro"
                                               onclick="return confirm('Tem certeza que deseja excluir permanentemente esta venda?')">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-exclamation-circle fa-2x text-muted"></i>
                                    <p class="mt-2 mb-0">Nenhuma transação encontrada</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Ativar/desativar seletores de mês/ano
document.querySelector('[name="periodo"]').addEventListener('change', function() {
    const isMes = this.value === 'mes';
    document.querySelectorAll('[name="mes"], [name="ano"]').forEach(el => {
        el.disabled = !isMes;
    });
});
</script>

<?php include('../includes/footer.php'); ?>