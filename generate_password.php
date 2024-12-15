<?php
$password = "sua_senha_aqui";
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Hash da senha: " . $hash;
?>
