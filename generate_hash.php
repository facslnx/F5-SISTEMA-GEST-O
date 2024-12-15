<?php
$password = 'fernanduh123';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Hash gerado: " . $hash;
?>
