<?php
require_once("send.php");
header("Content-Type: text/html; charset=utf-8", true);

// Garante que a tabela existe
mysqli_query($con, "CREATE TABLE IF NOT EXISTS denuncias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codpedido INT NOT NULL,
    codcadastro VARCHAR(50) NOT NULL,
    tipo VARCHAR(100),
    motivo TEXT,
    data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(codpedido),
    INDEX(codcadastro)
)");

// Buscar denúncias com nome do prestador e do pedido
$lista = mysqli_query($con, "
    SELECT d.*, p.NOME as prestador_nome, p.CELULAR as prestador_celular
    FROM denuncias d
    LEFT JOIN parceiro p ON p.id = d.codcadastro
    ORDER BY d.data_registro DESC
");

$total = $lista ? mysqli_num_rows($lista) : 0;
?>

<div class="layout-page">
    <?php require_once("nav-topo.php"); ?>
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Moderação /</span> Denúncias</h4>

            <div class="card mb-4">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-danger"><?php echo $total; ?></h3>
                    <small class="text-muted">Denúncias registradas</small>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="mb-0">Denúncias de clientes</h5></div>
                <div class="table-responsive text-nowrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Prestador denunciado</th>
                                <th>Pedido</th>
                                <th>Tipo</th>
                                <th>Motivo</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $temReg = false;
                        while ($lista && $row = mysqli_fetch_assoc($lista)) {
                            $temReg = true;
                            $nome = $row['prestador_nome'] ?: ('ID #'.$row['codcadastro']);
                            $data = !empty($row['data_registro']) ? date('d/m/Y H:i', strtotime($row['data_registro'])) : '-';
                        ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($nome); ?></strong><br><small class="text-muted"><?php echo htmlspecialchars($row['prestador_celular'] ?? ''); ?></small></td>
                                <td>#<?php echo htmlspecialchars($row['codpedido']); ?></td>
                                <td><span class="badge bg-label-warning"><?php echo htmlspecialchars($row['tipo'] ?: 'Não especificado'); ?></span></td>
                                <td style="white-space:normal;max-width:300px;"><?php echo htmlspecialchars($row['motivo'] ?: '-'); ?></td>
                                <td><?php echo $data; ?></td>
                            </tr>
                        <?php } ?>
                        <?php if (!$temReg) { ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">Nenhuma denúncia registrada.</td></tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
