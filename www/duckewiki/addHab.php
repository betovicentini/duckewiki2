<?php
	include_once '../../includes_pl/db_connect.php';
	include_once '../../includes_pl/functions.php';
	require_once './model/hab.php';
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
	$title = txt('novo').' '.txt('hab');
} else {
	$title = txt('edit').' '.txt('hab');
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
function fechaLogo(id,who,texto) {
	window.opener.handlePopupResult(id,who,texto);
    window.close();
}
function tipoChange(who) {
	var tipos = document.getElementsByName(who.name);
	var div = document.getElementById('divdescr');
	for (var i=0; i<tipos.length; i++) {
		if (tipos[i].value == 'Class' && tipos[i].checked) {
			div.style.display = 'block';
			break;
		} else
		if (tipos[i].value == 'Local' && tipos[i].checked) {
			div.style.display = 'none';
			break;
		}
	}
}
function aoCarregar(edit) {
	if (edit == '') {
		refill('frmHab');
	}
	tipoChange(document.getElementsByName('radtipo')[0]);
	document.getElementsByName("txtnome")[0].focus();
	prepFormRadios(document.getElementById('divFrm'));
}
</script>	
	</head>
<?php
	$tabela = 'hab';
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
		$habitat = new Habitat(
			$_SESSION['user_id'],
			date('d/m/Y H:i:s'),
			get('txtnome'),
			get('valpai'),	
			get('valloc'),	
			get('txtgpsid'),
			get('radtipo'),
			get('txadescr')
			);

		$arrPar = $habitat->getArray();

		/**
			Estou deixando v1 e v2, porque quanto tiro da erro na inserção ou remoção de TAXON (corrigir)
		*/

		/*$v1 = $_SESSION['user_id']; 
		$v2 = date('d/m/Y H:i:s');*/
				
		//$arrPar = array($v1,$v2,$v3,$v4,$v5,$v6,$v7,$v8);
		$cols = 'addby,adddate,nome,pai,loc,gpsid,tipo,descr'; // deve ir numa linha só, sem espaços
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
	/*<h1 style='text-align:center'><?= $title?> </h1>
	echo "$divRes";
	<form id='frmHab' autocomplete='off' method='post' action=''>*/
	?>

	<dl>
		<dt>
			<label>
				<?= txt('nome') ?>
				<span style='color:red'>*</span>
			</label>
		</dt>
	<dd>
		<input name='txtnome' type='text' value=<?="'$nome'"?> onkeyup='requiredKeyUp()' oninput='store(this)' />
	</dd>
	</dl>

<?php
	$cmbLabel = txt('habpai');
	$cmbTableName = 'hab';
	$cmbFieldNames = 'nome';
	$cmbCaseSensitive = 0;
	$who = 'pai';
	$cmbQuery = 'hab';
	$cmbPHP = '';
	include('build_cmb.php');

	$mmLabelH = txt('habtax');
	$mmLabel1 = txt('taxdisp');
	$mmLabel2 = txt('taxsel');
	$who = 'taxa';
	$mmQuery = 'habtax';
	$mmTableName = 'tax';
	$mmTableLink = 'habtax.tax';
	include('build_mm.php');
?>
	<dl>
		<dt>
			<label>
				<?= txt('tipo') ?>
				<span style='color:red'>*</span>
			</label>
		</dt>
	<dd>

<?php
	if ($tipo == 'Class') {
		echo "<input type='radio' id='radtipoc' name='radtipo' value='Class' onclick='store(this);tipoChange(this)' checked><label for='radtipoc'>".txt('class')."</label>";
		echo " <input type='radio' id='radtipol' name='radtipo' value='Local' onclick='store(this);tipoChange(this)'><label for='radtipol'>".txt('local')."</label>";
	} else {
		echo "<input type='radio' id='radtipoc' name='radtipo' value='Class' onclick='store(this);tipoChange(this)'><label for='radtipoc'>".txt('class')."</label>";
		echo " <input type='radio' id='radtipol' name='radtipo' value='Local' onclick='store(this);tipoChange(this)' checked><label for='radtipol'>".txt('local')."</label>";
	}
?>

	</dd></dl>
	<div id='divdescr'>
		<dl>
			<dt>
				<label>
					<?= txt('descr') ?>
				</label>
			</dt>
			<dd>
				<textarea name='txadescr'><?= $descr ?></textarea>
			</dd>
		</dl>
	</div>

	<input type='hidden' name='hidmmData' value=<?="'$hidmmData'"?> />

<?php
	$col = 'hab';
	// variáveis já marcadas praquele hábitat
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
