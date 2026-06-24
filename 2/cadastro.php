<?php 
require("send.php");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - USERVICE</title>
    <link rel="stylesheet" href="global-font-size.css">
    <script>document.documentElement.style.setProperty('font-size','16px','important');</script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #1a2332 0%, #2d4a6b 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 13px;
        }
                .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 13px;
        }

        .logo {
            font-size: 19px;
            font-weight: bold;
            color: #00d4ff;
            text-shadow: 0 0 15px rgba(0, 212, 255, 0.3);
            letter-spacing: 1px;
        }

        .step-indicator {
            color: #00d4ff;
            font-size: 10px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* adicionando estilo do botão voltar igual opcoes2.html */
        .back-btn {
            background: linear-gradient(145deg, #00d4ff, #00f0ff);
            border: 2px solid #00f0ff;
            color: #1a2332;
            font-size: 11px;
            font-weight: 600;
            padding: 6px 13px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(0, 240, 255, 0.3);
        }

        .back-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(0, 240, 255, 0.5);
            background: linear-gradient(145deg, #00f0ff, #40ffff);
        }

        /* Registration card container with USERVICE styling */
        .register-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 32px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(0, 212, 255, 0.2);
            margin: 16px 0;
        }

        /* Logo styling */
        .logo-container {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo {
            font-size: 32px;
            font-weight: bold;
            color: #00d4ff;
            text-shadow: 0 0 10px rgba(0, 212, 255, 0.3);
            letter-spacing: 2px;
        }

        .logo-subtitle {
            font-size: 14px;
            color: #666;
            margin-top: 8px;
        }

        .page-title {
            font-size: 24px;
            color: #1a2332;
            margin-bottom: 24px;
            text-align: center;
        }

        /* Form styling */
        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #1a2332;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-input, .form-select {
            width: 100%;
            padding: 16px;
            border: 2px solid rgba(0, 212, 255, 0.2);
            border-radius: 8px;
            font-size: 16px;
            background: rgba(255, 255, 255, 0.9);
            transition: all 0.3s ease;
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: #00d4ff;
            box-shadow: 0 0 0 3px rgba(0, 212, 255, 0.1);
        }

        .form-textarea {
            width: 100%;
            padding: 16px;
            border: 2px solid rgba(0, 212, 255, 0.2);
            border-radius: 8px;
            font-size: 14px;
            background: rgba(255, 255, 255, 0.9);
            transition: all 0.3s ease;
            min-height: 80px;
            resize: vertical;
        }

        .form-textarea:focus {
            outline: none;
            border-color: #00d4ff;
            box-shadow: 0 0 0 3px rgba(0, 212, 255, 0.1);
        }

        /* Validation messages */
        .validation-message {
            position: absolute;
            right: 12px;
            top: -5px;
            font-size: 13px;
            color: #dc3545;
            z-index: 10;
        }

        /* Password validation */
        .password-validation {
            margin-top: 8px;
            font-size: 14px;
        }

        .password-validation.valid {
            color: #28a745;
        }

        .password-validation.invalid {
            color: #dc3545;
        }

        /* Service categories accordion */
        .categories-section {
            margin: 24px 0;
        }

        .search-container {
            position: relative;
            margin-bottom: 16px;
        }

        .search-input {
            width: 100%;
            padding: 13px 16px;
            border: 2px solid rgba(0, 212, 255, 0.2);
            border-radius: 25px;
            font-size: 14px;
            background: rgba(255, 255, 255, 0.9);
        }

        .search-input:focus {
            outline: none;
            border-color: #00d4ff;
        }

        .accordion {
            border: 1px solid rgba(0, 212, 255, 0.2);
            border-radius: 8px;
            overflow: hidden;
        }

        .accordion-item {
            border-bottom: 1px solid rgba(0, 212, 255, 0.1);
        }

        .accordion-item:last-child {
            border-bottom: none;
        }

        .accordion-header {
            background: rgba(0, 212, 255, 0.1);
            padding: 16px;
            cursor: pointer;
            font-weight: 600;
            color: #1a2332;
            transition: background 0.3s ease;
        }

        .accordion-header:hover {
            background: rgba(0, 212, 255, 0.2);
        }

        .accordion-content {
            padding: 16px;
            background: white;
            display: none;
        }

        .accordion-content.show {
            display: block;
        }

        .checkbox-group {
            display: grid;
            grid-template-columns: 1fr;
            gap: 8px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .checkbox-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #00d4ff;
        }

        .checkbox-item label {
            font-size: 14px;
            color: #1a2332;
            cursor: pointer;
        }

        /* Selected services display */
        .selected-services {
            background: rgba(0, 212, 255, 0.1);
            border: 1px solid rgba(0, 212, 255, 0.2);
            border-radius: 8px;
            padding: 16px;
            margin-top: 16px;
            display: none;
        }

        .selected-services.show {
            display: block;
        }

        .selected-services h4 {
            color: #00d4ff;
            font-size: 16px;
            margin-bottom: 8px;
        }

        .selected-services-list {
            font-size: 14px;
            color: #1a2332;
            font-weight: 600;
        }

        /* Captcha */
        .captcha-container {
            background: rgba(255, 193, 7, 0.1);
            border: 1px solid rgba(255, 193, 7, 0.3);
            border-radius: 8px;
            padding: 16px;
            text-align: center;
        }

        .captcha-question {
            font-size: 18px;
            font-weight: 600;
            color: #1a2332;
            margin-bottom: 8px;
        }

        .captcha-input {
            width: 100px;
            text-align: center;
            margin: 0 auto;
        }

        /* Buttons */
        .register-button {
            width: 100%;
            background: linear-gradient(145deg, #00d4ff, #00f0ff);
            color: #1a2332;
            border: none;
            padding: 16px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(0, 212, 255, 0.3);
            margin-top: 16px;
        }

        .register-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 212, 255, 0.4);
        }

        .register-button:disabled {
            background: #ccc;
            color: #666;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .error-notice {
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #dc3545;
            padding: 16px;
            border-radius: 8px;
            margin-top: 16px;
            text-align: center;
            font-size: 14px;
            display: none;
        }

        .error-notice.show {
            display: block;
        }

        /* WhatsApp help button */
        .whatsapp-help {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }

        .whatsapp-help img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            .register-card {
                padding: 24px;
            }
            
            .checkbox-group {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body>
            <div class="header">
        <div class="logo">USERVICE</div>
        <!-- substituindo step-indicator por botão voltar igual opcoes2.html -->
        <button class="back-btn" onclick="window.history.back()">← Voltar</button>
    </div>
    <div class="register-card">

        <h2 class="page-title">Cadastre-se 🚀</h2>

        <!-- Registration form with vertical mobile layout -->
        <form id="formAuthentication" action="salvarCadastro.php" method="POST">
            <div class="form-group">
                <label for="perfil" class="form-label">Tipo de Cadastro</label>
                <select name="perfil" id="teste" class="form-select" required>
                    <option value="F">Pessoa Física</option>
                    <option value="J">Pessoa Jurídica</option>
                </select>
            </div>

            <div class="form-group cpff">
                <label for="cpf" class="form-label">CPF</label>
                <input
                    type="text"
                    class="form-input cpf"
                    id="cpf"
                    name="cpf"
                    onblur="validarDados('cpf', document.getElementById('cpf').value);"
                    autofocus
                />
                <div id="campo_cpf" class="validation-message"></div>
            </div>

            <div class="form-group cnpjj" style="display: none;">
                <label for="cnpj" class="form-label">CNPJ</label>
                <input
                    type="text"
                    class="form-input cnpj"
                    id="cnpj"
                    name="cnpj"
                    onblur="validarDados('cnpj', document.getElementById('cnpj').value);"
                />
                <div id="campo_cnpj" class="validation-message"></div>
            </div>

            <div class="form-group">
                <label for="nome" class="form-label">Nome</label>
                <input
                    type="text"
                    class="form-input"
                    name="nome"
                    required
                />
            </div>

            <div class="form-group">
                <label for="telefone" class="form-label">Celular</label>
                <input
                    type="text"
                    id="telefone"
                    class="form-input celular"
                    name="celular"
                    required
                />
            </div>

            <div class="form-group">
                <label for="estado" class="form-label">Estado</label>
                <select name="estado" id="estado" class="form-select" required>
                    <option value="MT">Mato Grosso</option>
                    <?php
                    $sqlUf = "SELECT * from estados where Uf <> 'MT' ORDER BY Nome asc";
                    $resUf = mysqli_query( $con, $sqlUf ); 
                    while ( $row = mysqli_fetch_assoc( $resUf ) ) { ?>
                    <option value="<?php echo $row['Uf']; ?>"><?php echo $row['Nome']; ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label for="cidade" class="form-label">Cidade</label>
                <select name="cidade" id="cidade" class="form-select" required>
                    <option value="Sinop">Sinop</option>
                    <?php
                    $sqlCity = "SELECT * from cidades where Uf = 'MT' ORDER BY Nome asc";
                    $sqlCity = mysqli_query( $con, $sqlCity ); 
                    while ( $row = mysqli_fetch_assoc( $sqlCity ) ) { ?>
                    <option value="<?php echo $row['Nome']; ?>"><?php echo $row['Nome']; ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label for="pass" class="form-label">Senha</label>
                <input id="pass"
                       type="password"
                       name="pass"
                       minlength="3"
                       class="form-input"
                       required>
            </div>
            
            <div class="form-group">
                <label for="confirm_pass" class="form-label">Confirme a senha</label>
                <input id="confirm_pass"
                       class="form-input"
                       type="password"
                       minlength="3"
                       name="confirm_pass"
                       required
                       onkeyup="validate_password()">
                <div id="wrong_pass_alert" class="password-validation"></div>
            </div>

            <!-- Service categories section with accordion -->
            <div class="categories-section">
                <div class="search-container">
                    <input type="search" id="accordion_search_bar" class="search-input" placeholder="Procurar Categoria" />
                </div>
                
                <div class="accordion" id="accordion">
                    <?php
                    $sql = "SELECT * from grupos ORDER BY titulo asc";
                    $res = mysqli_query( $con, $sql ); 
                    while ( $row = mysqli_fetch_assoc( $res ) ) { ?>
                        <div class="accordion-item" id="collapse<?php echo $row['codigo']; ?>_container">
                            <div class="accordion-header" onclick="toggleAccordion('collapse<?php echo $row['codigo']; ?>')">
                                <?php echo $row['titulo']; ?>
                            </div>
                            <div id="collapse<?php echo $row['codigo']; ?>" class="accordion-content">
                                <div class="checkbox-group">
                                    <?php
                                    $sql2 = "SELECT * from categoria where codgrupo='".$row['codigo']."' ORDER BY titulo asc";
                                    $res2 = mysqli_query( $con, $sql2 ); 
                                    while ( $rowSub = mysqli_fetch_assoc( $res2 ) ) { ?>
                                        <div class="checkbox-item">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                value="<?php echo $rowSub['titulo']; ?>"
                                                name="subcategoria[]"
                                                onClick="getValues()" 
                                                id="chkAccordion<?php echo $row['codigo']; ?>Child<?php echo $rowSub['codigo']; ?>"
                                            />
                                            <label for="chkAccordion<?php echo $row['codigo']; ?>Child<?php echo $rowSub['codigo']; ?>">
                                                <?php echo $rowSub['titulo']; ?>
                                            </label>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?> 
                </div>

                <div class="selected-services" id="selectedServices">
                    <h4>Serviços Selecionados:</h4>
                    <div id="selecaoCursos" class="selected-services-list"></div>
                </div>
            </div>

            <div class="form-group">
                <label for="serviconao" class="form-label">Outros Serviços</label>
                <textarea class="form-textarea" name="serviconao" placeholder="INFORME OS SERVIÇOS QUE VOCÊ PRESTA E NÃO ENCONTROU"></textarea>
            </div>

            <div class="form-group enviar">
                <div class="captcha-container">
                    <div class="captcha-question">
                        Quanto é <span class="captcha"><?php echo(rand(1,9)); ?></span> + <span class="captcha"><?php echo(rand(1,9)); ?></span>?
                    </div>
                    <input name="soma" type="tel" class="form-input captcha-input" required>
                </div>
            </div>

            <div class="enviar">
                <button type="submit" class="register-button">
                    Fazer Cadastro
                </button>
            </div>

            <div class="error-notice enviar2">
                <strong>Verifique se todos os dados estão corretos</strong>
            </div>

            <div class="enviar2" style="display:none;">
                <button disabled class="register-button">
                    Fazer Cadastro
                </button>
            </div>
        </form>
    </div>

    <!-- WhatsApp help button -->
    <div class="whatsapp-help">
        <a href="https://api.whatsapp.com/send?phone=5566992537077&text=Ola,%20tudo%20bem?%20preciso%20de%20ajuda%20com%20o%20cadastro%20no%20site" target="_blank">
           <img src="https://images.tcdn.com.br/static_inst/integracao/imagens/whatsapp.png" alt="WhatsApp" />
        </a>
    </div>

    <script type="text/javascript">
        $(function(){
            $("#cidade").select2();
        }); 

        $(document).ready(function () {
            $("#estado").change(function(){
                let id = $(this).val();  
                $.ajax({
                    url:"https://gessomt.app.br/pediuservico/cidades.php",
                    method:"POST",
                    dataType: "HTML",
                    data: {"id": id}
                }).done(function(data){
                    $("#cidade").html(data);
                });
            });
        });

        function getKindSupplier(type) {
            if (type == "F") {
                $(".cpff").show();
                $(".cnpjj").hide();
            }
            if (type == "J") {
                $(".cnpjj").show();
                $(".cpff").hide();
            }
        }

        $(document).on("change", "#teste", function () {
            getKindSupplier($(this).val());
        });

        var req;
        function validarDados(campo, valor) {
            if (window.XMLHttpRequest) {
                req = new XMLHttpRequest();
            } else if (window.ActiveXObject) {
                req = new ActiveXObject("Microsoft.XMLHTTP");
            }
            var url = "<?php echo $urlserver; ?>validacao.php?campo=" + campo + "&valor=" + valor;
            req.open("Get", url, true);
            req.onreadystatechange = function () {
                if (req.readyState == 1) {
                    document.getElementById("campo_" + campo + "").innerHTML = '<span style="color: gray;">Verificando...</span>';
                }
                if (req.readyState == 4 && req.status == 200) {
                    var resposta = req.responseText;
                    if (req.responseText == 01) {
                        var resposta = "";
                    }
                    document.getElementById("campo_" + campo + "").innerHTML = resposta;
                }
            };
            req.send(null);
        }

        function validate_password() {
            var pass = document.getElementById('pass').value;
            var confirm_pass = document.getElementById('confirm_pass').value;
            if (pass != confirm_pass) {
                document.getElementById('wrong_pass_alert').className = 'password-validation invalid';
                document.getElementById('wrong_pass_alert').innerHTML = '☒ As senhas não conferem';
                $(".enviar").hide(); 
                $(".enviar2").show();
            } else {
                document.getElementById('wrong_pass_alert').className = 'password-validation valid';
                document.getElementById('wrong_pass_alert').innerHTML = '🗹 Senhas iguais';
                $(".enviar2").hide(); 
                $(".enviar").show();
            }
        }

        function getValues() {   
            $("#selectedServices").addClass('show');
            var pacote = document.querySelectorAll("input[name^='subcategoria[']:checked")
            var values = []; 
            for (var i = 0; i < pacote.length; i++) {
                values.push(pacote[i].value);
            } 
            document.getElementById("selecaoCursos").innerHTML = values.join(', ');
        }

        var checkboxes = document.querySelectorAll("input[name^='subcategoria[']");
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].addEventListener('click', getValues, false);
        }

        function toggleAccordion(id) {
            const content = document.getElementById(id);
            content.classList.toggle('show');
        }

        // Search functionality
        $.expr[":"].containsCaseInsensitive = function (n, i, m) {
            return jQuery(n).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
        };
        
        $("#accordion_search_bar").on("change keyup paste click", function () {
            var searchTerm = $(this).val();
            $(".accordion-item").each(function () {
                var panelContainerId = "#" + $(this).attr("id");
                $(panelContainerId + ":not(:containsCaseInsensitive(" + searchTerm + "))").hide();
                $(panelContainerId + ":containsCaseInsensitive(" + searchTerm + ")").show();
            });
        });

        // Mask configurations
        jQuery(document).ready(function($){
            var CpfCnpjMaskBehavior = function (val) {
                return val.replace(/\D/g, '').length <= 11 ? '000.000.000-009' : '000.000.000-009';
            },
            cpfCnpjpOptions = {
                onKeyPress: function(val, e, field, options) {
                    field.mask(CpfCnpjMaskBehavior.apply({}, arguments), options);
                }
            };
            $('.cpf').mask(CpfCnpjMaskBehavior, cpfCnpjpOptions);

            var CnpjMaskBehavior = function (val) {
                return val.replace(/\D/g, '').length <= 11 ? '00.000.000/0000-00' : '00.000.000/0000-00';
            },
            cnpjOptions = {
                onKeyPress: function(val, e, field, options) {
                    field.mask(CnpjMaskBehavior.apply({}, arguments), options);
                }
            };
            $('.cnpj').mask(CnpjMaskBehavior, cnpjOptions);

            var CelularMaskBehavior = function (val) {
                return val.replace(/\D/g, '').length <= 11 ? '(00) 00000-0000' : '(00) 00000-0000';
            },
            celularOptions = {
                onKeyPress: function(val, e, field, options) {
                    field.mask(CelularMaskBehavior.apply({}, arguments), options);
                }
            };
            $('.celular').mask(CelularMaskBehavior, celularOptions);
        });

        // Captcha validation
        $("form").on("submit", function(){
            var s1 = parseInt($(".captcha:eq(0)").text());
            var s2 = parseInt($(".captcha:eq(1)").text());
            var ss = parseInt($("[name='soma']").val());

            if(s1+s2 != ss){
                alert("Soma incorreta!");
                return false;
            }
        });
    </script>
</body>
</html>
