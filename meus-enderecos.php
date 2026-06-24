<?php
require_once("send.php");

// Identifica o prestador logado pelo cookie de login (CPF/CNPJ) ou cookies alternativos
$codigo = 0;
if (isset($_COOKIE['login']) && !empty($_COOKIE['login'])) {
    $qEdit = mysqli_query($con, "SELECT id FROM parceiro WHERE CNPJ_CPF='".mysqli_real_escape_string($con, $_COOKIE['login'])."'");
    if ($qEdit && $rEdit = mysqli_fetch_array($qEdit)) {
        $codigo = $rEdit['id'];
    }
} elseif (isset($_COOKIE['id']) && !empty($_COOKIE['id'])) {
    $codigo = (int)$_COOKIE['id'];
} elseif (isset($_COOKIE['id_prestador']) && !empty($_COOKIE['id_prestador'])) {
    $codigo = (int)$_COOKIE['id_prestador'];
} elseif (isset($_COOKIE['cpf_cnpj_unificado']) && !empty($_COOKIE['cpf_cnpj_unificado'])) {
    // Login unificado: resolve o id do prestador pelo CPF/CNPJ
    $cpfLimpo = preg_replace('/\D/', '', $_COOKIE['cpf_cnpj_unificado']);
    $cpfEsc = mysqli_real_escape_string($con, $cpfLimpo);
    $qEdit = mysqli_query($con, "SELECT id FROM parceiro WHERE REPLACE(REPLACE(REPLACE(REPLACE(CNPJ_CPF,'.',''),'-',''),'/',''),' ','') = '$cpfEsc' LIMIT 1");
    if ($qEdit && $rEdit = mysqli_fetch_array($qEdit)) {
        $codigo = $rEdit['id'];
    }
}

// Busca o endereço vinculado ao cadastro (apenas se houver cadastro válido)
$rowEnd = [];
if ($codigo > 0) {
    $queryEnd = mysqli_query($con, "SELECT * FROM endereco_prestador WHERE cod_cadastro='".$codigo."' LIMIT 1");
    if ($queryEnd && $r = mysqli_fetch_array($queryEnd)) {
        $rowEnd = $r;
    }
}

// Helper para preencher campos com segurança
function campoEnd($rowEnd, $campo) {
    return isset($rowEnd[$campo]) ? htmlspecialchars($rowEnd[$campo]) : '';
}

$navAtiva = 'servicos';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Endereços - USERVICE</title>
    <?php if (file_exists(__DIR__ . '/pwa-include.php')) include 'pwa-include.php'; ?>
    <script src="https://code.jquery.com/jquery-3.0.0.min.js"></script>
    <link rel="stylesheet" href="global-font-size.css">
    <script>document.documentElement.style.setProperty('font-size','16px','important');</script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #1a2332 0%, #2d4a6b 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Arial', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-bottom: 70px;
            color: #fff;
        }
        .main-content {
            flex: 1;
            padding: 16px;
            max-width: 600px;
            margin: 0 auto;
            width: 100%;
        }
        .page-title {
            text-align: center;
            color: #00d4ff;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 16px;
            text-shadow: 0 0 10px rgba(0, 212, 255, 0.3);
        }
        .aviso-sem-cadastro {
            background: rgba(251,191,36,0.12);
            border: 1px solid rgba(251,191,36,0.4);
            color: #fbbf24;
            padding: 14px;
            border-radius: 10px;
            text-align: center;
            font-size: 13px;
            margin-bottom: 16px;
            line-height: 1.5;
        }
        .content-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .address-form { display: flex; flex-direction: column; gap: 18px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-label { font-weight: 600; color: #1a2332; font-size: 14px; }
        .form-input {
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #fff;
            color: #1a2332;
        }
        .form-input:focus {
            outline: none;
            border-color: #00d4ff;
            box-shadow: 0 0 0 3px rgba(0, 212, 255, 0.1);
        }
        @media (min-width: 480px) {
            .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        }
        .form-buttons { display: flex; flex-direction: column; gap: 12px; margin-top: 8px; }
        .btn {
            padding: 13px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            display: block;
        }
        .btn-primary {
            background: linear-gradient(145deg, #00d4ff, #0ea5e9);
            color: #fff;
            box-shadow: 0 4px 12px rgba(0, 212, 255, 0.3);
        }
        .btn-primary:active { transform: scale(0.99); }
        .btn-secondary { background: #e9ecef; color: #495057; }
        @media (min-width: 480px) {
            .form-buttons { flex-direction: row; justify-content: center; }
            .btn { flex: 1; max-width: 220px; }
        }
    </style>
</head>
<body>
<?php include('header-app.php'); ?>

<div class="main-content">
    <div class="page-title">📍 Meu Endereço</div>

    <?php if ($codigo <= 0) { ?>
        <div class="aviso-sem-cadastro">
            ⚠️ Para cadastrar um endereço você precisa ter um cadastro de prestador.
            Faça login como prestador ou complete seu cadastro.
        </div>
    <?php } elseif (empty($rowEnd)) { ?>
        <div class="aviso-sem-cadastro">
            📌 Você ainda não cadastrou um endereço. Preencha os campos abaixo para começar a receber pedidos na sua região.
        </div>
    <?php } ?>

    <div class="content-card">
        <form action="converteendemcord.php" method="get" class="address-form" id="meuFormulario">
            <div class="form-group">
                <label class="form-label">CEP:</label>
                <input type="text" id="cep" name="cep" value="<?php echo campoEnd($rowEnd, 'cep'); ?>" maxlength="9" class="form-input" placeholder="13483-000" autofocus>
            </div>

            <div class="form-group">
                <label class="form-label">Endereço:</label>
                <input type="text" id="endereco" name="endereco" value="<?php echo campoEnd($rowEnd, 'endereco'); ?>" class="form-input">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Número:</label>
                    <input type="text" id="n" name="n" value="<?php echo campoEnd($rowEnd, 'n'); ?>" class="form-input">
                </div>

                <div class="form-group">
                    <label class="form-label">Bairro:</label>
                    <input type="text" id="bairro" name="bairro" value="<?php echo campoEnd($rowEnd, 'bairro'); ?>" class="form-input">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">UF:</label>
                    <input type="text" id="uf" name="uf" value="<?php echo campoEnd($rowEnd, 'uf'); ?>" class="form-input" maxlength="2">
                </div>

                <div class="form-group">
                    <label class="form-label">Cidade:</label>
                    <input type="text" id="cidade" name="cidade" value="<?php echo campoEnd($rowEnd, 'cidade'); ?>" class="form-input">
                </div>
            </div>

            <div class="form-buttons">
                <input type="submit" value="💾 SALVAR" class="btn btn-primary">
                <button type="button" onclick="limparFormulario()" class="btn btn-secondary">🔄 TROCAR ENDEREÇO</button>
            </div>
        </form>
    </div>
</div>

<?php if (file_exists(__DIR__ . '/bottom-nav.php')) include('bottom-nav.php'); ?>

<script>
    function limparFormulario() {
        document.getElementById("meuFormulario").reset();
        document.getElementById("cep").focus();
    }

    $("#cep").blur(function(){
        var cep = this.value.replace(/[^0-9]/, "");
        if(cep.length != 8){ return false; }
        var url = "https://viacep.com.br/ws/"+cep+"/json/";
        $.getJSON(url, function(dadosRetorno){
            try{
                $("#endereco").val(dadosRetorno.logradouro);
                $("#bairro").val(dadosRetorno.bairro);
                $("#cidade").val(dadosRetorno.localidade);
                $("#uf").val(dadosRetorno.uf);
            }catch(ex){}
        });
    });
</script>
</body>
</html>
