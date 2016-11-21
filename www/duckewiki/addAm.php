<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
require_once './model/am.php';
sec_session_start();
$edit = getGet('edit');
if ($edit == '') {
	$title = txt('nova').' '.txt('am');
} else {
	$title = txt('edit').' '.txt('am');
}
?>
<!DOCTYPE html>
<html lang='BR'>
	<head>
		<meta charset='UTF-8'>
		<title><?= $title ?> </title>
		<link rel="stylesheet" type="text/css" href="cssDuckeWiki.css">
		<script src='funcoes.js'></script>
<script>
function requiredKeyUp() {
	//btnSave.disabled = (txtNome.value == '');
}
/*function fechaLogo(id,who,texto) {
	window.opener.handlePopupResult(id,who,texto);
    window.close();
}*/
function aoCarregar(edit) {
	if (edit == '') {
		refill('frmAm');
	}
	//tipoChange(document.getElementsByName('radtipo')[0]);
	//document.getElementsByName("txtnome")[0].focus();
	//prepFormRadios(document.getElementById('divFrm'));
}
</script>	
<?php
$tabela = 'am';
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
	$amostra = new Amostra(
		$_SESSION['user_id'],
		date('d/m/Y H:i:s'),
		get('txttag'),
		get('valtipo'),	
		get('extdate'),	
		get('valcol'),
		get('valesp'),
		get('valpl'),
		get('valloc')
		);
	$arrPar = $amostra->getArray();
	$cols = 'addby,adddate,tag,tipo,extdate,col,esp,pl,loc'; // deve ir numa linha só, sem espaços
	$arrCol = explode(',',$cols);
	if ($edit) {
		$q = "update $tabela set (";
		if (algumaMudou($q)) { // pelo menos uma coluna mudou -> edita
			montaQuery($q);
			$res = pg_query_params($conn,$q,[$edit]);
			if ($res) {
				$divRes = "<div id='divSucesso' style='background-color:#00FF00'>Registro atualizado com sucesso! ($q)</div>";
				updateRow($tabela,$edit);
				if ($close) {
					$body = "<body onload='fechaLogo($edit)'>";
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
		/*$MMs = explode('|',get('hidmmData'));
		foreach ($MMs as $MM) {
			$M = explode(';',$MM);
			if (atualizaSubTabela($M,$divRes1)) {
				$divRes.=$divRes1;
			}
		}*/
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
/*<h1 style='text-align:center'><?=$title?> </h1>
<?="$divRes"?>
<form id='frmAm' autocomplete='off' method='post' action=''>*/
echoHeader();
?>
<dl>
	<?=dtlab('tag')?>
<dd>
	<input name='txttag' type='text' value=<?="'$tag'"?> onkeyup='requiredKeyUp()' oninput='store(this)' />
</dd>
</dl>
<?php
$cmbLabel = txt('tipo');
$cmbTableName = 'amostratipo';
$cmbFieldNames = 'nome';
$cmbCaseSensitive = 0;
$who = 'tipo';
$cmbQuery = 'amtipo';
$cmbPHP = '';
include('build_cmb.php');

$meses = ['',txt('mes01'),txt('mes02'),txt('mes03'),txt('mes04'),txt('mes05'),txt('mes06'),
	txt('mes07'),txt('mes08'),txt('mes09'),txt('mes10'),txt('mes11'),txt('mes12')];
?>
<dl>
<?=dtlab('data')?>
<dd>
<input type='text' name='txtdia' size=5 placeholder='dia' value=<?="'$dia'"?> oninput='store(this)' onkeyup="txtdiakeyup(this,'selmes','txtano')" />
<select name='selmes' style='width: 150px' onchange='store(this);selmeschange(this,\"txtdia\",\"txtano\")'>
<?php
for ($i=0; $i<13; $i++) {
	if (!empty($mes) && $mes == $i) {
		echo "<option value=$i selected>$meses[$i]</option>";
	} else {
		echo "<option value=$i>$meses[$i]</option>";
	}
}
echo "</select>
<input type='text' name='txtano' size=5 placeholder='".txt('ano')."' value='$ano' oninput='store(this)' onblur='txtanoblur(this,\"txtdia\",\"selmes\")' /></dd>
</dl>";

$cmbLabel = txt('am.col');
$cmbTableName = 'pess';
$cmbFieldNames = 'abrev,prenome,segundonome,sobrenome';
$cmbCaseSensitive = 0;
$who = 'colet';
$cmbQuery = 'pess';
$cmbPHP = 'addPess.php';
include('build_cmb.php');

$cmbLabel = txt('esp');
$cmbTableName = 'esp';
$cmbFieldNames = 'num';
$cmbCaseSensitive = 0;
$who = 'esp';
$cmbQuery = 'esp';
$cmbPHP = 'addEsp.php';
include('build_cmb.php');

$cmbLabel = txt('pl');
$cmbTableName = 'pl';
$cmbFieldNames = 'pltag';
$cmbCaseSensitive = 0;
$who = 'pltag';
$cmbQuery = 'pl';
$cmbPHP = 'addPl.php';
include('build_cmb.php');

$cmbLabel = txt('loc');
$cmbTableName = 'loc';
$cmbFieldNames = 'nome';
$cmbCaseSensitive = 0;
$who = 'loc';
$cmbQuery = 'loc';
$cmbPHP = 'addLoc.php';
include('build_cmb.php');

/*
frmAm.txtcol=vian
frmAm.txtesp=Vicentini, A. 1560
frmAm.txtloc=Rio Negro
frmAm.txtpl=435
frmAm.valcol=70
frmAm.valesp=8156
frmAm.valloc=11870
frmAm.valpl=5860
*/

$col = 'am';
// variáveis já marcadas praquela amostra
if ($edit != '') {
	echo "<div id='divVar'>";
	include 'showVar.php';
	echo "</div>";
}

// formulários disponíveis
if (isset($_GET['frmid'])) {
	$frmid = getGet('frmid');
	$$col = $edit;
}

$q = "select id,nome from frm where addby = $1 or shared = 'S' order by nome";
$res = pg_query_params($conn,$q,[$_SESSION['user_id']]);
echo "<dl><dt><label>".txt('choosefrm').":</label></dt><dd><select id='selForms' onchange='selFormsChange(this,\"".$_SESSION['cfg.frmdest']."\",\"$edit\",\"esp\")'>\n";
echo "<option value=''>".txt('choosefrm1')."</option>\n";
while ($row = pg_fetch_array($res,NULL,PGSQL_NUM)) {
	if ($row[0] == $frmid) {
		echo "<option value='$row[0]' selected>$row[1]</option>\n";
	} else {
		echo "<option value='$row[0]'>$row[1]</option>\n";
	}
}
echo "</select></dd></dl>\n<div id='divFrm' style='background-color:#".$_SESSION['cfg.corfrm']."'>";

if (isset($_GET['frmid'])) {
	include 'drawForm.php';
}
?>
</div>
<div id='divOverlay'>
	<div id='divDialog'></div>
</div>

<?php
	echoButtons();
?>
</form>
</body>
</html>
