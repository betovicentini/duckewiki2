<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
require_once './model/herb.php';
sec_session_start();
$edit = getGet('edit');
if ($edit == '') {
	$title = txt('novo').' '.txt('herb');
} else {
	$title = txt('edit').' '.txt('herb');
}
?>
<!DOCTYPE html> 
	<html lang='BR'>
	<head>
		<meta charset='UTF-8'>
		<title><?= $title ?></title>
		<link rel="stylesheet" type="text/css" href="css/cssDuckeWiki.css">
		<script src='js/funcoes.js'></script>
<script type="text/javascript" >
/*function fechaLogo(id,who,texto) {
	window.opener.handlePopupResult(id,who,texto);
    window.close();
}*/
function siglatoup(str) {
	var sg = str.toUpperCase();
	document.getElementsByName('txtsigla')[0].value = sg
}
/*ESTA FUNCAO ATUALIZA A PAGINA COM O RETORNO DE getIdxherb.php*/
function updateIdxHerb() {  
    var pronto = false;
	var div1 = document.getElementById('divDialog');
    if (HttpReq.readyState == 4 && HttpReq.status == 200) {
        /*div.innerHTML = HttpReq.responseText; */
        var myArr = JSON.parse(HttpReq.responseText);
        var resposta = myArr['resposta'];
        //alert(resposta);
        div1.innerHTML = resposta;
        var myDados = myArr['dados'];
        var i;
        var onome;
        var ovalue;
        var out = "";
        var fields = document.getElementById('divRest');
		fields.style.display = 'block';
        for(i = 0; i < myDados.length; i++) {
	        onome =  myDados[i].name;
	        ovalue =  myDados[i].value;
			if (onome=='nome') {
				document.getElementsByName('txtnome')[0].value = ovalue;
			}
			if (onome=='endereco') {
				document.getElementsByName('txaend')[0].innerHTML = ovalue;
			}
			if (onome=='phone') {
				document.getElementsByName('txtfone')[0].value = ovalue;
			}
			if (onome=='email') {
				document.getElementsByName('txtmail')[0].value = ovalue;
			}
			if (onome=='website') {
				document.getElementsByName('txturl')[0].value = ovalue;
			}
			if (onome=='correspondent') {
				//alert(ovalue);
				document.getElementsByName('txacorresp')[0].innerHTML = ovalue;
				//document.getElementsById('txacorresp').innerHTML = "\""+ovalue+"\"";
			}
		}
		pronto = true;
	}   
	else {
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
// busca em index herbariorum
function getIdxHerb() {
	var sigla = document.getElementsByName('txtsigla')[0].value;
	//alert(sigla);
	if (sigla !=='') {
		var div1 = document.getElementById('divDialog');
		div1.innerHTML = 'Procurando Index Herbariorum...';
		div1.style.visibility = 'visible';
		var divO = document.getElementById('divOverlay');
		divO.style.visibility = 'visible';
		divUpd = 'divIDXHERB';
		var url = 'getIdxHerb.php?sigla='+sigla;
		//alert(url);
		conecta(url,updateIdxHerb);
	} else {
		alert("Precisa PRIMEIRO indicar a sigla do herbário");
	}
}
function aoCarregar(edit) {
	if (typeof edit === 'undefined') {
		refill('frmHerb');
		var fields = document.getElementById('divRest');
		fields.style.display = 'none';
	}
	document.getElementsByName("txsigla")[0].focus();
}
</script>

<?php
$tabela = 'herb';
$update = getGet('update');
$close = getGet('close');
$h1 = "<h1 style='text-align:center'>$title</h1>";
if ($edit == '') {
	emptyRow($tabela);
} else {
	updateRow($tabela,$edit);
}
$body = "<body onload='aoCarregar(\"$edit\")' >";
$divRes = '';
if (!empty($post)) {
	$herbario = new Herbario(
		$_SESSION['user_id'],
		date('Y/m/d H:i:s'),
		getPost('txtnome'),
		getPost('txtsigla'),
		getPost('valcurad'),
		getPost('txaend'),
		getPost('txtfone'),
		getPost('txturl'),
		getPost('txtmail'),
		getPost('txacorresp')
	);
	$arrPar =  $herbario->getArray();
	//$arrPar = array($v1,$v2,$v3,$v4,$v5,$v6,$v7,$v8);
	$cols = 'addby,adddate,nome,sigla,curad,ender,fone,url,email,contato'; // deve ir numa linha só, sem espaços
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
		} else { // nada mudou na tabela principal
			$divRes = "<div id='divSucesso' style='background-color:#FF0000'>Não há mudanças a atualizar!</div>";
		}
		/*$MMs = explode('|',get('hidmmData'));
		echo "MMs = ".get('hidmmData')."<BR><BR>";
		foreach ($MMs as $MM) {
			$M = explode(';',$MM);
			if (atualizaSubTabela($M,$divRes1)) {
				$divRes.=$divRes1;
			}
		}*/
	} else { // não está editando -> insere
		switch (registroExiste($tabela,$validcols=array("sigla"))) {
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

?>
</head>
<body onload='aoCarregar(<?= "$edit" ?>)'>
<?php
$loginErro = login_error($conn);
if ($loginErro) {
	exit("<p>Você não tem autorização de acesso a esta página (erro: $loginErro). Por favor, <a href='index.php' target='main'>volte para a tela inicial e faça o login</a>.</p>");
}
echoHeader();
?>
<div id='divSigla'>
<dl >
	<?= dtlab('herb.sigla') ?>
	<dd>
		<input required name='txtsigla' type='text' value=<?= "'$sigla'" ?> onkeyup='requiredKeyUp()'  oninput='store(this)' onblur='siglatoup(this.value);getIdxHerb(this.value)' />
	</dd>
</dl>
</div>
<div id='divIDXHERB'></div>
<div id='divRest'>
<div>
<dl id='dlNome' >
	<?= dtlab('herb.nome') ?>
	<dd>
		<input required name='txtnome' type='text' value=<?= "'$nome'" ?> onkeyup='requiredKeyUp()' oninput='store(this)' onblur='corrige(this)' />
	</dd>
</dl>
</div>
<div id='divPess' >
<?php
	$cmbLabCode = 'herb.cur';
	$cmbTableName = 'pess';
	$cmbFieldNames = 'curad';
	$cmbCaseSensitive = 0;
	$who = 'curad';
	$cmbQuery = 'pess';
	$cmbPHP = 'addPess.php';
	include('build_cmb.php');
?>
</div>
<!-- true abaixo indica obrigatoriedade (* vermelho) -->
<div>
<dl >
	<?= dtlab('correspon') ?>
	<dd>
		<textarea name='txacorresp'  oninput='store(this)' ><?="'$contato'"?></textarea>
	</dd>
	<?= dtlab('ender') ?>
	<dd>
		<textarea id='txaend'  name='txaend'  oninput='store(this)' onblur='corrige(this)'  ><?="'$ender'"?> </textarea>
	</dd>

	<?= dtlab('fone') ?>
	<dd>
		<input id='txtfone' name='txtfone' type='text' value=<?= "'$fone'"?> oninput='store(this)' onblur='corrige(this)' />
	</dd>

	<?= dtlab('url') ?>
	<dd>
		<input id='txturl'  name='txturl' type='text' value=<?="'$url'"?> oninput='store(this)' onblur='corrige(this)' />
	</dd>

	<?= dtlab('herb.mail') ?>
	<dd>
		<input id='txtmail'  name='txtmail' type='text' value=<?="'$email'"?> oninput='store(this)' onblur='corrige(this)' />
	</dd>
</dl>
</div>
</div>

<div id='divOverlay'>
	<div id='divDialog'></div>
</div>
<div id='tooltip'></div>

<?php
	echoButtons();
?>
</form>
</body>
</html>
