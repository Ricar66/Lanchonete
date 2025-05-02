<?php
session_start();
 if (!isset($_SESSION['admin_id'])) {
     header("Location: login.php");
     exit();
}

include('../includes/conexao.php');
include('../includes/header.php');

// Adicionar novo cliente
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['adicionar_cliente'])) {
    $nome = $_POST['nome'];
    
    $sql = "INSERT INTO tb_clientes (nome) VALUES (?)";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("s", $nome);
    $stmt->execute();
    
    $cliente_id = $stmt->insert_id;
    
    // Adicionar telefones
    if (!empty($_POST['telefones'])) {
        foreach ($_POST['telefones'] as $telefone) {
            if (!empty($telefone)) {
                $sql = "INSERT INTO tb_telefones (cliente_id, numero) VALUES (?, ?)";
                $stmt = $conexao->prepare($sql);
                $stmt->bind_param("is", $cliente_id, $telefone);
                $stmt->execute();
            }
        }
    }
    
    // Adicionar endereço
    if (!empty($_POST['logradouro'])) {
        $sql = "INSERT INTO tb_enderecos (cliente_id, logradouro, numero, complemento, bairro, cidade, estado, cep)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("isssssss", 
            $cliente_id,
            $_POST['logradouro'],
            $_POST['numero'],
            $_POST['complemento'],
            $_POST['bairro'],
            $_POST['cidade'],
            $_POST['estado'],
            $_POST['cep']
        );
        $stmt->execute();
    }
    
    header("Location: clientes.php?sucesso=Cliente adicionado com sucesso!");
    exit();
}

// Excluir cliente
if (isset($_GET['excluir'])) {
    $id = $_GET['excluir'];
    
    // Primeiro excluir telefones e endereços
    $conexao->query("DELETE FROM tb_telefones WHERE cliente_id = $id");
    $conexao->query("DELETE FROM tb_enderecos WHERE cliente_id = $id");
    
    // Depois excluir o cliente
    $conexao->query("DELETE FROM tb_clientes WHERE id = $id");
    
    header("Location: clientes.php?sucesso=Cliente excluído com sucesso!");
    exit();
}
?>

<div class="container mt-4">
    <h2><i class="fas fa-users"></i> Gerenciamento de Clientes</h2>
    <hr>
    
    <?php if (isset($_GET['sucesso'])): ?>
        <div class="alert alert-success"><?php echo $_GET['sucesso']; ?></div>
    <?php endif; ?>
    
    <!-- Formulário para adicionar cliente -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-user-plus"></i> Adicionar Novo Cliente</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="form-group">
                    <label for="nome">Nome do Cliente</label>
                    <input type="text" class="form-control" id="nome" name="nome" required>
                </div>
                
                <div class="form-group">
                    <label>Telefones</label>
                    <div id="telefones-container">
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" name="telefones[]" placeholder="Telefone">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" onclick="adicionarTelefone()">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="logradouro">Endereço</label>
                    <input type="text" class="form-control" id="logradouro" name="logradouro" placeholder="Logradouro">
                </div>
                
                <div class="form-row">
                    <div class="form-group col-md-2">
                        <input type="text" class="form-control" name="numero" placeholder="Número">
                    </div>
                    <div class="form-group col-md-4">
                        <input type="text" class="form-control" name="complemento" placeholder="Complemento">
                    </div>
                    <div class="form-group col-md-6">
                        <input type="text" class="form-control" name="bairro" placeholder="Bairro">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <input type="text" class="form-control" name="cidade" placeholder="Cidade">
                    </div>
                    <div class="form-group col-md-2">
                        <input type="text" class="form-control" name="estado" placeholder="UF" maxlength="2">
                    </div>
                    <div class="form-group col-md-4">
                        <input type="text" class="form-control" name="cep" placeholder="CEP">
                    </div>
                </div>
                
                <button type="submit" name="adicionar_cliente" class="btn btn-primary">
                    <i class="fas fa-save"></i> Salvar Cliente
                </button>
            </form>
        </div>
    </div>
    
    <!-- Lista de clientes -->
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-list"></i> Lista de Clientes</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Telefones</th>
                            <th>Endereço</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT c.id, c.nome, 
                                GROUP_CONCAT(t.numero SEPARATOR ', ') as tb_telefones,
                                CONCAT(e.logradouro, ', ', e.numero, ' - ', e.bairro, ', ', e.cidade, '/', e.estado) as tb_enderecos
                                FROM tb_clientes c
                                LEFT JOIN tb_telefones t ON c.id = t.cliente_id
                                LEFT JOIN tb_enderecos e ON c.id = e.cliente_id
                                GROUP BY c.id
                                ORDER BY c.nome";
                        $result = $conexao->query($sql);
                        
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>
                                    <td>{$row['id']}</td>
                                    <td>{$row['nome']}</td>
                                    <td>{$row['tb_telefones']}</td>
                                    <td>{$row['tb_enderecos']}</td>
                                    <td>
                                        <a href='editar_cliente.php?id={$row['id']}' class='btn btn-sm btn-warning'>
                                            <i class='fas fa-edit'></i>
                                        </a>
                                        <a href='clientes.php?excluir={$row['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Tem certeza que deseja excluir?\")'>
                                            <i class='fas fa-trash'></i>
                                        </a>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>Nenhum cliente cadastrado</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function adicionarTelefone() {
    const container = document.getElementById('telefones-container');
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `
        <input type="text" class="form-control" name="telefones[]" placeholder="Telefone">
        <div class="input-group-append">
            <button class="btn btn-outline-danger" type="button" onclick="this.parentNode.parentNode.remove()">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    `;
    container.appendChild(div);
}
</script>

<?php include('../includes/footer.php'); ?>