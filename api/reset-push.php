<?php
// Limpa inscrições push antigas (necessário após trocar chaves VAPID)
require_once('../send.php');

header('Content-Type: text/html; charset=utf-8');

// Limpa inscrições antigas
mysqli_query($con, "DELETE FROM push_subscriptions");
$deleted = mysqli_affected_rows($con);

echo "<h2>Push Reset</h2>";
echo "<p>Inscricoes removidas: $deleted</p>";
echo "<p>Agora os prestadores precisam:</p>";
echo "<ol>";
echo "<li>Abrir o app no Chrome do celular</li>";
echo "<li>Fazer login</li>";
echo "<li>Permitir notificacoes quando solicitado</li>";
echo "</ol>";
echo "<p><a href='test-push.php'>Verificar inscricoes</a></p>";
echo "<p><a href='../buscar.php'>Voltar ao app</a></p>";
?>
