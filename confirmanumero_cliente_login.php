<?php
require_once("send.php");

$celular = isset($_GET['celularCli']) ? $_GET['celularCli'] : '';
$numerolimpo = preg_replace('/\D/', '', $celular);

if (isset($_GET['cadastro']) && $_GET['cadastro'] == '1') {
    // Cadastro novo
    $nomeCli = isset($_GET['nome_completo']) ? $_GET['nome_completo'] : '';
    
    $queryUsuario = mysqli_query($con, "SELECT * FROM clientes WHERE CELULAR='".$celular."'");
    $totalUsuario = mysqli_num_rows($queryUsuario);

    if ($totalUsuario > 0) {
        echo "<script>alert('Já existe um cadastro com este número. Faça login.'); window.location.href='login-unificado.php';</script>";
        exit;
    }

    $dataCad = date("Y-m-d");
    $queryEnvio = mysqli_query($con, "INSERT INTO clientes (TIPO, NOME, CNPJ_CPF, TELEFONE, CELULAR, ESTADO, MUNICIPIO, dataCad) VALUES
    ('', '$nomeCli', '', '', '$celular', '', '', '$dataCad')") or die(mysqli_error($con));

    $contaUltimo = mysqli_fetch_array(mysqli_query($con, "SELECT max(x.id) FROM clientes x")) or die(mysqli_error($con));
    $id = $contaUltimo[0];

    $queryEnviochat = mysqli_query($con, "INSERT INTO users (user_id, name, username, password, p_p, last_seen, celular) VALUES
            ('".$id."', '$nomeCli', '$nomeCli', '', 'user-default.png', '', '$celular')") or die(mysqli_error($con));

    $nomeUsuario = $nomeCli;
} else {
    // Login existente
    // Primeiro tenta buscar o cliente
    $queryUsuario = mysqli_query($con, "SELECT * FROM clientes WHERE CELULAR='".$celular."'");
    $totalUsuario = mysqli_num_rows($queryUsuario);
    
    if ($totalUsuario == 0) {
        // Tenta buscar como prestador
        $queryPrestador = mysqli_query($con, "SELECT * FROM parceiro WHERE CELULAR='".$celular."'");
        $totalPrestador = mysqli_num_rows($queryPrestador);
        
        if ($totalPrestador > 0) {
            $selecionaPrestador = mysqli_fetch_object($queryPrestador);
            $nomeCli = $selecionaPrestador->NOME;
            $CNPJ_CPF = $selecionaPrestador->CNPJ_CPF;
            
            // Criar registro de cliente para o prestador
            $dataCad = date("Y-m-d");
            $queryEnvio = mysqli_query($con, "INSERT INTO clientes (TIPO, NOME, CNPJ_CPF, TELEFONE, CELULAR, ESTADO, MUNICIPIO, dataCad) VALUES
            ('', '$nomeCli', '$CNPJ_CPF', '', '$celular', '', '', '$dataCad')") or die(mysqli_error($con));
            
            $contaUltimo = mysqli_fetch_array(mysqli_query($con, "SELECT max(x.id) FROM clientes x")) or die(mysqli_error($con));
            $id = $contaUltimo[0];
            $nomeUsuario = $nomeCli;
        } else {
            echo "<script>alert('Número não cadastrado. Faça o cadastro primeiro.'); window.location.href='login-unificado.php';</script>";
            exit;
        }
    } else {
        $selecionaUsuario = mysqli_fetch_object($queryUsuario);
        $nomeUsuario = $selecionaUsuario->NOME;
        $id = $selecionaUsuario->id;
    }
}

// Gerar código de confirmação (será exibido na tela)
$min = 1000;
$max = 9999;
$gera = rand($min, $max);
$enviarnumero = $gera;

// Salva o código no banco para validação
$expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
mysqli_query($con, "DELETE FROM verification_codes WHERE celular='$numerolimpo' AND usado=0");
mysqli_query($con, "INSERT INTO verification_codes (celular, codigo, tipo, expires_at) VALUES ('$numerolimpo', '$enviarnumero', 'login', '$expiresAt')");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Código - USERVICE</title>
    <link rel="stylesheet" href="global-font-size.css">
    <script>document.documentElement.style.setProperty('font-size','16px','important');</script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #1a2332 0%, #2d4a6b 100%);
            font-family: 'Arial', sans-serif;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .main-container {
            flex: 1; display: flex; flex-direction: column;
            padding: 16px; gap: 16px; overflow-y: auto;
            align-items: center; justify-content: center;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px; padding: 24px;
            width: 100%; max-width: 400px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(0, 212, 255, 0.2);
        }
        .form-container { text-align: center; }
        .form-group { margin-bottom: 16px; text-align: left; }
        .form-label { display: block; font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 8px; }
        .form-input {
            width: 100%; padding: 12px 16px;
            border: 2px solid #d1d5db; border-radius: 8px;
            font-size: 16px; transition: all 0.3s ease; background: #f9fafb;
        }
        .form-input:focus { outline: none; border-color: #00d4ff; background: white; }
        .submit-button {
            width: 100%;
            background: linear-gradient(145deg, #00d4ff, #00f0ff);
            color: #1a2332; border: none; padding: 12px;
            border-radius: 8px; font-size: 18px; font-weight: 600;
            cursor: pointer; transition: all 0.3s ease; margin-bottom: 16px;
            box-shadow: 0 4px 12px rgba(0, 212, 255, 0.3);
        }
        .submit-button:hover { transform: translateY(-2px); }
        .resend-button {
            width: 100%;
            background: transparent;
            color: #00d4ff; border: 2px solid #00d4ff;
            padding: 10px; border-radius: 8px;
            font-size: 14px; font-weight: 600;
            cursor: pointer; transition: all 0.3s ease; margin-bottom: 8px;
        }
        .resend-button:hover { background: rgba(0, 212, 255, 0.1); }
        .resend-button:disabled { opacity: 0.5; cursor: not-allowed; }
        .info-text { font-size: 14px; color: #374151; margin-bottom: 16px; line-height: 1.5; }
        .phone-number { font-weight: bold; color: #00d4ff; }
        .change-link { color: #00d4ff; text-decoration: none; font-weight: 600; font-size: 14px; }
        .timer-text { font-size: 12px; color: #6b7280; margin-top: 4px; }
    </style>
</head>
<body>
    <?php include('topo2.php'); ?>

    <div class="main-container">
        <div class="login-card">
            <div class="form-container">
                <p class="info-text">
                    <span class="phone-number"><?php echo $celular; ?></span>
                    <a href="login-unificado.php" class="change-link"> Trocar número</a><br>
                    <strong style="color: #00d4ff; font-size: 28px; display: block; margin: 15px 0; letter-spacing: 8px;"><?php echo $enviarnumero; ?></strong>
                    Digite o código acima para confirmar sua identidade
                </p>

                <form action="criarcookiewats_login.php" method="get">
                    <input type="hidden" name="nomeCli" value="<?php echo $nomeUsuario; ?>" />
                    <input type="hidden" name="numero" value="<?php echo $enviarnumero; ?>" />
                    <input type="hidden" name="celular" value="<?php echo $celular; ?>" />
                    <input type="hidden" name="codcliente" value="<?php echo isset($id) ? $id : ''; ?>" />
                    
                    <div class="form-group">
                        <label class="form-label">Código de verificação</label>
                        <input type="tel" class="form-input" placeholder="Digite o código recebido" name="confirmanumero" required>
                    </div>
                    
                    <button type="submit" class="submit-button">Confirmar</button>
                </form>

                <button type="button" class="resend-button" onclick="window.location.reload()">
                    🔄 Gerar novo código
                </button>
                <p class="timer-text">O código expira em 10 minutos</p>
            </div>
        </div>
    </div>

    <script>
        // Código exibido na tela - não precisa de countdown
    </script>
</body>
</html>
