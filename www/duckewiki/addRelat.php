<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();
echo "<!DOCTYPE html>
<html lang='BR'>
<head>
<meta charset='UTF-8'>";
$edit = getGet('edit');
$tabela = 'relat';
if ($edit == '') {
	$title = txt('novo').' '.txt('relat');
	emptyRow($tabela);
} else {
	$title = txt('edit').' '.txt('relat');
	updateRow($tabela,$edit);
}
echo "<title>$title</title>";
?>
<link rel="stylesheet" type="text/css" href="cssDuckeWiki.css">
<script src='funcoes.js'></script>
<script type='text/javascript'>
/*
- Quando carrega do Storage, radio selecionado não apaga no primeiro, mas no segundo clique
- Tabelas relat e relatf
- relat (id, nome, addby, adddate)
- relatf (id, relat, tipo, n, i, s, val, case, tnum, grp, ordem)
*/
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
	//divDst.removeChild(child); // joga fora se destino for divSrc
	child.parentNode.removeChild(child);
	if (divDst.children.length <= 1) {
		document.getElementById('spnDest').style.display = 'block'; // mostra o texto 'Arraste os itens para cá.'
	}
	updateStorage();
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
	} else
	if (e.target.id.substr(0,10) == 'chkdraggrp') {
		if (e.target.checked) {
			getDivDrag(e.target).style.paddingBottom = '50px';
		} else {
			getDivDrag(e.target).style.paddingBottom = '';
		}
	}
	updateStorage();
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
	//console.log('keydown');
	if (e.keyCode == 13) {
		//console.log('keydown13');
		var txt = getDragSub(e.target.parentNode,'spndragtxt');
		var num = txt.id.substr(10);
		e.target.style.display = 'none';
		txt.style.display = 'inline';
		txt.innerHTML = e.target.value;
		// atualiza o componente hidden
		var hid = document.getElementById('hiddragtxt'+num);
		hid.value = txt.innerHTML;
		updateStorage();
	} else
	if (e.keyCode == 27) {
		//console.log('keydown27');
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
/** ao soltar uma caixa (em src ou em dst -> neste caso cria um clone) */
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
		for (i=0; i<clone.children.length; i++) { // renomeia cada filho do clone (adiciona _cloneNum)
			//if (typeof(clone.children[i].id) !== 'undefined') {
			if (clone.children[i].id != '') {
				clone.children[i].id = clone.children[i].id+'_'+cloneNum;
				if (clone.children[i].name != '') {
					clone.children[i].name = clone.children[i].name+'_'+cloneNum;
				}
				if (['spndragfmt','spndraggrp','spndragnum','spndragshn'].indexOf(clone.children[i].id.substr(0,10)) >= 0) {
					clone.children[i].style.display = 'inline'; // mostra as opções de formatação
					for (j=0; j<clone.children[i].children.length; j++) { // renomeia cada neto do clone (adiciona _cloneNum)
						if (clone.children[i].children[j].id != '') {
							clone.children[i].children[j].id = clone.children[i].children[j].id+'_'+cloneNum;
						}
						if (clone.children[i].children[j].name != '') {
							clone.children[i].children[j].name = clone.children[i].children[j].name+'_'+cloneNum;
						}
					}
				} else
				if (clone.children[i].id.substr(0,10) == 'spndragtxt') {
					clone.children[i].name = clone.children[i].id;
				}
			}
		}
	} else {
		clone = document.getElementById(data); // divDst -> divDst
	}
	var divDst = document.getElementById('divDst');
	var divSrc = document.getElementById('divSrc');
	var spnDst = document.getElementById('spnDest');
	//alert(e.target.id);
	if (e.target == divDst || e.target == spnDst) {
		divDst.insertBefore(clone,null);
	} else
	if (getDivDrag(e.target).id.substr(0,8) == 'divdrag3' &&
		getDivDrag(e.target).style.paddingBottom == '50px') {
		getDivDrag(e.target).insertBefore(clone,null);
	} else
	if (e.target != divSrc) {
		divDst.insertBefore(clone,getDivDrag(e.target));
	}
	spnDst.style.display = 'none'; // oculta o texto 'Arraste os itens para cá.'
	updateStorage();
}
function updateStorage() {
	var i, j, div = document.getElementById('divDst'), k=0, ordem=0;
	// limpa valores antigos
	while (localStorage.getItem('relat.dst'+k) !== null) {
		localStorage.removeItem('relat.dst'+(k++));
	}
	k=0;
	// insere novos valores
	var dst = '', id, num, pos, num2, tipo, txt, hid, N, I, S, Case, qrySel, tiponum, grp, showN, L;
	for (i=0; i<div.children.length; i++) {
		if (div.children[i].tagName.toLowerCase() == 'div') {
			ordem++;
			id = div.children[i].id;			// divdrag1_1
			num = id.substr(7);
			pos = num.indexOf('_');
			num2 = num.substr(pos);
			tipo = parseInt(num.substr(0,1),10);
			txt = document.getElementById('spndragtxt'+num).innerHTML;
			tiponum = '';
			grp = '';
			showN = '';
			if ([2,3].indexOf(tipo) >= 0) {
				hid = document.getElementById('hiddragtxt'+num).value;
				if (tipo == 2) {
					qrySel = document.querySelector('input[name = "num'+num2+'"]:checked');
					if (qrySel) {
						tiponum = qrySel.value;
					}
					showN = document.getElementById('chkdragshn'+num).checked;
				} else
				if (tipo == 3) {
					grp = document.getElementById('chkdraggrp'+num).checked;
				}
			} else {
				hid = '';
			}
			N = document.getElementById('chkdragfmtN'+num).checked;
			I = document.getElementById('chkdragfmtI'+num).checked;
			S = document.getElementById('chkdragfmtS'+num).checked;
			if (document.getElementById('raddragfmtCa'+num).checked) {
				Case = 1;
			} else
			if (document.getElementById('raddragfmtCb'+num).checked) {
				Case = 2;
			} else
			if (document.getElementById('raddragfmtCc'+num).checked) {
				Case = 3;
			} else {
				Case = 0;
			}
			L = {'tipo':tipo,'txt':txt,'val':hid,'n':N,'i':I,'s':S,'case':Case,'tnum':tiponum,'grp':grp,'ordem':ordem,'shown':showN};
			localStorage.setItem('relat.dst'+(k++),JSON.stringify(L));
			if (grp) {
				for (j=0; j<div.children[i].children.length; j++) {
					if (div.children[i].children[j].tagName.toLowerCase() == 'div') {
						ordem++;
						id = div.children[i].children[j].id;
						num = id.substr(7);
						pos = num.indexOf('_');
						num2 = num.substr(pos);
						tipo = parseInt(num.substr(0,1),10);
						txt = document.getElementById('spndragtxt'+num).innerHTML;
						tiponum = '';
						//grp = '';
						showN = '';
						if ([2,3].indexOf(tipo) >= 0) {
							hid = document.getElementById('hiddragtxt'+num).value;
							if (tipo == 2) {
								qrySel = document.querySelector('input[name = "num'+num2+'"]:checked');
								if (qrySel) {
									tiponum = qrySel.value;
								}
								showN = document.getElementById('chkdragshn'+num).checked;
							} else
							if (tipo == 3) {
								//grp = document.getElementById('chkdraggrp'+num).checked;
							}
						} else {
							hid = '';
						}
						N = document.getElementById('chkdragfmtN'+num).checked;
						I = document.getElementById('chkdragfmtI'+num).checked;
						S = document.getElementById('chkdragfmtS'+num).checked;
						if (document.getElementById('raddragfmtCa'+num).checked) {
							Case = 1;
						} else
						if (document.getElementById('raddragfmtCb'+num).checked) {
							Case = 2;
						} else
						if (document.getElementById('raddragfmtCc'+num).checked) {
							Case = 3;
						} else {
							Case = 0;
						}
						L = {'tipo':tipo,'txt':txt,'val':hid,'n':N,'i':I,'s':S,'case':Case,'tnum':tiponum,'grp':'','ordem':ordem,'shown':showN};
						localStorage.setItem('relat.dst'+(k-1)+'_'+ordem,JSON.stringify(L));
					}
				}
			}
		}
	}
}
function retrieveStorage() {
	var i,j,k=0, value, clone, num, txt;
	var divDst = document.getElementById('divDst');
	var cloneNum, cloneId;
	while (localStorage.getItem('relat.dst'+k) !== null) {
		value = JSON.parse(localStorage.getItem('relat.dst'+(k++)));
		clone = document.getElementById('divdrag'+value.tipo).cloneNode(true);
		cloneNum=0;
		do {
			cloneNum++;
			//cloneId = 'relat.dst'+k+'_'+cloneNum;
			cloneId = 'divdrag'+value.tipo+'_'+cloneNum;
		} while (document.getElementById(cloneId));
		num = value.tipo+'_'+cloneNum;
		clone.id = cloneId;
		clone.ondblclick = dblClick; // 'dblClick(event)' não faria mais sentido ??
		for (i=0; i<clone.children.length; i++) { // renomeia cada filho do clone (adiciona _cloneNum)
			//if (typeof(clone.children[i].id) !== 'undefined') {
			if (clone.children[i].id !== '') {
				clone.children[i].id = clone.children[i].id+'_'+cloneNum;
				if (clone.children[i].name != '') {
					clone.children[i].name = clone.children[i].name+'_'+cloneNum;
				}
				if (['spndragfmt','spndraggrp','spndragnum','spndragshn'].indexOf(clone.children[i].id.substr(0,10)) >= 0) {
					clone.children[i].style.display = 'inline'; // mostra as opções de formatação
					for (j=0; j<clone.children[i].children.length; j++) { // renomeia cada neto do clone (adiciona _cloneNum)
						if (clone.children[i].children[j].id != '') {
							clone.children[i].children[j].id = clone.children[i].children[j].id+'_'+cloneNum;
						}
						if (clone.children[i].children[j].name != '') {
							clone.children[i].children[j].name = clone.children[i].children[j].name+'_'+cloneNum;
						}
					}
				} else
				if (clone.children[i].id.substr(0,10) == 'spndragtxt') {
					clone.children[i].name = clone.children[i].id;
				}
			}
		}
		prepFormRadios(clone); // se houver controles 'radio', permite que sejam desmarcados
		divDst.insertBefore(clone,null);
		document.getElementById('spndragtxt'+num).innerHTML = value.txt;
		document.getElementById('hiddragtxt'+num).value = value.txt;
		if ([2,3].indexOf(value.tipo) >= 0) {
			document.getElementById('hiddragtxt'+num).value = value.val;
			if (value.tipo == 2) {
				switch (value.tnum) {
					case 'med' :
						document.getElementById('raddragnumM2_'+cloneNum).checked = true;
						break;
					case 'ran' :
						document.getElementById('raddragnumR2_'+cloneNum).checked = true;
						break;
					case 'min' :
						document.getElementById('raddragnumI2_'+cloneNum).checked = true;
						break;
					case 'max' :
						document.getElementById('raddragnumA2_'+cloneNum).checked = true;
						break;
				}
				document.getElementById('chkdragshn2_'+cloneNum).checked = value.shown;
			} else
			if (value.tipo == 3) {
				document.getElementById('chkdraggrp3_'+cloneNum).checked = value.grp;
				if (value.grp) {
					document.getElementById('divdrag3_'+cloneNum).style.paddingBottom = '50px';
				}
			}
		}
		txt = getDragSub(clone,'spndragtxt');
		document.getElementById('chkdragfmtN'+num).checked = value.n;
		if (value.n) {
			txt.style.fontWeight = 'bold';
		}
		document.getElementById('chkdragfmtI'+num).checked = value.i;
		if (value.i) {
			txt.style.fontStyle = 'italic';
		}
		document.getElementById('chkdragfmtS'+num).checked = value.s;
		if (value.s) {
			txt.style.textDecoration = 'underline';
		}
		switch (value.case) {
			case 1 :
				document.getElementById('raddragfmtCa'+num).checked = true;
				txt.style.textTransform = 'lowercase';
				break;
			case 2 :
				document.getElementById('raddragfmtCb'+num).checked = true;
				txt.style.textTransform = 'uppercase';
				break;
			case 3 :
				document.getElementById('raddragfmtCc'+num).checked = true;
				txt.style.textTransform = 'capitalize';
				break;
		}
	}
	if (k > 0) {
		var spnDst = document.getElementById('spnDest');
		spnDst.style.display = 'none'; // oculta o texto 'Arraste os itens para cá.'
	}
}
function btnRelatSaveClick() {
	var i, div = document.getElementById('divDst'), k=0;
	k=0;ordem=0;
	var dst = '', id, num, pos, num2, tipo, txt, hid, N, I, S, Case, qrySel, tiponum, grp, showN, L;
	var F = frmRelat;
	for (i=0; i<div.children.length; i++) {
		if (div.children[i].tagName.toLowerCase() == 'div') {
			ordem++;
			id = div.children[i].id;
			num = id.substr(7);
			pos = num.indexOf('_');
			num2 = num.substr(pos);
			tipo = parseInt(num.substr(0,1),10);
			txt = document.getElementById('spndragtxt'+num).innerHTML;
			tiponum = '';
			grp = '';
			showN = '';
			if ([2,3].indexOf(tipo) >= 0) {
				hid = document.getElementById('hiddragtxt'+num).value;
				if (tipo == 2) {
					qrySel = document.querySelector('input[name = "num'+num2+'"]:checked');
					if (qrySel) {
						tiponum = qrySel.value;
					}
					showN = document.getElementById('chkdragshn'+num).checked;
				} else
				if (tipo == 3) {
					grp = document.getElementById('chkdraggrp'+num).checked;
				}
			} else {
				hid = txt;
			}
			N = document.getElementById('chkdragfmtN'+num).checked;
			I = document.getElementById('chkdragfmtI'+num).checked;
			S = document.getElementById('chkdragfmtS'+num).checked;
			if (document.getElementById('raddragfmtCa'+num).checked) {
				Case = 1;
			} else
			if (document.getElementById('raddragfmtCb'+num).checked) {
				Case = 2;
			} else
			if (document.getElementById('raddragfmtCc'+num).checked) {
				Case = 3;
			} else {
				Case = 0;
			}
			//L = {'tipo':tipo,'txt':txt,'val':hid,'n':N,'i':I,'s':S,'case':Case,'tnum':tiponum,'grp':grp,'ordem':ordem};
			L = {'tipo':tipo,'val':hid,'n':N,'i':I,'s':S,'case':Case,'tnum':tiponum,'grp':grp,'ordem':ordem,'shown':showN};
			addHidden(F,'all'+ordem,JSON.stringify(L));
			//localStorage.setItem('relat.dst'+(k++),JSON.stringify(L));
		}
	}
	F.submit();
}
function aplicaAoFiltro() {
	var sel = document.getElementById('selFiltro');
	if (sel.value != '0') {
		var i, div = document.getElementById('divDst'), k=0, ordem=0;
		var dst = '', id, num, pos, num2, tipo, txt, hid, N, I, S, Case, qrySel, tiponum, grp, showN, L, Ls='{';
		// converte campos do relatório em texto, para submeter a relFiltro.php (via AJAX)
		for (i=0; i<div.children.length; i++) {
			if (div.children[i].tagName.toLowerCase() == 'div') {
				ordem++;
				id = div.children[i].id;			// divdrag1_1
				num = id.substr(7);
				pos = num.indexOf('_');
				num2 = num.substr(pos);
				tipo = parseInt(num.substr(0,1),10);
				txt = document.getElementById('spndragtxt'+num).innerHTML;
				tiponum = '';
				grp = '';
				showN = '';
				if ([2,3].indexOf(tipo) >= 0) {
					hid = document.getElementById('hiddragtxt'+num).value;
					if (tipo == 2) {
						qrySel = document.querySelector('input[name = "num'+num2+'"]:checked');
						if (qrySel) {
							tiponum = qrySel.value;
						}
						showN = document.getElementById('chkdragshn'+num).checked;
					} else
					if (tipo == 3) {
						grp = document.getElementById('chkdraggrp'+num).checked;
					}
				} else {
					hid = '';
				}
				N = document.getElementById('chkdragfmtN'+num).checked;
				I = document.getElementById('chkdragfmtI'+num).checked;
				S = document.getElementById('chkdragfmtS'+num).checked;
				if (document.getElementById('raddragfmtCa'+num).checked) {
					Case = 1;
				} else
				if (document.getElementById('raddragfmtCb'+num).checked) {
					Case = 2;
				} else
				if (document.getElementById('raddragfmtCc'+num).checked) {
					Case = 3;
				} else {
					Case = 0;
				}
				L = {'tipo':tipo,'txt':txt,'val':hid,'n':N,'i':I,'s':S,'case':Case,'tnum':tiponum,'grp':grp,'ordem':ordem,'shown':showN};
				Ls = Ls+'"campo'+i+'":'+JSON.stringify(L)+',';
			}
		}
		Ls = Ls.substr(0,Ls.length-1)+'}';
		divUpd = 'divPreview';
		var url = 'relFiltro.php?filtro='+sel.value+'&fields='+Ls;
		conecta(url,update);
	}
}
function aoCarregar(edit) {
	if (edit == '') {
		retrieveStorage();
		refill('frmRelat');
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
$edit = getGet('edit');
echo "</head><body onload='aoCarregar(\"$edit\")'>";
$loginErro = login_error($conn);
if ($loginErro) {
	exit("<p>Você não tem autorização de acesso a esta página (erro: $loginErro). Por favor, <a href='index.php' target='main'>volte para a tela inicial e faça o login</a>.</p>");
}
$divRes = '';
if (!empty($post)) {
	//print_r($post);
	$v1 = $_SESSION['user_id'];
	$v2 = date('d/m/Y H:i:s');
	$v3 = get('txtnome');
	$inserefields = false;
	if ($edit) {
		$newID = $edit;
		$q = "select nome from relat where id = $1";
		$res = pg_query_params($conn,$q,[$edit]);
		if ($res) {
			if ($row = pg_fetch_array($res,NULL,PGSQL_NUM)) {
				if ($v3 != $row[0]) { // mudou o nome
					$q = "update relat set nome = $1 where id = $2";
					$res = pg_query_params($conn,$q,[$v3,$edit]);
					if ($res) {
						// funcionou
					} else {
						// erro
					}
				}
			}
		}
		$q = "delete from relatf where relat = $1";
		$res = pg_query_params($conn,$q,[$edit]);
		if ($res) {
			$inserefields = true;
		} else {
			// erro ao excluir fields anteriores
		}
	} else {
		// não está editando -> insere
		$q = "insert into relat (addby,adddate,nome) values ($1,$2,$3) returning id";
		$res = pg_query_params($conn,$q,[$v1,$v2,$v3]);
		if ($res) {
			$newID = pg_fetch_array($res,NULL,PGSQL_NUM)[0];
			$inserefields = true;
		}
	}
	if ($inserefields) {
		$q = "insert into relatf (addby,adddate,relat,tipo,val,n,i,s,cas,tnum,grp,ordem) values ";
		$ordem = 1;
		while (!empty($post["all$ordem"])) {
			$item = json_decode($post["all$ordem"]);
			print_r($item);
			$v3 = $item->tipo;
			$v4 = $item->val ? $item->val : '';
			$v5 = $item->n ? $item->n : '0';
			$v6 = $item->i ? $item->i : '0';
			$v7 = $item->s ? $item->s : '0';
			$v8 = $item->case;
			$v9 = $item->tnum ? $item->tnum : '';
			$v10 = $item->grp ? 'S' : 'N';
			$q .= "($v1,'$v2',$newID,$v3,'$v4','$v5','$v6','$v7',$v8,'$v9','$v10',$ordem), ";
			$ordem++;
		}
		$q = substr($q,0,-2).";";
		echo "$q<BR><BR>";
		$res = pg_query($conn,$q);
		if ($res) {
			$divRes = "<div id='divSucesso' style='background-color:#00FF00'>Registro inserido com sucesso!</div>";
		} else {
			$divRes = "<div id='divSucesso' style='background-color:#FF0000'>Erro ao inserir registro!</div>";
		}
	}
	/*foreach($post as $key => $val) {
		if (substr($key,0,10) == 'hiddragtxt' && strlen($key) > 11) {
			
		}
	}
	$v4 = get('txturl');
	$v5 = get('txtfinanc');
	$v6 = get('txtprocs');
	$v7 = get('txtlogo');
	$v8 = get('valfrmmrf');
	$v9 = get('valfrmhab');
	$arrPar = [];
	for ($i=1; $i<=9; $i++) {
		$arrPar[] = ${"v$i"};
	}*/
}
echoHeader();
/*echo "<h1 style='text-align:center'>$title</h1>\n$divRes\n";
echo "<form id='frmRelat' autocomplete='off' method='post' action=''>\n";*/
echo "<dl>
<dt>Nome do Relatório</dt>
<dd><input type='text' id='txtnome' name='txtnome' oninput='store(this)' value='$nome' /></dd>";
?>
</dl>
<p>Escolha o tipo de campo a inserir no seu relatório...</p>
<div id='divSrc' ondragover='allowDrop(event)' ondrop='dropSrc(event)' style='border:1px solid #000;background-color:#FFF;'>
	<div id='divdrag1' class='drag' draggable='true' ondragstart='drag(event)' style='background-color:#F00'>
		<span id='spndragtxt1'>Texto</span>
		<input id='hiddragtxt1' name='hiddragtxt1' type='hidden' />
		<span id='spndragfmt1' style='display:none;background-color:rgba(255,255,0,0.5)'>
			<input id='chkdragfmtN1' name='chkdragfmtN1' type='checkbox' onchange='chkChange(event)' /><strong>N</strong>
			<input id='chkdragfmtI1' name='chkdragfmtI1' type='checkbox' onchange='chkChange(event)' /><i>I</i>
			<input id='chkdragfmtS1' name='chkdragfmtS1' type='checkbox' onchange='chkChange(event)' /><u>S</u>
			<input id='raddragfmtCa1' name='caixa1' value='cb' type='radio' onchange='chkChange(event)' />abc
			<input id='raddragfmtCb1' name='caixa1' value='CA' type='radio' onchange='chkChange(event)' />ABC
			<input id='raddragfmtCc1' name='caixa1' value='Ca' type='radio' onchange='chkChange(event)' />Abc
		</span>
		<input id='txtdrag1' type='text' onkeydown='txtKeyDown(event)' style='display:none;width:40em' />
	</div>
	<div id='divdrag2' class='drag' draggable='true' ondragstart='drag(event)' style='background-color:#0F0'>
		<input type='hidden' id='hiddragtxt2' name='hiddragtxt2' />
		<span id='spndragtxt2'>Variáveis quantitativas</span>
		<span id='spndragfmt2' style='display:none;background-color:rgba(255,255,0,0.5)'>
			<input id='chkdragfmtN2' name='chkdragfmtN2' type='checkbox' onchange='chkChange(event)' /><strong>N</strong>
			<input id='chkdragfmtI2' name='chkdragfmtI2' type='checkbox' onchange='chkChange(event)' /><i>I</i>
			<input id='chkdragfmtS2' name='chkdragfmtS2' type='checkbox' onchange='chkChange(event)' /><u>S</u>
			<input id='raddragfmtCa2' name='caixa2' value='cb' type='radio' onchange='chkChange(event)' />abc
			<input id='raddragfmtCb2' name='caixa2' value='CA' type='radio' onchange='chkChange(event)' />ABC
			<input id='raddragfmtCc2' name='caixa2' value='Ca' type='radio' onchange='chkChange(event)' />Abc
		</span>
		<span id='spndragnum2' style='display:none;background-color:rgba(255,0,255,0.5)'>
			<input id='raddragnumM2' name='num' value='m' type='radio' onchange='chkChange(event)' />média
			<input id='raddragnumR2' name='num' value='r' type='radio' onchange='chkChange(event)' />range
			<input id='raddragnumI2' name='num' value='i' type='radio' onchange='chkChange(event)' />min
			<input id='raddragnumA2' name='num' value='a' type='radio' onchange='chkChange(event)' />max
		</span>
		<span id='spndragshn2' style='display:none;background-color:rgba(0,255,255,0.5)'>
			<input id='chkdragshn2' name='chkdragshn2' type='checkbox' onchange='chkChange(event)' />mostrar N
		</span>
		<input id='txtdrag2' type='text' onkeyup='txtKeyUp(event)' style='display:none;width:40em' />
	</div>
	<div id='divdrag3' class='drag' draggable='true' ondragstart='drag(event)' style='background-color:#00F'>
		<input type='hidden' id='hiddragtxt3' name='hiddragtxt3' />
		<span id='spndragtxt3'>Variáveis qualitativas</span>
		<span id='spndragfmt3' style='display:none;background-color:rgba(255,255,0,0.5)'>
			<input id='chkdragfmtN3' name='chkdragfmtN3' type='checkbox' onchange='chkChange(event)' /><strong>N</strong>
			<input id='chkdragfmtI3' name='chkdragfmtI3' type='checkbox' onchange='chkChange(event)' /><i>I</i>
			<input id='chkdragfmtS3' name='chkdragfmtS3' type='checkbox' onchange='chkChange(event)' /><u>S</u>
			<input id='raddragfmtCa3' name='caixa3' value='cb' type='radio' onchange='chkChange(event)' />abc
			<input id='raddragfmtCb3' name='caixa3' value='CA' type='radio' onchange='chkChange(event)' />ABC
			<input id='raddragfmtCc3' name='caixa3' value='Ca' type='radio' onchange='chkChange(event)' />Abc
		</span>
		<span id='spndraggrp3' style='display:none;background-color:rgba(0,255,255,0.5)'>
			<input id='chkdraggrp3' name='chkdraggrp3' type='checkbox' onchange='chkChange(event)' />agrupar por
		</span>
		<input id='txtdrag3' type='text' onkeyup='txtKeyUp(event)' style='display:none;width:40em' />
	</div>
</div>
<p>...e arraste-o para o espaço abaixo. Depois clique duas vezes para editá-lo.</p>
<div id='divDst' ondragover='allowDrop(event)' ondrop='drop(event)' style='border:1px solid #000;background-color:#FFF;padding-bottom:50px;'>
<?php
if ($edit == '') {
	echo "<span id='spnDest' style='color:#888;padding:5px;display:block;'>Arraste os itens para cá.</span>";
} else {
	echo "<span id='spnDest' style='color:#888;padding:5px;display:none;'>Arraste os itens para cá.</span>";
	$cont1 = 1;
	$cont2 = 1;
	$cont3 = 1;
	$q = "select * from relatf where relat=$1 order by ordem";
	$res = pg_query_params($conn,$q,[$edit]);
	if ($res) {
		while ($row = pg_fetch_array($res,NULL,PGSQL_ASSOC)) {
			$n = $row['n'] == 1 ? 'checked' : '';
			$i = $row['i'] == 1 ? 'checked' : '';
			$s = $row['s'] == 1 ? 'checked' : '';
			$estilo = '';
			if ($n != '') {
				$estilo = 'font-weight:bold;';
			}
			if ($i != '') {
				$estilo = $estilo.'font-style:italic;';
			}
			if ($s != '') {
				$estilo = $estilo.'text-decoration:underline;';
			}
			$cas = $row['cas'];
			$val = $row['val'];
			switch ($row['tipo']) {
				case 1 :
					echo 
	"<div id='divdrag1_$cont1' class='drag' draggable='true' ondragstart='drag(event)' style='background-color:#F00'>
		<span id='spndragtxt1_$cont1' style='$estilo'>$val</span>
		<input id='hiddragtxt1_$cont1' name='hiddragtxt1_$cont1' type='hidden' />
		<span id='spndragfmt1_$cont1' style='background-color:rgba(255,255,0,0.5)'>
			<input id='chkdragfmtN1_$cont1' name='chkdragfmtN1_$cont1' type='checkbox' onchange='chkChange(event)' $n /><strong>N</strong>
			<input id='chkdragfmtI1_$cont1' name='chkdragfmtI1_$cont1' type='checkbox' onchange='chkChange(event)' $i /><i>I</i>
			<input id='chkdragfmtS1_$cont1' name='chkdragfmtS1_$cont1' type='checkbox' onchange='chkChange(event)' $s /><u>S</u>\n";
			if ($cas == 1) {
				echo "<input id='raddragfmtCa1_$cont1' name='caixa1_$cont1' value='cb' type='radio' onchange='chkChange(event)' checked />abc";
			} else {
				echo "<input id='raddragfmtCa1_$cont1' name='caixa1_$cont1' value='cb' type='radio' onchange='chkChange(event)' />abc";
			}
			if ($cas == 2) {
				echo "<input id='raddragfmtCb1_$cont1' name='caixa1_$cont1' value='CA' type='radio' onchange='chkChange(event)' checked />ABC";
			} else {
				echo "<input id='raddragfmtCb1_$cont1' name='caixa1_$cont1' value='CA' type='radio' onchange='chkChange(event)' />ABC";
			}
			if ($cas == 3) {
				echo "<input id='raddragfmtCc1_$cont1' name='caixa1_$cont1' value='Ca' type='radio' onchange='chkChange(event)' checked />Abc";
			} else {
				echo "<input id='raddragfmtCc1_$cont1' name='caixa1_$cont1' value='Ca' type='radio' onchange='chkChange(event)' />Abc";
			}
		echo "</span>
		<input id='txtdrag1_$cont1' type='text' onkeydown='txtKeyDown(event)' style='display:none;width:40em' />
	</div>";
					$cont1++;
					break;
				case 2 :
					$tnum = $row['tnum'];
					$showN = $row['shown'];
					$q2 = "select pathname from key where id=$1";
					$res2 = pg_query_params($conn,$q2,[$val]);
					$txt = 'Variáveis quantitativas';
					if ($res2) {
						if ($row2 = pg_fetch_array($res2,NULL,PGSQL_NUM)) {
							$txt = $row2[0];
						}
					}
					echo
	"<div id='divdrag2_$cont2' class='drag' draggable='true' ondragstart='drag(event)' style='background-color:#0F0'>
		<input type='hidden' id='hiddragtxt2_$cont2' name='hiddragtxt2_$cont2' value='$val' />
		<span id='spndragtxt2_$cont2' style='$estilo'>$txt</span>
		<span id='spndragfmt2_$cont2' style='background-color:rgba(255,255,0,0.5)'>
			<input id='chkdragfmtN2_$cont2' name='chkdragfmtN2_$cont2' type='checkbox' onchange='chkChange(event)' $n /><strong>N</strong>
			<input id='chkdragfmtI2_$cont2' name='chkdragfmtI2_$cont2' type='checkbox' onchange='chkChange(event)' $i /><i>I</i>
			<input id='chkdragfmtS2_$cont2' name='chkdragfmtS2_$cont2' type='checkbox' onchange='chkChange(event)' $s /><u>S</u>\n";
			if ($cas == 1) {
				echo "<input id='raddragfmtCa2_$cont2' name='caixa2_$cont2' value='cb' type='radio' onchange='chkChange(event)' checked />abc";
			} else {
				echo "<input id='raddragfmtCa2_$cont2' name='caixa2_$cont2' value='cb' type='radio' onchange='chkChange(event)' />abc";
			}
			if ($cas == 2) {
				echo "<input id='raddragfmtCb2_$cont2' name='caixa2_$cont2' value='CA' type='radio' onchange='chkChange(event)' checked />ABC";
			} else {
				echo "<input id='raddragfmtCb2_$cont2' name='caixa2_$cont2' value='CA' type='radio' onchange='chkChange(event)' />ABC";
			}
			if ($cas == 3) {
				echo "<input id='raddragfmtCc2_$cont2' name='caixa2_$cont2' value='Ca' type='radio' onchange='chkChange(event)' checked />Abc";
			} else {
				echo "<input id='raddragfmtCc2_$cont2' name='caixa2_$cont2' value='Ca' type='radio' onchange='chkChange(event)' />Abc";
			}
		echo "</span>
		<span id='spndragnum2_$cont2' style='background-color:rgba(255,0,255,0.5)'>";
			if ($tnum == 'm') {
				echo "<input id='raddragnumM2_$cont2' name='num_$cont2' value='m' type='radio' onchange='chkChange(event)' checked />média";
			} else {
				echo "<input id='raddragnumM2_$cont2' name='num_$cont2' value='m' type='radio' onchange='chkChange(event)' />média";
			}
			if ($tnum == 'r') {
				echo "<input id='raddragnumR2_$cont2' name='num_$cont2' value='r' type='radio' onchange='chkChange(event)' checked />range";
			} else {
				echo "<input id='raddragnumR2_$cont2' name='num_$cont2' value='r' type='radio' onchange='chkChange(event)' />range";
			}
			if ($tnum == 'i') {
				echo "<input id='raddragnumI2_$cont2' name='num_$cont2' value='i' type='radio' onchange='chkChange(event)' checked />min";
			} else {
				echo "<input id='raddragnumI2_$cont2' name='num_$cont2' value='i' type='radio' onchange='chkChange(event)' />min";
			}
			if ($tnum == 'a') {
				echo "<input id='raddragnumA2_$cont2' name='num_$cont2' value='a' type='radio' onchange='chkChange(event)' checked />max";
			} else {
				echo "<input id='raddragnumA2_$cont2' name='num_$cont2' value='a' type='radio' onchange='chkChange(event)' />max";
			}
		echo "</span>
		<span id='spndragn2_$cont2' style='background-color:rgba(0,255,255,0.5)'>";
			if ($showN) {
				echo "<input id='chkdragn2_$cont2' name='chkdragn2' type='checkbox' onchange='chkChange(event)' checked />mostrar N";
			} else {
				echo "<input id='chkdragn2_$cont2' name='chkdragn2' type='checkbox' onchange='chkChange(event)' />mostrar N";
			}
		echo "</span>
		<input id='txtdrag2_$cont2' type='text' onkeyup='txtKeyUp(event)' style='display:none;width:40em' />
	</div>";
					$cont2++;
					break;
				case 3 :
					$grp = $row['grp'];
					$q2 = "select pathname from key where id=$1";
					$res2 = pg_query_params($conn,$q2,[$val]);
					$txt = 'Variáveis qualitativas';
					if ($res2) {
						if ($row2 = pg_fetch_array($res2,NULL,PGSQL_NUM)) {
							$txt = $row2[0];
						}
					}
					if ($grp == 'S') {
						echo "<div id='divdrag3_$cont3' class='drag' draggable='true' ondragstart='drag(event)' style='background-color:#00F;padding-bottom:50px'>";
					} else {
						echo "<div id='divdrag3_$cont3' class='drag' draggable='true' ondragstart='drag(event)' style='background-color:#00F'>";
					}
		echo "<input type='hidden' id='hiddragtxt3_$cont3' name='hiddragtxt3' value='$val' />
		<span id='spndragtxt3_$cont3' style='$estilo'>$txt</span>
		<span id='spndragfmt3_$cont3' style='background-color:rgba(255,255,0,0.5)'>
			<input id='chkdragfmtN3_$cont3' name='chkdragfmtN3_$cont3' type='checkbox' onchange='chkChange(event)' $n /><strong>N</strong>
			<input id='chkdragfmtI3_$cont3' name='chkdragfmtI3_$cont3' type='checkbox' onchange='chkChange(event)' $i /><i>I</i>
			<input id='chkdragfmtS3_$cont3' name='chkdragfmtS3_$cont3' type='checkbox' onchange='chkChange(event)' $s /><u>S</u>\n";
			if ($cas == 1) {
				echo "<input id='raddragfmtCa3_$cont3' name='caixa3_$cont3' value='cb' type='radio' onchange='chkChange(event)' checked />abc";
			} else {
				echo "<input id='raddragfmtCa3_$cont3' name='caixa3_$cont3' value='cb' type='radio' onchange='chkChange(event)' />abc";
			}
			if ($cas == 2) {
				echo "<input id='raddragfmtCb3_$cont3' name='caixa3_$cont3' value='CA' type='radio' onchange='chkChange(event)' checked />ABC";
			} else {
				echo "<input id='raddragfmtCb3_$cont3' name='caixa3_$cont3' value='CA' type='radio' onchange='chkChange(event)' />ABC";
			}
			if ($cas == 3) {
				echo "<input id='raddragfmtCc3_$cont3' name='caixa3_$cont3' value='Ca' type='radio' onchange='chkChange(event)' checked />Abc";
			} else {
				echo "<input id='raddragfmtCc3_$cont3' name='caixa3_$cont3' value='Ca' type='radio' onchange='chkChange(event)' />Abc";
			}
		echo "</span>
		<span id='spndraggrp3_$cont3' style='background-color:rgba(0,255,255,0.5)'>";
			if ($grp == 'S') {
				echo "<input id='chkdraggrp3_$cont3' name='chkdraggrp3_$cont3' type='checkbox' onchange='chkChange(event)' checked />agrupar por";
			} else {
				echo "<input id='chkdraggrp3_$cont3' name='chkdraggrp3_$cont3' type='checkbox' onchange='chkChange(event)' />agrupar por";
			}
		echo "</span>
		<input id='txtdrag3_$cont3' type='text' onkeyup='txtKeyUp(event)' style='display:none;width:40em' />
	</div>";
					$cont3++;
					break;
			}
		}
	}
}
echo "</div>
<div id='divRelatSearch'></div>";
echo "<BR><dl>
<dt>Visualizar com o filtro</dt>
<dd>
<select id='selFiltro'>";
$dirname = './usr/'.$_SESSION['user_id'].'/filter/';
$dir1 = glob($dirname.'*'); # all filters available
if (sizeof($dir1) > 0) {
	echo "<option value=0>-- Filtros --</option>";
	foreach ($dir1 as $fname) {
		$fname = substr($fname,strlen($dirname));
		echo "<option>$fname</option>";
	}
}
echo "</select><button id='btnAplica' type='button' onclick='aplicaAoFiltro()'>Aplicar</button></dd></dl>
<div id='divPreview'></div>";
echo "<div class='wrapper'>
<button id='btnSave' type='button' onclick='btnRelatSaveClick(this)'>".txt('addSave')."</button>
<button id='btnReset' type='button' onclick='btnResetClick(this)'>".txt('addClear')."</button>
<button id='btnCancel' type='button' onclick='btnCancelClick()'>".txt('addCancel')."</button>
<button id='btnStore' type='button' onclick='seeStore()'>".txt('addLookSt')."</button>
<button id='btnClear' type='button' onclick='clearStore()'>".txt('addClearSt')."</button>
</div>";
?>
</form>
</body>
</html>
