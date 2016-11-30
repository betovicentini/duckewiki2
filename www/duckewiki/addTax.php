<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
require_once './model/tax.php';
sec_session_start();
$edit = getGet('edit');
if ($edit == '') {
	$title = txt('novo').' '.txt('tax');
} else {
		$title = txt('edit').' '.txt('tax');
}
$corBG = $_SESSION['cfg.corbut'];
?>
<!DOCTYPE html> 
	<html lang='BR'>
	<head>
		<meta charset='UTF-8'>
		<title> <?= $title ?> </title>
		<link rel="stylesheet" type="text/css" href="css/cssDuckeWiki.css">
		<script src='js/funcoes.js'></script>
<script>
function getVals(text,sep,titles) { // pega os valores das colunas especificadas (titles) na primeira linha abaixo dos títulos
    // os titles devem estar na mesma ordem encontrada em text (mais rápido)
    // não podia haver títulos com início repetido (ex: 'year full' e 'year') -> (t+'%') resolveu, mas não pode incluir a última coluna
    // as linhas devem ser separadas por alguma tag <br>, <br />, mas apenas 1 tag! (não <p></p>, p.ex.)
    var ps=[], p=0, n=0, t;
    do {
        t = titles[n];
        while (text.indexOf(t+'%') > 0) { // remove de text o que não for t
            text = text.substr(text.indexOf('%')+1);
            p++;
        }
        ps.push(p);
        n++;
    } while (n < titles.length);
    text = text.substr(text.indexOf('>')+1); // encontra a mudança de linha
    var vals=[],i;
    textArr = text.split('%');
    for (i=0; i<textArr.length; i++) {
        if (ps.indexOf(i) >= 0) {
            vals.push(textArr[i]);
        }
    }
    return(vals);
}
function update2() { // getIPNI.php (busca dados automaticamente do IPNI)
    var pronto = false;
    if (HttpReq.readyState == 4 && HttpReq.status == 200) {
        /*var div = document.getElementById(divUpd);
        div.innerHTML = HttpReq.responseText;*/
        pronto = true;
        var titles = ['Id','Species author','Rank','Authors','Basionym author','Publishing author','Publication','Collation','Publication year full','Publication year','Basionym','Nomenclatural synonym','Geographic unit as text'];
        var vals = getVals(HttpReq.responseText,'%',titles);
        if (typeof vals[3] !== 'undefined') {
			document.getElementsByName('txtauttxt')[0].value = vals[3];
			document.getElementsByName('txtbibtxt')[0].value = vals[6]+' '+vals[7];
			document.getElementsByName('txtano')[0].value = vals[8];
			document.getElementById('radmorfN').checked = true; // alert! SE ... ?
			document.getElementById('spnBib').style.display = 'inline';
			document.getElementById('spnAutor').style.display = 'none';
		} else {
			document.getElementsByName('txtauttxt')[0].value = '';
			document.getElementsByName('txtbibtxt')[0].value = '';
			document.getElementsByName('txtano')[0].value = '';
			document.getElementById('radmorfN').checked = false;
			document.getElementById('radmorfS').checked = false;
			document.getElementById('radvalidN').checked = false;
			document.getElementById('radvalidS').checked = false;
			document.getElementById('spnBib').style.display = 'none';
			document.getElementById('spnAutor').style.display = 'none';
		}
    } else {
        if (HttpReq.readyState == 4) {
            pronto = true;
            alert("Erro: " + HttpReq.statusText);
        }
    }
    if (pronto) {
        var div1 = document.getElementById('divDialog');
        div1.style.visibility = 'hidden';
        var divO = document.getElementById('divOverlay');
        divO.style.visibility = 'hidden';
    }
}
function update3() { // getMOBOT.php (busca nomes válidos para ID pelo MOBOT)
    var pronto = false;
    if (HttpReq.readyState == 4 && HttpReq.status == 200) {
		pronto = true;
		var div = document.getElementById(divUpd);
        div.innerHTML = "";
        div.innerHTML = HttpReq.responseText;
    } else {
        if (HttpReq.readyState == 4) {
			pronto = true;
            alert("Erro: " + HttpReq.statusText);
        }
    }
    if (pronto) {
        var div1 = document.getElementById('divDialog');
        div1.style.visibility = 'hidden';
        var divO = document.getElementById('divOverlay');
        divO.style.visibility = 'hidden';
    }
}

function update1() { // getMOBOT.php (busca dados automaticamente do MOBOT)
    var pronto = false;
    if (HttpReq.readyState == 4 && HttpReq.status == 200) {
        /*var div = document.getElementById(divUpd);
        div.innerHTML = HttpReq.responseText;*/
        var t = HttpReq.responseText;
        var p = t.indexOf('}');
        if (p < 0) {
            // Não encontrou no MOBOT, procura no IPNI
            p = t.indexOf('[');
            t = t.substr(p+1);
            p = t.indexOf(']');
            var pai = t.substr(0,p);
            t = t.substr(p+2);
            p = t.indexOf(']');
            var nome = t.substr(0,p);
            t = t.substr(p+2);
            p = t.indexOf(']');
            var rank1 = t.substr(0,p);
            t = t.substr(p+2);
            p = t.indexOf(']');
            var rank2 = t.substr(0,p);
            var div1 = document.getElementById('divDialog');
            div1.innerHTML = 'Procurando IPNI...';
            var url = 'getIPNI.php?pai='+pai+'&nome='+nome+'&rank1='+rank1+'&rank2='+rank2;
            conecta(url,update2);
        } else { // encontrou no MOBOT
            pronto = true;
            var ID = t.substr(1,p-1);
            //alert(ID);
			t = t.substr(p+1);
			p = t.indexOf('}');
			f = t.substr(1,p-1);
            if (f == 'Legitimate' || f=='No opinion') {
                document.getElementsByName('valrepos')[0].value = ID;
					store(document.getElementsByName('valrepos')[0]);
                document.getElementsByName('txtrepos')[0].value = "MOBOT";
					store(document.getElementsByName('txtrepos')[0]);
                document.getElementById('radvalidS').checked = true;
  					store(document.getElementById('radvalidS'));              
                // SÓ MOSTRA O RESTO QUANDO FOR LEGÍTIMO?
                t = t.substr(p+1);
                p = t.indexOf('}');
                f = t.substr(1,p-1);
                document.getElementsByName('txtauttxt')[0].value = f;
					store(document.getElementsByName('txtauttxt')[0]);

                t = t.substr(p+1);
                p = t.indexOf('}');
                f = t.substr(1,p-1);
                document.getElementsByName('txtbibtxt')[0].value = f;
					store(document.getElementsByName('txtbibtxt')[0]);

                t = t.substr(p+1);
                p = t.indexOf('}');
                f = t.substr(1,p-1);
                document.getElementsByName('txtano')[0].value = f;
  					store(document.getElementsByName('txtano')[0]);              
				p = t.indexOf('[')+1;
				t = t.substr(p);
				p = t.indexOf(']');
				var pai = t.substr(0,p);
				t = t.substr(p+2);
				p = t.indexOf(']');
				var nome = t.substr(0,p);
				// Required fields: author, title, journal, year, volume
				// Optional fields: number, pages, month, note, key
				// https://en.wikipedia.org/wiki/BibTeX
				var bibTex = '@article{'+pai+'_'+nome+",\nauthor={"+document.getElementsByName('txtauttxt')[0].value+
					"},\ntitle={},\nyear={"+document.getElementsByName('txtano')[0].value+
					"},\njournal={},\nvolume={}\n}";
				if (document.getElementsByName('txtnome')[0].value != nome) {
					document.getElementsByName('txtnome')[0].value = nome;
  					store(document.getElementsByName('txtnome')[0]);              
				}
				document.getElementById('radmorfN').checked = true;
  					store(document.getElementById('radmorfN'));              
				document.getElementById('spnBib').style.display = 'inline';
				document.getElementById('spnAutor').style.display = 'none';
            } //else {
                // não é legítimo, pede nomes válidos para aquele ID
				//var url = 'getMOBOT.php?id='+ID; // não estou usando mais o valid...
				// ...E TAMBÉM DEVE ATUALIZAR O 'PUBLICADO'
				//conecta(url,update3);
            //}
        }
        // FALTA DAR O STORE() EM TUDO
    } 
    else {
        if (HttpReq.readyState == 4) {
            alert("Erro: " + HttpReq.statusText);
            pronto = true;
        }
    }
    if (pronto) {
        var div1 = document.getElementById('divDialog');
        div1.style.visibility = 'hidden';
        var divO = document.getElementById('divOverlay');
        divO.style.visibility = 'hidden';
    }
}
function updateRank() {
    if (HttpReq.readyState == 4) {
		if (HttpReq.status == 200) {
			var div = document.getElementById(divUpd);
			div.innerHTML = HttpReq.responseText;
			showMorf(document.getElementsByName('selrank')[0]);
		} else {
            alert("Erro: " + HttpReq.statusText);
		}
    }
}
function getValidName(nome) {
	divUpd = 'divMOBOT';
	var pai = document.getElementById('txttaxpai').value;
	nome = nome.substr(nome.indexOf(' ')+1); // tira o pai do nome
	var hid = document.getElementById('valtaxpai');
	var pbar = hid.value.indexOf('|');
	var rankPai = hid.value.substr(pbar+1);
	var rankNome = document.getElementsByName('selrank')[0].value;
	var url = 'getMOBOT.php?pai='+pai+'&nome='+nome+'&rank1='+rankPai+'&rank2='+rankNome;
	
	// ERRO: DEVE OU NÃO MOSTRAR TÁXONS QUE NÃO SEJAM ESPÉCIE (EX: Bixa orellana var. urucurana)?
	
	conecta(url,update1);
}
function clickMOBOT() {
	var pai = document.getElementById('txttaxpai').value;
	var nome = document.getElementsByName('txtnome')[0].value;
	if (pai != '' && nome != '') {
		var url = 'http://www.tropicos.org/NameSearch.aspx?name='+pai+'+'+nome;
		window.open(url);
	}
}
function getMOBOT() {
	var pai = document.getElementById('txttaxpai').value;
	var nome = document.getElementsByName('txtnome')[0].value;
	var hid = document.getElementById('valtaxpai');
	var pbar = hid.value.indexOf('|');
	var rankPai = hid.value.substr(pbar+1);
	var rankNome = document.getElementsByName('selrank')[0].value;
	var radS = document.getElementById('radmorfS');
	var radN = document.getElementById('radmorfN');
	if (pai != '' && nome != '' && radN.checked) {
		var div1 = document.getElementById('divDialog');
		div1.innerHTML = 'Procurando MOBOT...';
		div1.style.visibility = 'visible';
		var divO = document.getElementById('divOverlay');
		divO.style.visibility = 'visible';
		
		divUpd = 'divMOBOT';
		var url = 'getMOBOT.php?pai='+pai+'&nome='+nome+'&rank1='+rankPai+'&rank2='+rankNome;
		conecta(url,update1);
	} else {
		alert("Precisa PRIMEIRO indicar um pai, o nome e se é nome publicado");
	}
}

/*ESTA FUNCAO ATUALIZA A PAGINA COM O RETORNO DE getMycobank.php*/
function updateMycoBank() {  // getMycobank.php (busca dados automaticamente do Mycobank)
    var pronto = false;
    if (HttpReq.readyState == 4 && HttpReq.status == 200) {
        /*div.innerHTML = HttpReq.responseText; */
        var myArr = JSON.parse(HttpReq.responseText);
        var resposta = myArr['resposta'];
        var div1 = document.getElementById('divDialog');
        div1.innerHTML = resposta;
        var myDados = myArr['dados'];
        var dl =  myDados.length;
        if (dl>0) {
        var i;
        var onome;
        var ovalue;
        var out = "";
        for(i = 0; i < myDados.length; i++) {
	        onome =  myDados[i].name;
	        ovalue =  myDados[i].value;
	        if (onome=='mycobankid') {
				document.getElementsByName('valrepos')[0].value = ovalue;
				document.getElementsByName('txtrepos')[0].value = "Mycobank";
				store(document.getElementsByName('valrepos')[0]);
				store(document.getElementsByName('txtrepos')[0]);
			}
			if (onome=='autor') {
				document.getElementsByName('txtauttxt')[0].value = ovalue;
				store(document.getElementsByName('txtauttxt')[0]);
			}
			if (onome=='pubrevista') {
				document.getElementsByName('txtbibtxt')[0].value = ovalue;
				store(document.getElementsByName('txtbibtxt')[0]);
			}
			if (onome=='pubano') {
				document.getElementsByName('txtano')[0].value = ovalue;
				store(document.getElementsByName('txtano')[0]);
			}
			if (onome=='nome' && document.getElementsByName('txtnome')[0].value != ovalue) {
				document.getElementsByName('txtnome')[0].value = ovalue;
				store(document.getElementsByName('txtnome')[0]);
			}
		}
		document.getElementById('radvalidS').checked = true;
  					store(document.getElementById('radvalidS'));              
		document.getElementById('radmorfN').checked = true;
  					store(document.getElementById('radmorfN'));              
		document.getElementById('spnBib').style.display = 'inline';
		document.getElementById('spnAutor').style.display = 'none';
		} 
		else {
			alert(resposta);
            pronto = true;
		}
	//var aut = document.getElementsByName('txtauttxt')[0].value;
	//var aut2 = aut.split(",");
	//if (aut2.length>1) {
		//var lastname = aut2[0];
	//} else {
		//var lastname = aut;
	//}
	//var ano = document.getElementsByName('txtano')[0].value;
	//var revista = document.getElementsByName('txtbibtxt')[0].value;
	//var bibTex = '@article{'+lastname+'_'+ano+",\nauthor={"+aut+"},\ntitle={},\nyear={"+ano+"},\njournal={"+revista+"},\nvolume={}\n}";
		pronto = true;
	}   else {
        if (HttpReq.readyState == 4) {
            alert("Erro: " + HttpReq.statusText);
            pronto = true;
        }
    }
    if (pronto) {
        div1.style.visibility = 'hidden';
        var divO = document.getElementById('divOverlay');
        divO.style.visibility = 'hidden';
    }
}

function getMycobank() {
	var pai = document.getElementById('txttaxpai').value;
	var nome = document.getElementsByName('txtnome')[0].value;
	var hid = document.getElementById('valtaxpai');
	var pbar = hid.value.indexOf('|');
	var rankPai = hid.value.substr(pbar+1);
	var rankNome = document.getElementsByName('selrank')[0].value;
	var radS = document.getElementById('radmorfS');
	var radN = document.getElementById('radmorfN');
	if (pai != '' && nome != '' && radN.checked) {
		var div1 = document.getElementById('divDialog');
		div1.innerHTML = 'Procurando Mycobank...';
		div1.style.visibility = 'visible';
		var divO = document.getElementById('divOverlay');
		divO.style.visibility = 'visible';
		divUpd = 'divMOBOT';
		var url = 'getMycobank.php?pai='+pai+'&nome='+nome+'&rank1='+rankPai+'&rank2='+rankNome;
		//alert(url);
		conecta(url,updateMycoBank);
	} else {
		alert("Precisa PRIMEIRO indicar um pai, o nome e se é nome publicado");
	}
}

function showMorf(who) {
	var morf = document.getElementById('divMorf');
	var espec = document.getElementById('divEspec');
	//if (who.value >= 140) {
		morf.style.display = 'block';
	//} else {
		//morf.style.display = 'none';
	//}
	//alert(who.value);
	if (who.value <= 180 || who.value == 300) { // ou 'Clado'
		espec.style.display = 'block';
	} else {
		espec.style.display = 'none';
	}
}
function clkSit(n) {
	var bib, aut, divV;
	bib = document.getElementById('spnBib');
	fsbib = document.getElementById('fsBib');
	fsaddinfo = document.getElementById('fsaddinfo');
	fsautor = document.getElementById('fsautor');
	fsForm  = document.getElementById('fsForm');
	aut = document.getElementById('spnAutor');
	outext = document.getElementById('dloutext');
	//divV = document.getElementById('divValid');
	mobot = document.getElementById('imgmobot');
	mobotlink = document.getElementById('imgmobotlink');
	mycobank = document.getElementById('imgmycobank');
	mycobanklink = document.getElementById('imgmycobanklink');
	divN = document.getElementById('divNome');
	divN.style.display = 'block';
	fsautor.style.display = 'block';
	fsForm.style.display = 'block';
	if (n > 1) { // publicado
		fsbib.style.display = 'block';
		bib.style.display = 'inline';
		aut.style.display = 'inline';
		//divV.style.display = 'block';
		fsaddinfo.style.display = 'block';
		outext.style.display = 'inline';
		mobot.style.display = 'none';
		mycobank.style.display = 'none';
		mobotlink.style.display = 'inline';
		mycobanklink.style.display = 'inline';
	} else { // não publicado
		fsbib.style.display = 'none';
		aut.style.display = 'inline';
		outext.style.display = 'none';
		fsaddinfo.style.display = 'none';
		mobot.style.display = 'none';
		mobotlink.style.display = 'none';
		mycobanklink.style.display = 'none';
		mycobank.style.display = 'none';
	}
}
function handlePopupResult(id,who='',texto='') {
	var txt = document.getElementById('txt'+who);
	txt.value = texto;
	var hid = document.getElementById('val'+who);
	hid.value = id;
}
function fechaLogo(id,who='',texto='') {
	window.opener.handlePopupResult(id,who,texto); // a janela que chamar uma segunda, e esperar retorno, deve ter uma função 'handlePopupResult'
	//clearLocalStore('frmStuff');
    window.close();
}
function aoCarregar(edit) {
	if (typeof edit === 'undefined' || edit == '') { // senão usa os valores do id a ser editado
		refill('frmTax');
		//var bib, aut, radS, radN, divV, fsbib, fsaddinfo, outext, fsautor; // DENTRO DESTE IF?
		var bib = document.getElementById('spnBib');
		var aut = document.getElementById('spnAutor');
		var fsbib = document.getElementById('fsBib');
		var fsaddinfo = document.getElementById('fsaddinfo');
		var outext = document.getElementById('dloutext');
		var fsautor = document.getElementById('fsautor');
		var fsForm  = document.getElementById('fsForm');
		var radS = document.getElementById('radmorfS');
		var radN = document.getElementById('radmorfN');
		var divV = document.getElementById('divValid');
		var divN = document.getElementById('divNome');
		var divM = document.getElementById('divMorf');
		//divR = document.getElementById('divRank');
		var mobot = document.getElementById('imgmobot');
		var mobotlink = document.getElementById('imgmobotlink');
		var mycobank = document.getElementById('imgmycobank');
		var mycobanklink = document.getElementById('imgmycobanklink');
		var oreposid = document.getElementsByName('valrepos')[0].value;
		var orepostxt = document.getElementsByName('txtrepos')[0].value;
		var rankNome = document.getElementsByName('selrank')[0].value;
		if (radN.checked) { // publicado
			bib.style.display = 'inline';
			aut.style.display = 'inline';
			outext.style.display = 'inline';
			divV.style.display = 'block';
			divN.style.display = 'block';
			if (orepostxt=='Mycobank') {
				mobot.style.display = 'none';
				mobotlink.style.display = 'none';
				mycobank.style.display = 'inline';
			} else {
				if (orepostxt=='MOBOT') {
					mycobank.style.display = 'none';
					mobotlink.style.display = 'none';
					mycobanklink.style.display = 'none';
				}  else {
					mycobank.style.display = 'none';
					mobot.style.display = 'none';
				}
			}
		} else
		if (radS.checked) { // não publicado
			bib.style.display = 'none';
			aut.style.display = 'inline';
			outext.style.display = 'none';
			fsaddinfo.style.display = 'none';
			fsbib.style.display = 'none';
			divV.style.display = 'none';
			divN.style.display = 'block';
			mycobank.style.display = 'none';
			mobot.style.display = 'none';
			mobotlink.style.display = 'none';
			mycobanklink.style.display = 'none';
		} else {
			//se nao esta marcado esconde o resto
			fsaddinfo.style.display = 'none';
			fsbib.style.display = 'none';
			fsautor.style.display = 'none';
			fsForm.style.display = 'none';
			divN.style.display = 'none';
			divV.style.display = 'none';
			divM.style.display = 'none';
		}
	} else {
		var rank = document.getElementsByName('selrank')[0];
		showMorf(rank);
	}
	document.getElementsByName("txttaxpai")[0].focus();
}
</script>
<?php
$tabela = 'tax';
$update = getGet('update');
$close = getGet('close');
if ($edit == '') {
	emptyRow($tabela); // cria uma variável php para cada coluna na tabela 'tax', todas vazias
} else {
	updateRow($tabela,$edit); // cria uma variável php para cada coluna na tabela 'tax', com os valores de id = $edit
}
$body = "<body onload='aoCarregar(\"$edit\")'>";
$divRes = '';
if (!empty($post)) {
	$v1 = $_SESSION['user_id'];
	$v2 = date('d/m/Y H:i:s');

	//date('d/m/Y H:i:s'),

	$taxon = new Taxon(
		$_SESSION['user_id'],
		date('Y-m-d H:i:s'),
		getPost('txtnome'),
		getPost('selrank'),
		getPost('valtaxpai'),
		getPost('radvalid'),
		getPost('valespec'),
		getPost('valaut'),
		getPost('txtauttxt'),
		getPost('txtano'),
		getPost('txtidadecrown'),
		getPost('txtidadestem'),
		getPost('valbib'),
		getPost('txtbibtxt'),
		getPost('valrepos'),
		getPost('txtrepos')
		);

	$arrPar =  $taxon->getArray();
	//echo "<pre>";
	//print_r($arrPar);
	//echo "</pre>";
	//$arrPar = array($v1,$v2,$v3,$v4,$v5,$v6,$v7,$v8,$v9,$v10,$v11,$v12,$v13,$v14);
	$cols = 'addby,adddate,nome,rank,taxpai,valido,sinonvalid,espec,public,aut,auttxt,ano,idadecrown,idadestem,bib,bibtext,reposid,repos'; // deve ir numa linha só, sem espaços
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
					//$body = "<body onload='fechaLogo($edit)'>";
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
		echo "Existe?<BR>";
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

echo "<fieldset>";

$cmbLabel = txt('taxpai');
$cmbNeed = true;
$cmbTableName = 'tax';
$cmbFieldNames = 'nome,rank';
$who = 'taxpai';
$cmbQuery = 'taxpai';
$cmbPHP = '';
include('build_cmb.php');
?>
<div id='divRank'>
	<?php include "getRank.php"; // $hidValue from taxpai ?>
</div>
<div id='divMorf'>
	<dl>
		<?= dtlab('sit',true) ?>
		<dd>
			<?php
				$morfs = "";
				$morfn = "";
				if ($morf == 'S') {
					$morfs = "checked";
				} elseif ($morf == 'N') { 
					$morfn = "checked";
				}
				echo "<input required type='radio' id='radmorfS' name='radmorf' ".$morfn." value='S' onclick='store(this);clkSit(1)'>".txt('publN');
				echo " <input required type='radio' id='radmorfN' name='radmorf' ".$morfs." value='N' onclick='store(this);clkSit(2)'>".txt('publS');
			?>
		</dd>
	</dl>
</div>
<div id='divNome'>
<dl>
	<?= dtlab('nome') ?>
	<dd>
		<input required type='text' name='txtnome' value=<?= "'$nome'";?> oninput='store(this)'  />
		<!--- onblur='getMOBOT()' /> --->
		<button id='imgmobotlink' style=<?= "'$corBG'";?> title=<?= "'".txt('ch.mobot')."'" ?>  type='button'  onclick='getMOBOT()' ><img style='cursor: pointer;'  src='icon16/cor/mobotlink.png' height='16' /></button>
		<button id='imgmycobank'  style=<?= "'$corBG'";?> title=<?= "'".txt('tit.mycobank.mycobank')."'"?>  type='button'  onclick='alert("nao implementado")'  ><img style='cursor: pointer;' src='icon16/cor/mycobank.png'  height='16'   /></button>
		<button id='imgmycobanklink' style=<?= "'$corBG'";?> title=<?= "'".txt('ch.mycobank')."'"?>  type='button'  onclick='getMycobank()' ><img style='cursor: pointer;' src='icon16/cor/mycobanklink.png'  height='16'   /></button>
		<button id='imgmobot' style=<?= "'$corBG'";?> title=<?= "'".txt('tit.mobot')."'" ?>  type='button'  onclick='clickMOBOT()' ><img style='cursor: pointer;'  src='icon16/cor/mobot.png' height='16'  /></button>
	</dd>
</dl>
</div>
<div>
<dl>
	<dd>
<input type='hidden' name='valrepos' value=<?= "'$reposid'";?> onchange='store(this)'  /> 
<input type='hidden' name='txtrepos' value=<?= "'$repos'";?> onchange='store(this)'   />
	</dd>
</dl>
</div>
</div>
<div id='divMOBOT'></div>
	<div id='divValid'>
		<dl>
			<?= dtlab('valid',true) ?>
			<dd>
				<?php
					if ($valido == 'S') {
						echo "<input required type='radio' id='radvalidS' name='radvalid' value='S' onclick='store(this)' checked>".txt('validS');
						echo " <input required type='radio' id='radvalidN' name='radvalid' value='N' onclick='store(this)'>".txt('validN');
					} else
					if ($valido == 'N') {
						echo "<input required type='radio' id='radvalidS' name='radvalid' value='S' onclick='store(this)'>".txt('validS');
						echo " <input required type='radio' id='radvalidN' name='radvalid' value='N' onclick='store(this)' checked>".txt('validN');
					} else {
						echo "<input required type='radio' id='radvalidS' name='radvalid' value='S' onclick='store(this)'>".txt('validS');
						echo " <input required type='radio' id='radvalidN' name='radvalid' value='N' onclick='store(this)'>".txt('validN');
					}
				?>
			</dd>
		</dl>
	</div>
</fieldset><br/>
<fieldset id='fsautor' >
	<legend><?= txt('aut')?> 
		<span id='spnAutor' style='color:red;display:none'>*</span>
	</legend>
	<?php
		$cmbLabel = txt('tab')." '".txt('pesss')."'";
		$cmbTableName = 'pess';
		$cmbFieldNames = 'abrev,prenome';
		$who = 'aut';
		$cmbQuery = 'pess';
		$cmbPHP = 'addPess.php';
		include('build_cmb.php');
	?>
	<dl id='dloutext'>
		<dt>
			<label><?= txt('outext') ?></label>
		</dt>
		<dd>
			<input type='text' name='txtauttxt'  value=<?= "'$aut'";?>  oninput='store(this)' />
		</dd>
	</dl>
</fieldset>
<br/>


<fieldset id='fsBib' >
	<legend> 
		<?=txt('bibref').' '.txt('bibrefsorig')?> 
		<span id='spnBib' style='color:red;display:none'>*</span>
	</legend>
	<?php
		$cmbLabel = txt('tab')." '".txt('refs')."'";
		$cmbTableName = 'bib';
		$cmbFieldNames = 'title,autor';
		$who = 'bib';
		$cmbQuery = 'bib';
		$cmbPHP = 'addBib.php';
		include('build_cmb.php');
	?>
	<dl>
		<dt>
			<label><?= txt('text') ?></label>
		</dt>
		<dd>
			<input type='text' name='txtbibtxt'  value=<?= "'$bibtext'";?> oninput='store(this)' />
		</dd>
	</dl>
	<dl>
		<dt>
			<label><?= txt('ano') ?></label>
		</dt>
		<dd>
			<input type='text' name='txtano' value=<?= "'$ano'";?>  oninput='store(this)' />
		</dd>
	</dl>
</fieldset><br>
<fieldset id='fsaddinfo' >
	<legend> 
		<?=txt('taxaddinfo')?> 
	</legend>
<dl>
	<dt>
		<label><?= txt('idadecrown').' ('.txt('MA').')' ?> </label>
	</dt>
	<dd>
		<input type='text' name='txtidadecrown' value=<?= "'$idadecrown'";?> oninput='store(this)' />
	</dd>
	</dl>
	<dl>
		<dt>
			<label> <?= txt('idadestem').' ('.txt('MA').')'?> </label>
		</dt>
		<dd>
			<input type='text' name='txtidadestem' value=<?= "'$idadestem'";?>  oninput='store(this)' />
		</dd>
	</dl>
	<div id='divEspec'>
		<?php	
			$mmLabelH = txt('especs');
			$mmLabel1 = txt('pesdisp');
			$mmLabel2 = txt('pessel');
			$who = 'especs';
			$mmQuery = 'taxespec';
			$mmTableName = 'pess';
			$mmTableLink = 'taxespec.espec';
			include('build_mm.php');
		?>
	</div>
<?php
	$mmLabelH = txt('sins');
	$mmLabel1 = txt('sindisp');
	$mmLabel2 = txt('sinsel');
	$who = 'sins';
	$mmQuery = 'taxsin';
	$mmTableName = 'tax';
	$mmTableLink = 'taxsin.sin';
	include('build_mm.php');
?>
<?php
	$mmLabelH = txt('verns');
	$mmLabel1 = txt('esp.verndisp');
	$mmLabel2 = txt('esp.vernsel');
	$who = 'verns';
	$mmTableName = 'vern';
	$mmQuery = 'vern';
	$mmTableLink = 'espvern.vern';
	include('build_mm.php');
?>
</fieldset><br />
<fieldset id='fsForm' >
<input type='hidden' name='hidmmData' value=<?= "'$hidmmData'";?> />
<?php
$col = 'tax';
// variáveis já marcadas praquele táxon
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
?>
</div>
</fieldset>

<div id='divOverlay'>
	<div id='divDialog'></div>
</div>
<div id='tooltip'></div>

<?= echoButtons(); ?>
</form>
</body>
</html>
