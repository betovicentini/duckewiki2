<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();
echo "<!DOCTYPE html>
<html lang='BR'>
<head>
<meta charset='UTF-8'>";
$title = txt('imgs');
echo "<title>$title</title>";
if (!empty($_GET['lado'])) {
	$L = $_GET['lado'];
} else {
	$L = 600;
}
if (!empty($_GET['tab'])) {
	$tab = $_GET['tab'];
	switch ($tab) {
		case 'especimenes' :
			$tab = 'esp';
		break;
	}
	
} else {
	$tab = '';
}
if (!empty($_GET['tabid'])) {
	$tabid = $_GET['tabid'];
} else {
	$tabid = '';
}
?>
<link rel="stylesheet" type="text/css" href="cssDuckeWiki.css">
<script src='funcoes.js'></script>
<script type='text/javascript'>
var cnv1, ctx1, cnvZ, ctxZ, l, zoomLevel = 1, lado = Math.round(<?php echo $L ?>/zoomLevel), x, y;
var img = null, imgAnt = null, scrTopAnt = 0;
var rf; // rf = Reduction Factor = width/naturalWidth e.g. 100/1000 = 0.1 = reduziu pra 10% do tamanho original
function imgMouseMove(e) {
	if (!e.ctrlKey) {
		img = e.target;
		var div = document.getElementById('divimgs');
		var rect = img.getBoundingClientRect();
		var rect0 = div.getBoundingClientRect();
		if (img != imgAnt) {
			rf = img.width/img.naturalWidth;
			l = lado*rf;
			cnv1.width = l;
			cnv1.height = l;
			ctx1.strokeStyle = '#F00';
			ctx1.strokeRect(0.5,0.5,l-1.5,l-1.5);
			imgAnt = img;
		}
		cnv1.style.left = (e.pageX-l/2)+'px';
		cnv1.style.top = (e.pageY-l/2)+'px';
		x = Math.round(img.naturalWidth*(e.clientX-l/2-rect.left)/img.width);
		y = Math.round(img.naturalHeight*(e.clientY-l/2-rect.top)/img.height);
		if (img.complete) {
			ctxZ.fillStyle = '#888';
			ctxZ.fillRect(0,0,<?php echo "$L,$L" ?>);
			ctxZ.mozImageSmoothingEnabled = document.getElementById('chkAlias').checked;
			ctxZ.drawImage(img,x,y,lado,lado,0,0,<?php echo "$L,$L" ?>);
		} else {
			console.log('incompleta!');
		}
	}
}
function imgMouseDown(e) {
	if (e.buttons == 1 && e.target == img) {
		zoomLevel*=2;
		if (zoomLevel > 10) {
			zoomLevel = 0.5;
		}
		document.getElementById('spnZoom').innerHTML = zoomLevel+'x';
		lado = Math.round(<?php echo $L ?>/zoomLevel);
		var rect = img.getBoundingClientRect();
		rf = img.width/img.naturalWidth;
		l = lado*rf;
		cnv1.width = l;
		cnv1.height = l;
		ctx1.strokeStyle = '#F00';
		ctx1.strokeRect(0.5,0.5,l-1.5,l-1.5);
		cnv1.style.left = (e.pageX-l/2)+'px';
		cnv1.style.top = (e.pageY-l/2)+'px';
		x = Math.round(img.naturalWidth*(e.clientX-l/2-rect.left)/img.width);
		y = Math.round(img.naturalHeight*(e.clientY-l/2-rect.top)/img.height);
		if (img.complete) {
			ctxZ.fillStyle = '#888';
			ctxZ.fillRect(0,0,<?php echo "$L,$L" ?>);
			ctxZ.mozImageSmoothingEnabled = document.getElementById('chkAlias').checked;
			ctxZ.drawImage(img,x,y,lado,lado,0,0,<?php echo "$L,$L" ?>);
		} else {
			console.log('incompleta!');
		}
	}
}
function cnvDblClick(e) {
	zoomLevel*=2;
	var voltou = false;
	if (zoomLevel > 10) {
		zoomLevel = 0.5;
		voltou = true;
	}
	document.getElementById('spnZoom').innerHTML = zoomLevel+'x';
	lado = Math.round(<?php echo $L ?>/zoomLevel);
	var rect = img.getBoundingClientRect();
	rf = img.width/img.naturalWidth;
	l = lado*rf;
	cnv1.width = l;
	cnv1.height = l;
	ctx1.strokeStyle = '#F00';
	ctx1.strokeRect(0.5,0.5,l-1.5,l-1.5);
	var top = Math.round(cnv1.style.top.substr(0,cnv1.style.top.length-2)*10)/10; // round converte pra número
	var left = Math.round(cnv1.style.left.substr(0,cnv1.style.left.length-2)*10)/10; // round converte pra número
	if (voltou) {
		cnv1.style.top = (top-l/2)+'px';
		cnv1.style.left = (left-l/2)+'px';
		x -= lado/2;
		y -= lado/2;
	} else {
		cnv1.style.top = (top+l/2)+'px';
		cnv1.style.left = (left+l/2)+'px';
		x += lado/2;
		y += lado/2;
	}
	if (img.complete) {
		ctxZ.fillStyle = '#888';
		ctxZ.fillRect(0,0,<?php echo "$L,$L" ?>);
		ctxZ.mozImageSmoothingEnabled = document.getElementById('chkAlias').checked;
		ctxZ.drawImage(img,x,y,lado,lado,0,0,<?php echo "$L,$L" ?>);
	} else {
		console.log('incompleta!');
	}
}
var cnvMouseIsDown = false, cnvMouseX, cnvMouseY;
function cnvMouseMove(e) {
	if (cnvMouseIsDown) {
		var top = Math.round(cnv1.style.top.substr(0,cnv1.style.top.length-2)*10)/10; // round converte pra número
		var left = Math.round(cnv1.style.left.substr(0,cnv1.style.left.length-2)*10)/10; // round converte pra número
		var dx = (e.pageX-cnvMouseX)/zoomLevel;
		var dy = (e.pageY-cnvMouseY)/zoomLevel;
		top -= dy*rf;
		left -= dx*rf;
		cnv1.style.top = top+'px';
		cnv1.style.left = left+'px';
		x -= dx;
		y -= dy;
		if (img.complete) {
			ctxZ.fillStyle = '#888';
			ctxZ.fillRect(0,0,<?php echo "$L,$L" ?>);
			ctxZ.mozImageSmoothingEnabled = document.getElementById('chkAlias').checked;
			ctxZ.drawImage(img,x,y,lado,lado,0,0,<?php echo "$L,$L" ?>);
		} else {
			console.log('incompleta!');
		}
		cnvMouseX = e.pageX;
		cnvMouseY = e.pageY;
	}
}
function cnvMouseDown(e) {
	cnvMouseIsDown = true;
	cnvMouseX = e.pageX;
	cnvMouseY = e.pageY;
	cnvZ.style.cursor = 'grabbing';
}
function cnvMouseUp() {
	cnvMouseIsDown = false;
	cnvZ.style.cursor = 'grab';
}
function lKeyDown(e) {
	if (e.keyCode == 13) {
		var F = document.getElementById('frmImg');
		F.submit();
	}
}
function divScroll(e) {
	if (typeof(cnv1) !== 'undefined') {
		var div = document.getElementById('divimgs');
		var top = Math.round(cnv1.style.top.substr(0,cnv1.style.top.length-2)*10)/10; // round converte pra número
		top += (scrTopAnt-div.scrollTop);
		cnv1.style.top = top+'px';
		scrTopAnt = div.scrollTop;
	}
}
function chkAliasChange(who) {
	ctxZ.fillStyle = '#888';
	ctxZ.fillRect(0,0,<?php echo "$L,$L" ?>);
	ctxZ.mozImageSmoothingEnabled = who.checked;
	ctxZ.drawImage(img,x,y,lado,lado,0,0,<?php echo "$L,$L" ?>);
}
function aoCarregar() {
	cnv1 = document.getElementById('cnv1');
	ctx1 = cnv1.getContext('2d');
	cnvZ = document.getElementById('cnvZoom');
	ctxZ = cnvZ.getContext('2d');
}
/*
	. duplo-clique no canvas muda o zoom
	. checkbox de anti-aliasing deve atualizar a imagem
	. mãozinha de arrastar sobre o canvas
	- tirar o update/close da url
	- restringir o canvas da lupa ao div das imagens?
	- salvar tamanho da imagem em cfg

create table img (
id serial primary key, filename text, w int, h int); -- no futuro outros parâmetros, como tamanho em kB, luminosidade, tom predominante, etc
create table imgitem (
id serial primary key, tab varchar(15), tabid int, imgid int);

*/
</script>
<?php
pullCfg();
echo "</head><body onload='aoCarregar()'>";
$loginErro = login_error($conn);
if ($loginErro) {
	exit("<p>Você não tem autorização de acesso a esta página (erro: $loginErro). Por favor, <a href='index.php' target='main'>volte para a tela inicial e faça o login</a>.</p>");
}
echo "<form id='frmImg' autocomplete='off' method='get' action=''>\n";

if ($tab != '' && $tabid != '') {
	$q = "select i.* from imgitem ii
	left join img i on i.id = ii.imgid
	where ii.tab = $1 and ii.tabid = $2";
	$res = pg_query_params($conn,$q,[$tab,$tabid]);
	if ($res) {
		$arrImgs = [];
		$arrWs = [];
		$arrHs = [];
		while ($row = pg_fetch_array($res,NULL,PGSQL_NUM)) {
			$arrImgs[] = $row[1];
			$arrWs[] = $row[2];
			$arrHs[] = $row[3];
		}
	}
} else {
	$arrImgs = ['cosmos-space-wallpaper-2.jpg','Crescent_Moon_ESO.jpg','Crab_Nebula.jpg','Saturn_eclipse_crop.jpg'];
	$arrWs = [1680,2377,3864,1152];
	$arrHs = [1050,3679,3864,542];
}

$pad = '2px';

echo "<table style='margin:auto'><tr><td style='padding-top:$pad;padding-left:$pad;background-color:#000'>";
echo "<div id='divimgs' style='background-color:#888;max-height:$L"."px;overflow-y:scroll' onscroll='divScroll(event)'>";
$i = 0;
foreach ($arrImgs as $imagem) {
	if ($arrWs[$i] > $arrHs[$i]) { // imagem horizontal
		$w = round($L/2);
		$h = $w*$arrHs[$i]/$arrWs[$i];
	} else { // imagem vertical ou quadrada
		$h = round($L/2);
		$w = $h*$arrWs[$i]/$arrHs[$i];
	}
	$i++;
	echo "<img id='imgthmb$i' src='img/$imagem' width=".$w."px height=".$h."px onmousemove='imgMouseMove(event)' onmousedown='imgMouseDown(event)'><BR>";
}
echo "<canvas id='cnv1' width=30 height=30 style='background-color:rgba(1,1,1,0);position:absolute;pointer-events:none' onmousemove='imgMouseMove(event)'></canvas>";
echo "</div>";
echo "</td><td style='padding:$pad;padding-bottom:0px;background-color:#000'>";
echo "<canvas id='cnvZoom' width=$L height=$L style='background-color:#FFF;cursor:grab' onmousedown='cnvMouseDown(event)' onmousemove='cnvMouseMove(event)' onmouseup='cnvMouseUp()' ondblclick='cnvDblClick(event)'></canvas>"; // MUDAR CURSOR pra mão aberta
echo "</td></tr></table>";
echo "<span style='padding:10px'>zoom: <span id='spnZoom'>1x</span> <img src='icon/question.png' title='clique nas miniaturas para mudar o nível do zoom\n(segure o Ctrl para imobilizar a lupa)'>\n
<input type='checkbox' id='chkAlias' onchange='chkAliasChange(this)' checked>anti-aliasing <img src='icon/question.png' title='suaviza o zoom, reduzindo o efeito quadriculado dos pixels'></span>\n
tamanho: <input name='lado' type='text' value=$L style='width:50px;text-align:center' onkeydown='lKeyDown(event)' /> <img src='icon/question.png' title='digite o tamanho desejado (em pixels) para a lateral do canvas e pressione Enter'>";
?>
</form>
</body>
</html>
