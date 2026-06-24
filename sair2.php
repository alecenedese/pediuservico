<?php
unset($_COOKIE['tipo']);
setcookie('tipo', null, -1, '/');

unset($_COOKIE['nome']);
setcookie('nome', null, -1, '/');

unset($_COOKIE['login']);
setcookie('login', null, -1, '/');

unset($_COOKIE['senha']);
setcookie('senha', null, -1, '/');

echo "<script>window.location.href='index.php';</script>";