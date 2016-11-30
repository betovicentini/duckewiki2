<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();
/*
 * To-do list:
 * - Inflorescência (aqui é avô)
 *   - Indumento
 *     - Inflorescência Tipo
 *     - Inflorescência Tamanho
 *     - Inflorescência Densidade
 * - Cálice ...
 * - Corola ...
 * - Inflorescência (aqui é pai)
 *   - Tipo
 *   - Posição ...
 * 
 * - Colocar unidades de medida na frente do campo <input>
 * Aqui e em embedForm.php
 */
?>
<!DOCTYPE html>
<html lang='BR'>
<head>
<meta charset='UTF-8'>
<title>Novo/Editar Formulário</title>
<link rel="stylesheet" type="text/css" href="css/cssDuckeWiki.css">
<script src='js/funcoes.js'></script>
<script type='text/javascript'>
function selFormsChange1() {
	var F = document.getElementById('frm');
	// cria um hidden input
	var sel = document.getElementById('selForms');
	if (sel.value != '') {
		addHidden(F,'frmid',sel.value);
	}
	F.submit();
}
function aoCarregar() {
	var elements = document.getElementById("frm").elements;
	var element;
	var i;
	var names = [];
	var objs = [];
	for (i=0; i<elements.length; i++) {
		element = elements[i];
		if (element.type == 'radio') {
			if (names.indexOf(element.name) < 0) {
				names.push(element.name);
				objs[element.name] = null;
			}
			element.onclick = function() {
				if (objs[this.name] == this) {
					this.checked = false;
					objs[this.name] = null;
				} else {
					objs[this.name] = this;
				}
			}
		}
	}
	document.getElementById("selForms").focus();
}
/*function chkClick(who) {
	var i;
	var chks = document.getElementsByName(who.name);
	for (i=0; i<chks.length; i++) {
		if (chks[i].value != who.value) {
			chks[i].checked = false;
		}
	}
}*/
</script>
</head>
<body onload='aoCarregar()'>
<?php
$loginErro = login_error($conn);
if ($loginErro) {
	exit("<p>Você não tem autorização de acesso a esta página. Por favor, <a href='index.php' target='main'>volte para a tela inicial e faça o login</a>.</p>");
}
echo "<form id='frm' name='frm' autocomplete='off' method='get' action='' style='background-color:#DCC964'>\n";
$frmid = getGet('frmid');
$q = "select id,nome from form where addby = $1 or shared = 'S' order by nome";
$res = pg_query_params($conn,$q,[$_SESSION['user_id']]);
echo "<select id='selForms' onchange='selFormsChange1()'>\n";
echo "<option value=''>- escolha um formulário -</option>\n";
while ($row = pg_fetch_array($res,NULL,PGSQL_NUM)) {
	if ($row[0] == $frmid) {
		echo "<option value='$row[0]' selected>$row[1]</option>\n";
	} else {
		echo "<option value='$row[0]'>$row[1]</option>\n";
	}
}
echo "</select>\n";
if ($frmid != '') {
	include 'drawForm.php';
	echo "<BR>
	<div class='wrapper'>
	<button id='btnSave' type='button' onclick='btnSaveClick()'>Salvar</button>
	<button id='btnCancel' type='button' onclick='btnCancelClick()'>Cancelar</button>
	</div>";
}
?>
</form>
</body>
</html>
