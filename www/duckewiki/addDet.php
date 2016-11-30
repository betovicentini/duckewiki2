<?php
	include_once '../../includes_pl/db_connect.php';
	include_once '../../includes_pl/functions.php';
	require_once './model/det.php';
	sec_session_start();
	$edit = getGet('edit');
	$add = getGet('add');
	if ($edit == '') {
		$title = 'Nova Identificação';
	} else {
		$title = 'Editar Identificação';
	}
?>

<!DOCTYPE html>
<html lang='BR'>
<head>
	<meta charset='UTF-8'>
	<title><?= $title ?> </title>
	<link rel="stylesheet" type="text/css" href="css/cssDuckeWiki.css">
	<script src='js/funcoes.js'></script>
<script>
function aoCarregar(edit) {
	if (edit == '') {
		refill('frmDet');
	}
	document.getElementById("txtTax").focus();
}
function fechaLogo(id,who,texto) {
	window.opener.handlePopupResult(id,who,texto);
    window.close();
}
</script>
<?php
$tabela = 'det';
$update = getGet('update');
$close = getGet('close');
if ($add != '') {
	updateRow($tabela,$add);
} else
if ($edit == '') {
	emptyRow($tabela);
} else {
	updateRow($tabela,$edit);
}
$body = "<body onload='aoCarregar(\"$edit\")'>";
$divRes = '';
if (!empty($post)) {
	$v1 = $_SESSION['user_id'];
	$v2 = date('d/m/Y H:i:s');

	$determinacao = new Determinacao(
		$_SESSION['user_id'],
		date('d/m/Y H:i:s'),
		get('txanotes'), 
		get('valdetby'), 
		get('txtano'), 
		get('selmes'), 
		get('txtdia'), 
		get('radconf'), 
		get('radmodif'), 
		get('valrefcol'), 
		get('txtrefcolnum'), 
		get('txtrefherb'), 
		get('txtrefherbnum'), 
		get('txtrefdetby'), 
		get('txtrefano'), 
		get('selrefmes'), 
		get('txtrefdia'), 
		get('valdettax')
	);

	$arrPar = $determinacao->getArray();
	//$arrPar = array($v1,$v2,$v3,$v4,$v5,$v6,$v7,$v8,$v9,$v10,$v11,$v12,$v13,$v14,$v15,$v16,$v17,$v18);
	$cols = "addby,adddate,notes,detby,ano,mes,dia,conf,modif,refcol,refcolnum,refherb,refherbnum,refdetby,refano,refmes,refdia,tax"; // deve ir numa linha só, sem espaços
	$arrCol = explode(',',$cols);
	if ($edit) {
		$q = "update $tabela set (";
		if (algumaMudou($q)) { // pelo menos uma coluna mudou -> edita
			montaQuery($q);
			$res = pg_query_params($conn,$q,[$edit]);
			if ($res) {
				$divRes = "<div id='divSucesso' style='background-color:#00FF00'>Registro atualizado com sucesso! ($q)</div>";
			} else {
				pg_send_query_params($conn,$q,[$edit]);
				$res = pg_get_result($conn);
				$resErr = pg_result_error($res);
				$divRes = "<div id='divSucesso' style='background-color:#FF0000'>Erro ao atualizar registro ($q): $resErr</div>";
			}
		} else {
			$divRes = "<div id='divSucesso' style='background-color:#FF0000'>Não há mudanças a atualizar!</div>";
		}
	} else { // não está editando -> insere
		switch (registroExiste($tabela)) {
			case 'f' :
				insereUm($tabela,$close,$divRes,$body,get('txtdettax'));
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
/*<h1 style='text-align:center'> <?= $title ?> </h1>
<?= $divRes ?>
<form id='frmDet' autocomplete='off' method='post' action=''>*/
$cmbLabel = 'Nome científico';
$cmbNeed = true;
$cmbTableName = 'tax';
$cmbFieldNames = 'nome,rank';
$cmbCaseSensitive = 1;
$who = 'dettax';
$cmbQuery = 'tax';
$cmbPHP = '';
include('build_cmb.php');
?>

<dl>
	<dt>
		<label>Índice de confiança</label>
	</dt>
	<dd>Pouca confiança
		<?php
		$nEstados = 5;
		for ($i=1; $i<=$nEstados; $i++) {
			if ($i == $conf) {
				echo "<input type='radio' id='radconf$i' name='radconf' value='$i' onclick='store(this)' checked>";
			} else {
				echo "<input type='radio' id='radconf$i' name='radconf' value='$i' onclick='store(this)'>";
			}
		}
		?>
		Muita confiança
	</dd>
</dl>

<dl>
	<dt><label>Modificador do nome</label></dt>
	<dd>
		<input type='radio' id='radmodcf' name='radmodif' value='cf' onclick='store(this)'>cf.
		<input type='radio' id='radmodaff' name='radmodif' value='aff' onclick='store(this)'>aff.
		<input type='radio' id='radmodss' name='radmodif' value='ss' onclick='store(this)'>s.s.
		<input type='radio' id='radmodsl' name='radmodif' value='sl' onclick='store(this)'>s.l.
		<input type='radio' id='radmodvaff' name='radmodif' value='vaff' onclick='store(this)'>vel aff.
		<input type='radio' id='radmodno' name='radmodif' value='' onclick='store(this)' checked>nenhum
	</dd>
</dl>

<div id='divRef'>
	<fieldset>
		<legend>Referência do nome</legend>
		<?php
		$cmbLabel = 'Coletor';
		$cmbTableName = 'pess';
		$cmbFieldNames = 'abrev,prenome';
		$cmbCaseSensitive = 0;
		$who = 'refcol';
		$cmbQuery = 'pess';
		$cmbPHP = 'addPess.php';
		include('build_cmb.php');
		?>

		<dl>
			<dt><label>Número</label></dt>
			<dd>				
				<input type='text' name='txtrefcolnum' size=5 value='<?= $refcolnum?>' oninput='store(this)' />
			</dd>
		</dl>
		<dl>
			<dt><label>Herbário</label></dt>
			<dd><input type='text' name='txtrefherb' oninput='store(this)' /></dd>
			<dt><label>Número</label></dt>
			<dd><input type='text' size=5 name='txtrefherbnum' oninput='store(this)' /></dd>
		</dl>

		<dl>
			<dt><label>Determinado por</label></dt>
			<dd><input type='text' name='txtDeterm' value='' oninput='store(this)' /></dd>
			<dt><label>Data</label></dt>
			<dd><input type='text' name='txtDetData' size=5 value='' oninput='store(this)' /></dd>
		</dl>
	</fieldset>
</div>

<dl>
	<dt><label>Notas</label></dt>
	<dd><textarea name='txanotes' cols=30 rows=3 oninput='store(this)'><?= $notes?></textarea></dd>
</dl>
<?php
$cmbLabel = 'Quem identificou?';
$cmbField = 'detby';
$cmbTableName = 'pess';
$cmbFieldNames = 'abrev,prenome';
$who = 'detby';
$cmbQuery = 'pess';
$cmbPHP = 'addPess.php';
include('build_cmb.php');

if ($ano) {
	$d = $dia;
	$m = $mes;
	$a = $ano;
} else {
	$d = date('d');
	$m = date('m');
	$a = date('Y');
}
$meses = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho',
	'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
echo "<dl><dt><label>Data</label></dt>
<dd><input type='text' name='txtdia' size=5 placeholder='Dia' value='$d' oninput='store(this)' />
<select name='selmes' style='width: 150px' onchange='store(this)' />";
for ($i=0; $i<13; $i++) {
	if ($m == $i) {
		echo "<option value=$i selected>$meses[$i]</option>";
	} else {
		echo "<option value=$i>$meses[$i]</option>";
	}
}
?>
</select>
<input type='text' name='txtano' size=5 placeholder='Ano' value='<?= $a ?>' oninput='store(this)' />
</dd></dl>

<input type='hidden' name='hidmmData' value='<?= $hidmmData?>' />

<?= echoButtons();?>
</form>
</body>
</html>
