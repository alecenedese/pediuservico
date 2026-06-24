<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
						<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>
						<script type="text/javascript">
						$(".telefone").mask("(00) 00000-0000");
            $("#cpf").mask("000.000.000-00");
            $("#cnpj").mask("00.000.000/0000-00");
						</script>

<script type="text/javascript">

(function ($) {
  
  $('#tipo').select();
  function getKindSupplier(type) {
    
    if(type == 'F') {
     
      $('.cpff').show();
      $('.cnpjj').hide();
    }
    
    if(type == 'J') {
      $('.cnpjj').show();
      $('.cpff').hide();
    }
    
    
  }
  
  $(document).on('change', '#tipo', function () {
    getKindSupplier( $(this).val() );
  });
  
}(jQuery));

	//	Variável que receberá o objeto XMLHttpRequest
	var req;
	function validarDados(campo, valor) {
		// Verificar o Browser
		// Firefox, Google Chrorme, Safari e outros
		if(window.XMLHttpRequest) {
			req	= new XMLHttpRequest();
		}
		// Internet Explorer
		else if(window.ActiveXObject) {
			req = new ActiveXObject("Microsoft.XMLHTTP");
		}
		// Aqui vão os valores, caso haja mais de um, e o nome do campo que pediu a requisição.
		var url = "https://gessomt.app.br/pediuservico/adm2/validacao.php?campo="+campo+"&valor="+valor;
		// Chamada do método open para processar a requisição
		req.open("Get", url, true); 
		// Quando o objeto recebe o retorno, chamamos a função callback();
		req.onreadystatechange = function() {
			// Exibindo mensagem de carregar
			if(req.readyState == 1) {
				document.getElementById('campo_' + campo + '').innerHTML = '<font color="gray">Verificando...</font>';
			}
			// Verifica se o Ajax realizou todas as operações corretamente (essencial)
			if(req.readyState == 4 && req.status == 200) {
				// Resposta retornada pelo executor.php
				var resposta = req.responseText;
				if(req.responseText==01) {
					var resposta = "";
					
				} else {
          
				}
				// Abaixo colocamos a resposta na div do campo que fez a requisição
				document.getElementById('campo_'+ campo +'').innerHTML = resposta;
			}

		}
		req.send(null);
	}	
	</script>	
  
  <!-- Layout container -->
  <div class="layout-page">
          <!-- Navbar -->

          <?php require_once("nav-topo.php"); ?>

          <!-- / Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->

            <div class="container-xxl flex-grow-1 container-p-y">
            <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Parceiro /</span> Novo Registro</h4>

              <!-- Basic Layout -->
              <div class="row">
                <div class="col-xs">
                  <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                      <h5 class="mb-0"></h5>
                 
                    </div>
                    <div class="card-body">
                      <form action="https://gessomt.app.br/pediuservico/adm2/salvar.php" method="post" class="row">

                      <input type="hidden" name="acao"value="editar">

                      <div class="mb-3 col-md-3 col-sm-3">
                        <label for="exampleFormControlSelect1" class="form-label">Status</label>
                        <select class="form-select" id="exampleFormControlSelect1" name="status" aria-label="Default select example" required>
                          <option value="1">Ativo</option>
                          <option value="0" idSelectCidade>Inativo</option>
                         
                        </select>
                      </div>  
                      <div class="mb-3 col-md-12 col-sm-12">
                      </div>  
                      
                      <div class="mb-3 col-md-2 col-sm-2">
                        <label for="exampleFormControlSelect1" class="form-label">Perfil do Cliente</label>
                        <select class="form-select" id="tipo" name="perfil" aria-label="Default select example" required>
                          <option >Selecione..</option>
                          <option value="F">Física</option>
                          <option value="J">Jurídica</option>
                         
                        </select>
                      </div>   
                      
                    

                        <div class="mb-3 col-md-3 col-sm-3 cpff" style="display:none">
                          <label class="form-label" for="basic-default-fullname">Nome Completo</label>
                          <input type="text" class="form-control" id="basic-default-fullname" name="nome" placeholder="John Doe" />
                        </div>

                        <div class="mb-3 col-md-2 col-sm-2 cpff" style="display:none; position:relative">
                          <label class="form-label" for="basic-default-company">CPF</label>
                          <input style="position:relative" type="text" class="form-control" id="cpf" name="cpf" onblur="validarDados('cpf', document.getElementById('cpf').value);"  />
                          <div id="campo_cpf" style="right: 12px; font-size: 12px; color: red; position: absolute; top:-1px; z-index:9999; ">
															
															</div>
                        
                        </div>

                        <div class="mb-3 col-md-2 col-sm-2 cpff" style="display:none">
                          <label class="form-label" for="basic-default-company">RG</label>
                          <input type="text" class="form-control" id="basic-default-company" name="rg" />
                        </div>

                      


                      
                        <div class="mb-3 col-md-3 col-sm-3 cnpjj" style="display:none">
                            <label class="form-label" for="basic-default-fullname">Razao ou Fatansia</label>
                            <input type="text" class="form-control" id="basic-default-fullname" name="nome" placeholder="John Doe" />
                          </div>

                          <div class="mb-3 col-md-2 col-sm-2 cnpjj" style="display:none; position:relative">
                            <label class="form-label" for="basic-default-company">Cnpj</label>
                            <input style="position:relative" type="text" class="form-control" id="cnpj" name="cnpj" onblur="validarDados('cnpj', document.getElementById('cnpj').value);" />
                            <div id="campo_cnpj" style="right: 12px; font-size: 12px; color: red; position: absolute; top:-1px; z-index:9999; ">
														
															</div>
                          </div>

                          <div class="mb-3 col-md-2 col-sm-2 cnpjj" style="display:none">
                            <label class="form-label" for="basic-default-company">IE</label>
                            <input type="text" class="form-control" id="basic-default-company" name="rg" />
                          </div>
                    
                        <div class="mb-3 col-md-3 col-sm-3">
                          <label class="form-label" for="basic-default-email">Email Pessoal</label>
                          <div class="input-group input-group-merge">
                          <div id="campo_email" style="right: 12px; font-size: 12px; color: red; position: absolute; top:-1px; z-index:9999; ">
															
															</div>
                            <input
                              type="text"
                              name="email"
                              id="email"
                              onblur="validarDados('email', document.getElementById('email').value);" 
                              class="form-control"
                              aria-label="john.doe"
                              style="position:relative"
                              aria-describedby="basic-default-email2"
                              />
                           
                          </div>
                        </div>
                        <div class="mb-3 col-md-3 col-sm-3">
                          <label class="form-label" for="basic-default-email">Email Comercial</label>
                          <div class="input-group input-group-merge">
                            <input
                              type="text"
                              name="email_comercial"
                              id="email_comercial"
                              class="form-control"
                            
                              aria-label="john.doe"
                              aria-describedby="basic-default-email2"
                              />
                            
                          </div>
                        </div>

                        <div class="mb-3 col-md-2 col-sm-2">
                          <label class="form-label" for="basic-default-phone">Celular</label>
                          <input
                            type="text"
                            name="celular"
                            id="basic-default-phone"
                            class="form-control phone-mask telefone"
                           
                            required/>
                        </div>
   
                        <div class="mb-3 col-md-3 col-sm-3">   
                          </div>                 

                        <div class="mb-3 col-md-3 col-sm-3">
                          <label class="form-label" for="basic-default-fullname">Endereço</label>
                          <input type="text" class="form-control" id="basic-default-fullname" name="endereco"  />
                        </div>

                         <div class="mb-3 col-md-1 col-sm-1">
                          <label class="form-label" for="basic-default-fullname">Número</label>
                          <input type="text" class="form-control" id="basic-default-fullname" name="numero" />
                        </div>

                        <div class="mb-3 col-md-2 col-sm-2">
                          <label for="exampleFormControlSelect1" class="form-label">Tipo de Imóvel</label>
                          <select class="form-select" id="exampleFormControlSelect1" name="tipo_imovel" aria-label="Default select example" >
                            <option >Selecione..</option>
                            <option value="Casa">Casa</option>
                            <option value="Predio">Prédio</option>
                            <option value="Sobrado">Sobrado</option>
                            <option value="KITNET">KITNET</option>
  
                          </select>
                        </div>

                        <div class="mb-3 col-md-3 col-sm-3">
                          <label class="form-label" for="basic-default-fullname">Referência</label>
                          <input type="text" class="form-control" id="basic-default-fullname" name="referencia" />
                        </div>

                        <div class="mb-3 col-md-3 col-sm-3">
                          <label class="form-label" for="basic-default-fullname">Bairro</label>
                          <input type="text" class="form-control" id="basic-default-fullname" name="bairro" />
                        </div>  
                        
                        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
 $(function(){
  $("#defaultSelect").select2();
 }); 

 $(document).ready(function(){
 
 jQuery.fn.carregaCidades = function() {
      
     var args                    = arguments[0] || {};
     var idSelectCidade          = args.idSelectCidade;
      
     var paginaPhpCidades        = 'https://gessomt.app.br/pediuservico/adm2/cidades.php';
    
     var carregandoMsg           = '' 
  
     var carregandoClass         = 'class';
     var jsonPrimeiroElemento    = '(selecione a cidade)';
     var primeiroElemento        = $(idSelectCidade).find('option:first').html();

     if( $(this).val() ) {
         $(idSelectCidade).hide();
         $(idSelectCidade).after('<span class='+ carregandoClass +'>'+carregandoMsg+'</span>');      
         $.getJSON(paginaPhpCidades+'?search=',{cod_estados: $(this).val(), ajax: 'true'}, function(j){
             var options = '<option value="">'+jsonPrimeiroElemento+'</option>';    
             for (var i = 0; i < j.length; i++) {
                 options += '<option value="' + j[i].cod_cidades + '">' + j[i].nome + '</option>';
             } 
             $(idSelectCidade).html(options).show();
             $(idSelectCidade).next().remove();
         });
     } else {
         $(idSelectCidade).html('<option value="">'+primeiroElemento+'</option>');
     }
      
 };
 $("#cod_estados option:first").attr('selected','selected');
 $('#cod_estados').change(function(){ $(this).carregaCidades({idSelectCidade: '#defaultSelect'}); })
}); 
</script>
<div class="mb-3 col-md-3 col-sm-3">
                          <label class="form-label" for="basic-default-fullname">Estado</label>
                         
                          <select name="estado" id="cod_estados" style="heigth:40px !important;"  class="form-select"  required>
                              <option value="MT">Mato Grosso</option>
                              <?php
                          $sqlUf = "SELECT * from estados where Uf <> 'MT' ORDER BY Nome asc";
                          $resUf = mysqli_query( $con, $sqlUf ); 
                          while ( $row = mysqli_fetch_assoc( $resUf ) ) { ?>
                          <option value="<?php echo $row['Uf']; ?>"><?php echo $row['Nome']; ?></option>
                          <?php } ?>
                            </select>


                        </div>  


                        <div class="mb-3 col-md-3 col-sm-3">
                          <label class="form-label" for="basic-default-fullname">Cidade</label>
                          <select name="cidade"  id="defaultSelect" style="heigth:40px !important;" class="form-select" required>
                  <option value="Sinop">Sinop</option>
                      <?php
                  $sqlCity = "SELECT * from cidades where Uf = 'MT' ORDER BY Nome asc";
                  $sqlCity = mysqli_query( $con, $sqlCity ); 
                  while ( $row = mysqli_fetch_assoc( $sqlCity ) ) { ?>
                  <option value="<?php echo $row['Nome']; ?>"><?php echo $row['Nome']; ?></option>
                  <?php } ?>
                    </select>
                   
                        </div>   

                        <div class="mb-3 col-md-3 col-sm-3">
                          <label class="form-label" for="basic-default-fullname">Senha</label>
                          <input type="password" class="form-control" id="basic-default-fullname" name="senha" />
                        </div>  
                        

                        <div class="mb-3 col-md-12 col-sm-12"></div>                       

                        <button type="submit" class="btn btn-primary mb-3 col-md-3 col-sm-3">Salvar</button>
                      </form>
                    </div>
                  </div>
                </div>
                
              </div>
            

              </div>
              </div>
              </div>
              </div>    