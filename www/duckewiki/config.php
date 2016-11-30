<?php
/*
 * cfg.corschema setado para 6. Ler valores de cor, e não de cfg.cor...
 * 
 * 
 */
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();
echo "<!DOCTYPE html>
<html lang='BR'>
<head>
<meta charset='UTF-8'>";
$title = txt('cfg');
echo "<title>$title</title>";
?>
<link rel="stylesheet" type="text/css" href="css/cssDuckeWiki.css">
<script src='js/funcoes.js'></script>
<script src='js/cor.js'></script>
<script type='text/javascript'>
/*function aoCarregar(arr) {
	for (var i=0; i<arr.length; i++) {
		Cores.push(arr[i]);
	}
	cnv = document.getElementById('cnvGetCor');
	cnv.width = w;
	cnv.height = h;
	ctx = cnv.getContext("2d");
	for (var i=0; i<arr.length; i++) {
		carregaCor('cnv'+corF[i],'#'+Cores[i]);
	}
}*/
var schema = null;
function updateCor() {
    if (HttpReq.readyState == 4) {
		if (HttpReq.status == 200) {
			//var div = document.getElementById(divUpd);
			var t = HttpReq.responseText;
			alert(t);
		} else {
            alert("Erro: " + HttpReq.statusText);
		}
    }
}
function fechaLogo(id,who,texto) {
	window.opener.handlePopupResult();
	window.close();
}
var Cores = [];
<?php
	$corF = ['bg','but','tit','butbar','frm','tbh','tbo','tbe'];
// ao adicionar um item em corF, inserir também no BD (cfg), ...
// ... no código PHP abaixo (sobrou algum?) e em functions.php.readConfig()
	$corLab = [];
	for ($i=0; $i<sizeof($corF); $i++) {
		$corLab[] = txt('cfg.'.$corF[$i]);
	}
	$varCorF =  "var corF = [";
	foreach ($corF as $cor) {
		$varCorF = $varCorF."'$cor',";
	}
	$varCorF = substr($varCorF,0,-1)."];\n";
	echo $varCorF; // cria a variável no javascript
?>
var which;
/** atualiza o vetor das cores selecionadas (Cores), atualiza a tela (label,cnv,txt)
*/
/*function clicaOk() {
	var cor = HSVtoRGB(hue,corX,corY);
	cor = int2hex(cor.r) + int2hex(cor.g) + int2hex(cor.b);
	Cores[corF.indexOf(which)] = cor;
	var label = document.getElementById('lbl'+which);
	label.innerHTML = '#'+cor;
	carregaCor('cnv'+which,'#'+cor); // colore o retângulo
	var txt = document.getElementsByName('txt'+which)[0];
	txt.value = cor; // atualiza o input hidden para o submit
	clicaCancel();
	var sel = document.getElementById('selcores');
	sel.selectedIndex = 0;
	schema = null;
	//sel.selectedItem?? = 0;
}*/
function restauraCores() {
	// primeiro pergunta se quer salvar o esquema original. Se sim, salva a tela do usuário?
	var F = document.getElementById('frmCfg');
	addHidden(F,'reset','cores');
	F.submit();
}
// array1 (corF) = bg,but,tit,butbar,frm,tbh,tbo,tbe
// array2 (Cores)= 90B090,B0DAB0,A07060,C0C0A0,DCC964,E0AB78,FFFFFF,CCFFCC
// array3 (v) = 'bg,90B090',...
function updateArray(arr1,arr2,v) {
	var i, pos, cod, cor, ind;
	// tira o id+号, depois converte pra array
	pos = v.indexOf('号');
	v = v.substr(pos+1).split(',');
	for (i=0; i<v.length; i++) {
		pos = v[i].indexOf('=');
		cod = v[i].substr(0,pos);
		cor = v[i].substr(pos+1);
		ind = arr1.indexOf(cod);
		arr2[ind] = cor;
	}
}
function cor(nome) {
	return Cores[corF.indexOf(nome)];
}
/** pinta as cores alternadas das tabelas, no modo de visualização */
function altrows(d,cor1,cor2) {
	var i, j, tableElements = d.getElementsByTagName('table');
	for (j=0; j<tableElements.length; j++) {
		var table = tableElements[j];
		if (table.id != 'tblMnu' && table.id != 'tblBut' && table.id != 'tblFoot') {
			var rows = table.getElementsByTagName('tr');
			for (i=0; i<rows.length; i++) {
				if (i % 2 == 0) {
					rows[i].style.backgroundColor = cor1;
				} else {
					rows[i].style.backgroundColor = cor2;
				}
			}
		}
	}
}
function visualiza() {
	/*document.getElementsByTagName('input');
	document.querySelector('form');*/
	var n, d;
	for (n=0; n<2; n++) {
		if (n == 0) {
			d = document;
		} else {
			d = window.opener.document;
			var mnu = d.getElementById('divMnu');
			mnu.style.backgroundColor = '#'+cor('butbar');
			mnu = d.getElementById('trMnu');
			mnu.style.backgroundColor = '#'+cor('butbar');
			mnu = d.getElementById('tdMnuMic');
			mnu.style.backgroundColor = '#'+cor('tit');
			mnu = d.getElementById('tdMnuTit');
			mnu.style.backgroundColor = '#'+cor('tit');
			mnu = d.getElementById('tdMnuBut');
			mnu.style.backgroundColor = '#'+cor('butbar');
			var tbl = d.getElementById('tr1Main');
			if (tbl != null) {
				altrows(d,'#'+cor('tbo'),'#'+cor('tbe'));
				tbl.style.backgroundColor = '#'+cor('tbh');
				tbl = d.getElementById('tr2Main');
				tbl.style.backgroundColor = '#'+cor('tbh');
			}
		}
		d.body.style.background = '#'+cor('bg');
		var i, el = d.getElementsByTagName('button'), r, g, b, bgcor;
		for (i=0; i<el.length; i++) {
			//console.log(el[i]);
			if (el[i].className == 'menu') {
				// merge cores dos botões com cores da barra de botões
				r = hex2int(cor('but').substr(0,2));
				g = hex2int(cor('but').substr(2,2));
				b = hex2int(cor('but').substr(4,2));
				bgcor = 'rgba('+r+','+g+','+b+',0.5)';
				el[i].style.backgroundColor = bgcor;
			} else {
				el[i].style.backgroundColor = '#'+cor('but');
			}
		}
	}
}
function getOther(who) {
	var i, nome, pos;
	for (i=0; i<who.children.length; i++) {
		if (who.children[i].selected) {
			pos = who.children[i].value.indexOf('号');
			schema = who.children[i].value.substr(0,pos);
		}
	}
	updateArray(corF,Cores,who.value); // atualiza corF e Cores
	for (i=0; i<corF.length; i++) {
		carregaCor('cnv'+corF[i],'#'+Cores[i]); // colore os canvas
		document.getElementById('lbl'+corF[i]).innerHTML = '#'+Cores[i];
		document.getElementsByName('txt'+corF[i])[0].value = Cores[i]; // atualiza o input hidden para o submit
	}
}
function updateCor() {
    if (HttpReq.readyState == 4) {
		if (HttpReq.status == 200) {
			if (HttpReq.responseText[0] == '0') {
				alert('Esquema de cores "'+HttpReq.responseText.substr(1)+'" salvo com sucesso!');
			} else {
				alert('Erro ao salvar esquema de cores: '+HttpReq.responseText);
			}
		} else {
            alert("Erro ao salvar esquema de cores: " + HttpReq.statusText);
		}
    }
}
var sorted = 1;
/** salva o esquema de cores associado a um nome e ao usuário que o criou */
function salvaCor() {
	var nome = prompt('Salvar cores como:');
	var selLista = document.getElementById('selcores').children;
	var i;
	var nomeUser = '<?php echo $_SESSION['username']; ?>';
	var jaExiste = false;
	for (i=0; i<selLista.length; i++) {
		if (sorted == 1 && selLista[i].innerHTML == nomeUser+'|'+nome ||
			sorted == 2 && selLista[i].innerHTML == nome+'|'+nomeUser) {
			jaExiste = true;
		}
	}
	if (nome != '' && nome != null) {
		if (!jaExiste) {
			var c = '';
			for (i=0; i<Cores.length; i++) {
				c = c+Cores[i]+',';
			}
			c = c.substr(0,c.length-1);
			var url = 'saveCor.php?nome='+nome+'&col='+corF+'&cor='+c;
			conecta(url,updateCor);
			// depois de salvar, atualizar a lista de cores (com a atual selecionada)
		} else {
			alert('Você já tem um conjunto de cores com este nome!'); // perguntar se quer sobrescrever
		}
	}
}
function btncfgSaveClick(who) {
	var F = who.form;
	if (schema != null) {
		addHidden(F,'schema',schema);
	}
	F.submit();
}
function selSchemaSort() {
	sorted = 3-sorted; // 1 -> 2; 2 -> 1
	divUpd = 'divSchema';
	var url = 'corSchemaSort.php?sort='+sorted;
	conecta(url,update);
}
function aoCarregar(arr) {
	//document.getElementsByName('radexpfs')[0].focus();
	for (var i=0; i<arr.length; i++) {
		Cores.push(arr[i]);
	}
	cnv = document.getElementById('cnvGetCor');
	cnv.width = w;
	cnv.height = h;
	ctx = cnv.getContext("2d");
	for (var i=0; i<arr.length; i++) {
		carregaCor('cnv'+corF[i],'#'+Cores[i]);
	}
}
</script>
<?php
$close = getGet('close');
$h1 = "<h1 style='text-align:center'>$title</h1>";
$user = $_SESSION['user_id'];
$divRes = '';
// opções default
$expfs = 'N';
$frmdest = 'S'; // Self
$show = 'U'; // in Use
$corbg = '90B090';
$corbut = 'B0DAB0';
$cortit = 'A07060';
$corbutbar = 'C0C0A0';
$cortbh = 'AACCAA';
$cortbo = 'FFFFFF';
$cortbe = 'CCFFCC';
$corfrm = 'DCC964';
if (!empty($_POST['reset'])) { // restaura cores originais
	if ($_POST['reset'] == 'cores') {
		$q = "update cfg set (corbg,corbut,cortit,corbutbar,cortbh,cortbo,cortbe,corfrm) = ('$corbg','$corbut','$cortit','$corbutbar','$cortbh','$cortbo','$cortbe','$corfrm') where usr = $user";
		$res = pg_query($conn,$q);
		if ($res) {
			$divRes = "<div id='divSucesso' style='background-color:#00FF00'>Sucesso ao restaurar cores! ($q)</div>";
			if ($close) {
				$body = "<body onload='fechaLogo()'>";
			}
		} else {
			pg_send_query($conn,$q);
			$res = pg_get_result($conn);
			$resErr = pg_result_error($res);
			$divRes = "<div id='divSucesso' style='background-color:#FF0000'>Erro ao restaurar cores ($q): $resErr</div>";
		}
	}
}
// LÊ AS CONFIGURAÇÕES
$q = "select count(*) from cfg where usr = $1";
$res = pg_query_params($conn,$q,[$user]);
if ($res) {
	$row = pg_fetch_array($res,null,PGSQL_NUM);
	// usuário não está na tabela de configurações ainda
	if ($row[0] == 0) {
		//echo "Criando configurações de usuário...<BR>";
		$q = "insert into cfg (usr) values ($1)";
		$res = pg_query_params($conn,$q,[$user]);
		if ($res) {
			//echo "...configurações criadas com sucesso.<BR>";
		}
	}
	//echo "Lendo configurações de usuário...<BR>";
	if (readConfig()) {
		$expfs = $_SESSION['cfg.expfs'];
		$frmdest = $_SESSION['cfg.frmdest'];
		$show = $_SESSION['cfg.show'];
		foreach ($corF as $cor) {
			${"cor$cor"} = $_SESSION["cfg.cor$cor"];
		}
		//echo "...configurações lidas com sucesso.<BR>";
	} else {
		//echo "...erro ao ler configurações.<BR>";
	}
}
$body = "<body onload='aoCarregar([";
foreach ($corF as $cor) {
	$body = $body.'"'.${"cor$cor"}.'",';
}
//$body = substr($body,0,-1)."])' onmouseup='mouseUp()'>";
$body = substr($body,0,-1)."])'>";
// SETA AS CONFIGURAÇÕES
if (!empty($post) && empty($_POST['reset'])) {
	$v1 = getPost('radexpfs',$expfs);
	$v2 = getPost('radfrmdest',$frmdest);
	$v3 = getPost('radshow',$show);
	$schema = null;
	$i = 5; // último $v acima + 1 (corschema) + 1 (começa do próximo)
	$q = "update cfg set (expfs,frmdest,show,corschema";
	if (isset($_POST['schema'])) {
		$schema = $_POST['schema'];
	}
	foreach ($corF as $cor) {
		${"v$i"} = getPost("txt$cor",${"cor$cor"});
		$i++;
		$q = $q.",cor$cor";
	}
	$q = $q.") = (";
	for ($k=1; $k<$i; $k++) {
		if ($k == 4) { // último $v acima + 1
			if ($schema == null) {
				$q .= 'null,';
			} else {
				$q .= $schema.',';
			}
		} else {
			$q .= "'".${"v$k"}."',";
		}
	}
	$q = substr($q,0,-1).") where usr = $user";
	echo "$q<BR><BR>";
	$res = pg_query($conn,$q);
	if ($res) {
		$divRes = "<div id='divSucesso' style='background-color:#00FF00'>Sucesso ao atualizar configuração! ($q)</div>";
		if ($close) {
			$body = "<body onload='fechaLogo()'>";
			//$body = "<body>";
		}
	} else {
		pg_send_query($conn,$q);
		$res = pg_get_result($conn);
		$resErr = pg_result_error($res);
		$divRes = "<div id='divSucesso' style='background-color:#FF0000'>Erro ao atualizar configuração ($q): $resErr</div>";
	}
	// LÊ AS CONFIGURAÇÕES
	$q = "select count(*) from cfg where usr = $1";
	$res = pg_query_params($conn,$q,[$user]);
	if ($res) {
		$row = pg_fetch_array($res,null,PGSQL_NUM);
		// usuário não está na tabela de configurações ainda
		if ($row[0] == 0) {
			//echo "Criando configurações de usuário...<BR>";
			$q = "insert into cfg (usr) values ($1)";
			$res = pg_query_params($conn,$q,[$user]);
			if ($res) {
				//echo "...configurações criadas com sucesso.<BR>";
			}
		}
		//echo "Lendo configurações de usuário...<BR>";
		if (readConfig()) {
			$expfs = $_SESSION['cfg.expfs'];
			$frmdest = $_SESSION['cfg.frmdest'];
			$show = $_SESSION['cfg.show'];
			foreach ($corF as $cor) {
				${"cor$cor"} = $_SESSION["cfg.cor$cor"];
			}
			//echo "...configurações lidas com sucesso.<BR>";
		} else {
			//echo "...erro ao ler configurações.<BR>";
		}
	}
}
pullCfg();
echo "</head>
$body";
$loginErro = login_error($conn);
if ($loginErro) {
	exit("<p>Você não tem autorização de acesso a esta página (erro: $loginErro). Por favor, <a href='index.php' target='main'>volte para a tela inicial e faça o login</a>.</p>");
}
echo "$h1";
echo $divRes;
echo "<form id='frmCfg' autocomplete='off' method='post' action=''>\n<dl>";

echo "<dt><label>".txt('cfg.expf')."</label></dt><dd>";
$expfschkS = $expfs == 'S' ? ' checked' : '';
$expfschkN = $expfs == 'N' ? ' checked' : '';
echo "<input type='radio' name='radexpfs' value='S' onclick='store(this)'$expfschkS>".txt('sim');
echo " <input type='radio' name='radexpfs' value='N' onclick='store(this)'$expfschkN>".txt('não');
echo "</dd>";

echo "<dt><label>".txt('cfg.frmd')."</label></dt><dd>";
$frmdestchkS = $frmdest == 'S' ? ' checked' : '';
$frmdestchkT = $frmdest == 'T' ? ' checked' : '';
$frmdestchkW = $frmdest == 'W' ? ' checked' : '';
echo "<input type='radio' name='radfrmdest' value='S' onclick='store(this)'$frmdestchkS>".txt('cfg.self');
echo " <input type='radio' name='radfrmdest' value='T' onclick='store(this)'$frmdestchkT>".txt('cfg.novaba');
echo " <input type='radio' name='radfrmdest' value='W' onclick='store(this)'$frmdestchkW>".txt('cfg.novjan');
echo "</dd>";
echo "<dt><label>".txt('cfg.show')."</label></dt><dd>";
$showchkA = $show == 'A' ? ' checked' : '';
$showchkU = $show == 'U' ? ' checked' : '';
echo "<input type='radio' name='radshow' value='A' onclick='store(this)'$showchkA>".txt('cfg.all');
echo " <input type='radio' name='radshow' value='U' onclick='store(this)'$showchkU>".txt('cfg.use');
echo "</dd>";

// cores
$w = 50;
$h = 30;
echo "<fieldset><legend>".txt('cfg.cor')."</legend>";
for ($i=0; $i<sizeof($corF); $i++) { // para cada cor selecionável (fundo, botões...)
	echo "<dt>$corLab[$i]</dt><dd><canvas width=$w height=$h id='cnv$corF[$i]' onclick='getCor(\"$corF[$i]\")'></canvas>
		<label id='lbl$corF[$i]' onclick='lblClick(this)'>#".${"cor$corF[$i]"}."</label>
		<input type='hidden' name='txt$corF[$i]' /></dd>\n";
	if ($i == 4) {
		echo "<fieldset><legend>".txt('tab')."</legend>";
	}
}
echo "</fieldset><BR>
<dt>&nbsp;</dt><dd><button type='button' onclick='salvaCor()'>Salvar esquema de cores</button></dd>";
// Seletor de esquema de cores
echo "<div id='divSchema'>";
include 'corSchemaSort.php';
echo "</div>";
// visualizar
echo "<dt>&nbsp;</dt><dd><button type='button' onclick='visualiza()'>Visualizar</button></dd>";
// restaurar
echo "<dt>".txt('cfg.orig')."</dt><dd><button type='button' onclick='restauraCores()'>".txt('cfg.rest')."</button></dd>
</fieldset>";

echo "</dl>";

echo "<div id='divCor' style='position:absolute;display:none;' onmousedown='cnvMouseDown(event,1)' onmousemove='cnvMouseMove(event)'>
	<canvas id='cnvGetCor'></canvas>
</div>";
echo "<div class='wrapper'>
<button id='btnSave' type='button' onclick='btncfgSaveClick(this)'>".txt('addSave')."</button>
<button id='btnReset' type='button' onclick='btnResetClick(this)'>".txt('addClear')."</button>
<button id='btnCancel' type='button' onclick='btncfgCancelClick()'>".txt('addCancel')."</button>
<button id='btnStore' type='button' onclick='seeStore()'>".txt('addLookSt')."</button>
<button id='btnClear' type='button' onclick='clearStore()'>".txt('addClearSt')."</button>
</div>";
?>
</form>
</body>
</html>
