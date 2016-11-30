<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();
$csv = $_FILES['filCSV'];
$fname = $csv['name'];
$fullname = "upload/csv/$fname";
$tmpname = $_FILES['filCSV']['tmp_name'];
move_uploaded_file($tmpname,$fullname);
$csv = file($fullname);
$head = explode('	',$csv[0]);
/*
 * para converter o encoding: iconv() ou mb_convert_encoding()
 * diferenças: http://stackoverflow.com/a/25510116/1086511
 * ex: iconv(mb_detect_encoding($text, mb_detect_order(), true), "UTF-8", $text);
 * Solução interessante? http://stackoverflow.com/a/8187917/1086511
 * 
 */
$title = 'Importar CSV';
?>
<!DOCTYPE html>
<html lang='BR'>
<head>
<meta charset='UTF-8'>
<title><?= $title ?> </title>
<link rel="stylesheet" type="text/css" href="css/cssDuckeWiki.css">
<script src='js/funcoes.js'></script>
<script type='text/javascript'>
var step=1, title, selEtc=null, optBD=null;
var selFil, selCol, selLnk, btnNext, btnBack, btnUnir, btnDesunir;
function selTabChange(who) {
	var i, std = '';
	for (i=0; i<selLnk.options.length; i++) {
		std = std + selLnk.options[i].value + '件';
	}
	std = std.substr(0,std.length-1); // manda tudo que já foi selecionado
	divUpd = 'divCols';
	url = 'addcsvcols.php?tabela='+who.options[who.selectedIndex].value+'&std='+std;
	conecta(url,update);
}
function sendCols() {
	var i, std = '', val, pos;
	for (i=0; i<selLnk.options.length; i++) {
		val = selLnk.options[i].value;
		pos = val.indexOf('|');
		val = val.substr(0,pos);
		std = std + val + '件';
	}
	std = std.substr(0,std.length-1); // manda tudo que já foi selecionado
	divUpd = 'divStep5';
	url = 'addcsv1.php?std='+std+'&fname=<?=$fullname?>';
	conecta(url,update);
}
function selFilChange(selFil) {
	btnUnir.disabled = optBD == null || selFil.selectedIndex < 0;
}
function selColChange(who) {
	optBD = who.options[who.selectedIndex];
	btnUnir.disabled = who.selectedIndex < 0 || selFil.selectedIndex < 0;
}
function selLnkChange(who) {
	btnDesunir.disabled = who.selectedIndex < 0;
}
function btnUne() {
	var option = document.createElement('option');
	option.value = selFil.options[selFil.selectedIndex].text+'号'+
		optBD.value+'|'+optBD.parentNode.id;
	//if (step == 2 || step == 4) {
	if ([2,4].indexOf(step) >= 0) {
		option.text = selFil.options[selFil.selectedIndex].text+' --- '+optBD.text;
	} else
	if (step == 3) {
		var selTab = document.getElementById('selTab');
		option.text = selFil.options[selFil.selectedIndex].text+' --- '+
			selTab.options[selTab.selectedIndex].text+'.'+optBD.text;
	}
	selLnk.add(option);
	selFil.remove(selFil.selectedIndex);
	optBD.parentNode.remove(optBD.parentNode.selectedIndex);
	optBD = null;
	btnUnir.blur();
	btnUnir.disabled = true;
	btnNext.disabled = false;
}
function btnDesune() {
	var item = selLnk.options[selLnk.selectedIndex].value;
	var pos1 = item.indexOf('号');
	var valFil = item.substr(0,pos1);
	var valBD = item.substr(pos1+1);
	var pos2 = valBD.indexOf('|');
	var colBD = valBD.substr(0,pos2);
	var selBD = valBD.substr(pos2+1);
	// devolve a coluna do arquivo
	var option = document.createElement('option');
	option.text = valFil;
	selFil.add(option);
	// devolve a coluna do BD
	if (step == 2) {
		item = selLnk.options[selLnk.selectedIndex].text;
		pos1 = item.indexOf(' --- ');
		var txtBD = item.substr(pos1+5);
		option = document.createElement('option');
		option.value = colBD;
		option.text = txtBD;
		document.getElementById(selBD).add(option);
	} else
	if (step == 3) {
		option = document.createElement('option');
		option.value = colBD;
		pos = colBD.indexOf('.');
		option.text = colBD.substr(pos+1);
		selCol.add(option);
	}
	selLnk.remove(selLnk.selectedIndex);
	selLnkChange(selLnk); // atualiza o disabled do botão Desunir
	btnNext.disabled = selLnk.options.length == 0;
}
function btnPrevStep() {
	var divShow, divHide;
	switch (step) {
		case 2:
			divHide = document.getElementById('divStep2-4');
			divShow = document.getElementById('divStep1');
			divHide.style.display = 'none';
			divShow.style.display = 'block';
			btnBack.disabled = true;
			btnNext.disabled = false;
			break;
		case 3:
			divHide = document.getElementById('divStep3');
			divShow = document.getElementById('divStep2');
			divHide.style.display = 'none';
			divShow.style.display = 'block';
			break;
		case 4:
			divHide = document.getElementById('divStep4');
			divShow = document.getElementById('divStep3');
			divHide.style.display = 'none';
			divShow.style.display = 'block';
			break;
		case 5:
			divHide = document.getElementById('divStep5');
			divShow = document.getElementById('divStep2-4');
			divHide.style.display = 'none';
			divShow.style.display = 'block';
			btnNext.disabled = false;
			break;
	}
	step--;
	btnBack.blur();
}
function btnNextStep() {
	var divShow, divHide, i, labs, h1;
	switch (step) {
		case 1:
			divHide = document.getElementById('divStep1');
			divShow = document.getElementById('divStep2-4');
			// altera o título
			labs = divHide.getElementsByTagName('label');
			for (i=0; i<labs.length; i++) {
				if (labs[i].htmlFor == document.querySelector('input[name=radTipo]:checked').id) {
					h1 = document.getElementsByTagName('h1')[0];
					h1.innerHTML = title + ' - ' + labs[i].innerHTML;
					break;
				}
			}
			divHide.style.display = 'none';
			divShow.style.display = 'block';
			btnNext.disabled = selLnk.options.length == 0;
			break;
		case 2:
			divHide = document.getElementById('divStep2');
			divShow = document.getElementById('divStep3');
			divHide.style.display = 'none';
			divShow.style.display = 'block';
			break;
		case 3:
			divHide = document.getElementById('divStep3');
			divShow = document.getElementById('divStep4');
			divHide.style.display = 'none';
			divShow.style.display = 'block';
			break;
		case 4:
			divHide = document.getElementById('divStep2-4');
			divShow = document.getElementById('divStep5');
			divHide.style.display = 'none';
			divShow.style.display = 'block';
			btnNext.disabled = true;
			sendCols();
			break;
	}
	step++;
	optBD = null;
	btnUnir.disabled = true;
	btnBack.disabled = false;
	btnNext.blur();
}
function radTipoChange() {
	btnNext.disabled = false;
	// oculta todos os selEtc...
	var i, selsEtc = document.getElementsByTagName('select');
	for (i=0; i<selsEtc.length; i++) {
		if (selsEtc[i].id.substr(0,6) == 'selEtc') {
			selsEtc[i].style.display = 'none';
		}
	}
	// ... e mostra apenas aquele vinculado ao tipo de dado sendo importado
	switch (document.querySelector('input[name=radTipo]:checked').id) {
		case 'radTipoEsp':
		case 'radTipoEspPl':
			selEtc = document.getElementById('selEtcEsp');
			selEtc.style.display = 'block';
			break;
		case 'radTipoPl':
			selEtc = document.getElementById('selEtcPl');
			selEtc.style.display = 'block';
			break;
		case 'radTipoAm':
			selEtc = document.getElementById('selEtcAm');
			selEtc.style.display = 'block';
			break;
		case 'radTipoLoc':
			selEtc = document.getElementById('selEtcLoc');
			selEtc.style.display = 'block';
			break;
		case 'radTipoTax':
			selEtc = document.getElementById('selEtcTax');
			selEtc.style.display = 'block';
			break;
		case 'radTipoPess':
			selEtc = document.getElementById('selEtcPess');
			selEtc.style.display = 'block';
			break;
	}
}
function txtVarKeyUp(e,who) {
	if (e.keyCode == 38 || e.keyCode == 40) { // seta pra cima | pra baixo
		var selSearch = document.getElementById('selVarCSV');
		if (selSearch) {
			selSearch.focus();
			if (e.keyCode == 38) { // pra cima
				selSearch.selectedIndex = selSearch.length-1;
			} else { // pra baixo
				selSearch.selectedIndex = 0;
			}
			optBD = selSearch.options[selSearch.selectedIndex];
		}
	} else {
		divUpd = 'divVar';
		var url = 'getLike.php?what='+who.value+'&query=varcsv&who=VarCSV&m=N';
		conecta(url,update);
	}
}
function aoCarregar() {
	var h1 = document.getElementsByTagName('h1')[0];
	title = h1.innerHTML;
	selFil = document.getElementById('selFil'); // colunas do arquivo (FILe)
	selCol = document.getElementById('selCol'); // colunas do BD
	selLnk = document.getElementById('selLnk'); // SELECT com os links entre colunas do arquivo e do BD
	btnNext = document.getElementById('btnNext');
	btnBack = document.getElementById('btnBack');
	btnUnir = document.getElementById('btnUnir');
	btnDesunir = document.getElementById('btnDesunir');
}
</script>
<?php
$body = "";
$divRes = '';
if (!empty($post)) {
}
pullCfg();
echo "</head><body onload='aoCarregar()' style='overflow-y:scroll'>";
$loginErro = login_error($conn);
if ($loginErro) {
	exit("<p>Você não tem autorização de acesso a esta página (erro: $loginErro). Por favor, <a href='index.php' target='main'>volte para a tela inicial e faça o login</a>.</p>");
}
?>
<h1 style='text-align:center'><?=$title?></h1>
<?=$divRes?>
<form id='frmcsv' autocomplete='off' method='post' action=''>
<?php
/*
 * princípios
 * 
 * - espaço só o mínimo necessário.
 * - letras só o mínimo necessário.
 * - palavras só o mínimo necessário.
 * Por mínimo necessário entenda-se:
 * mínimo de caracteres necessários para se conseguir o melhor desempenho com a máxima legibilidade.
 * 
 * */
?>
<div id='divStep1'>
<table style='margin-left:auto;margin-right:auto;width:300px'>
<tr>
<td style='text-align:left'>
Tipos de dados:<BR>
<input type='radio' name='radTipo' id='radTipoEsp' onchange='radTipoChange()' /><label for='radTipoEsp'>Especímenes</label><BR>
<input type='radio' name='radTipo' id='radTipoEspPl' onchange='radTipoChange()' /><label for='radTipoEspPl'>Especímenes de plantas marcadas</label><BR>
<input type='radio' name='radTipo' id='radTipoPl' onchange='radTipoChange()' /><label for='radTipoPl'>Plantas marcadas</label><BR>
<input type='radio' name='radTipo' id='radTipoAm' onchange='radTipoChange()' /><label for='radTipoAm'>Amostras</label><BR>
<input type='radio' name='radTipo' id='radTipoLoc' onchange='radTipoChange()' /><label for='radTipoLoc'>Localidades</label><BR>
<input type='radio' name='radTipo' id='radTipoTax' onchange='radTipoChange()' /><label for='radTipoTax'>Táxons</label><BR>
<input type='radio' name='radTipo' id='radTipoPess' onchange='radTipoChange()' /><label for='radTipoPess'>Pessoas</label><BR>
</td>
</tr>
</table>
</div>
<div id='divStep2-4' style='display:none'>
<table style='margin-left:auto;margin-right:auto'>
<tr>
<td style='text-align:left'>
<?php
	echo txt('colde')."<BR>$fname<BR>";
	echo "<select id='selFil' size=18 style='width:200px' onchange='selFilChange(this)'>";
	foreach ($head as $col) {
		echo "<option>$col</option>";
	}
	echo "</select>";
?>
</td>
<td>
	<button type='button' id='btnUnir' disabled onclick='btnUne()'>Unir</button><BR>
	<button type='button' id='btnDesunir' disabled onclick='btnDesune()'>Desunir</button>
</td>
<td style='text-align:left;width:200px'>
<div id='divStep2' style='display:block'>
	Identificador<BR>
<!--input type='radio' name='radId' id='radIdID' onchange='radIdChange()' /><label for='radIdID'>ID da tabela</label><BR>
<input type='radio' name='radId' id='radIdEtc' onchange='radIdChange()' /><label for='radIdEtc'>Outras colunas</label><BR-->
<select id='selEtcEsp' size=8 style='width:200px;display:none' onchange='selColChange(this)'>
	<option value='esp.id'>ID da tabela</option>
	<option value='esp.col'>Coletor</option>
	<option value='esp.num'>Número</option>
	<option value='esp.col+esp.num'>Coletor+Número</option>
	<option value='esp.ano'>Ano</option>
</select>
<select id='selEtcPl' size=8 style='width:200px;display:none' onchange='selColChange(this)'>
	<option value='pl.id'>ID da tabela</option>
	<option value='pl.pltag'>Tag</option>
	<option value='pl.loc'>Localidade</option>
</select>
<select id='selEtcAm' size=8 style='width:200px;display:none' onchange='selColChange(this)'>
	<option value='am.id'>ID da tabela</option>
</select>
<select id='selEtcLoc' size=8 style='width:200px;display:none' onchange='selColChange(this)'>
	<option value='loc.id'>ID da tabela</option>
	<option value='loc.nome'>Nome da localidade</option>
</select>
<select id='selEtcTax' size=8 style='width:200px;display:none' onchange='selColChange(this)'>
	<option value='tax.id'>ID da tabela</option>
</select>
<select id='selEtcPess' size=8 style='width:200px;display:none' onchange='selColChange(this)'>
	<option value='pess.id'>ID da tabela</option>
</select>
</div>
<div id='divStep3' style='display:none'>
	Tabelas<BR>
	<select id='selTab' size=8 onchange='selTabChange(this)'>
<?php
	$q = "select table_name from information_schema.tables 
	where table_schema = 'public'
	order by table_name";
	$res = pg_query($conn,$q);
	if ($res) {
		$tabelas = [];
		while ($row = pg_fetch_array($res,NULL,PGSQL_NUM)) {
			$q1 = "select obj_description('public.$row[0]'::regclass)";
			$res1 = pg_query($conn,$q1);
			if ($res1) {
				if ($row1 = pg_fetch_array($res1,NULL,PGSQL_NUM)) {
					$tabelas[$row[0]] = $row1[0];
				}
			} else {
				// Erro ao conectar! (2)
			}
		}
		asort($tabelas); // mantém índices
		foreach ($tabelas as $key => $value) {
			echo "<option value='$key'>$value</option>";
		}
	} else {
		// Erro ao conectar! (1)
	}
?>	
	</select><BR>
	<BR><div id='divCols'></div>
</div>
<div id='divStep4' style='display:none'>
	Variáveis<BR>
	<input type='text' id='txtVar' onkeyup='txtVarKeyUp(event,this)' /><BR>
	<div id='divVar'></div>
</div>
</td>
</tr>
<tr>
<td colspan=3>
<select id='selLnk' size=8 style='width:500px' onchange='selLnkChange(this)'></select>
</td>
</tr>
</table>
</div>
<div id='divStep5' style='display:none'>
	Importando...<BR>
</div>
<div class='wrapper'>
<button id='btnBack' type='button' onclick='btnPrevStep()' disabled>&lt;&lt; Voltar</button>
<button id='btnNext' type='button' onclick='btnNextStep()' disabled>Próximo &gt;&gt;</button>
</div>
</form>
</body>
</html>
