<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();
echo "<!DOCTYPE html>
<html lang='BR'>
<head>
<meta charset='UTF-8'>";
$title = txt('report');
echo "<title>$title</title>";
?>
<link rel="stylesheet" type="text/css" href="cssDuckeWiki.css">
<script src='js/funcoes.js'></script>
<script type='text/javascript'>
function drag(e) {
	e.dataTransfer.setData("text",e.target.id);
}
function allowDrop(e) {
	e.preventDefault();
}
function dropSrc(e) {
	e.preventDefault(); // default seria não mover
	var data = e.dataTransfer.getData("text"); // id do objeto movido
	var child = document.getElementById(data); // objeto movido
	var divDst = document.getElementById('divDst');
	divDst.removeChild(child); // joga fora se destino for divSrc
	if (divDst.childNodes.length <= 3) { // Enter e Tab inseridos no HTML contam como childNodes
		document.getElementById('spnDest').style.display = 'block'; // mostra o texto 'Arraste os itens para cá.'
	}
}
function getDivDrag(elem) {
	if (typeof(elem.id) !== 'undefined') {
		while (elem.id.substr(0,7) != 'divdrag') {
			elem = elem.parentNode;
		}
		return elem;
	}
}
function chkChange(e) {
	if (e.target.id.substr(0,10) == 'chkdragfmt') {
		var fmt = e.target.id.substr(10,1);
		var txt = getDragSub(e.target.parentNode.parentNode,'spndragtxt');
		switch (fmt) {
			case 'N' :
				if (e.target.checked) {
					txt.style.fontWeight = 'bold';
				} else {
					txt.style.fontWeight = 'normal';
				}
				break;
			case 'I' :
				if (e.target.checked) {
					txt.style.fontStyle = 'italic';
				} else {
					txt.style.fontStyle = 'normal';
				}
				break;
			case 'S' :
				if (e.target.checked) {
					txt.style.textDecoration = 'underline';
				} else {
					txt.style.textDecoration = 'none';
				}
				break;
		}
	} else
	if (e.target.id.substr(0,10) == 'raddragfmt') {
		var fmt = e.target.id.substr(10,1);
		var txt = getDragSub(e.target.parentNode.parentNode,'spndragtxt');
		switch (fmt) {
			case 'C' :
				if (!e.target.checked) {
					txt.style.textTransform = 'none';
				} else
				if (e.target.value == 'cb') {
					txt.style.textTransform = 'lowercase';
				} else
				if (e.target.value == 'CA') {
					txt.style.textTransform = 'uppercase';
				} else
				if (e.target.value == 'Ca') {
					txt.style.textTransform = 'capitalize';
				}
				break;
		}
	}
}
function txtKeyUp(e) {
	if (e.keyCode == 38 || e.keyCode == 40) { // seta pra cima | pra baixo
		var selSearch = document.getElementById('selRelatSearch');
		if (selSearch != null) {
			selSearch.focus();
			if (e.keyCode == 38) { // pra cima
				selSearch.selectedIndex = selSearch.length-1;
			} else { // pra baixo
				selSearch.selectedIndex = 0;
			}
		}
	} else {
		divUpd = 'divRelatSearch';
		var divSearch = document.getElementById(divUpd);
		e.target.parentNode.appendChild(divSearch);
		if (e.target.id.substr(7,1) == '2') {
			var url = 'getLike.php?what='+e.target.value+'&query=varquant&who=RelatSearch&m=N';
		} else
		if (e.target.id.substr(7,1) == '3') {
			var url = 'getLike.php?what='+e.target.value+'&query=varquali&who=RelatSearch&m=N';
		}
		conecta(url,update);
	}
}
function txtKeyDown(e) {
	if (e.keyCode == 13) {
		var txt = getDragSub(e.target.parentNode,'spndragtxt');
		e.target.style.display = 'none';
		txt.style.display = 'inline';
		txt.innerHTML = e.target.value;
	} else
	if (e.keyCode == 27) {
		var txt = getDragSub(e.target.parentNode,'spndragtxt');
		e.target.style.display = 'none';
		txt.style.display = 'inline';
		e.target.value = txt.innerHTML; // restaura o txtdrag
	}
}
function dblClick(e) {
	var alvo;
	if (e.target.id.substr(0,10) == 'spndragtxt') { // alvo é o pai do spn
		alvo = e.target.parentNode;
	} else
	if (e.target.id.substr(0,7) == 'divdrag') { // ou o próprio div
		alvo = e.target;
	} else {
		return;
	}
	var txt = getDragSub(alvo,'txtdrag');
	txt.style.display = 'inline';
	txt.focus();
	txt = getDragSub(alvo,'spndragtxt');
	txt.style.display = 'none';
}
function drop(e) {
	e.preventDefault(); // default seria não mover
	var data = e.dataTransfer.getData("text"); // id do objeto movido
	var clone; // vai receber uma cópia do objeto (divSrc -> divDst) ou o próprio objeto (divDst -> divDst)
	if (document.getElementById(data).parentNode.id == 'divSrc') { // divSrc -> divDst
		clone = document.getElementById(data).cloneNode(true);
		var cloneNum=0, cloneId;
		do {
			cloneNum++;
			cloneId = data+'_'+cloneNum;
		} while (document.getElementById(cloneId));
		clone.id = cloneId;
		prepFormRadios(clone); // se houver controles 'radio', permite que sejam desmarcados
		clone.ondblclick = dblClick; // 'dblClick(event)' não faria mais sentido ??
		var i,j;
		for (i=0; i<clone.childNodes.length; i++) { // renomeia cada filho do clone (adiciona _cloneNum)
			if (typeof(clone.childNodes[i].id) !== 'undefined') {
				clone.childNodes[i].id = clone.childNodes[i].id+'_'+cloneNum;
				if (['spndragfmt','spndraggrp','spndragnum'].indexOf(clone.childNodes[i].id.substr(0,10)) >= 0) {
					clone.childNodes[i].style.display = 'inline'; // mostra as opções de formatação
					for (j=0; j<clone.childNodes[i].childNodes.length; j++) { // renomeia cada neto do clone (adiciona _cloneNum)
						if (typeof(clone.childNodes[i].childNodes[j].id) !== 'undefined') {
							clone.childNodes[i].childNodes[j].id = clone.childNodes[i].childNodes[j].id+'_'+cloneNum;
						}
					}
				}
			}
		}
	} else {
		clone = document.getElementById(data); // divDst -> divDst
	}
	var divDst = document.getElementById('divDst');
	var divSrc = document.getElementById('divSrc');
	var spnDst = document.getElementById('spnDest');
	if (e.target == divDst || e.target == spnDst) {
		divDst.insertBefore(clone,null);
	} else
	if (e.target == divSrc) {
	} else {
		divDst.insertBefore(clone,getDivDrag(e.target));
	}
	spnDst.style.display = 'none'; // oculta o texto 'Arraste os itens para cá.'
}
function aoCarregar() {
	//prepFormRadios();
	var elements = document.getElementById("divFrm").getElementsByTagName('input');
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
}
</script>
<style>
p {
	padding-left:10px;
}
</style>
<?php
pullCfg();
echo "</head><body>";
$loginErro = login_error($conn);
if ($loginErro) {
	exit("<p>Você não tem autorização de acesso a esta página (erro: $loginErro). Por favor, <a href='index.php' target='main'>volte para a tela inicial e faça o login</a>.</p>");
}
echo "<h1 style='text-align:center'>$title</h1>\n";
echo "<form id='frmReport' autocomplete='off' method='post' action=''>\n";
?>
<p>Escolha o tipo de campo a inserir no seu relatório...</p>
<div id='divSrc' ondragover='allowDrop(event)' ondrop='dropSrc(event)' style='border:1px solid #000;background-color:#FFF;'>
	<div id='divdrag1' class='drag' draggable='true' ondragstart='drag(event)' style='background-color:#F00'>
		<span id='spndragtxt1'>Texto</span>
		<span id='spndragfmt1' style='display:none;background-color:rgba(255,255,0,0.5)'>
			<input id='chkdragfmtN1' type='checkbox' onchange='chkChange(event)' /><strong>N</strong>
			<input id='chkdragfmtI1' type='checkbox' onchange='chkChange(event)' /><i>I</i>
			<input id='chkdragfmtS1' type='checkbox' onchange='chkChange(event)' /><u>S</u>
			<input id='raddragfmtC1' name='caixa' value='cb' type='radio' onchange='chkChange(event)' />abc
			<input id='raddragfmtC1' name='caixa' value='CA' type='radio' onchange='chkChange(event)' />ABC
			<input id='raddragfmtC1' name='caixa' value='Ca' type='radio' onchange='chkChange(event)' />Abc
		</span>
		<input id='txtdrag1' type='text' onkeydown='txtKeyDown(event)' style='display:none;width:40em' />
	</div>
	<div id='divdrag2' class='drag' draggable='true' ondragstart='drag(event)' style='background-color:#0F0'>
		<input type='hidden' id='hiddragtxt2' />
		<span id='spndragtxt2'>Variáveis quantitativas</span>
		<span id='spndragfmt2' style='display:none;background-color:rgba(255,255,0,0.5)'>
			<input id='chkdragfmtN2' type='checkbox' onchange='chkChange(event)' /><strong>N</strong>
			<input id='chkdragfmtI2' type='checkbox' onchange='chkChange(event)' /><i>I</i>
			<input id='chkdragfmtS2' type='checkbox' onchange='chkChange(event)' /><u>S</u>
			<input id='raddragfmtC2' name='caixa' value='cb' type='radio' onchange='chkChange(event)' />abc
			<input id='raddragfmtC2' name='caixa' value='CA' type='radio' onchange='chkChange(event)' />ABC
			<input id='raddragfmtC2' name='caixa' value='Ca' type='radio' onchange='chkChange(event)' />Abc
		</span>
		<span id='spndragnum2' style='display:none;background-color:rgba(255,0,255,0.5)'>
			<input id='raddragnumM2' name='num' value='med' type='radio' onchange='chkChange(event)' />média
			<input id='raddragnumR2' name='num' value='ran' type='radio' onchange='chkChange(event)' />range
			<input id='raddragnumI2' name='num' value='min' type='radio' onchange='chkChange(event)' />min
			<input id='raddragnumA2' name='num' value='max' type='radio' onchange='chkChange(event)' />max
		</span>
		<input id='txtdrag2' type='text' onkeyup='txtKeyUp(event)' style='display:none;width:40em' />
	</div>
	<div id='divdrag3' class='drag' draggable='true' ondragstart='drag(event)' style='background-color:#00F'>
		<input type='hidden' id='hiddragtxt3' />
		<span id='spndragtxt3'>Variáveis qualitativas</span>
		<span id='spndragfmt3' style='display:none;background-color:rgba(255,255,0,0.5)'>
			<input id='chkdragfmtN3' type='checkbox' onchange='chkChange(event)' /><strong>N</strong>
			<input id='chkdragfmtI3' type='checkbox' onchange='chkChange(event)' /><i>I</i>
			<input id='chkdragfmtS3' type='checkbox' onchange='chkChange(event)' /><u>S</u>
			<input id='raddragfmtC3' name='caixa' value='cb' type='radio' onchange='chkChange(event)' />abc
			<input id='raddragfmtC3' name='caixa' value='CA' type='radio' onchange='chkChange(event)' />ABC
			<input id='raddragfmtC3' name='caixa' value='Ca' type='radio' onchange='chkChange(event)' />Abc
		</span>
		<span id='spndraggrp3' style='display:none;background-color:rgba(0,255,255,0.5)'>
			<input id='chkdraggrp3' type='checkbox' onchange='chkChange(event)' />agrupar por
		</span>
		<input id='txtdrag3' type='text' onkeyup='txtKeyUp(event)' style='display:none;width:40em' />
	</div>
</div>
<p>...e arraste-o para o espaço abaixo. Depois clique duas vezes para editá-lo.</p>
<div id='divDst' ondragover='allowDrop(event)' ondrop='drop(event)' style='border:1px solid #000;background-color:#FFF;padding-bottom:50px;'>
	<span id='spnDest' style='color:#888;padding:5px;display:block;'>Arraste os itens para cá.</span>
</div>
<div id='divRelatSearch'></div>
<?php
echoButtons();
?>
</form>
</body>
</html>
