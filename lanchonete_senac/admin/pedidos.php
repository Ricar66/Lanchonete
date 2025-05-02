<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include('../includes/conexao.php');
include('../includes/header.php');

// Processar novo pedido
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['finalizar_pedido'])) {
    try {
        $conexao->begin_transaction();

        // ========== ALTERAÇÃO IMPORTANTE ========== //
        $cliente_id = !empty($_POST['cliente_id']) ? $_POST['cliente_id'] : null;
        // ========================================== //

        // Inserir pedido principal
        $stmt = $conexao->prepare("INSERT INTO tb_pedidos (cliente_id, observacoes) VALUES (?, ?)");
        $stmt->bind_param("ss", $cliente_id, $_POST['observacoes']); // Alterado para 's' para permitir NULL
        $stmt->execute();
        $pedido_id = $conexao->insert_id;
        $stmt->close();

        // Processar itens do pedido
        if (isset($_POST['produtos']) && is_array($_POST['produtos'])) {
            foreach ($_POST['produtos'] as $produto) {
                // Buscar preço do produto
                $stmt_produto = $conexao->prepare("SELECT preco FROM tb_produtos WHERE id = ?");
                $stmt_produto->bind_param("i", $produto['id']);
                $stmt_produto->execute();
                $preco_unitario = $stmt_produto->get_result()->fetch_assoc()['preco'];
                $stmt_produto->close();

                // Inserir item principal
                $stmt_item = $conexao->prepare("INSERT INTO tb_itens_pedido 
                    (pedido_id, produto_id, quantidade, preco_unitario) 
                    VALUES (?, ?, ?, ?)");
                $stmt_item->bind_param("iiid", $pedido_id, $produto['id'], $produto['quantidade'], $preco_unitario);
                $stmt_item->execute();
                $item_pedido_id = $conexao->insert_id;
                $stmt_item->close();

                // Processar adicionais
                if (!empty($produto['adicionais'])) {
                    $adicionais = json_decode($produto['adicionais'], true);
                    foreach ($adicionais as $adicional) {
                        $stmt_adicional = $conexao->prepare("INSERT INTO tb_itens_pedido_adicionais 
                            (item_pedido_id, adicional_id, quantidade) 
                            VALUES (?, ?, ?)");
                        $stmt_adicional->bind_param("iii", $item_pedido_id, $adicional['id'], $adicional['quantidade']);
                        $stmt_adicional->execute();
                        $stmt_adicional->close();
                    }
                }
            }
        }

        // Calcular o total do pedido
        $total_pedido = 0;
        foreach ($itensPedido as $item) {
            $total_pedido += $item['total'];
        }

        // Inserir na tabela de vendas
        $stmt_venda = $conexao->prepare("INSERT INTO tb_vendas 
    (pedido_id, data_venda, cliente_id, valor_total) 
    VALUES (?, NOW(), ?, ?)");
        $stmt_venda->bind_param("iid", $pedido_id, $cliente_id, $total_pedido);
        $stmt_venda->execute();

        $conexao->commit();
        header("Location: nota_fiscal.php?id=" . $pedido_id);
        exit();
    } catch (Exception $e) {
        $conexao->rollback();
        error_log("Erro ao processar pedido: " . $e->getMessage());
        echo "<div class='alert alert-danger'>Erro: " . $e->getMessage() . "</div>";
    }
}


// Buscar categorias para filtro
$categorias = $conexao->query("SELECT * FROM tb_categorias ORDER BY nome");
?>

<div class="container mt-5">
    <h2>Fazer Novo Pedido</h2>
    <form method="post" id="formPedido">
        <!-- Seção de Cliente Modificada -->
        <div class="mb-3">
            <label for="cliente_id" class="form-label">Cliente:</label>
            <select class="form-select" id="cliente_id" name="cliente_id">
                <option value="">Cliente não cadastrado</option> <!-- Opção padrão alterada -->
                <?php
                $clientes = $conexao->query("SELECT * FROM tb_clientes");
                while ($cliente = $clientes->fetch_assoc()) {
                    echo "<option value='{$cliente['id']}'>{$cliente['nome']}</option>";
                }
                ?>
            </select>
        </div>

        <!-- Filtro de Categorias -->
        <div class="mb-4">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-secondary active" data-categoria="todos">
                    <i class="fas fa-th"></i> Todos
                </button>
                <?php while ($cat = $categorias->fetch_assoc()): ?>
                    <button type="button" class="btn btn-outline-secondary" data-categoria="<?= $cat['id'] ?>">
                        <i class="fas <?= $cat['icone'] ?>"></i> <?= $cat['nome'] ?>
                    </button>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Seção de Produtos -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Adicionar Itens</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label for="produto_id" class="form-label">Produto:</label>
                        <select class="form-select" id="produto_id">
                            <option value="">Selecione um produto</option>
                            <?php
                            $produtos = $conexao->query("
                                SELECT p.*, c.nome AS categoria 
                                FROM tb_produtos p
                                JOIN tb_categorias c ON p.categoria_id = c.id
                                WHERE p.ativo = 1
                                ORDER BY p.nome
                            ");
                            while ($produto = $produtos->fetch_assoc()) {
                                echo "<option value='{$produto['id']}' 
                                    data-preco='{$produto['preco']}'
                                    data-categoria='{$produto['categoria_id']}'>
                                    {$produto['nome']} ({$produto['categoria']})
                                </option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="quantidade" class="form-label">Quantidade:</label>
                        <input type="number" class="form-control" id="quantidade" min="1" value="1">
                    </div>

                    <div class="col-md-5">
                        <label class="form-label">Adicionais:</label>
                        <div class="adicionais-container">
                            <?php
                            $adicionais = $conexao->query("SELECT * FROM tb_adicionais WHERE ativo = 1");
                            while ($adicional = $adicionais->fetch_assoc()) {
                                echo "<div class='form-check'>
                                    <input class='form-check-input' type='checkbox' 
                                        data-id='{$adicional['id']}'
                                        data-nome='{$adicional['nome']}'
                                        data-preco='{$adicional['preco']}'>
                                    <label class='form-check-label'>
                                        {$adicional['nome']} (+ R$ " . number_format($adicional['preco'], 2, ',', '.') . ")
                                    </label>
                                </div>";
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="button" class="btn btn-primary" onclick="adicionarItem()">
                        <i class="fas fa-plus-circle"></i> Adicionar Item
                    </button>
                </div>
            </div>
        </div>

        <!-- Lista de Itens Adicionados -->
        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Itens do Pedido</h5>
            </div>
            <div class="card-body">
                <ul class="list-group" id="listaItens"></ul>
            </div>
        </div>

        <!-- Observações -->
        <div class="mb-3">
            <label for="observacoes" class="form-label">Observações:</label>
            <textarea class="form-control" name="observacoes" id="observacoes" rows="3"></textarea>
        </div>

        <!-- Botão Finalizar -->
        <button type="submit" name="finalizar_pedido" class="btn btn-success btn-lg">
            <i class="fas fa-check-circle"></i> Finalizar Pedido
        </button>
    </form>
</div>

<script>
    // Filtragem por categoria
    document.querySelectorAll('[data-categoria]').forEach(btn => {
        btn.addEventListener('click', function() {
            const categoria = this.dataset.categoria;

            // Atualizar botões ativos
            document.querySelectorAll('[data-categoria]').forEach(b =>
                b.classList.remove('active'));
            this.classList.add('active');

            // Filtrar produtos
            document.querySelectorAll('#produto_id option').forEach(option => {
                if (categoria === 'todos' || option.dataset.categoria === categoria) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            });

            // Resetar seleção
            document.getElementById('produto_id').selectedIndex = 0;
        });
    });
    let itensPedido = [];

    function adicionarItem() {
        const produtoSelect = document.getElementById('produto_id');
        const produtoId = produtoSelect.value;
        const produtoNome = produtoSelect.options[produtoSelect.selectedIndex].text;
        const precoBase = parseFloat(produtoSelect.options[produtoSelect.selectedIndex].dataset.preco);
        const quantidade = parseInt(document.getElementById('quantidade').value);

        if (!produtoId || isNaN(quantidade) || quantidade < 1) {
            alert('Selecione um produto e quantidade válida!');
            return;
        }

        // Coletar adicionais selecionados
        const adicionais = [];
        document.querySelectorAll('.adicionais-container input:checked').forEach(checkbox => {
            adicionais.push({
                id: parseInt(checkbox.dataset.id),
                nome: checkbox.dataset.nome,
                preco: parseFloat(checkbox.dataset.preco),
                quantidade: quantidade
            });
        });

        // Calcular preço total
        const precoTotal = (precoBase * quantidade) +
            adicionais.reduce((sum, a) => sum + (a.preco * a.quantidade), 0);

        // Adicionar ao array
        itensPedido.push({
            produto: {
                id: parseInt(produtoId),
                nome: produtoNome,
                preco: precoBase,
                quantidade: quantidade
            },
            adicionais: adicionais,
            total: precoTotal
        });

        atualizarListaItens();
        limparCampos();
    }

    function atualizarListaItens() {
        const lista = document.getElementById('listaItens');
        lista.innerHTML = '';

        itensPedido.forEach((item, index) => {
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center';
            li.innerHTML = `
            <div>
                <strong>${item.produto.nome}</strong> (${item.produto.quantidade}x)
                ${item.adicionais.length > 0 ? 
                    `<div class="text-muted small mt-1">
                        + ${item.adicionais.map(a => `${a.nome} (${a.quantidade}x)`).join(', ')}
                    </div>` : ''}
            </div>
            <div>
                <span class="badge bg-primary rounded-pill">R$ ${item.total.toFixed(2)}</span>
                <button class="btn btn-danger btn-sm ms-2" onclick="removerItem(${index})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
            lista.appendChild(li);
        });
    }

    function removerItem(index) {
        itensPedido.splice(index, 1);
        atualizarListaItens();
    }

    function limparCampos() {
        document.getElementById('produto_id').selectedIndex = 0;
        document.getElementById('quantidade').value = 1;
        document.querySelectorAll('.adicionais-container input').forEach(checkbox => {
            checkbox.checked = false;
        });
    }

    // Preparar dados para envio
    document.getElementById('formPedido').addEventListener('submit', function(e) {
        const inputsHidden = document.querySelectorAll('input[name^="produtos"]');
        inputsHidden.forEach(input => input.remove());

        itensPedido.forEach((item, index) => {
            // Produto principal
            criarInputHidden(`produtos[${index}][id]`, item.produto.id);
            criarInputHidden(`produtos[${index}][quantidade]`, item.produto.quantidade);

            // Adicionais
            if (item.adicionais.length > 0) {
                criarInputHidden(`produtos[${index}][adicionais]`, JSON.stringify(item.adicionais));
            }
        });
    });

    function criarInputHidden(name, value) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        document.getElementById('formPedido').appendChild(input);
    }
</script>

<?php include('../includes/footer.php'); ?>