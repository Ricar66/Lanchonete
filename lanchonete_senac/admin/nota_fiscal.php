<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include('../includes/conexao.php');
include('../includes/header.php');

$pedido_id = $_GET['id'] ?? 0;

// Buscar dados principais do pedido
// CORREÇÃO DA QUERY AQUI:
// Buscar dados do pedido (não depende da venda)
$stmt_pedido = $conexao->prepare("
    SELECT 
        p.*,
        COALESCE(c.nome, 'Cliente não cadastrado') AS cliente_nome,
        e.logradouro, e.numero, e.complemento,
        e.bairro, e.cidade, e.estado, e.cep,
        t.numero AS telefone
    FROM tb_pedidos p
    LEFT JOIN tb_clientes c ON p.cliente_id = c.id
    LEFT JOIN tb_enderecos e ON c.id = e.cliente_id
    LEFT JOIN tb_telefones t ON c.id = t.cliente_id
    WHERE p.id = ?
");
$stmt_pedido->bind_param("i", $pedido_id);
// ====================================== //

$stmt_pedido->bind_param("i", $pedido_id);
$stmt_pedido->execute();
$pedido = $stmt_pedido->get_result()->fetch_assoc();
$stmt_pedido->close();

// Montar endereço completo
$endereco_completo = 'Não informado';
if ($pedido && !empty($pedido['logradouro'])) {
    $endereco = [
        $pedido['logradouro'] . ', ' . $pedido['numero'],
        $pedido['complemento'],
        $pedido['bairro'],
        $pedido['cidade'] . '/' . $pedido['estado'],
        'CEP: ' . $pedido['cep']
    ];
    $endereco_completo = implode(' - ', array_filter($endereco));
}

// Buscar itens do pedido com adicionais
$stmt_itens = $conexao->prepare("
    SELECT 
        ip.id AS item_id,
        ip.quantidade,
        ip.preco_unitario AS preco_base,
        pr.nome AS produto_nome,
        pr.descricao,
        cat.nome AS categoria,
        cat.icone AS categoria_icone,
        COALESCE(
            GROUP_CONCAT(
                CONCAT(a.nome, ' (', a.preco, ' x ', ipa.quantidade, ')') 
                SEPARATOR '; '
            ), 'Nenhum'
        ) AS adicionais,
        COALESCE(SUM(a.preco * ipa.quantidade), 0) AS total_adicionais,
        (ip.preco_unitario * ip.quantidade) + COALESCE(SUM(a.preco * ipa.quantidade), 0) AS subtotal
    FROM tb_itens_pedido ip
    LEFT JOIN tb_itens_pedido_adicionais ipa ON ip.id = ipa.item_pedido_id
    LEFT JOIN tb_adicionais a ON ipa.adicional_id = a.id
    JOIN tb_produtos pr ON ip.produto_id = pr.id
    JOIN tb_categorias cat ON pr.categoria_id = cat.id
    WHERE ip.pedido_id = ?
    GROUP BY ip.id
    ORDER BY cat.nome, pr.nome
");
$stmt_itens->bind_param("i", $pedido_id);
$stmt_itens->execute();
$itens = $stmt_itens->get_result();

$total_geral = 0;
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Fiscal #<?= $pedido_id ?> - Toninho Lanches</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @media print {
            body * {
                visibility: hidden;
            }

            .container,
            .container * {
                visibility: visible;
            }

            .container {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                font-size: 12px;
            }

            .no-print {
                display: none !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }

            .table {
                page-break-inside: avoid;
            }
        }

        .categoria-header {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 8px 15px;
            margin: 15px 0;
            font-weight: bold;
        }

        .adicional-item {
            font-size: 0.9em;
            color: #6c757d;
        }

        .total-box {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container mt-4">

        <!-- Cabeçalho da Empresa -->
        <div class="text-center mb-4 border-bottom pb-4">
            <img src="../assets/imagens/file.enc" alt="Logo" style="height: 80px;" class="mb-3">
            <h2 class="mb-1">Toninho Lanches</h2>
            <div class="text-muted small">
                <div>CNPJ: 00.000.000/0001-00</div>
                <div>João Ferracini, 124 - Castelo Branco Novo - Ribeirão Preto/SP</div>
                <div>Telefone: (16) 99999-9999</div>
            </div>
        </div>

        <!-- Cabeçalho do Pedido -->
        <div class="d-flex justify-content-between mb-4">
            <div>
                <h4><i class="fas fa-receipt"></i> Nota Fiscal #<?= $pedido_id ?></h4>
                <div class="text-muted small">
                    Emissão: <?= date('d/m/Y H:i:s') ?>
                </div>
            </div>
            <button onclick="window.print()" class="btn btn-sm btn-primary no-print align-self-start">
                <i class="fas fa-print"></i> Imprimir
            </button>
        </div>

        <!-- Informações do Cliente -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-user"></i> Dados do Cliente</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div><strong>Nome:</strong> <?= htmlspecialchars($pedido['cliente_nome'] ?? 'Não informado') ?></div>
                        <div><strong>Telefone:</strong> <?= htmlspecialchars($pedido['telefone'] ?? 'Não informado') ?></div>
                    </div>
                    <div class="col-md-6">
                        <div><strong>Endereço:</strong> <?= htmlspecialchars($endereco_completo) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalhes do Pedido -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-list"></i> Itens do Pedido</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 15%">Categoria</th>
                                <th style="width: 25%">Produto</th>
                                <th style="width: 20%">Descrição</th>
                                <th style="width: 8%">Qtd</th>
                                <th style="width: 12%">Unitário</th>
                                <th style="width: 20%">Adicionais</th>
                                <th style="width: 10%">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($itens->num_rows > 0): ?>
                                <?php
                                $categoria_atual = null;
                                while ($item = $itens->fetch_assoc()):
                                    $total_geral += $item['subtotal'];

                                    if ($item['categoria'] !== $categoria_atual) {
                                        echo '<tr class="categoria-header">
                                        <td colspan="7">
                                            <i class="fas ' . $item['categoria_icone'] . ' me-2"></i>
                                            ' . $item['categoria'] . '
                                        </td>
                                    </tr>';
                                        $categoria_atual = $item['categoria'];
                                    }
                                ?>
                                    <tr>
                                        <td><?= $item['categoria'] ?></td>
                                        <td><?= htmlspecialchars($item['produto_nome']) ?></td>
                                        <td><?= htmlspecialchars($item['descricao'] ?? '-') ?></td>
                                        <td class="text-center"><?= $item['quantidade'] ?></td>
                                        <td class="text-end">R$ <?= number_format($item['preco_base'], 2, ',', '.') ?></td>
                                        <td>
                                            <?php if ($item['adicionais'] !== 'Nenhum'): ?>
                                                <?php foreach (explode('; ', $item['adicionais']) as $adicional): ?>
                                                    <div class="adicional-item"><?= htmlspecialchars($adicional) ?></div>
                                                <?php endforeach; ?>
                                                <?php if ($item['total_adicionais'] > 0): ?>
                                                    <div class="text-muted small mt-2">
                                                        <em>Total adicionais: R$ <?= number_format($item['total_adicionais'], 2, ',', '.') ?></em>
                                                    </div>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">Nenhum item encontrado neste pedido</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Totais e Observações -->
        <div class="row">
            <div class="col-md-8">
                <?php if (!empty($pedido['observacoes'])): ?>
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-sticky-note"></i> Observações</h5>
                        </div>
                        <div class="card-body">
                            <?= nl2br(htmlspecialchars($pedido['observacoes'])) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-md-4">
                <div class="total-box">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-bold">Total do Pedido:</span>
                        <span class="fw-bold">R$ <?= number_format($total_geral, 2, ',', '.') ?></span>
                    </div>
                    <div class="text-muted small">
                        <div>Status: <?= ucfirst($pedido['status']) ?></div>
                        <div>Data do Pedido: <?= date('d/m/Y H:i', strtotime($pedido['data_pedido'])) ?></div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4 no-print">
                <form method="post" action="finalizar_venda.php">
                    <input type="hidden" name="pedido_id" value="<?= $pedido_id ?>">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-check-circle"></i> Finalizar Venda
                    </button>
                </form>
            </div>

            <!-- Rodapé -->
            <div class="text-center mt-4 pt-4 border-top text-muted small">
                <div class="mb-2">Sistema desenvolvido por Toninho Lanches</div>
                <div>Este documento não possui valor fiscal</div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
$stmt_itens->close();
include('../includes/footer.php');
?>