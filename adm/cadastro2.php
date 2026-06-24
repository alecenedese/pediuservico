<?php
	$queryc = "select * from parceiro where CNPJ_CPF='".$_SESSION['documento']."'";
	$queryc= mysqli_query($con, $queryc);
	$row_c = mysqli_fetch_assoc($queryc);

?>
<style>
	@media screen and (max-width:991px){
		.Text_Left {text-align: left !important; }
		.fieldset-content {height: 100% !important; }
	}
	@media screen and (min-width:768px){
	}
	.actions ul li a { display: none !important; }
</style>
<style>
	
		.number { display:none !important; }
		.steps { border-top:none !important;
		    border-bottom:none !important;
    padding:0 !important;
		
		}
		.enviar {
			width: 170px;
			float:right;
    height: 50px;
    color: #fff;
    background: #ffa000;
    align-items: center;
    -moz-align-items: center;
    -webkit-align-items: center;
    -o-align-items: center;
    -ms-align-items: center;
    justify-content: center;
    -moz-justify-content: center;
    -webkit-justify-content: center;
    -o-justify-content: center;
    -ms-justify-content: center;
    text-decoration: none;
    border-radius: 5px;
    -moz-border-radius: 5px;
    -webkit-border-radius: 5px;
    -o-border-radius: 5px;
    -ms-border-radius: 5px;
			cursor:pointer;
}
			
	
	</style>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<link rel="stylesheet" href="<?php echo $urlserver; ?>passo/css/style.css">
	</head>
	<script type="text/javascript">
		
	$( document ).ready(function() {
     $(".campoCNPJ").hide();
	$("input:radio[name=documento]").on("change", function () {   
		if($(this).val() == "CPF")
		{
			
			$(".campoCPF").show(); 
			$(".campoCNPJ").hide();
		}
		else if($(this).val() == "CNPJ")
		{
			$(".campoCPF").hide(); 
			$(".campoCNPJ").show();   
		}
	});
	});
		

	$(document).ready(function () {
   // Usando um pequeno delay de 100ms porque às vezes o navegador preenche o campo logo que o documento está pronto, e pode não funcionar como esperado
   setTimeout(function(){
     $('#email2').removeAttr('disabled');
   }, 100);
});
		$(document).ready(function () {
   // Usando um pequeno delay de 100ms porque às vezes o navegador preenche o campo logo que o documento está pronto, e pode não funcionar como esperado
   setTimeout(function(){
      $('.senha2').removeAttr('disabled');
   }, 100);
});

		$(document).ready(function () {
   // Usando um pequeno delay de 100ms porque às vezes o navegador preenche o campo logo que o documento está pronto, e pode não funcionar como esperado
   setTimeout(function(){
      $('.senha1').removeAttr('disabled');
   }, 100);
});

		$(document).ready(function () {
   // Usando um pequeno delay de 100ms porque às vezes o navegador preenche o campo logo que o documento está pronto, e pode não funcionar como esperado
   setTimeout(function(){
     $('.senha').removeAttr('disabled');
   }, 100);
});	

	
	</script>	
	
	<div class="col-12">
		<div class="w-100 float-left text-center my-5">
			<div class="card bg-white w-100 p-3" style="margin: 0 auto; max-width: 1400px; border-radius: 12px; box-shadow:9px 8px 32px -11px rgba(199,195,199,1); ">

				<form action="<?php echo $urlserver; ?>confirmarDados.php?tipo=2" method="POST"  >
					<input type="hidden" name="acao" value="finalizar">
					<input type="hidden" value="Cadastro2" name="cadastro">
					<input type="hidden" value="<?php echo $_SESSION['documento']; ?>" name="documento">
					<h3>
						
					</h3>
					
						<div class="fieldset-content">
							<div class="form-group campoCPF" style="margin-top:5%;">
								<div class="col-12">
									<div class="row">
										<div class="col-12 col-md-12">
											<div class="row">
												<div class="col-12">
													<div class="row">
														<div class="col-10 col-lg-10 espaco text-left pt-3 Text_Left" style="font-size:90%;">
															Olá, <span style="font-weight:bold;"><?php echo $row_c['nome_razao']; ?></span>, Identificamos que já existe um cadastro no banco de dados dos Supermercados Machado. Para facilitar sua experiência em nossa loja virtual, basta confirmar os seus dados e criar uma senha.
														</div>

													</div>
												</div>
											</div>
										</div>

									</div>
								</div>

							</div>
							
							
							<div class="form-group">
								
								<span class="espaco" style=" color:#ED1D21;">&nbsp;&nbsp;&nbsp;*Por favor confirme seus dados.</span>
								
							</div>


							
							<div class="form-group">
								<div class="col-12">
									<div class="row">
										<div class="col-12 col-md-6">
											<div class="row">
												<div class="col-12">
													<div class="row">
														<div class="col-12 col-lg-4 text-right pt-3 Text_Left">
															Email
														</div>
														<div class="col-12 col-lg-8">
															<?php
																$email = explode("@", $row_c['email']);
															?>
															<input type="email" name="email" disabled="disabled"  autocomplete="off"placeholder="<?php echo $email[0]."@..."; ?>" class="campoInput w-100 float-left" id="email2" required/>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-12 col-md-6">
											<div class="row">
												<div class="col-12">
													<div class="row">
														
														<?php if($row_c['celular'] <> '') { ?>
														
														<div class="col-12 col-lg-4 text-right pt-3 Text_Left">
															Celular
														</div>
														<div class="col-12 col-lg-8">
															<input type="tel" id="celular" name="celular" disabled="disabled"  autocomplete="off" placeholder="<?php echo substr($row_c['celular'], 0, -4)."****"; ?>"  class="campoInput w-100 float-left senha1"   required/>
														</div>
														
														<?php } else { ?>
														
														<div class="col-12 col-lg-4 text-right pt-3 Text_Left">
															Telefone
														</div>
														<div class="col-12 col-lg-8">
															<input type="tel" id="telefone" name="telefone" disabled="disabled" autocomplete="off" placeholder="<?php echo substr($row_c['telefone'], 0, -4)."****"; ?>"  class="campoInput w-100 float-left senha2"  required/>
														</div>														
														
														<?php } ?>
													
													</div>
												</div>
											</div>
										</div>
										
										
										
										


									</div>
								</div>
							</div>
							


							<div class="form-group">
								<div class="col-12">
									<div class="row">
										<div class="col-12 col-md-6">
											<div class="row">
												<div class="col-12">
													<div class="row">
														<div class="col-12 col-lg-4 text-right pt-3 Text_Left">
															Cadastrar Senha
														</div>
														<div class="col-12 col-lg-8">
															<input type="password" name="password" disabled="disabled"  minlength="6" autocomplete="off" class="campoInput w-100 float-left senha" required/>
														</div>
													</div>
												</div>
											</div>
										</div>

									</div>
								</div>
							</div>						

							<div class="form-group form-password"></div>
						</div>
						<div class="fieldset-footer mudar" style="color:#000 !important; height:80px; font-size:105%;">
							<input type="submit" id="cadastrar" name="cadastrar" value="Validar Cadastro" class="enviar" >
						</div>
			

					
				</form>
			</div>
		</div>
	</div>
  	
	
	    <link rel="stylesheet" href="<?php echo $urlserver; ?>passo/fonts/material-icon/css/material-design-iconic-font.min.css">
    <!-- JS -->

	
</body>

</html>
