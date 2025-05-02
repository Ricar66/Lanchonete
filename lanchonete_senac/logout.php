<?php
session_start();
session_unset();
session_destroy();

header("Location:login.php?sucesso=Você saiu do sistema com sucesso!");
exit();
?>