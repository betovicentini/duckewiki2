<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();
/*
 * To-do list:
 * - padronizar toda a nomenclatura de controles (id/name):
 *  - código minúsculo de 3 letras (btn=button, sel=select, txt=input type text, txa=textarea, div=div, 
 *   Enter clicar no botão de inserir coletor extra...
 * - ... e depois navegar até deixar visível o que entrou
 *   clicar nos coletores extras para ampliar/reduzir...
 * - ... mas mantendo as seleções/posições?
 * - filtrar coletores disponíveis
 * - planta mostrar tag + [duas últimas localidades] (se planta não marcada, deixa em branco)
 * - quando mudar o coletor selecionado, tirá-lo da lista de coletores extras selecionados (se estiver lá)
 * - F5 não pode apagar o que já foi inserido!! (cookies?)
 * - como buscar plantas marcadas? Por tag? Por id?
 * - depois modificar para automatizar tudo a partir de arquivos texto?
 * - picker pra data pra não ter problema de digitar na ordem errada
 * - procurar na lista de outros coletores disponíveis pelo prenome
 * - quando clicar em Add1Esp na tela principal, ativar a janela se já estiver aberta (ou abrir outra? Evitar abrir + de 1)
 * - janela do popup está sem barra de rolagem
 * - quando digitar um gênero, mostrar também as espécies/subespécies?
 * ---------em casa
 * - conferir Nova Ident contradições ex: Muita confiança + cf.
 * - conferir Nova Amostra contradições ex: Lat negativa e Norte
 * - adicionar coordenadas em UTM
 * - FECHAR o datepicker quando sair pelo Tab/Esc
 * - 
 * ------------------
 * - form precisa ter id E name?
 * Using an ID rather than a name to identify and obtain a reference to a form is generally preferable.
 * http://www.dyn-web.com/tutorials/forms/references.php
 * 
 *   nomes/ids diferentes para cada form, para poder salvar no localStorage
 * - filtrar especímenes por id está dando "id ambíguo"
 * - editar não está mostrando valores nos cmb
 * 
 * - <dl></dl> não devem repetir em cada linha! (se bem que layout fica melhor assim)
 * - talvez separar tabela var em varesp, varpl, varhab...? Talvez acelere algumas delas, já que varesp é gigantesca e as outras não?
 * 
 * addPl
 * . - não tem coletor, só coletores (marcada por)
 * - coords da localidade (abre e fecha)
 * - coords da planta (x, y...)
 * 
 */
$edit = getGet('edit');
echo "<!DOCTYPE html>
<html lang='BR'>
<head>
<meta charset='UTF-8'>";
if ($edit == '') {
	$title = txt('nova').' '.txt('pl');
} else {
	$title = txt('edit').' '.txt('pl');
}
echo "<title>$title</title>";
?>
<link rel="stylesheet" type="text/css" href="cssDuckeWiki.css">
<script src='funcoes.js'></script>
<script>
var ajAnt = null;
function update2() {
    if (HttpReq.readyState == 4 && HttpReq.status == 200) {
		div = document.getElementById(divUpd);
        div.innerHTML = "";
        div.innerHTML = HttpReq.responseText;
		if (document.getElementById('txtvar')) {
			document.getElementById('txtvar').select();
		} else
		if (document.getElementById('txavar')) {
			document.getElementById('txavar').focus();
		} else
		if (document.getElementById('selposs')) {
			document.getElementById('selposs').focus();
		}
    } else {
        if (HttpReq.readtyState == 4) {
            alert("Erro: " + HttpReq.statusText);
        }
    }
}
window.onkeyup = function(e) {
	var key = e.keyCode ? e.keyCode : e.which;
	if (key == 27) { // Esc
		btnCancelVarClick();
	}
}
function varKeyUp(e,id,val) {
	if (val != 'txavar') { // não usa Enter pra salvar textarea, para poder inserir Enter no meio do texto
		var key = e.keyCode ? e.keyCode : e.which;
		if (key == 13) { // Enter
			btnSaveVarClick(id,val);
		}
	}
}
function editVar(id,val/*,esp*/) {
	divUpd = 'divDialog';
	var url = 'editVar.php?varid='+id+'&val='+val;//+'&esp='+esp;
	conecta(url,update2);
	var divO = document.getElementById('divOverlay');
	divO.style.visibility = 'visible';
}
function isElementInViewport(el) {
	var rect = el.getBoundingClientRect();
	return (
		rect.top >= 0 &&
		rect.left >= 0 &&
		rect.bottom <= (window.clientHeight || document.documentElement.clientHeight) && // window.client... parece funcionar melhor com scrollBars do que window.inner...
		rect.right <= (window.clientWidth || document.documentElement.clientWidth)
	);
}
/*function diasMes(mes,ano) {
	return new Date(ano,mes,0).getDate();
}
function txtdiakeyup(who,mes,ano) {
	var cmes = document.getElementsByName(mes)[0];
	var cano = document.getElementsByName(ano)[0];
	var ndias = diasMes(cmes.selectedIndex,cano.value);
	who.value = who.value.match(/\d+/); // deixa apenas os números
	if (who.value > ndias) {
		who.value = ndias;
	} else
	if (who.value < 1 && who.value != '') {
		who.value = 1;
	}
	// store novo valor
}
function txtanoblur(who,dia,mes) {
	who.value = who.value.match(/\d+/); // deixa apenas os números
	var hoje = new Date();
	var ano = hoje.getFullYear();
	var ano2 = parseInt(ano.toString().substr(2,2));
	var ano3 = parseInt(ano.toString().substr(1,3));
	var seculo = ano-ano2;
	if (who.value.length <= 2) {
		if (parseInt(who.value) <= ano2) {
			who.value = seculo + parseInt(who.value);
		} else {
			who.value = seculo-100 + parseInt(who.value);
		}
	} else
	if (who.value.length == 3) {
		if (parseInt(who.value) <= ano3) {
			who.value = '2' + who.value;
		} else {
			who.value = '1' + who.value;
		}
	} else
	if (who.value.length >= 4) {
		if (parseInt(who.value) > ano) {
			who.value = ano;
		} else {
			who.value = parseInt(who.value);
		}
	}
	// store novo valor
	txtdiakeyup(document.getElementsByName(dia)[0],mes,who.name);
}
function selmeschange(who,dia,ano) {
	if (who.selectedIndex == 0) {
		document.getElementsByName(dia)[0].value = '';
	} else {
		txtdiakeyup(document.getElementsByName(dia)[0],who.name,ano);
	}
}*/
function handlePopupResult(id,who,texto) {
	if (typeof who === 'undefined') who = '';
	if (typeof texto === 'undefined') texto = '';
	// função chamada em addDet.php.fechaLogo() e addPess
	var val = document.getElementsByName('val'+who)[0];
	val.value = id;
	store(val); // precisa guardar senão perde no refill do aoCarregar()
	if (who == 'det') {
		var spn = document.getElementById('spn'+who);
		spn.innerHTML = texto;
		store(spn);
	} else
	if (['col','loc','hab','prj'].indexOf(who) >= 0) {
		var txt = document.getElementById('txt'+who);
		txt.value = texto;
		store(txt);
	}
	modoEdit(); // muda a cor do fundo, habilita o botão "Salvar"...
}
function txtnummaxblur(who) {
	document.getElementById('divcomum').style.display = 'block';
	if (who.value == '') { // apenas 1 indivíduo
		document.getElementById('divindiv').style.display = 'block';
		//document.getElementsByName('txtpl')[0].focus();
	} else { // vários indivíduos
		document.getElementById('divindiv').style.display = 'none';
		//document.getElementsByName('txtdia')[0].focus();
	}
}
function fechaLogo(id,who,texto) {
	window.opener.handlePopupResult(id,who,texto);
    window.close();
}
function refillDivSpan() {
	// preencher de novo o div_mm e o span_det
	//alert(1);
}
function aoCarregar(edit) {
	if (edit == '') { // não está editando (== '' vs typeof === 'undefined' ?? -> ver addEsp.php)
		refill('frmPl');
	} else { // está editando
		document.getElementById('divcomum').style.display='block'; // DEPENDE DOS CAMPOS numcolet E numcoletaté
		document.getElementById('divindiv').style.display='block';
	}
	document.getElementsByName("txtnum")[0].focus();
	//modoEdit();
	prepFormRadios(document.getElementById('divFrm'));
}
</script>
<?php
$tabela = 'pl';
$update = getGet('update');
$close = getGet('close');
$h1 = "<h1 style='text-align:center'>$title</h1>";
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
	$v3 = get('valcol');
	$v4 = get('txtnum');
	$v5 = get('txtdia');
	$v6 = get('selmes');
	$v7 = get('txtano');
	$v8 = get('valdet');
	$v9 = get('valhab');
	$v10 = get('valloc');
	$v11 = get('txtlat');
	$v12 = get('txtlon');
	$v13 = get('txtalt');
	$v14 = get('valprj');
	$arrPar = [];
	for ($i=1; $i<=14; $i++) {
		$arrPar[] = ${"v$i"};
	}
	//$arrPar = array($v1,$v2,$v3,$v4,$v5,$v6,$v7,$v8,$v9,$v10,$v11,$v12,$v13,$v14);
	$cols = 'addby,adddate,col,num,dia,mes,ano,det,hab,loc,lat,lon,alt,prj'; // deve ir numa linha só, sem espaços
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
				if (get('txtnummax') > $v4) { // $v4 = numcol
					// insere vários
					$min = $v4;
					$max = get('txtnummax');
					$q = "insert into $tabela ($cols)\n values ";
					$j = 1; // vai de 1 a count($arrPar)*($max-$min+1) (total de parâmetros passados na query)
					for ($k=$min; $k<=$max; $k++) {
						$q .= '(';
						for ($i=1; $i<=count($arrPar); $i++) {
							$q.="$$j,"; //$q.="$$i,";
							$j++;
						}
						$q = substr($q,0,-1)."),\n";
					}
					$q = substr($q,0,-2)." returning id;";
					echo "<BR><BR>SQL-múltiplos: $q<BR><BR>";
					$arrPar2 = [];
					for ($k=$min; $k<=$max; $k++) {
						for ($i=0; $i<20; $i++) {
							if ($i == 3) {
								$arrPar2[] = $k;
							} else {
								$arrPar2[] = $arrPar[$i];
							}
						}
					}
					print_r($arrPar2);
					$res = pg_query_params($conn,$q,$arrPar2);
					
					//tentaInserirM($tabela,$v4,get('txtnummax'),$q);
					//$newIDs = pg_fetch_array($res,NULL,PGSQL_NUM);
					//echo "SQL Múltiplos: $q<BR>";
					if ($res) {
						$mmValStd = get("mmVal$subWho"."Std");
						if ($mmValStd != '') {
							$newID = pg_fetch_array($res,NULL,PGSQL_NUM)[0];
							print_r($newID);
							$itens = explode(';',$mmValStd);
							$q1 = "insert into $subTabela (addby,adddate,$subLkpField,$subFields) values ";
							for ($k=0; $k<=$max-$min; $k++) {
								$ordem = 1;
								foreach ($itens as $item) {
									$q1.="($v1,'$v2',$newID+$k,$item,$ordem),";
									$ordem++;
								}
							}
							$q1 = substr($q1,0,-1).";";
							echo "query1: $q1<BR><BR>";
							$res1 = pg_query($conn,$q1);
							if ($res1) {
								$divRes = "<div id='divSucesso' style='background-color:#00FF00'>Registros inseridos com sucesso!</div>";
								/*if ($close) { // ?
									$body = "<body onload='fechaLogo($newID)'>";
								}*/
							} else {
								pg_send_query($conn,$q1);
								$res1 = pg_get_result($conn);
								$resErr = pg_result_error($res1);
								$divRes = "<div id='divSucesso' style='background-color:#FF0000'>Erro ao inserir coletores extras (múltiplos registros): $resErr</div>";
							}
						} else {
							$divRes = "<div id='divSucesso' style='background-color:#00FF00'>Registros inseridos com sucesso!</div>";
						}
					} else {
						pg_send_query_params($conn,$q,$arrPar);
						$res = pg_get_result($conn);
						$resErr = pg_result_error($res);
						$divRes = "<div id='divSucesso' style='background-color:#FF0000'>Erro ao inserir registros: $resErr</div>";
					}
				} else {
					insereUm($tabela,$close,$divRes,$body);
				}
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
/*echo "$h1
$divRes
<form id='frmPl' autocomplete='off' method='post' action=''>\n";*/

echo "<dl><dt><label>".txt('num1')."</label></dt><dd><input name='txtnum' type='text' size=10 value='$num' oninput='store(this)' />";
if ($edit == '') {
	echo "<label> ".txt('num2')." </label><input name='txtnummax' type='text' size=10 oninput='store(this)' onblur='txtnummaxblur(this)' />";
}
echo "</dd></dl>";

// indiv: det, coord.planta
echo "<div id='divindiv' style='display:none'>";

echo "<dl><dt><label>".txt('esp.tax')."</label></dt><dd>";
if ($det) { // se já existe uma determinação pra este especímene
	echo "<button type='button' id='btntax' onclick='preCallAdd(\"addDet.php?edit=$det\",800,0,1,1,\"det\",\"esp\",\"det\")'>".txt('esp.taxsel')."</button>";
	echo "<div id='divdetpai' style='display:inline'>";
	if (!empty($detFIXO)) {
		echo "<span id='spndet' style='background-color:lightgreen'> [det = $det] ";
	} else {
		echo "<span id='spndet'> [det = $det] ";
	}
	echo getTaxFromDet($det);
	echo "</span>";
	echo "</div>";
} else {
	echo "<button type='button' id='btntax' onclick='callAdd(\"addDet.php\",800,0,1,1,\"det\",\"esp\",\"det\")'>".txt('esp.taxsel')."</button>";
	echo "<div id='divdetpai' style='display:inline'>";
	echo "<span id='spndet'></span>";
	echo "</div>";
}
echo "<input type='hidden' name='valdet' value='$det' />";
echo "</dd></dl>";

echo "<div id='divCoordPlA'><dl><dt><a href='javascript:popCoords(1,\"divCoordPl\")'>".txt('pl.coords')."</a></dt></dl></div>
<div id='divCoordPlB' style='display:none'><dl><dt><a href='javascript:popCoords(0,\"divCoordPl\")'>".txt('pl.coords')."</a></dt>

<dd>
	<table>
<tr>
	<td style='text-align:left'>
		<label>X </label><input name='txtPosX' type='text' class='short' oninput='store(this)' /> m
		<label>Y </label><input name='txtPosY' type='text' class='short' oninput='store(this)' /> m
	</td>
</tr>
<tr>
	<td>
		<label>".txt('lado')."</label>
			<input type='radio' id='radLadoEsq' name='radLado' value='Esq' onclick='store(this)'>".txt('esq')."
			<input type='radio' id='radLadoDir' name='radLado' value='Dir' onclick='store(this)'>".txt('dir')."
	</td>
</tr>
<tr>
<td style='text-align:left'>
	<label>".txt('ang')."</label><input name='txtAng' type='text' value='' class='short' oninput='store(this)' />°
	<label>".txt('raio')."</label><input name='txtRaio' type='text' value='' class='short' oninput='store(this)' /> m
</td>
</tr>
	</table>
</dd>
</dl>
</div>
</div>"; // divindiv

// comum: data, coletores, localidade, hábitat, projeto
echo "<div id='divcomum' style='display:none'>";

$mmLabelH = txt('pl.markby');
$mmLabel1 = txt('pesdisp');
$mmLabel2 = txt('pessel');
$who = 'cols';
$mmTableName = 'pess';
$mmQuery = 'pess2';
$mmTableLink = 'plcols.col';
include('build_mm.php');

//echo "<dl><dt>".txt('pl.amostra').":</dt><dd>addEsp deveria fazer referência à planta marcada...</dd></dl>";
if (isset($edit)) {
	echo "<dl style='background-color:lightgreen'><dt>".txt('pl.amostra').":</dt><dd>";
	$q = "select e.id,p.abrev,e.num from esp e
	join pess p on p.id = e.col
	where pl = $1";
	$res = pg_query_params($conn,$q,[$edit]);
	if ($res) {
		$texto = '';
		while ($row = pg_fetch_array($res,NULL,PGSQL_NUM)) {
			$texto .= "$row[1] $row[2], ";
		}
		$texto = substr($texto,0,-2);
		echo $texto;
	}
	echo "</dd></dl>";
}

$meses = ['',txt('mes01'),txt('mes02'),txt('mes03'),txt('mes04'),txt('mes05'),txt('mes06'),
	txt('mes07'),txt('mes08'),txt('mes09'),txt('mes10'),txt('mes11'),txt('mes12')];
echo "<dl><dt><label>".txt('data')."</label></dt>
<dd><input type='text' name='txtdia' size=5 placeholder='".txt('dia')."' value='$dia' oninput='store(this)' onkeyup='txtdiakeyup(this,\"selmes\",\"txtano\")' />
<select name='selmes' style='width: 150px' onchange='store(this);selmeschange(this,\"txtdia\",\"txtano\")'>";
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

$cmbLabel = txt('loc');
$cmbTableName = 'loc';
$cmbFieldNames = 'nome';
$cmbCaseSensitive = 0;
$who = 'loc';
$cmbQuery = 'loc';
$cmbPHP = 'addLoc.php';
include('build_cmb.php');

$cmbLabel = txt('hab');
$cmbTableName = 'hab';
$cmbFieldNames = 'nome';
$cmbCaseSensitive = 0;
$who = 'hab';
$cmbQuery = 'hab';
$cmbPHP = 'addHab.php';
include('build_cmb.php');

echoCoords();

$cmbLabel = txt('prj');
$cmbTableName = 'prj';
$cmbFieldNames = 'nome';
$cmbCaseSensitive = 0;
$who = 'prj';
$cmbQuery = 'prj';
$cmbPHP = 'addPrj.php';
include('build_cmb.php');

echo "<input type='hidden' name='hidmmData' value='$hidmmData' />";

$col = 'pl';
// variáveis já marcadas praquela planta
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
echo "<dl><dt><label>".txt('choosefrm').":</label></dt><dd><select id='selForms' onchange='selFormsChange(this,\"".$_SESSION['cfg.frmdest']."\",$edit,\"$col\")'>\n";
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
</div>"; // divcomum

echo "<div id='divOverlay'>
	<div id='divDialog'></div>
</div>
<div id='tooltip'></div>";

echoButtons();
?>
</form>
</body>
</html>
