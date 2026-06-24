<?php session_start();
require_once ("send.php");

if(isset($_COOKIE['celularCli'])) {
    
    $queryEnvioLogin = mysqli_query($con, "INSERT INTO pega_contato (nome, celular, codpedido, codcadastro, codcliente, aceito_orcamento) VALUES
     ('".$_COOKIE['nomeCli']."', '".$_COOKIE['celularCli']."', '".$_GET['codpedido']."', '".$_GET['codcadastro']."', '".$_COOKIE['codcliente']."', 'sim')") or die(mysqli_error($con));

    $editaPedidoCads = mysqli_query($con, "update disparo_pedidos set aceito='ac' where codpedido = '".$_GET['codpedido']."' and codcadastro = '".$_GET['codcadastro']."'") or die(mysqli_error($con));

    echo "<script>window.location.href='meus-orcamentos-cli.php?codpedido=".$_GET['codpedido']."';</script>";

} elseif(isset($_COOKIE['celularPrestador'])) {
    
        $queryEnvioLogin = mysqli_query($con, "INSERT INTO pega_contato (nome, celular, codpedido, codcadastro, codcliente, aceito_orcamento) VALUES
         ('".$_COOKIE['nome']."', '".$_COOKIE['celularPrestador']."', '".$_GET['codpedido']."', '".$_GET['codcadastro']."', '".$_COOKIE['id']."', 'sim')") or die(mysqli_error($con));
    
        $editaPedidoCads = mysqli_query($con, "update disparo_pedidos set aceito='ac' where codpedido = '".$_GET['codpedido']."' and codcadastro = '".$_GET['codcadastro']."'") or die(mysqli_error($con));
    
        echo "<script>window.location.href='meus-orcamentos-cli.php?codpedido=".$_GET['codpedido']."';;</script>";
    } else {

}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mão Amiga APP - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#00b4d8',
                        secondary: '#0077b6',
                        accent: '#48cae4',
                        background: '#f8f9fa',
                        'muted-foreground': '#6c757d',
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #00b4d8 0%, #0077b6 100%);
        }
        .card-shadow {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>
<script>
jQuery(document).ready(function($){
 // == CPF AND CNPJ MASK ==
 var CpfCnpjMaskBehavior = function (val) {
 return val.replace(/\D/g, '').length <= 11 ? '(00) 00000-0000' : '(00) 00000-0000';
 },
 cpfCnpjpOptions = {
 onKeyPress: function(val, e, field, options) {
 field.mask(CpfCnpjMaskBehavior.apply({}, arguments), options);
 }
 };
 $('.celular').mask(CpfCnpjMaskBehavior, cpfCnpjpOptions);
 /* === END MASK FIELDS === */
 });
 
 
     </script>
</head>
<body class="min-h-screen bg-background">
    <!-- Header -->
    <header class="gradient-bg text-white">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center">
                <div class="bg-white p-2 rounded-full">
                    <img src="logo1.png" alt="Mão Amiga APP" class="h-10 w-auto">
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="#" class="flex items-center text-white hover:text-accent transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Início
                </a>
                <button class="block lg:hidden text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
        <div class="wave-shape">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 120" class="w-full h-auto">
                <path fill="#f8f9fa" fill-opacity="1" d="M0,64L80,69.3C160,75,320,85,480,80C640,75,800,53,960,48C1120,43,1280,53,1360,58.7L1440,64L1440,120L1360,120C1280,120,1120,120,960,120C800,120,640,120,480,120C320,120,160,120,80,120L0,120Z"></path>
            </svg>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-md mx-auto -mt-10 px-6 pb-12 relative z-10">
        <div class="bg-white rounded-2xl card-shadow overflow-hidden">
            <!-- Form Selector -->
            <div class="flex">
                <button id="login-tab" class="flex-1 py-5 font-medium text-center text-primary border-b-2 border-primary">
                    Entrar
                </button>
                <button id="register-tab" class="flex-1 py-5 font-medium text-center text-gray-400 hover:text-gray-600 transition-colors">
                    Cadastrar
                </button>
            </div>

            <!-- Login Form -->
            <div id="login-form" class="p-8">
                <div class="flex items-center justify-center mb-8">
                    <div class="w-16 h-16 rounded-full gradient-bg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                </div>
                
                <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Bem-vindo!</h2>
                
                <div class="space-y-5">
                    <div>
                    <form action="confirmanumero_cliente_pedido.php" method="get">
                        <input type="hidden" class="form-control" name="nome" value="<?php echo $_GET['nome']; ?>" />
                        <input type="hidden" class="form-control" name="codpedido" value="<?php echo $_GET['codpedido']; ?>" />
                        <input type="hidden" class="form-control" name="codcadastro" value="<?php echo $_GET['codcadastro']; ?>" />

                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Seu número de celular</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </div>
                                <input type="tel" id="phone" name="celularCli" class="celular w-full pl-10 pr-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="(00) 00000-0000">
                            </div>
                        </div>
                        
                        <button type="submit" class="w-full gradient-bg hover:opacity-90 text-white font-medium py-3 px-4 rounded-lg transition-opacity">
                            Entrar
                        </button>

                    </form>
                    
                    <div class="text-center">
                        <p class="text-sm text-gray-500">
                            Não tem uma conta? <button id="show-register" class="text-primary font-medium hover:underline">Cadastre-se</button>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Register Form -->
            <div id="register-form" class="p-8 hidden">
                <div class="flex items-center justify-center mb-8">
                    <div class="w-16 h-16 rounded-full gradient-bg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                </div>
                
                <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Crie sua conta</h2>
                
                <div class="space-y-5">
                <form action="confirmanumero_cliente_pedido.php" method="get">
    <input type="hidden" name="cadastro" value="true">
    <input type="hidden" class="form-control" name="nome" value="<?php echo $_GET['nome']; ?>" />
    <input type="hidden" class="form-control" name="codpedido" value="<?php echo $_GET['codpedido']; ?>" />
    <input type="hidden" class="form-control" name="codcadastro" value="<?php echo $_GET['codcadastro']; ?>" />
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nome completo</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <input type="text" id="name" name="nomeCli" required class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="Seu nome completo">
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <label for="register-phone" class="block text-sm font-medium text-gray-700 mb-1">Número de celular</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </div>
                                <input type="tel" name="celularCli2" required id="register-phone" class="celular w-full pl-10 pr-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="(00) 00000-0000">
                            </div>
                        </div>
                        
                        <button type="submit" class="mt-3 w-full gradient-bg hover:opacity-90 text-white font-medium py-3 px-4 rounded-lg transition-opacity">
                            Criar conta
                        </button>
                    </form>
                    <div class="text-center">
                        <p class="text-sm text-gray-500">
                            Já tem uma conta? <button id="show-login" class="text-primary font-medium hover:underline">Faça login</button>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Additional Info -->
        <div class="mt-8 text-center">
            <p class="text-sm text-gray-500">
                Ao continuar, você concorda com nossos <a href="#" class="text-primary hover:underline">Termos de Serviço</a> e <a href="#" class="text-primary hover:underline">Política de Privacidade</a>.
            </p>
        </div>
    </main>

    <script>
        // Tab switching functionality
        document.getElementById('login-tab').addEventListener('click', function() {
            document.getElementById('login-tab').classList.add('text-primary', 'border-b-2', 'border-primary');
            document.getElementById('login-tab').classList.remove('text-gray-400');
            document.getElementById('register-tab').classList.add('text-gray-400');
            document.getElementById('register-tab').classList.remove('text-primary', 'border-b-2', 'border-primary');
            document.getElementById('login-form').classList.remove('hidden');
            document.getElementById('register-form').classList.add('hidden');
        });
        
        document.getElementById('register-tab').addEventListener('click', function() {
            document.getElementById('register-tab').classList.add('text-primary', 'border-b-2', 'border-primary');
            document.getElementById('register-tab').classList.remove('text-gray-400');
            document.getElementById('login-tab').classList.add('text-gray-400');
            document.getElementById('login-tab').classList.remove('text-primary', 'border-b-2', 'border-primary');
            document.getElementById('register-form').classList.remove('hidden');
            document.getElementById('login-form').classList.add('hidden');
        });
        
        // Link switching
        document.getElementById('show-register').addEventListener('click', function() {
            document.getElementById('register-tab').click();
        });
        
        document.getElementById('show-login').addEventListener('click', function() {
            document.getElementById('login-tab').click();
        });
    </script>
</body>
</html>