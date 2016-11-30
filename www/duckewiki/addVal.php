<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
require_once './model/val.php';
sec_session_start();

$edit = getGet('edit');
if ($edit == '') {
	$title = txt('nova').' '.txt('val');
} else {
	$title = txt('edit').' '.txt('val');
}

?>
<!DOCTYPE html>
<html lang='BR'>
	<head>
		<meta charset='UTF-8'>
		<title><?php $title ?> </title>
		<link rel="stylesheet" type="text/css" href="css/cssDuckeWiki.css">
		<script src='js/funcoes.js'></script>
<script>
function aoCarregar(edit) {
	if (edit == '') { // senão usa os valores do id a ser editado
		refill('frmKey');
		
		
		
	}
	document.getElementsByName("seltipo")[0].focus();
}
</script>
<?php
$tabela = 'val';
$update = getGet('update');
$close = getGet('close');

if ($edit == '') {
	emptyRow($tabela); // cria uma variável php para cada coluna na tabela $tabela, todas vazias
} else {
	updateRow($tabela,$edit); // cria uma variável php para cada coluna na tabela $tabela, com os valores 
}
$body = "<body onload='aoCarregar(\"$edit\")'>";
$divRes = '';
if (!empty($post)) {
	$v1 = $_SESSION['user_id'];
	$v2 = date('d/m/Y H:i:s');
	
	$variaveis = new Categoria(
		$v1,
		$v2,
 		getPost('txadef'),
		getPost('txtnome')
	);
	$arrPar = $variaveis->getArray();
	$cols = 'addby,adddate,valdef,valname'; // deve ir numa linha só, sem espaços
	$arrCol = explode(',',$cols);
	if ($edit) {
		$q = "update $tabela set (";
		if (algumaMudou($q)) { // pelo menos uma coluna mudou -> edita
			$arr2 = montaQuery($q);
			$res = pg_query_params($conn,$q,[$edit]);
			if ($res) {
				add2dwh($arr2,$divRes); // add to data warehouse
				updateRow($tabela,$edit);
				if ($close) {
					//$body = "<body onload='fechaLogo($edit)'>";
				}
			} else {
				pg_send_query_params($conn,$q,[$edit]);
				$res = pg_get_result($conn);
				$resErr = pg_result_error($res);
				$divRes = "<div id='divSucesso' style='background-color:#FF0000'>Erro ao atualizar registro ($q): $resErr</div>";
			}
		} else {
			$divRes = "<div id='divSucesso' style='background-color:#FF0000'>Não há mudanças a atualizar!</div>";
		}
	} 
	else { // não está editando -> insere
		echo "Existe?<BR>";
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
/*<h1 style='text-align:center'><?= $title?></h1>
<?= $divRes?>
<form id='frmKey' autocomplete='off' method='post' action=''>*/
?>
<dl id='dlNome' >
	<dt><label><?= txt('nome')?> </label></dt>
	<dd><input name='txtnome' type='text' value='<?= $valname?>' oninput='store(this)'  /></dd>
</dl>
<dl id='dlDef' >
	<dt><label><?= txt('def')?></label></dt>
	<dd><textarea name='txadef' oninput='store(this)'><?= $valdef?></textarea></dd>
</dl>

<!--- imgicon NAO ESTA IMPLEMENTADO NEM AQUI NEM EM addKey.php -->
<dl id='dlImg' >
	<?=dtlab('imgicon')?>
	<dd><input type='file' name='filIcone' accept='image/*' onchange='store(this)'></dd>
</dl>

<?= echoButtons(); ?>
</form>
</body>
</html>
