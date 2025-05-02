<?php
// Redireciona para a área administrativa se já estiver logado
session_start();
if (isset($_SESSION['admin_id'])) {
    header("Location: admin/dashboard.php");
    exit();
}

// Se não estiver logado, redireciona para a página de login
header("Location: admin/login.php");
exit();
?>