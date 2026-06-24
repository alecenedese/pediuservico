<?php error_reporting(0);
header('Content-Type: text/html; charset=utf-8');
session_start();
$campo = $_GET['campo'];
$valor = $_GET['valor'];


function isCpfValid($cpf)
{
	//Etapa 1: Cria um array com apenas os digitos numricos, isso permite receber o cpf em diferentes formatos como "000.000.000-00", "00000000000", "000 000 000 00" etc...
	$j = 0;
	for ($i = 0; $i < (strlen($cpf)); $i++) {
		if (is_numeric($cpf[$i])) {
			$num[$j] = $cpf[$i];
			$j++;
		}
	}
	//Etapa 2: Conta os dgitos, um cpf vlido possui 11 dgitos numricos.
	if (count($num) != 11) {
		$isCpfValid = false;
	}
	//Etapa 3: Combinaes como 00000000000 e 22222222222 embora no sejam cpfs reais resultariam em cpfs vlidos aps o calculo dos dgitos verificares e por isso precisam ser filtradas nesta parte.
	else {
		for ($i = 0; $i < 10; $i++) {
			if ($num[0] == $i && $num[1] == $i && $num[2] == $i && $num[3] == $i && $num[4] == $i && $num[5] == $i && $num[6] == $i && $num[7] == $i && $num[8] == $i) {
				$isCpfValid = false;
				break;
			}
		}
	}
	//Etapa 4: Calcula e compara o primeiro dgito verificador.
	if (!isset($isCpfValid)) {
		$j = 10;
		for ($i = 0; $i < 9; $i++) {
			$multiplica[$i] = $num[$i] * $j;
			$j--;
		}
		$soma = array_sum($multiplica);
		$resto = $soma % 11;
		if ($resto < 2) {
			$dg = 0;
		} else {
			$dg = 11 - $resto;
		}
		if ($dg != $num[9]) {
			$isCpfValid = false;
		}
	}
	//Etapa 5: Calcula e compara o segundo dgito verificador.
	if (!isset($isCpfValid)) {
		$j = 11;
		for ($i = 0; $i < 10; $i++) {
			$multiplica[$i] = $num[$i] * $j;
			$j--;
		}
		$soma = array_sum($multiplica);
		$resto = $soma % 11;
		if ($resto < 2) {
			$dg = 0;
		} else {
			$dg = 11 - $resto;
		}
		if ($dg != $num[10]) {
			$isCpfValid = false;
		} else {
			$isCpfValid = true;
		}
	}
	//Trecho usado para depurar erros.
	/*
			 if($isCpfValid==true)
				 {
					 echo "<font color="GREEN">Cpf  Vlido</font>";
				 }
			 if($isCpfValid==false)
				 {
					 echo "<font color="RED">Cpf Invlido</font>";
				 }
			 */
	//Etapa 6: Retorna o Resultado em um valor booleano.
	return $isCpfValid;
}
function valida_cnpj2($cnpj)
{
	// Deixa o CNPJ com apenas nmeros
	$cnpj = preg_replace('/[^0-9]/', '', $cnpj);

	// Garante que o CNPJ  uma string
	$cnpj = (string) $cnpj;

	// O valor original
	$cnpj_original = $cnpj;

	// Captura os primeiros 12 nmeros do CNPJ
	$primeiros_numeros_cnpj = substr($cnpj, 0, 12);

	/**
	 * Multiplicao do CNPJ
	 *
	 * @param string $cnpj Os digitos do CNPJ
	 * @param int $posicoes A posio que vai iniciar a regresso
	 * @return int O
	 *
	 */
	if (!function_exists('multiplica_cnpj')) {
		function multiplica_cnpj($cnpj, $posicao = 5)
		{
			// Varivel para o clculo
			$calculo = 0;

			// Lao para percorrer os item do cnpj
			for ($i = 0; $i < strlen($cnpj); $i++) {
				// Clculo mais posio do CNPJ * a posio
				$calculo = $calculo + ($cnpj[$i] * $posicao);

				// Decrementa a posio a cada volta do lao
				$posicao--;

				// Se a posio for menor que 2, ela se torna 9
				if ($posicao < 2) {
					$posicao = 9;
				}
			}
			// Retorna o clculo
			return $calculo;
		}
	}

	// Faz o primeiro clculo
	$primeiro_calculo = multiplica_cnpj($primeiros_numeros_cnpj);

	// Se o resto da diviso entre o primeiro clculo e 11 for menor que 2, o primeiro
	// Dgito  zero (0), caso contrrio  11 - o resto da diviso entre o clculo e 11
	$primeiro_digito = ($primeiro_calculo % 11) < 2 ? 0 : 11 - ($primeiro_calculo % 11);

	// Concatena o primeiro dgito nos 12 primeiros nmeros do CNPJ
	// Agora temos 13 nmeros aqui
	$primeiros_numeros_cnpj .= $primeiro_digito;

	// O segundo clculo  a mesma coisa do primeiro, porm, comea na posio 6
	$segundo_calculo = multiplica_cnpj($primeiros_numeros_cnpj, 6);
	$segundo_digito = ($segundo_calculo % 11) < 2 ? 0 : 11 - ($segundo_calculo % 11);

	// Concatena o segundo dgito ao CNPJ
	$cnpj = $primeiros_numeros_cnpj . $segundo_digito;

	// Verifica se o CNPJ gerado  idntico ao enviado
	if ($cnpj === $cnpj_original) {
		return true;
	}
}


// Verificando o campo CPF
if ($campo == "cpf") {
	require_once("send.php");


	// Verifica o CPF
	if (isCpfValid($valor)) {


		$queryc2 = "select * from parceiro where CNPJ_CPF='" . $valor . "'";
		$queryc2 = mysqli_query($con, $queryc2);
		$rowConta2 = mysqli_num_rows($queryc2);


		if ($rowConta2 == 0) {



		} else { ?>

			<?php
			echo "CPF  Já Cadastrado <br>";
		}


	} else { ?>
		<style>
			.enviar {
				display: none !important;
			}

			.enviar2 {
				display: block !important;
			}

			.btn-primary {
				display: none !important;
			}
		</style>
		<?php
		echo "CPF Inválido  <br>";
	}



}
if ($campo == "cnpj") {
	require_once("send.php");


	// Verifica o CPF
	if (valida_cnpj2($valor)) {

		$queryc2 = "select * from parceiro where CNPJ_CPF='" . $valor . "'";
		$queryc2 = mysqli_query($con, $queryc2);
		$rowConta2 = mysqli_num_rows($queryc2);


		if ($rowConta2 == 0) { ?>

<style>
				.enviar {
					display: block !important;
				}

				.enviar2 {
					display: none !important;
				}

				.btn-primary {
					display: block !important;
				}
			</style>
		<?php	} else {  ?>
			<style>
				.enviar {
					display: none !important;
				}

				.enviar2 {
					display: block !important;
				}

				.btn-primary {
					display: none !important;
				}
			</style>
			<?php
			echo "CNPJ já cadastrado <br>";
		}


	} else {  ?>
		<style>
			.enviar {
				display: none !important;
			}

			.enviar2 {
				display: block !important;
			}

			.btn-primary {
				display: none !important;
			}
		</style>
		<?php
		echo "CNPJ Inválido  <br>";
	}


}




?>