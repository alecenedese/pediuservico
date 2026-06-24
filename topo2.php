<?php
// topo2.php - Agora usa header-app.php unificado
include_once(__DIR__ . '/header-app.php');

// Mantém verificação de endereço do prestador
if(isset($_COOKIE['login'])) {
    $queryEditc = mysqli_query($con, "select * from parceiro where CNPJ_CPF='".$_COOKIE['login']."'");
    $rowEdit = mysqli_fetch_array($queryEditc);
    
    if ($rowEdit) {
        $queryEnd = mysqli_query($con, "SELECT * FROM endereco_prestador WHERE cod_cadastro='".$rowEdit['id']."'");
        $rowEnd = mysqli_fetch_array($queryEnd);
        $contotalend = mysqli_num_rows($queryEnd);
        
        if ($contotalend == 0) {
            $paginaAtual = basename($_SERVER['PHP_SELF']);
            $paginasExcluidas = ['edicao2.php', 'opcoes_servicos.php', 'opcoes.php', 'opcoes2.php', 'opcoes3.php', 'categoria.php', 'buscar.php', 'servicos.php', 'novomapa.php', 'novomapa2.php', 'mapa.php', 'meus-orcamentos-cli.php', 'login-cliente.php', 'confirmanumero_cliente_login.php', 'confirmanumero_cliente_pedido.php', 'pegar_contato.php', 'aguarda-prestador.php', 'chat.php', 'editar-cadastro-cliente.php', 'aceita-orcamento.php', 'painel.php', 'login-unificado.php', 'verificar-celular.php'];
            
            if ($paginaAtual == 'edicao.php') {
                echo "<script>alert('Para aparecer na busca dos clientes, é necessário cadastrar um endereço'); window.location.href='edicao2.php';</script>";
            } elseif (!in_array($paginaAtual, $paginasExcluidas)) {
                echo "<script>alert('Para aparecer na busca dos clientes, é necessário cadastrar um endereço'); window.location.href='edicao2.php';</script>";
            }
        }
    }
}
?>