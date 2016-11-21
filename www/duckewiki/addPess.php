<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
require_once './model/pess.php';
sec_session_start();
/*
 * To-do list:
 * - padronizar toda a nomenclatura de controles (id/name):
 *  - código minúsculo de 3 letras (btn=button, sel=select...)
 *  - colocar o id[nome] dos controles no alto da página
 * - não consigo editar a URL na janela pop-up!
 */
$edit = getGet('edit');
if ($edit == '') {
	$title = txt('nova').' '.txt('pess');
} else {
	$title = txt('edit').' '.txt('pess');
}
?>

<!DOCTYPE html>
<html lang='BR'>
<head>
<meta charset='UTF-8'>
<title> <?php echo $title ?></title>		
<link rel="stylesheet" type="text/css" href="cssDuckeWiki.css">
<script src='funcoes.js'></script>
<script>
function requiredKeyUp() {
	//btnSave.disabled = (txtNome.value == '') || (txtSobrenome.value == '');
}
function fechaLogo(id,who,texto) {
	window.opener.handlePopupResult(id,who,texto);
	window.close();
}
function aoCarregar(edit) {
	if (edit == '') {
		refill('frmPess');
	}
	document.getElementsByName("txtprenome")[0].focus();
}
function corrige(who) {
	if (who.value != '') {
		var partes = who.value.split(' ');
		var i;
		for (i=0; i<partes.length; i++) { // faz para cada nome separado: 'de Loyola' vira 'de' + 'Loyola'
			if (partes[i].length >= 2) {
				var ch1 = partes[i].charAt(0);
				var ch2 = partes[i].charAt(1);
				var resto = partes[i].substr(1);
				if (ch1 === ch1.toUpperCase() && ch2 === ch2.toUpperCase()) { // ROdrigo ou RODRigo -> Rodrigo
					partes[i] = ch1+resto.toLowerCase();
				} else
				if (partes[i].length >= 4 && partes[i] == partes[i].toLowerCase()) { // rodrigo -> Rodrigo (mas não 'de' ou 'van')
					partes[i] = ch1.toUpperCase()+resto.toLowerCase();
				}
			}
		}
		who.value = partes.join(' ');
		store(who);
	}
}
</script>
</head>

<?php
$tabela = 'pess';
$update = getGet('update');
$close = getGet('close');


if ($edit == '') {
	emptyRow($tabela);
} else {
	updateRow($tabela,$edit);
}

$body = "<body onload='aoCarregar(\"$edit\")'>";
$divRes = '';

if (!empty($post)) {
	print_r($post);
	echo "<BR><BR>";		

	$pessoa = new Pessoa(
	$_SESSION['user_id'],
	date('d/m/Y H:i:s'),
	
	get('txtprenome'),
	get('txtsobrenome'),
	get('txtsegundonome'),
	
	get('txtemail'),
	get('txanotas'),
	get('txtabrev')
	);
	
	$arrPar = $pessoa->getArray();
	
	//$arrPar = array($v1,$v2,$v3,$v4,$v5,$v6,$v7,$v8);
	$cols = 'addby,adddate,prenome,sobrenome,segundonome,email,notas,abrev'; // deve ir numa linha só, sem espaços
	$arrCol = explode(',',$cols);
	if ($edit) {
		$q = "update $tabela set (";
		if (algumaMudou($q)) { // pelo menos uma coluna mudou -> edita
			$arr2 = montaQuery($q);				

			$res = pg_query_params($conn,$q,[$edit]);
			if ($res) {
				add2dwh($arr2,$divRes); // add to data warehouse
				updateRow($tabela,$edit);
				if ($update) {
					
				}
				if ($close) {
					$body = "<body onload='fechaLogo($edit)'>";
				}
			} else {
				pg_send_query_params($conn,$q,[$edit]);
				$res = pg_get_result($conn);
				$resErr = pg_result_error($res);
				$divRes = "<div id='divSucesso' style='background-color:#FF0000'>Erro ao atualizar registro ($q): $resErr</div>";
			}
		} else { // nada mudou na tabela principal
			$divRes = "<div id='divSucesso' style='background-color:#FF0000'>Não há mudanças a atualizar!</div>";
		}
		$MMs = explode('|',get('hidmmData'));
		echo "MMs = ".get('hidmmData')."<BR><BR>";
		foreach ($MMs as $MM) {
			$M = explode(';',$MM);
			if (atualizaSubTabela($M,$divRes1)) {
				$divRes.=$divRes1;
			}
		}
	} else { // não está editando -> insere
		switch (registroExiste($tabela)) {
			case 'f' :
				echo "Não existe<BR><BR>";
				insereUm($tabela,$close,$divRes,$body);
				break;
			case 't' :
				$divRes = "<div id='divSucesso' style='background-color:#FF0000'>Registro já existe.</div>";
				break;
			default :
				pg_send_query_params($conn,$q,$arrPar);
				$res = pg_get_result($conn);
				$resErr = pg_result_error($res);
				$divRes = "<div id='divSucesso' style='background-color:#FF0000'>Erro na query: $resErr</div>";
		}
	} // fim do insert
}
pullCfg();
echo "</head>
$body";
$loginErro = login_error($conn);

if ($loginErro) {
	exit("<p>Você não tem autorização de acesso a esta página (erro: $loginErro). Por favor, <a href='index.php' target='main'>volte para a tela inicial e faça o login</a>.</p>");
}
echoHeader();
/*	<h1 style='text-align:center'> <?= $title ?> </h1>
	<?= "$divRes" ?>
	<form id='frmPess' autocomplete='off' method='post' action=''>*/
?>
<dl>
	<?= /*dtlab('pes.nome1',true,'txtprenome')*/
	dtlab('pes.nome1') ?>
	<dd>
		<input required pattern='\D+' name='txtprenome' type='text' value=<?="'$prenome'"?>  onkeyup='requiredKeyUp()' oninput='store(this)' onblur='corrige(this)' />
	</dd>
</dl>

<dl>
	<?= dtlab('pes.nome2') ?>
	<dd>
		<input name='txtsegundonome' type='text' value=<?="'$segundonome'"?> oninput='store(this)' onblur='corrige(this)' />
	</dd>
</dl>

<dl>
	<?= dtlab('pes.nome3') ?>
	<dd>
		<input required pattern='\D+' name='txtsobrenome' type='text' value=<?="'$sobrenome'"?> onkeyup='requiredKeyUp()' oninput='store(this)' onblur='corrige(this)' />
	</dd>
</dl>

<dl>
	<dt>
		<label>
			<?= txt('pes.abrev')?> 
		</label>
	</dt>
	<dd>
		<input name='txtabrev' type='text' value=<?="'$abrev'"?> oninput='store(this)' />
	</dd>
</dl>
<?php		
$mmLabelH = txt('taxespec');
$mmLabel1 = txt('taxdisp');
$mmLabel2 = txt('taxsel');
$who = 'especs';
$mmQuery = 'espectax';
//$mmTableName = 'pess'; // TIRAR $mmTableName DOS OUTROS build_mm !!
$mmFieldPai = 'espec';
$mmTableLink = 'taxespec.tax';
include('build_mm.php');
?>

<dl>
	<dt>
		<label> <?= txt('pes.mail'); ?> </label>
	</dt>
	<dd>
		<input name='txtemail' type='text' value=<?= "'$email'";?> oninput='store(this)' />
	</dd>
</dl>

<dl>
	<dt>
		<label> <?= txt('notas'); ?> </label>
	</dt>
	<dd>
		<textarea name='txanotas' oninput='store(this)'>
			<?=$notas; ?>
		</textarea>
	</dd>
</dl>

<input type='hidden' name='hidmmData' value=<?="'$hidmmData'"?> />
	
<?php
	echoButtons();
?>
</form>
</body>
</html>
