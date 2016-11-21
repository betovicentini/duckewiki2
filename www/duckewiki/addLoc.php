<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
require_once './model/loc.php';

sec_session_start();
/* 
	Olhar correçõesLocalidade.txt
*/
$edit = getGet('edit');
if ($edit == '') {
	$title = txt('nova').' '.txt('loc');
} else {
	$title = txt('edit').' '.txt('loc');
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
function fechaLogo(id,who,texto) {
	window.opener.handlePopupResult(id,who,texto);
    window.close();
}
function aoCarregar(edit) {
	//if (edit == '') {
	if (typeof(edit) === 'undefined') {
		refill('frmLoc');
	} else {
		mudaPai(edit,'pai');
	}
	document.getElementsByName("txtnome")[0].focus();
	prepFormRadios(document.getElementById('divFrm'));
}
</script>

<?php
$tabela = 'loc';
$update = getGet('update');
$close = getGet('close');

if ($edit == '') {
	emptyRow($tabela);
} else {
	updateRow($tabela,$edit);
}

$body = "<body onload='aoCarregar($edit)'>";
$divRes = '';
if (!empty($post)) {
	$v1 = $_SESSION['user_id'];
	$v2 = date('d/m/Y H:i:s');	

	$localidade = new Localidade(
		$_SESSION['user_id'],
		date('d/m/Y H:i:s'),	
		get('txtnome'),
		get('txtsigla'),
		get('seltipo'),
		get('txtLatG'),
		get('txtLatM'),
		get('txtLatS'),
		get('radLatH'),		
		get('txtLonG'),
		get('txtLonM'),
		get('txtLonS'),	
		get('radLonH'),		
		get('txtdatum'),
		get('txtutmn'),
		get('txtutme'),
		get('txtutmz'),
		get('txtstartx'),
		get('txtstarty'),
		get('txtdimx'),
		get('txtdimy'),
		get('txtdimdiameter'),
		get('txtalt1'),
		get('txtalt2')
	);
	$arrPar = $localidade->getArray();
	
	//$arrPar = array($v1,$v2,$v3,$v4,$v5,$v6,$v7,$v8,$v9,$v10,$v11,$v12,$v13,$v14,$v15,$v16,$v17,$v18);
	$cols = 'addby,adddate,nome,sigla,tipo,lat,lon,datum,utmn,utme,utmz,startx,starty,dimx,dimy,dimdiameter,alt1,alt2'; // deve ir numa linha só, sem espaços
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
		$MMs = explode('|',get('hidmmData'));
		foreach ($MMs as $MM) {
			echo "<BR><BR>$MM<BR><BR>";
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
/*<h1 style='text-align:center'><?= $title ?> </h1>
<?= $divRes ?>
<form id='frmLoc' autocomplete='off' method='post' action=''>*/
	$mmLabelH = txt('locpai');
	$mmLabel1 = txt('locdisp');
	$mmLabel2 = txt('locsel');
	$mmTip = txt('locpai.tip');
	$who = 'pais';
	$mmQuery = 'locpai';
	$mmTableName = 'loc';
	$mmTableLink = 'locpai.pai';
	include('build_mm.php');
?>

<dl>
	<dt>
		<label><?= txt('nome') ?> </label>
	</dt>
	<dd>
		<input type='text' name='txtnome' value=<?="'$nome'"?> oninput='store(this)' />
	</dd>
</dl>
<dl>
	<dt>
		<label><?= txt('sigla') ?> </label>
	</dt>
	<dd>
		<input type='text' name='txtsigla' value=<?="'$sigla'"?> oninput='store(this)' />
	</dd>
</dl>

<dl>
	<dt>
		<label><?= txt('tipo') ?></label>
	</dt>
	<dd>
		<select name='seltipo' onchange='store(this)'>
			<option value=''></option>
				<?php
				$q = "select id,tipo from loctipo order by id";
				$res = pg_query($conn,$q);
				if ($res) {
					while ($row2 = pg_fetch_array($res,NULL,PGSQL_NUM)) {
						if ($row2[0] != 1) { // reservado
							if ($row2[0] == $tipo) {
								echo "<option value='$row2[0]' selected>$row2[1]</option>";
							} else {
								echo "<option value='$row2[0]'>$row2[1]</option>";
							}
						}
					}
				}
				?>
		</select>
	</dd>
</dl>

<dl>
	<dt>
		<label> <?= txt('coords') ?></label>
	</dt>
	<dd>
	<table>
		<tr>
			<td>
				<label>Lat</label>
			</td>
			<td colspan=3 style='text-align:left'>
<?php
if (!empty($lat)) {
	echo "$lat = ";
	$negativo = $lat < 0;
	$lat = abs($lat);
	$latG = floor($lat);
	$frac = $lat - $latG;
	$latM = $frac*60;
	$frac = $latM - floor($latM);
	$latM = floor($latM);
	$latS = round($frac*6000)/100;
	echo "<input name='txtLatG' type='text' class='short' value='$latG' oninput='store(this)' />°
	<input name='txtLatM' type='text' value='$latM' class='short' oninput='store(this)' />'
	<input name='txtLatS' type='text' value='$latS' class='short' oninput='store(this)' />\"";
	if ($negativo) {
		echo "<input type='radio' id='radLatHN' name='radLatH' value='N' onclick='store(this)'>N
		<input type='radio' id='radLatHS' name='radLatH' value='S' onclick='store(this)' checked>S</td>";
	} else {
		echo "<input type='radio' id='radLatHN' name='radLatH' value='N' onclick='store(this)' checked>N
		<input type='radio' id='radLatHS' name='radLatH' value='S' onclick='store(this)'>S</td>";
	}
} else {
	echo "<input name='txtLatG' type='text' class='short' oninput='store(this)' />°
	<input name='txtLatM' type='text' value=0 class='short' oninput='store(this)' />'
	<input name='txtLatS' type='text' value=0 class='short' oninput='store(this)' />\"
	<input type='radio' id='radLatHN' name='radLatH' value='N' onclick='store(this)'>N
	<input type='radio' id='radLatHS' name='radLatH' value='S' onclick='store(this)' checked>S</td>";
}
echo "</tr>
<tr>
	<td><label>Lon</label></td>
	<td colspan=3 style='text-align:left'>";
	if (!empty($lon)) {
		echo "$lon = ";
		$negativo = $lon < 0;
		$lon = abs($lon);
		$lonG = floor($lon);
		$frac = $lon - $lonG;
		$lonM = $frac*60;
		$frac = $lonM - floor($lonM);
		$lonM = floor($lonM);
		$lonS = round($frac*6000)/100;
		echo "<input name='txtLonG' type='text' class='short' value='$lonG' oninput='store(this)' />°
		<input name='txtLonM' type='text' value='$lonM' class='short' oninput='store(this)' />'
		<input name='txtLonS' type='text' value='$lonS' class='short' oninput='store(this)' />\"";
		if ($negativo) {
			echo "<input type='radio' id='radLonHE' name='radLonH' value='E' onclick='store(this)'>E
			<input type='radio' id='radLonHW' name='radLonH' value='W' onclick='store(this)' checked>W</td>";
		} else {
			echo "<input type='radio' id='radLonHE' name='radLonH' value='E' onclick='store(this)' checked>E
			<input type='radio' id='radLonHW' name='radLonH' value='W' onclick='store(this)'>W</td>";
		}
	} else {
		echo "<input name='txtLonG' type='text' class='short' oninput='store(this)' />°
		<input name='txtLonM' type='text' value=0 class='short' oninput='store(this)' />'
		<input name='txtLonS' type='text' value=0 class='short' oninput='store(this)' />\"
		<input type='radio' id='radLonHE' name='radLonH' value='E' onclick='store(this)'>E
		<input type='radio' id='radLonHW' name='radLonH' value='W' onclick='store(this)' checked>W</td>";
	}
?>
</tr>
<tr>
	<td colspan=4 style='text-align:left'>
		<label>UTM N </label>
		<input name='txtUTMn' type='text' value=<?= "'$utmn'";?> class='short' oninput='store(this)' />
		<label>UTM E </label>
		<input name='txtUTMe' type='text' value=<?= "'$utme'";?> class='short' oninput='store(this)' />
		<label>UTM Z </label>
		<input name='txtUTMz' type='text' value=<?= "'$utmz'";?> class='short' oninput='store(this)' />
	</td>
</tr>
<tr>
<td colspan=4 style='text-align:right'>
	<label>Alt1 (m)</label>
	<input name='txtalt1' type='text' value=<?= "'$alt1'";?> class='short' oninput='store(this)' />
	<label>Alt2 (m)</label>
	<input name='txtalt2' type='text' value=<?= "'$alt2'";?> class='short' oninput='store(this)' />
	<label>Datum </label>
	<input name='txtdatum' type='text' value=<?= "'$datum'";?> class='short' oninput='store(this)' /></td>
</tr>
	</table>
</dd>
</dl>

<div id='divstartxy' style='display:none'>
	<dl>
		<dt><label>StartX</label></dt>
		<dd><input type='text' name='txtstartx' value=<?= "'$startx'";?> oninput='store(this)' /></dd>
	</dl>
	<dl>
		<dt><label>StartY</label></dt>
		<dd><input type='text' name='txtstarty' value=<?= "'$starty'";?> oninput='store(this)' /></dd>
	</dl>
</div>
<dl>
	<dt><label>DimX</label></dt>
	<dd><input type='text' name='txtdimx' value=<?= "'$dimx'";?> oninput='store(this)' /></dd>
</dl>
<dl>
	<dt><label>DimY</label></dt>
	<dd><input type='text' name='txtdimy' value=<?= "'$dimy'";?> oninput='store(this)' /></dd>
</dl>
<dl>
	<dt><label><?= txt('diam') ?></label></dt>
	<dd><input type='text' name='txtdimdiameter' value=<?= "'$dimdiameter'";?> oninput='store(this)' /></dd>
</dl>

<input type='hidden' name='hidmmData' value=<?= "'$hidmmData'";?> />
<?php
	$col = 'loc';
	// variáveis já marcadas praquele local
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
	echo "<dl><dt><label>".txt('choosefrm').":</label></dt><dd><select id='selForms' onchange='selFormsChange(this,\"".$_SESSION['cfg.frmdest']."\",\"$edit\",\"$col\")'>\n";
	echo "<option value=''>".txt('choosefrm1')."</option>\n";
	while ($row = pg_fetch_array($res,NULL,PGSQL_NUM)) {
		if (!empty($frmid) && $row[0] == $frmid) {
			echo "<option value='$row[0]' selected>$row[1]</option>\n";
		} else {
			echo "<option value='$row[0]'>$row[1]</option>\n";
		}
	}
	echo "</select></dd></dl>\n";

	echo "<div id='divFrm' style='background-color:#".$_SESSION['cfg.corfrm']."'>";
	if (isset($_GET['frmid'])) {
		include 'drawForm.php';
	}
	echo "</div>

	<div id='divOverlay'>
		<div id='divDialog'></div>
	</div>
	<div id='tooltip'></div>";

	echoButtons();
?>
</form>
</body>
</html>
