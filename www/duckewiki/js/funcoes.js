var cmbAnt;
var colSort;
var divUpd = null;
var optUpd = null;
function update() {
	if (HttpReq.readyState == 4) {
		if (HttpReq.status == 200) {
			var div = document.getElementById(divUpd);
			div.innerHTML = HttpReq.responseText;
		} else {
			alert("Erro: " + HttpReq.statusText);
		}
	}
}
function conecta(url,funcao) { // makes AJAX connection
	if (document.getElementById) { // If Browser supports DHTML, Firefox, etc.
		if (window.XMLHttpRequest) {
			HttpReq = new XMLHttpRequest();
			HttpReq.onreadystatechange = funcao;
			HttpReq.open("GET", url, true);
			HttpReq.send(null);
		} else
		if (window.ActiveXObject) { // IE -- EXCLUIR ??
			HttpReq = new ActiveXObject("Microsoft.XMLHTTP");
			if (HttpReq) {
				HttpReq.onreadystatechange = funcao;
				HttpReq.open("GET", url, true);
				HttpReq.send();
			}
		}
	}
}
function whois(e) {
	//var source = e.target; // funciona no Firefox/Ubuntu
	//var source = e.srcElement; // não funciona no Firefox/Ubuntu (deve ser pro IE)
	var source = e.target || e.srcElement;
	if (source.id.substr(0,2) == 'mm') {
		return(source.id.substr(5,source.id.length));
	} else
	if (source.id.substr(0,3) == 'cmb') {
		return(source.id.substr(3,source.id.length)); // depois será 6
	} else {
		// não deve entrar aqui!
		return(source.id.substr(3,source.id.length));
	}
}

// funções multi-multi genéricas
function mmRem(who) {
	var i = 0;
	//var sel = document.getElementById('sel'+who+'Std'); // who = ColExtra, Taxa...
	var sel = document.getElementById('std'+who); // who = ColExtra, Taxa...
	while (i < sel.length) {
		if (sel[i].selected) {
			sel.remove(i);
		} else {
			i++;
		}
	}
	updateStd(who);
}
function mmselDblClick(e,multi) {
	var val = e.target.value;
	var txt = e.target.innerHTML;
	var nome = e.target.parentNode.id;
	var nome0 = nome.substr(3);
	//alert(nome+','+nome0);
	var std = document.getElementById('std'+nome0);
	if (multi == 'S') {
		mmAdd(nome0);
	} else {
		cmbselClick(nome0,multi);
	}
}
function mmselStdDblClick(e) {
	// REMOVER ITEM de qualquer _mm OU ADICIONAR O TOMBAMENTO para stdherbs ?? Definir comportamento do dblClick
	if (e.target.parentNode.name == 'stdherbs') {
		var v = e.target.value;
		var id;
		var t = e.target.text;
		if (v.indexOf('号') > 0) {
			id = v.substr(0,v.indexOf('号'));
			v = v.substr(v.indexOf('号')+1);
			var tomb = prompt("Digite o código de tombamento associado a este especímene no herbário '"+t+"'",v);
		} else {
			id = v;
			var tomb = prompt("Digite o código de tombamento associado a este especímene no herbário '"+t+"'");
		}
		if (tomb) {
			v = id+'号'+tomb;
			e.target.value = v;
			store(e.target.parentNode);
		}
	}
}
function updateMouseOver1() {
    if (HttpReq.readyState == 4) {
		if (HttpReq.status == 200) {
			optUpd.title = HttpReq.responseText;
		} else {
            alert("Erro: " + HttpReq.statusText);
		}
    }
}
function mmAdd(who) {
	//var val = document.getElementById('val'+who);
	var sel = document.getElementById('sel'+who); // selTaxa
	//var selStd = document.getElementById('sel'+who+'Std'); // selTaxaStd
	var selStd = document.getElementById('std'+who); // selTaxaStd
	var update = false;
	for (var i=0; i<sel.length; i++) {
		if (sel[i].selected) {
			var jaExiste = false;
			//if (val.value == sel[i].value) { // se não for multiple?
				//jaExiste = true;
			//} else
			for (var j=0; j<selStd.length; j++) {
				if (selStd[j].value == sel[i].value) {
					jaExiste = true;
				}
			}
			if (!jaExiste) {
				var option = document.createElement('option');
				option.value = sel[i].value;
				option.text = sel[i].text;
				//option.title = sel[i].title; // não funciona, pq sel[i] usa AJAX, não TITLE
				optUpd = option;
				conecta('getFull.php?id='+sel[i].value,updateMouseOver1);
				// Pergunta o número de tombamento associado ao exemplar naquele herbário (OBRIGATÓRIO?)
				if (who == 'herbs') {
					var tomb = prompt("Digite o código de tombamento associado a este especímene no herbário '"+sel[i].text+"'");
					if (tomb) {
						option.value = option.value+'号'+tomb;
						selStd.add(option); // se o mm for 'herbs', só adiciona com número de tombamento?
						update = true;
					}
				} else {
					selStd.add(option); // adiciona se for qualquer outro mm
					update = true;
				}
			}
		}
	}
	if (update) {
		updateStd(who);
	}
}
function updateStartXY() {
    if (HttpReq.readyState == 4) {
		if (HttpReq.status == 200) {
			var div = document.getElementById(divUpd);
			if (HttpReq.responseText == 'S') {
				div.style.display = 'block';
			} else {
				div.style.display = 'none';
			}
		} else {
            alert("Erro: " + HttpReq.statusText);
		}
    }
}
function mudaPai(id,dequem) {
	divUpd = 'divstartxy';
	if (id != null) { // se tem onde procurar, procura (dequem indica se o id é do próprio pai (loc) ou se deve buscar o pai dele (pai)
		var url = 'getLocPaiDimXY.php?id='+id+'&dequem='+dequem;
		conecta(url,updateStartXY);
	} else {
		var div = document.getElementById(divUpd);
		div.style.display = 'none';
	}
}
function updateStd(who) {
	var selStd = document.getElementById('std'+who);
	if (selStd.length > 0) {
		var txt = '';
		var val = '';
		for (var i=0; i<selStd.length; i++) {
			val = val + selStd[i].value + ';'; // pq tinha espaço aqui e na linha de baixo?
			txt = txt + selStd[i].text + ';';
		}
		txt = txt.substr(0,txt.length-1); // remove a última ';'
		val = val.substr(0,val.length-1); // remove a última ';'
		document.getElementById('mmVal'+who+'Std').value = val;
		document.getElementById('mmTxt'+who+'Std').value = txt;
	} else {
		document.getElementById('mmVal'+who+'Std').value = '';
		document.getElementById('mmTxt'+who+'Std').value = '';
	}
	if (who == 'pais') {
		if (selStd.length == 1) {
			mudaPai(selStd[0].value,'loc');
		} else {
			mudaPai(null,'loc');
		}
	}
	store(selStd);
}
function mmExpand(who,toExpand) {
	var divF = document.getElementById('divF'+who); // quando está fechado F
	var divA = document.getElementById('divA'+who); // quando está aberto A
	var divHF = document.getElementById('divHF'+who); // Header (label, à esquerda) Fechado
	var divHA = document.getElementById('divHA'+who); // Header Aberto
	var selStd = document.getElementById('std'+who);
	if (toExpand) { // expande
		divF.style.display = 'none';
		divA.style.display = '';
		divHF.style.display = 'none';
		divHA.style.display = '';
		var mmTxt = document.getElementById('txt'+who);
		mmTxt.focus();
	} else { // esconde
		var txt = '', lin, tit;
		for (var i=0; i<selStd.length; i++) {
			lin = selStd[i].text;
			tit = selStd[i].title;
			if (tit != '') {
				txt = txt + "<label title='"+tit+"'>"+lin+"</label>; ";
			} else {
				txt = txt + lin + ';'; // pq tinha espaço aqui?
			}
		}
		txt = txt.substr(0,txt.length-1); // remove a última ';'
		divF.innerHTML = txt;
		divF.style.display = '';
		divA.style.display = 'none';
		divHF.style.display = '';
		divHA.style.display = 'none';
	}
}
function mouseMove(e) {
	var esconde = false;
	if (typeof e.target.parentNode !== 'undefined') {
		if (typeof e.target.parentNode.id !== 'undefined') {
			if (e.target.parentNode.id.substr(0,3) != 'sel') {
				esconde = true;
			} // else == sel, mantém o 'tooltip'...
		} else {
			esconde = true; // ...em qualquer outro lugar, oculta
		}
	} else {
		esconde = true;
	}
	if (esconde) {
		var div = document.getElementById('tooltip');
		if (div != null) {
			div.style.display = 'none';
		}
	}
}
var mouseX, mouseY;
function updateMouseOver() {
    if (HttpReq.readyState == 4) {
		if (HttpReq.status == 200) {
			var div = document.getElementById(divUpd);
			div.innerHTML = HttpReq.responseText;
			if (div.innerHTML != '') {
				div.style.left = mouseX+'px';
				div.style.top = mouseY+'px';
				div.style.display = 'block';
			}
		} else {
            alert("Erro: " + HttpReq.statusText);
		}
    }
}
function mmoptMouseOver(e,who,query) {
	//var top = e.target.parentNode.getBoundingClientRect().top;
	divUpd = 'tooltip';
	var url = 'getFull.php?id='+who.value+'&query='+query;
	conecta(url,updateMouseOver);
	mouseX = e.pageX+20;
	mouseY = e.pageY+20;
}
function getGrp(value) {
	var pos = value.indexOf('号');
	if (pos > 0) {
		return value.substr(pos+1,value.length);
	} else {
		return '';
	}
}
function setGrp(opt,grp) {
	var pos = opt.value.indexOf('号');
	if (grp != '') {
		if (pos >= 0) { // troca o grupo
			opt.value = opt.value.substr(0,pos+1)+grp;
			var pos1 = opt.innerHTML.indexOf('[');
			opt.innerHTML = opt.innerHTML.substr(0,pos1)+'['+grp+']';
		} else { // adiciona um grupo
			opt.value = opt.value+'号'+grp;
			opt.innerHTML = opt.innerHTML+' ['+grp+']';
		}
	} else {
		if (pos >= 0) { // apaga o grupo
			opt.value = opt.value.substr(0,pos);
			var pos1 = opt.innerHTML.indexOf('[');
			opt.innerHTML = opt.innerHTML.substr(0,pos1-1);
		}
	}
}
function mmselStdUp(who) {
	var sel = document.getElementById('std'+who);
	for (var i=0; i<sel.length; i++) {
		if (sel[i].selected) {
			if ((i > 0) && (!sel[i-1].selected)) {
				// troca com o de cima
				var tempval = sel[i-1].value;
				var temptext = sel[i-1].text;
				sel[i-1].value = sel[i].value;
				sel[i-1].text = sel[i].text;
				sel[i].value = tempval;
				sel[i].text = temptext;
				sel[i].selected = false;
				sel[i-1].selected = true;
				if (who == 'var') { // addFrm.php
					var grp = getGrp(sel[i].value); // se '', exclui de sel[i-1] na próxima linha
					setGrp(sel[i-1],grp);
				}
			}
		}
	}
	updateStd(who);
	store(sel);
}
function mmselStdDown(who) {
	var sel = document.getElementById('std'+who);
	for (var i=sel.length-1; i>=0; i--) {
		if (sel[i].selected) {
			if ((i < sel.length-1) && (!sel[i+1].selected)) {
				// troca com o de baixo
				var tempval = sel[i+1].value;
				var temptext = sel[i+1].text;
				sel[i+1].value = sel[i].value;
				sel[i+1].text = sel[i].text;
				sel[i].value = tempval;
				sel[i].text = temptext;
				sel[i].selected = false;
				sel[i+1].selected = true;
				if (who == 'var') { // addFrm.php
					var grp = getGrp(sel[i].value); // se '', exclui de sel[i+1] na próxima linha
					setGrp(sel[i+1],grp);
				}
			}
		}
	}
	updateStd(who);
	store(sel);
}

function getDragSub(elem,which) {
	var i;
	//alert(which+','+elem.childNodes.length);
	for (i=0; i<elem.childNodes.length; i++) {
		if (typeof(elem.childNodes[i].id) !== 'undefined') {
			if (elem.childNodes[i].id.substr(0,which.length) == which) {
				return elem.childNodes[i];
			}
		}
	}
}

// funções de autocompletar -- versão Genérica
function cmbselClick(who,multi) { // who = 'Col', 'Prj', 'Tax', ...
	if (multi == 'N') {
		var div = document.getElementById('div'+who);
		var sel = document.getElementById('sel'+who);
		if (who == 'RelatSearch') {
			var txt = getDragSub(div.parentNode,'spndragtxt');
			if (txt) {
				txt.innerHTML = sel[sel.selectedIndex].text;
			}
			var hid = getDragSub(div.parentNode,'hiddragtxt');
			if (hid) {
				hid.value = sel[sel.selectedIndex].value;
			}
			txt.style.display = 'inline';
			txt = getDragSub(div.parentNode,'txtdrag');
			txt.style.display = 'none';
			sel.style.display = 'none';
			updateStorage(); // de relat.php
		} else {
			var txt = document.getElementById('txt'+who);
			var hid = document.getElementById('val'+who);
			txt.value = sel[sel.selectedIndex].text;
			div.innerHTML = '';
			if (hid) {
				hid.value = sel[sel.selectedIndex].value;
				store(hid);
				if (who == 'col') { // se mesmo coletor estiver como coletor extra, retira ele de lá
					var selColExtraSelected = document.getElementById('stdcols');
					for (var i=0;i<selColExtraSelected.length; i++) {
						if (selColExtraSelected[i].value == hid.value) {
							selColExtraSelected.remove(i);
						}
					}
					divUpd = 'divcoletnum';
					var url = 'getColetNum.php?id='+hid.value;
					//alert(hid.value);
					conecta(url,update);
				} else
				if (who == 'taxpai') {
					//alert(hid.value);
					var pbar = hid.value.indexOf('件');
					var rank = parseInt(hid.value.substr(pbar+1),10);
					//alert(rank);
					var selRank = document.getElementsByName('selrank')[0];
					divUpd = 'divRank';
					url = 'getRank.php?rankpai='+rank;
					conecta(url,updateRank); // tira os ranks impossíveis (acima de rank) de selRank
						//   VER SE O 'ID|RANK' NO HID NÃO ESTÁ CAUSANDO PROBLEMAS DE INSERÇÃO
						// ATUALIZAR O RANK A PARTIR DO REFILL
						
					/*selRank.value = rank; // MOVER PRA UM UPDATE PRÓPRIO
					store(selRank);
					showMorf(selRank);*/
					/*if (rank >= 220) {
						document.getElementById('divMorf').style.display = 'block'; // só mostra de espécie para baixo
					} else {
						document.getElementById('divMorf').style.display = 'none';
					}*/
				} else
				if (who == 'dettax') {
					var pbar = hid.value.indexOf('|');
					var rank = hid.value.substr(pbar+1);
					var divRef = document.getElementById('divRef');
					if (rank <= 140) {
						divRef.style.display='none';
					} else {
						divRef.style.display='block';
					}
				} else
				if (who == 'pl') {
					pltagchange(hid.value);
				}
			} else {
				alert('val'+who+' não encontrado!'); // jamais deve chegar aqui (só para denunciar erros no código)
			}
			store(txt);
		}
	}
}
function cmbselKeyDown(e,multi) {
	var who = whois(e);
	if (e.keyCode == 9) { // Tab
		if (multi == 'N') {
			cmbselClick(who,multi);
			cmbAnt = document.getElementById('txt'+who).value; // já que está no Focus, precisa estar aqui?
		}
	} else
	if (e.keyCode == 13) { // Enter
		if (multi == 'S') {
			mmAdd(who);
		}
	} else
	if (e.keyCode == 27) { // Esc
		document.getElementById('div'+who).innerHTML = '';
		document.getElementById('txt'+who).value = '';
		var F = document.getElementById('txt'+who).form;
		localStorage.removeItem(F.id+'.txt'+who);
		localStorage.removeItem(F.id+'.val'+who);
	}
}
function mmselStdKeyDown(e) {
	if (e.keyCode == 13) {
		var who = whois(e);
		mmRem(who);
	}
}
/** Quando aperta Enter para escolher um item no build_cmb */
function cmbselKeyUp(e,multi) { // se der Enter, é como se clicasse
	if (e.keyCode == 13) { // Enter
		var who = whois(e);
		//alert(who+','+multi);
		if (multi == 'N') {
			cmbselClick(who,multi);
			// devia pular o [+] (se houver) e ir pro próximo controle
			if (document.getElementById('txt'+who)) {
				cmbAnt = document.getElementById('txt'+who).value; // já que está no Focus, precisa estar aqui?
			}
		}
	}
}
function cmbtxtFocus(e) { // prepara o cmbAnt para o caso de haver mudanças
	var who = whois(e);
	//alert(who);
	cmbAnt = document.getElementById('txt'+who).value;
}
function cmbtxtKeyDown(e) { // se apertar o Esc, esconde o div com o sel
	var who = whois(e);
	//alert('down:'+who);
	if (e.keyCode == 27) { // Esc
		document.getElementById('div'+who).innerHTML = '';
		cmbAnt = '';
		document.getElementById('txt'+who).value = '';
		var F = document.getElementById('txt'+who).form;
		localStorage.removeItem(F.id+'.txt'+who);
		localStorage.removeItem(F.id+'.val'+who);
	}
}
//function cmbtxtKeyUp(e,multi,table,fields,caseSens,mmQuery) { // vai pro sel (setas) ou faz a busca (letras)
function cmbtxtKeyUp(e,multi,query) { // vai pro sel (setas) ou faz a busca (letras)
	var who = whois(e);
	if (e.keyCode == 38 | e.keyCode == 40) { // Seta para cima | baixo
		document.getElementById('sel'+who).focus();
		if (e.keyCode == 38) {
			document.getElementById('sel'+who).selectedIndex = document.getElementById('sel'+who).length-1;
		} else {
			document.getElementById('sel'+who).selectedIndex = 0;
		}
	} else {
		var what = document.getElementById('txt'+who).value;
		//alert('up:'+what);
		if (what != cmbAnt) {
			if (query != '') { // precisa?
				//var url = 'getLike.php?query='+query+'&t='+table+'&f='+fields+'&what='+what+'&who='+who+'&m='+multi+'&case='+caseSens;
				var url = 'getLike.php?query='+query+'&what='+what+'&who='+who+'&m='+multi;
				//alert(url);
				divUpd = 'div'+who;
				conecta(url,update);
				cmbAnt = what;
			}
		}
	}
}
function diasMes(mes,ano) {
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
	if (who.value != '') {
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
}
var sideBarWidth = screen.width - screen.availWidth;
function fileExists(url){
    var http = new XMLHttpRequest();
    http.open('HEAD',url,false);
    // https://developer.mozilla.org/en-US/docs/Web/API/XMLHttpRequest
    // ...Synchronous XMLHttpRequest on the main thread is deprecated because of its detrimental effects to the end user's experience. For more help http://xhr.spec.whatwg.org/
    http.send();
    return http.status != 404;
}
function callAdd(php,w,h,update,close,who,table,fields) {
	if (typeof w === 'undefined') w = 800;
	if (typeof h === 'undefined') h = 0;
	if (typeof update === 'undefined') update = 1;
	if (typeof close === 'undefined') close = 0;
	if (typeof who === 'undefined') who = '';
	if (typeof table === 'undefined') table = '';
	if (typeof fields === 'undefined') fields = '';
	if (h == 0) {
		h = screen.availHeight;
	}
	var l = sideBarWidth + (screen.availWidth-w)/2;
	var t = (screen.availHeight-h)/2;
	if (fileExists(php)) {
		var retorno;
		var retorno2;
		if (update == 1) {
			//retorno2 = '&who='+who+'&tab='+table+'&fields='+fields;
			retorno2 = '';
		} else {
			retorno2 = '';
		}
		if (php.indexOf('?') > 0) {
			retorno = '&update='+update+'&close='+close+retorno2;
		} else {
			retorno = '?update='+update+'&close='+close+retorno2;
		}
		//alert(php);
		/*var ww = window.open(php+retorno,php,'height='+h+',width='+w+',left='+l+',top='+t+',scrollbars=1');
		var wwCount = 0;
		for (var i=0; i<localStorage.length; i++) {
			if (localStorage.key(i).substr(0,2) == 'ww') {
				wwCount++;
			}
		}
		localStorage.setItem('ww'+(wwCount+1),ww.name);
		//openedWindows.push(ww);
		ww.focus();*/
		window.open(php+retorno,php,'height='+h+',width='+w+',left='+l+',top='+t+',scrollbars=1').focus();
		// about:config
		// dom.disable_window_flip = false
		// para o .focus() funcionar
		if (who != '' && document.getElementById('div'+who) != null) {
			document.getElementById('div'+who).innerHTML = '';
		}
	} else {
		alert('Arquivo '+php+' não encontrado!');
	}
}
function updateErase() {
    if (HttpReq.readyState == 4) {
		if (HttpReq.status == 200) {
			//var div = document.getElementById(divUpd);
			//div.innerHTML = HttpReq.responseText;
			//alert(HttpReq.responseText);
			// FALTA DAR A MENSAGEM AO USUÁRIO (SUCESSO OU FALHA) -> MESSAGEBOX
			location.reload(true);
		} else {
            alert("Erro: " + HttpReq.statusText);
		}
    }
}
function eraseRec(tab,id) {
	if (confirm('Tem certeza que deseja excluir o id '+id+' da tabela '+tab+'?')) {
		var url='eraseRec.php?tab='+tab+'&id='+id;
		conecta(url,updateErase);
	}
}
function updateDupl() {
    if (HttpReq.readyState == 4) {
		if (HttpReq.status == 200) {
			alert(HttpReq.responseText);
			location.reload(true);
		} else {
            alert("Erro: " + HttpReq.statusText);
		}
    }
}
function duplFrm(id,atual) {
	var novo;
	do {
		novo = prompt("Nome do novo formulário:", atual);
	} while (novo == atual && novo != null);
	if (novo != null) {
		var url = 'duplFrm.php?id='+id+'&newname='+novo;
		conecta(url,updateDupl);
	}
}
function ancestorForm(element) {
    if (element === null || element.tagName.toLowerCase() === "form") {
        return element;
    } else {
        return ancestorForm(element.parentNode);
    }
}
function ancestorDiv(element) {
	if (element === '' || element === undefined || element === null) {
		return null;
	} else
    if (element.tagName.toLowerCase() === "div") {
        return element;
    } else {
        return ancestorDiv(element.parentNode);
    }
}
function store(what) {
	// oninput (HTML5) vai salvar a cada tecla digitada (pode ficar lento em alguns PCs?)
	// onchange        vai salvar quando sair do controle (blur) (pode ser ruim para controles com muito texto)
	var F; // form que contém what
	var edita = true; // edição da maioria dos campos leva ao modo edição (muda a cor do fundo)
	if (what.id.substr(0,3) == 'spn' || what.id.substr(0,3) == 'div') { // span e div
		F = ancestorForm(what);
		localStorage.setItem(F.id+'.'+what.id,what.innerHTML);
	} else
	if (what.name != '') { // se controle usa 'name' (não é div nem span)
		//alert(what.name);
		F = what.form;
		/*if (what.name.substr(0,2) == 'mm' || what.id.substr(0,2) == 'mm') {
			//alert(what.name+','+what.id);
		}*/
		if (what.type == 'checkbox') {
			localStorage.setItem(F.id+'.'+what.name,what.checked);
		} else
		if (what.type == 'select-multiple') {
			if (what.length > 0) {
				var i, texto='', valor='';
				for (i=0; i<what.length; i++) {
					texto = texto+what[i].text+'件';
					valor = valor+what[i].value+'件';
				}
				texto = texto.substr(0,texto.length-1);
				valor = valor.substr(0,valor.length-1);
				localStorage.setItem(F.id+'.'+what.name,valor+'示'+texto);
			} else {
				localStorage.removeItem(F.id+'.'+what.name);
			}
		} else {
			if (what.name.substr(0,3) == 'txt') { // DIFERENCIAR O NOME DO TXT DO MM E DO CMB !! -> senão nenhum texto edita!
				if (typeof what.parentNode.parentNode.parentNode.parentNode.parentNode !== 'undefined') {
					var nomeWhat = what.name.substr(3); // restante após o 'txt'
					var nomeParA = what.parentNode.parentNode.parentNode.parentNode.parentNode.id.substr(0,4);
					var nomeParB = what.parentNode.parentNode.parentNode.parentNode.parentNode.id.substr(4);
					if (nomeWhat == nomeParB && nomeParA == 'divA') {
						edita = false;
					}
				}
			}
			if (what.value == '' && what.name.substr(0,3) == 'txt') {
				//alert(F.id+'.'+what.name+' = '+what.value);
				localStorage.removeItem(F.id+'.'+what.name);
				var ctrl = what.name.substr(3);
				if (document.getElementsByName('val'+ctrl) != null) {
					localStorage.removeItem(F.id+'.val'+ctrl);
				}
			} else {
				localStorage.setItem(F.id+'.'+what.name,what.value);
			}
		}
	}
	if (edita) {
		modoEdit(); // menos nos combobox enquanto filtra
	}
	// vai criar conflito com value=get(name) que está no html?
}
function refill(frm) {
	//alert(frm);
	var edita = false;
	var i, j, key, value, e;
	for (i=0; i<localStorage.length; i++) {
		key = localStorage.key(i);
		//alert(key);
		if (key.indexOf(frm) == 0) { // se a chave é do formulário ativo
			value = localStorage.getItem(key);
			key = key.substr(frm.length+1); // pula nome do form e . e vai para o nome do campo
			if (document.getElementsByName(key).length > 0) {
				e = document.getElementsByName(key);
			} else {
				e = document.getElementById(key);
			}
			if (e.length > 1) { // só para radio buttons?
				for (j=0; j<e.length; j++) {
					if (e[j].value == value) {
						e[j].checked = true;
						edita = true;
						break;
					}
				}
			} else {
				if (e.length > 0) {
					e = e[0];
				}
				if (e.id.substr(0,3) == 'spn' || e.id.substr(0,3) == 'div') { // span e div
					e.innerHTML = value;
					edita = true;
				} else
				if (e.type == 'select-multiple') {
					//alert(e.length);
					var pos = value.indexOf('示');
					var valores = value.substr(0,pos);
					valores = valores.split('件');
					var textos = value.substr(pos+1,value.length);
					textos = textos.split('件');
					var who = key.substr(3,key.length-3);
					var div = document.getElementById('divF'+who);
					if (div.innerHTML == '') {
						div.innerHTML = textos.join('; ');
					}
					for (j=0; j<valores.length; j++) {
						var option = document.createElement('option');
						option.value = valores[j];
						option.text = textos[j];
						e.add(option);
					}
					updateStd(who);
					edita = true;
				} else
				if (e.type == 'file') {
					// aparentemente não pode ser definido por aqui (por questão de segurança do padrão HTML)
				} else
				if (e.type == 'checkbox') {
					e.checked = (value == 'true');
					edita = true;
				} else {
					e.value = value; // funciona para input text, textarea (testar para checkbox)
					edita = true;
				}
			}
		}
	}
	if (edita) {
		modoEdit(); // menos nos combobox enquanto filtra
	}
}
function seeStore() {
	var i, key, value, txt='';
	var localStorageArray = new Array();
	for (i=0; i<localStorage.length; i++){
		localStorageArray[i] = localStorage.key(i)+'='+localStorage.getItem(localStorage.key(i));
	}
	localStorageArray = localStorageArray.sort();
	for (i=0; i<localStorageArray.length; i++) {
		txt = txt+localStorageArray[i]+'<BR>';
	}
	if (txt != '') {
		var div = document.getElementById('divSeeStore');
		if (div == null) {
			div = document.createElement('div');
			div.id = 'divSeeStore';
			div.style.position = 'absolute';
			document.body.appendChild(div);
		}
		div.innerHTML = txt;
	}
}
function clearStore() {
	localStorage.clear();
}
function clearLocalStore(frm) {
	var i=0, key;
	while (i<localStorage.length) {
		key = localStorage.key(i);
		if (key.indexOf(frm) == 0) { // se a chave é do formulário ativo
			localStorage.removeItem(key);
		} else {
			i++;
		}
	}
}
function btnSaveClick(who) {
	var F = who.form;
/*	var spn, spns, id, f, tipo, i, j, F = who.form, block = false, msg, lab;
	// get all elements by tag 'span'
	spns = F.getElementsByTagName('span');
	for (i=0; i<spns.length; i++) {
		spn = spns[i];
		if (spn.style.color == 'red' && spn.innerHTML == '*') {
			// for each span that is a red * (obligatory field), get id
			id = spn.id.substr(3);
			tipo = id.substr(0,3);
			// get label text
			lab = spn.parentNode.childNodes[0].wholeText;
			// search field (input, textarea...) of similar name
			f = document.getElementsByName(id);
			switch (tipo) {
				case 'txt' :
					block = f[0].value == '';
					msg = '"'+lab+'" em branco';
					break;
				case 'rad' :
					var algum = false;
					for (j=0; j<f.length; j++) {
						if (f[j].checked) {
							algum = true;
							break;
						}
					}
					block = !algum;
					msg = '"'+lab+'" em branco';
					break;
				case 'sel' :
				case 'int' :
				case 'flt' :
				case 'txa' :
			}
		}
		if (block) {
			break; // sai do for
		}
	}
	if (!block) {
		//alert('ok');
		F.submit();
	} else {
		alert(msg);
	}*/
	if (F.checkValidity()) {
		F.submit();
	} else {
		alert('Os campos destacados são obrigatórios.');
	}
}
function btnResetClick(who) {
	var F = who.form;
	var i;
	for (i=0; i<F.elements.length; i++) { // limpa também mm selecionados
		if (F.elements[i].hasAttribute('name')) {
			if (F.elements[i].name.substr(0,3) == 'std') {
				F.elements[i].options.length = 0;
			}
		}
	}
	var divs = F.getElementsByTagName('div'); // e limpa div com mm selecionados
	for (i=0; i<divs.length; i++) {
		if (divs[i].id.substr(0,4) == 'divF') {
			divs[i].innerHTML = '';
		}
	}
	var spns = F.getElementsByTagName('span'); // e limpa span com mm selecionados
	for (i=0; i<spns.length; i++) {
		if (spns[i].id.substr(0,3) == 'spn') {
			spns[i].innerHTML = '';
		}
	}
	// falta limpar o select do cmb.mm, talvez chamando ele de mmsel...?
	clearLocalStore(F.id);
	F.reset();
	if (F.id == 'frmTax') {
		document.getElementById('divMorf').style.display = 'none';
		//alert(F.id);
	}
	// preenche novamente os div e span apagados
	//alert(who.ownerDocument.title);
	if (typeof who.ownerDocument.defaultView.refillDivSpan === "function") {
		who.ownerDocument.defaultView.refillDivSpan(); // só se a função existir
	}
	//ADICIONADO BETO: em add.tax, quando limpa deveria recarregar a página depois de limpar, usando aoCarregar();
	//imagino que isso funcione para todos os casos de aoCarregar com edit="";
	if (typeof who.ownerDocument.defaultView.aoCarregar === "function") {
		who.ownerDocument.defaultView.aoCarregar(); // executa se a função existir
	}
	modoUnedit();
}
function btnCancelClick() {
	this.close();
}
function addHidden(theForm,key,value) {
    // Create a hidden input element, and append it to the form
    var input = document.createElement('input');
    input.type = 'hidden';
    input.name = key;
    input.value = value;
    theForm.appendChild(input);
}
function btnSaveVarClick(id,ctrl) { // ,esp
	var divO = document.getElementById('divOverlay');
	divO.style.visibility = 'hidden';
	divUpd = 'divVar'+id;
	var newval;
	if (ctrl == 'chkposs') {
		var elements = document.getElementById('divDialog').getElementsByTagName('input'); // só tem checkbox
		var i;
		var element;
		var text = '';
		for (i=0; i<elements.length; i++) {
			element = elements[i];
			if (element.checked) {
				text = text+element.value+';';
			}
		}
		newval = text.substr(0,text.length-1);
	} else {
		newval = document.getElementById(ctrl).value;
	}
	//alert('varid='+id+'; newval='+newval);
	var url = 'showVarId.php?varid='+id+'&newval='+newval;//+'&esp='+esp;
	conecta(url,update);
}
function btnCancelVarClick() {
	var divO = document.getElementById('divOverlay');
	divO.style.visibility = 'hidden';
}
function txtFilterKeyPress(e,quem,dir) {
	if (e.keyCode == 13) {
		var F = document.getElementById('frmMnu');
		//var coluna = quem;
		//alert(quem+': '+dir);
		addHidden(F,'sort',quem);
		var El = document.getElementById('frmTable').elements;
		var i;
		for (i=0; i<El.length; i++) { // tem que passar por todos os txtFilter...
			if (El[i].id.substr(0,9) == 'txtFilter') {
				if (El[i].value != '') {
					addHidden(F,El[i].name,El[i].value);
				}
			}
		}
		F.submit();
	}
}
function btnColClick(quem,dir,tabela) {
	var F = document.getElementById('frmMnu');
	var coluna = quem.id.substr(7);
	addHidden(F,'table',tabela); // ver se não vai dar conflito (tipo aparecer table 2 vezes)
	if (dir == 1) {
		addHidden(F,'sort',coluna);
	} else {
		addHidden(F,'sort','!'+coluna);
	}
	var El = document.getElementById('frmTable').elements;
	var i;
	for (i=0; i<El.length; i++) {
		if (El[i].id.substr(0,9) == 'txtFilter') {
			if (El[i].value != '') {
				addHidden(F,El[i].name,El[i].value);
			}
		}
	}
	F.submit();
}
/*function checkEnter(e) {
	e = e || event;
	var txtArea = /textarea/i.test((e.target || e.srcElement).tagName);
	return txtArea || (e.keyCode || e.which || e.charCode || 0) !== 13;
}
document.querySelector('form').onkeypress = checkEnter;*/

function stopRKey(evt) { // faz com que o Enter não envie o Form?
	var evt = (evt) ? evt : ((event) ? event : null);
	var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
	if ((evt.keyCode == 13) && (node.type=="text")) {
		return false;
	}
}
document.onkeypress = stopRKey; 
function handlePopupResult(w,id,who,texto) {
	var txt, val, div;
	if (w.document.getElementById('spn'+who) !== null) {
		spn = w.document.getElementById('spn'+who);
		if (w.document.getElementsByName('val'+who).length == 0) {
			val = w.document.getElementById('val'+who);
		} else {
			val = w.document.getElementsByName('val'+who)[0];
		}
		spn.innerHTML = texto;
		val.value = id;
		store(spn);
		store(val);
	} else {
		if (w.document.getElementsByName('val'+who).length == 0) {
			val = w.document.getElementById('val'+who);
		} else {
			val = w.document.getElementsByName('val'+who)[0];
		}
		if (w.document.getElementsByName('txt'+who).length == 0) {
			txt = w.document.getElementById('txt'+who);
		} else {
			txt = w.document.getElementsByName('txt'+who)[0];
		}
		txt.value = texto;
		val.value = id;
		store(txt);
		store(val);
	}
}
function popFS(grp,t,texto,texto1) { // FS = HTML's FieldSet
	if (grp < 0) {
		var divs = document.getElementsByTagName("div");
		var i;
		for (i=0; i<divs.length; i++) {
			if (divs[i].id.indexOf('div'+t+'FSa') == 0) {
				divs[i].style.display = 'none';
			} else
			if (divs[i].id.indexOf('div'+t+'FSb') == 0) {
				divs[i].style.display = 'block';
			}
		}
		var span = document.getElementById('spnVarFS');
		//span.innerHTML = "<a href='javascript:unpopFS(-1,\"Var\")'>&lt;Ocultar tudo&gt;</a>"
		span.innerHTML = "<a href='javascript:unpopFS(-1,\"Var\",\""+texto1+"\",\""+texto+"\")'>&lt;"+texto+"&gt;</a>"
	} else {
		var div1 = document.getElementById('div'+t+'FSa'+grp);
		var div2 = document.getElementById('div'+t+'FSb'+grp);
		div1.style.display = 'none';
		div2.style.display = 'block';
	}
}
function unpopFS(grp,t,texto,texto1) {
	if (grp < 0) {
		var divs = document.getElementsByTagName("div");
		var i;
		for (i=0; i<divs.length; i++) {
			if (divs[i].id.indexOf('div'+t+'FSa') == 0) {
				divs[i].style.display = 'block';
			} else
			if (divs[i].id.indexOf('div'+t+'FSb') == 0) {
				divs[i].style.display = 'none';
			}
		}
		var span = document.getElementById('spnVarFS');
		//span.innerHTML = "<a href='javascript:popFS(-1,\"Var\")'>&lt;Expandir tudo&gt;</a>"
		span.innerHTML = "<a href='javascript:popFS(-1,\"Var\",\""+texto1+"\",\""+texto+"\")'>&lt;"+texto+"&gt;</a>"
	} else {
		var div1 = document.getElementById('div'+t+'FSa'+grp);
		var div2 = document.getElementById('div'+t+'FSb'+grp);
		div1.style.display = 'block';
		div2.style.display = 'none';
	}
}
function popCoords(n,div) {
	var divA = document.getElementById(div+'A');
	var divB = document.getElementById(div+'B');
	if (n > 0) {
		divA.style.display = 'none';
		divB.style.display = 'block';
	} else {
		divA.style.display = 'block';
		divB.style.display = 'none';
	}
}
function selFormsChange(who,dest,val,col) {
	var php = 'browseForm.php?frmid='+who.value+'&'+col+'='+val+'&col='+col;
	//alert(dest);
	switch (dest) {
		case 'S' : // abre na mesma janela
			var url = window.location.href;
			var pos = url.indexOf('frmid=');
			if (pos > -1) { // trocar se já tiver frmid=
				pos += 6; // pula pro início do valor de frmid
				var pos1 = url.indexOf('&',pos);
				var frm;
				if (pos1 < 0) {
					url = url.substr(0,pos)+who.value;
				} else {
					url = url.substr(0,pos)+who.value;
				}
			} else
			if (url.indexOf('?') > -1) {
			   url += '&frmid='+who.value;
			} else {
			   url += '?frmid='+who.value;
			}
			//alert(url);
			window.location.href = url; // NÃO USA MAIS embedForm.php!!!
			break;
		case 'T' : // abre em nova aba
			window.open(php);
			break;
		default : // abre em nova janela 'W'
			callAdd(php);
			break;
	}
}
function prepFormRadios(where) {
	//var elements = document.getElementById("divFrm").getElementsByTagName('input');
	var elements = where.getElementsByTagName('input');
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
					
					var evt = document.createEvent("HTMLEvents");
					evt.initEvent("change", false, true);
					this.dispatchEvent(evt); //this.change();
					
					objs[this.name] = null;
				} else {
					objs[this.name] = this;
				}
			}
		}
	}
}
function jump2anchor(a,bringToFront) {
	var i, d, as, j, aj, achou=false;
	// procura nos <a> dentro da <div> principal (divVar)
	as = document.getElementsByTagName('a');
	for (j=0; j<as.length; j++) {
		aj = as[j];
		if (ancestorDiv(aj).id == 'divVar' && aj.name == a) {
			if (ajAnt != null) {
				ajAnt.style.backgroundColor='';
			}
			aj.style.backgroundColor = '#E3A052';
			if (!isElementInViewport(aj)) {
				aj.scrollIntoView();
			}
			ajAnt = aj;
			achou = true;
			break;
		}
	}
	if (!achou) {
		var ds = document.getElementsByTagName('div');
		// procura dentro dos <div> dentro de divVar
		for (i=0; i<ds.length; i++) {
			d = ds[i];
			if (d.id.indexOf('divVarFSb') == 0) {
				as = d.getElementsByTagName('a');
				var d1 = d.id.substr(0,8)+'a'+d.id.substr(9);
				d1 = document.getElementById(d1);
				for (j=0; j<as.length; j++) {
					aj = as[j];
					if (aj.name == a) {
						d.style.display = 'block';
						d1.style.display = 'none';
						if (ajAnt != null) {
							ajAnt.style.backgroundColor='';
						}
						aj.style.backgroundColor = '#E3A052';
						if (!isElementInViewport(aj)) {
							aj.scrollIntoView();
						}
						ajAnt = aj;
						achou = true;
						break;
					}
				}
				if (achou) { break; }
			}
		}
	}
	if (achou && bringToFront == 1) {
		window.focus();
	}
}
/** +micClick/+replyMsg = resultado do envio da mensagem */
function updateMsg() {
    if (HttpReq.readyState == 4) {
		if (HttpReq.status == 200) {
			if (HttpReq.responseText == '') {
				alert('Obrigado. Sua mensagem foi enviada.');
			} else {
				alert('Erro! '+HttpReq.responseText);
			}
		} else {
            alert("Erro: " + HttpReq.statusText);
		}
    }
}
/** envia mensagem pro administrador (ou outro usuário - FUTURAMENTE) */
function micClick() {
	var msg = prompt('Escreva uma mensagem para a equipe de desenvolvimento:');
	if (msg != '' && msg != null) { // '' se clicar OK, nulo se clicar Cancel
		var url = 'sendMsg.php?msg='+msg+'&url='+encodeURIComponent(window.location);
		//alert(url);
		conecta(url,updateMsg);
	}
}
function modoEdit() {
	document.body.style.background = '#D0B060';
	document.getElementById('btnSave').style.background = 'red';
}
function modoUnedit() {
	document.body.style.background = '';
	document.getElementById('btnSave').style.background = '';
}
function hex2int(hex) {
	return parseInt(hex,16);
}
function updateCorDB() {
	if (HttpReq.readyState == 4) {
		if (HttpReq.status == 200) {
			document.getElementById(divUpd).innerHTML = HttpReq.responseText;
			var cnv = document.getElementById('cnv'+divUpd.substr(6));
			if (cnv != null && cnv.innerHTML != '') {
				var ctx = cnv.getContext('2d');
				ctx.strokeStyle = '#000000';
				ctx.fillStyle = '#' + cnv.innerHTML;
				ctx.strokeRect(0.5,0.5,49,19);
				ctx.fillRect(1,1,48,18);
			}
		} else {
			alert("Erro: " + HttpReq.statusText);
		}
	}
}
function getCorDB(cor,field) {
	divUpd = 'divcor'+field;
	var url = 'getCorDB.php?cor='+cor+'&field='+field;
	conecta(url,updateCorDB);
}
window.addEventListener('mousemove', mouseMove, false);
