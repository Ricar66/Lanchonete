<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include('../includes/conexao.php');
include('../includes/header.php');

// Exibir mensagem de sucesso
if (isset($_GET['sucesso'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            ' . htmlspecialchars($_GET['sucesso']) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
}

// Processar novos dados
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Adicionar produto
    if (isset($_POST['adicionar_produto'])) {
        $nome = $_POST['nome'];
        $descricao = $_POST['descricao'];
        $preco = str_replace(',', '.', $_POST['preco']);
        $categoria_id = $_POST['categoria_id'];

        $stmt = $conexao->prepare("INSERT INTO tb_produtos (nome, descricao, preco, categoria_id, ativo) VALUES (?, ?, ?, ?, TRUE)");
        $stmt->bind_param("ssdi", $nome, $descricao, $preco, $categoria_id);
        $stmt->execute();
    }

    // Atualizar preço do produto
    if (isset($_POST['atualizar_preco']) && isset($_POST['tabela'])) {
        if ($_POST['tabela'] == 'tb_produtos') {
            $novo_preco = str_replace(',', '.', $_POST['novo_preco']);
            $categoria_id = $_POST['categoria_id'];
            $id = $_POST['id'];
            
            $stmt = $conexao->prepare("UPDATE tb_produtos SET preco = ?, categoria_id = ? WHERE id = ?");
            $stmt->bind_param("dii", $novo_preco, $categoria_id, $id);
            
            if ($stmt->execute()) {
                header("Location: produtos.php?sucesso=Preço+atualizado+com+sucesso!");
                exit();
            }
        }
    }

    // Atualizar adicional
    if (isset($_POST['atualizar_adicional'])) {
        $novo_preco = str_replace(',', '.', $_POST['novo_preco']);
        $stmt = $conexao->prepare("UPDATE tb_adicionais SET preco = ? WHERE id = ?");
        $stmt->bind_param("di", $novo_preco, $_POST['id']);
        $stmt->execute();
        header("Location: produtos.php?sucesso=Preço+do+adicional+atualizado!");
        exit();
    }

    // Adicionar adicional
    if (isset($_POST['adicionar_adicional'])) {
        $nome = $_POST['nome_adicional'];
        $preco = str_replace(',', '.', $_POST['preco_adicional']);

        $stmt = $conexao->prepare("INSERT INTO tb_adicionais (nome, preco, ativo) VALUES (?, ?, TRUE)");
        $stmt->bind_param("sd", $nome, $preco);
        $stmt->execute();
    }

    // Adicionar categoria
    if (isset($_POST['adicionar_categoria'])) {
        $nome = $_POST['nome_categoria'];
        $icone = $_POST['icone_categoria'];

        $stmt = $conexao->prepare("INSERT INTO tb_categorias (nome, icone) VALUES (?, ?)");
        $stmt->bind_param("ss", $nome, $icone);
        $stmt->execute();
    }
}

// Funções auxiliares
function listarCategorias($conexao) {
    $categorias = $conexao->query("SELECT * FROM tb_categorias");
    while ($cat = $categorias->fetch_assoc()) {
        echo "<option value='{$cat['id']}'>{$cat['nome']}</option>";
    }
}

function getNomeCategoria($conexao, $id) {
    $result = $conexao->query("SELECT nome FROM tb_categorias WHERE id = $id");
    return $result->fetch_assoc()['nome'];
}
?>

<div class="container mt-4">
    <h2><i class="fas fa-utensils"></i> Gerenciamento do Cardápio</h2>
    <hr>

    <!-- Seção de Categorias -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5><i class="fas fa-tags"></i> Categorias</h5>
        </div>
        <div class="card-body">
            <form method="POST" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="nome_categoria" placeholder="Nome da Categoria" required>
                    </div>
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="icone_categoria" placeholder="Ícone FontAwesome (ex: fa-burger)" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="adicionar_categoria" class="btn btn-success w-100">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </form>

            <div class="row row-cols-2 row-cols-md-4 g-4">
                <?php
                $categorias = $conexao->query("SELECT * FROM tb_categorias");
                while ($cat = $categorias->fetch_assoc()):
                ?>
                    <div class="col">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas <?= $cat['icone'] ?> fa-2x mb-3"></i>
                                <h5><?= $cat['nome'] ?></h5>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <!-- Seção de Produtos -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5><i class="fas fa-hamburger"></i> Produtos</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="nome" placeholder="Nome" required>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="categoria_id" required>
                            <option value="">Categoria</option>
                            <?= listarCategorias($conexao) ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control" name="preco" placeholder="Preço" required>
                    </div>
                    <div class="col-md-5">
                        <textarea class="form-control" name="descricao" placeholder="Descrição" rows="1"></textarea>
                    </div>
                    <div class="col-md-12">
                        <button type="submit" name="adicionar_produto" class="btn btn-success w-100">
                            <i class="fas fa-plus"></i> Adicionar Produto
                        </button>
                    </div>
                </div>
            </form>

            <div class="table-responsive mt-4">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Descrição</th>
                            <th>Categoria</th>
                            <th>Preço</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $produtos = $conexao->query("
                            SELECT p.*, c.nome AS categoria_nome 
                            FROM tb_produtos p
                            JOIN tb_categorias c ON p.categoria_id = c.id
                            WHERE p.ativo = TRUE
                            ORDER BY p.nome
                        ");
                        while ($p = $produtos->fetch_assoc()):
                        ?>
                            <tr>
                                <td><?= $p['nome'] ?></td>
                                <td><?= $p['descricao'] ?></td>
                                <td><?= $p['categoria_nome'] ?></td>
                                <td>R$ <?= number_format($p['preco'], 2, ',', '.') ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editar<?= $p['id'] ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="?excluir=<?= $p['id'] ?>&tabela=tb_produtos" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Tem certeza?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>

                            <!-- Modal Edição -->
                            <div class="modal fade" id="editar<?= $p['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Editar <?= $p['nome'] ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="tabela" value="tb_produtos">
                                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                                <div class="mb-3">
                                                    <label>Preço</label>
                                                    <input type="text" class="form-control" name="novo_preco"
                                                        value="<?= number_format($p['preco'], 2, ',', '') ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label>Categoria</label>
                                                    <select class="form-select" name="categoria_id" required>
                                                        <option value="<?= $p['categoria_id'] ?>"><?= $p['categoria_nome'] ?></option>
                                                        <?= listarCategorias($conexao) ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                                <button type="submit" name="atualizar_preco" class="btn btn-primary">Salvar</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Seção de Adicionais -->
    <div class="card">
        <div class="card-header bg-warning text-dark">
            <h5><i class="fas fa-plus-circle"></i> Adicionais</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="nome_adicional" placeholder="Nome do adicional" required>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="preco_adicional" placeholder="Preço (ex: 5,00)" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="adicionar_adicional" class="btn btn-success w-100">
                            <i class="fas fa-plus"></i> Adicionar
                        </button>
                    </div>
                </div>
            </form>

            <div class="table-responsive mt-4">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Preço</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $adicionais = $conexao->query("SELECT * FROM tb_adicionais WHERE ativo = TRUE ORDER BY nome");
                        while ($a = $adicionais->fetch_assoc()):
                        ?>
                            <tr>
                                <td><?= $a['nome'] ?></td>
                                <td>R$ <?= number_format($a['preco'], 2, ',', '.') ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editarAdicional<?= $a['id'] ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="?excluir=<?= $a['id'] ?>&tabela=tb_adicionais" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Tem certeza?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>

                            <!-- Modal Edição -->
                            <div class="modal fade" id="editarAdicional<?= $a['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Editar <?= $a['nome'] ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="tabela" value="tb_adicionais">
                                                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                                <div class="mb-3">
                                                    <input type="text" class="form-control" name="novo_preco"
                                                        value="<?= number_format($a['preco'], 2, ',', '') ?>" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                                <button type="submit" name="atualizar_adicional" class="btn btn-primary">Salvar</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>