<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';

require_once './model/esp.php';
sec_session_start();
$edit = getGet('edit');

if ($edit == '') {
	$title = txt('novo').' '.txt('esp');
} else {
	$title = txt('edit').' '.txt('esp');
}
?>
<!DOCTYPE html>
<html lang='BR'>
<head>
<meta charset='UTF-8'>
<link rel="stylesheet" type="text/css" href="cssDuckeWiki.css">
<script src='funcoes.js'></script>
<script>
function update2() {
    if (HttpReq.readyState == 4 && HttpReq.status == 200) {
		div = document.getElementById(divUpd);
        //div.innerHTML = "";
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

function updatePlTag() {
    if (HttpReq.readyState == 4 && HttpReq.status == 200) {
        var R = JSON.parse(HttpReq.responseText);
        var div = document.getElementById('divlocpai');
        // puxar os <dt> de txt() ainda em getPlLoc.php
        div.innerHTML = "<dl style='background-color:lightgreen'><dt>Localidade</dt><dd>"+R.loctxt+"</dd></dl>";
        div = document.getElementById('divhabpai');
        div.innerHTML = "<dl style='background-color:lightgreen'><dt>Habitat</dt><dd>"+R.habtxt+"</dd></dl>";
        div = document.getElementById('divdetpai');
        div.innerHTML = "<span id='spndet' style='background-color:lightgreen'> [det = "+R.detval+"] "+R.dettxt+"</span>";
        // esvaziar os controles novamente se apagar a planta marcada
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

function editVar(id,val) {
	divUpd = 'divDialog';
	var url = 'editVar.php?varid='+id+'&val='+val;
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
function handlePopupResult(id,who,texto) {
	if (typeof who === 'undefined') who = '';
	if (typeof texto === 'undefined') texto = '';
	// função chamada em addDet.php.fechaLogo() e addPess
	var val = document.getElementsByName('val'+who)[0];
	val.value = id;
	store(val); // precisa guardar senão perde no refill do aoCarregar()
	if (who == 'det') {
		var spn = document.getElementById('spn'+who);
		spn.innerHTML = ' [det = '+id+'] '+texto;
		store(spn);
	} else
	if (['col','loc','hab','prj'].indexOf(who) >= 0) {
		var txt = document.getElementById('txt'+who);
		txt.value = texto;
		store(txt);
	}
	modoEdit(); // muda a cor do fundo, habilita o botão "Salvar"...
}
function txtnummaxkeyup(e) {
	var fo, who=e.target;
	if ([9,13].indexOf(e.keyCode) >= 0) { // Tab ou Enter
		// foca o input adequado
		if (e.shiftKey) {
			document.getElementById('txtnum').focus();
		} else {
			// mostra o painel adequado
			document.getElementById('divcomum').style.display = 'block'; // mostra os controles mínimos
			if (who.value == '') { // apenas 1 indivíduo
				document.getElementById('divindiv').style.display = 'block'; // + controles
				document.getElementById('txtpl').focus(); // campo num2 em branco
			} else { // vários indivíduos
				document.getElementById('divindiv').style.display = 'none'; // - controles
				document.getElementById('txtdia').focus(); // campo num2 com valor
			}
		}
		e.preventDefault();
	}
}
/*function txtnummaxblur(who) {
	document.getElementById('divcomum').style.display = 'block'; // mostra os controles mínimos
	if (who.value == '') { // apenas 1 indivíduo
		document.getElementById('divindiv').style.display = 'block'; // + controles
		//document.getElementsByName('txtpl')[0].focus();
	} else { // vários indivíduos
		document.getElementById('divindiv').style.display = 'none'; // - controles
		//document.getElementsByName('txtdia')[0].focus();
	}
}*/
function preCallAdd(php,w,h,update,close,who,table,fields) {
	if (confirm("ATENÇÃO!\n\nA identificação se refere à planta marcada. Caso seja alterada, a mudança terá efeito na planta marcada e em todos os especímenes extraídos dela. Tem certeza que deseja continuar?")) {
		callAdd(php,w,h,update,close,who,table,fields);
	}
}
function pltagchange(val) {
	var url;
	if (val == '') {
		url = 'getPlLoc.php';
	} else {
		url = 'getPlLoc.php?pl='+val;
	}
	conecta(url,updatePlTag);
}
function fechaLogo(id,who,texto) {
	window.opener.handlePopupResult(id,who,texto);
    window.close();
}
function refillDivSpan() {
	// preencher de novo o div_mm e o span_det
	//alert(1);
}
//function aoCarregar(edit,pl,loc) {
function aoCarregar(edit) {
	if (typeof(edit) === 'undefined') { // não está editando (== '' vs typeof === 'undefined' ?? -> ver addEsp.php)
		refill('frmEsp');
		var colet = document.getElementById('valcol');
		divUpd = 'divcoletnum';
		var url = 'getColetNum.php?id='+colet.value;
		conecta(url,update);
	} else { // está editando
		document.getElementById('divcomum').style.display='block'; // DEPENDE DOS CAMPOS numcolet E numcoletaté
		document.getElementById('divindiv').style.display='block';
		//pltagchange(pl,edit,loc);
	}
	document.getElementById("txtcol").focus();
	prepFormRadios(document.getElementById('divFrm')); // AINDA É NECESSÁRIO AQUI? drawForm/etc não podem cuidar disso?
}
</script>
<title> <?= $title ?></title>		
<?php
$tabela = 'esp';
$update = getGet('update');
$close = getGet('close');



if ($edit == '') {
	emptyRow($tabela);
} else {
	updateRow($tabela,$edit);
}

$body = "<body onload='aoCarregar($edit)'>";
$divRes = '';

$especime = new Especime();	
if (!empty($post)) {
	echo "VARIÁVEIS NO POST: ";
	//print_r($post);
	echo "<br><br>";
	$v1 = $_SESSION['user_id'];
	$v2 = date('d/m/Y H:i:s');

	$especime->v1 = $_SESSION['user_id'];
	$especime->v2 = date('d/m/Y H:i:s');
	$especime->v3 = get('valcol');
	$especime->v4 = get('txtnum');
	$especime->v5 = get('txtdia');
	$especime->v6 = get('selmes');
	$especime->v7 = get('txtano');
	$especime->v8 = get('valdet');
	$especime->v9 = get('valhab');
	$especime->v10 = get('selherbaria');
	$especime->v11 = get('txtinpa');
	$especime->v12 = get('valloc');
	$especime->v13 = null;
	$especime->v14 = get('txtlat');
	$especime->v15 = get('txtlon');
	$especime->v16 = get('txtalt');
	$especime->v17 = get('valpl');
	$especime->v18 = get('valprj');
	$especime->v19 = get('txtsufix');
	$especime->txtfrmdia = get('txtfrmdia');
	$especime->selfrmmes = get('selfrmmes');
	$especime->txtfrmano = get('txtfrmano');
	//$v20 = get('txtverns');
	$arrPar = $especime->retornaComoArray();

	//$arrPar = array($v1,$v2,$v3,$v4,$v5,$v6,$v7,$v8,$v9,$v10,$v11,$v12,$v13,$v14,$v15,$v16,$v17,$v18,$v19,$v20);
	//$cols = 'addby,adddate,col,num,dia,mes,ano,det,hab,herb,inpa,loc,gps,lat,lon,alt,pl,prj,sufix,verns'; // deve ir numa linha só, sem espaços

	$cols = 'addby,adddate,col,num,dia,mes,ano,det,hab,herb,inpa,loc,gps,lat,lon,alt,pl,prj,sufix'; // deve ir numa linha só, sem espaços
	$arrCol = explode(',',$cols);
	if ($edit) {
		// primeiro atualiza variáveis do formulário (se tiver alguma)
		foreach ($post as $key => $value) {
			//echo "[$key: $value]<BR>";
			$inputTipo = substr($key,0,3);
			$key = substr($key,3);
			if (is_numeric($key) && $value != '' && $inputTipo != 'und') {
				//$q = "insert into var (esp,key,val,addby,adddate) values ($edit,$key,$value,$v1,$v2);";
				$q = 'insert into var (esp,key,val,addby,adddate) values ($1,$2,$3,$4,$5);';
				$res = pg_query_params($conn,$q,[$edit,$key,$value,$v1,$v2]);
				if ($res) {
					echo "$key: $value OK<BR>";
				} else {
					echo "$key: $value ERRO!<BR>";
				}
			}
		}

		$q = "update $tabela set (";
		if (algumaMudou($q)) { // pelo menos uma coluna mudou -> edita
			$arr2 = montaQuery($q);
			$res = pg_query_params($conn,$q,[$edit]);
			if ($res) {
				add2dwh($arr2,$divRes); // add to data warehouse
				updateRow($tabela,$edit);
				if ($close) { // NÃO DEVIA TER TAMBÉM O if ($update) ??
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
		echo "MMs = ".get('hidmmData')."<BR><BR>";
		foreach ($MMs as $MM) {
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
					if ($res) {
						$divRes = "<div id='divSucesso' style='background-color:#00FF00'>Registro inserido com sucesso!</div>";
						$newID = pg_fetch_array($res,NULL,PGSQL_NUM)[0];
						for ($k=0; $k<=$max-$min; $k++) {
							echo "newID = $newID<BR><BR>";
							if (insereSubTabelas(get('hidmmData'),$newID+$k,$divRes1)) {
								if ($close) { // só fecha se não der erro nas sub-tabelas
									//$body = "<body onload='fechaLogo($newID,\"$tabela\")'>";
								}
							}
						}
						$divRes.=$divRes1; // só a última (é um problema?)
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

$cmbLabCode = 'esp.col';
/*$cmbLabel = txt('esp.col');		// lab
$cmbTip = txt('esp.col.tip');*/
$cmbTableName = 'pess';		// tabref
$cmbFieldNames = 'abrev,prenome,segundonome,sobrenome';	// fLkp
$cmbCaseSensitive = 0;		// CaseSens
$who = 'col';				// field
$cmbQuery = 'pess';
$cmbPHP = 'addPess.php';	// 'add'.tabref (tabref = 'pess' -> 'pes')
include('build_cmb.php');
?>
<div id='divcoletnum'>
	<dl>
		<?= dtlab('num1') // outra versão em getColetNum.php ?>
		<dd>
			<input required name='txtnum' type='text' size=10 value=<?= "'$num'";?> oninput='store(this)' />
			<?php 
				if ($edit == '') {
					echo "<label> ".txt('num2')." </label> <img src='icon/question.png'><div class='tooltip'>".txt('num2.tip')."</div><input name='txtnummax' type='text' size=10 oninput='store(this)' onblur='txtnummaxblur(this)' />";
				}
			?>
		</dd>
	</dl>
</div>


<?php
// no caso de sequencia deve aparecer apenas: data, outros coletores, localidade, habitat, projeto
if (isset($_GET['frmid'])) {
	echo "<div id='divindiv' style='display:block'>";
} else {
	echo "<div id='divindiv' style='display:none'>";
}

$cmbLabCode = 'esp.plmarc';
$cmbTableName = 'pl';
$cmbFieldNames = 'pltag';
$cmbCaseSensitive = 0;
$who = 'pl';
$cmbQuery = 'pltag';
$cmbPHP = 'addPl.php';
include('build_cmb.php');

if ($pl != '') { // modifica variáveis para refletir planta marcada (melhor que usar getPlLoc.php?)
	$q = "select pl.id,pl.pltag,pl.loc,l.nome localidade,pl.lat,pl.lon,pl.det,pl.prj,pl.det,pl.hab,h.nome habitat
	from pl
	join loc l on l.id = pl.loc
	join hab h on h.id = pl.hab
	where pl.id=$1";
	$res = pg_query_params($conn,$q,[$pl]);
	if ($res) {
		if ($row = pg_fetch_array($res,NULL,PGSQL_ASSOC)) {
			$loc = $row['loc'];
			$locFIXO = true;
			$det = $row['det'];
			$detFIXO = true;
			$hab = $row['hab'];
			$habFIXO = true;
		}
	}
}
?>

<dl>
	<?= dtlab('esp.tax')?> 
	<dd>
		<?php
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
		?>
		<input type='hidden' name='valdet' value=<?= "'$det'";?> value='$det' />
	</dd>
</dl>
<?php	
$mmLabCode = 'verns';
//$mmLabelH = txt('verns');
$mmLabel1 = txt('esp.verndisp');
$mmLabel2 = txt('esp.vernsel');
$who = 'verns';
$mmTableName = 'vern';
$mmQuery = 'vern';
$mmTableLink = 'espvern.vern';
include('build_mm.php');

$mmLabCode = 'herbs';
//$mmLabelH = txt('esp.herb');
$mmLabel1 = txt('esp.herbdisp');
$mmLabel2 = txt('esp.herbsel');
$who = 'herbs';
$mmTableName = 'herb';
$mmQuery = 'herb';
$mmTableLink = 'espherb.herb';
$mmExtraField = 'tomb'; // código associado a cada herbário
include('build_mm.php');
?>

<dl>
	<?= dtlab('ninpa')?>
	<dd>
		<input name='txtinpa' value=<?= "'$inpa'";?> type='text' oninput='store(this)' />
	</dd>
</dl>
</div>

<?php
if (isset($_GET['frmid'])) {
	echo "<div id='divcomum' style='display:block'>";
} else {
	echo "<div id='divcomum' style='display:none'>";
}

$meses = ['',txt('mes01'),txt('mes02'),txt('mes03'),txt('mes04'),txt('mes05'),txt('mes06'),
	txt('mes07'),txt('mes08'),txt('mes09'),txt('mes10'),txt('mes11'),txt('mes12')];
?>

<dl>
	<?= dtlab('data')?>
	<dd>
		<input type='text' name='txtdia' size=5 placeholder='dia' value=<?= "'$dia'";?> oninput='store(this)' onkeyup="txtdiakeyup(this,'selmes','txtano')" />

<select name='selmes' style='width: 150px' onchange='store(this);selmeschange(this,\"txtdia\",\"txtano\")'>

<?php
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

$mmLabCode = 'esp.outcol';
//$mmLabelH = txt('esp.outcol');
$mmLabel1 = txt('esp.coldisp');
$mmLabel2 = txt('esp.colsel');
$who = 'cols';
$mmTableName = 'pess';
$mmQuery = 'pess1';				// fica (ou vai ser sempre igual a $mmTableName? OLHA DEPOIS)
$mmTableLink = 'espcols.col';	// fica (pra formar a variável pro POST)
include('build_mm.php');

echo "<div id='divlocpai'>";
$cmbLabCode = 'loc';
//$cmbLabel = txt('loc');
$cmbTableName = 'loc';
$cmbFieldNames = 'nome';
$cmbCaseSensitive = 0;
$who = 'loc';
$cmbFIXO = !empty($locFIXO);
$cmbQuery = 'loc';
$cmbPHP = 'addLoc.php';
include('build_cmb.php');
echo "</div>";

echo "<div id='divhabpai'>";
$cmbLabCode = 'hab';
//$cmbLabel = txt('hab');
$cmbTableName = 'hab';
$cmbFieldNames = 'nome';
$cmbCaseSensitive = 0;
$who = 'hab';
$cmbFIXO = !empty($habFIXO);
$cmbQuery = 'hab';
$cmbPHP = 'addHab.php';
include('build_cmb.php');
echo "</div>";

$cmbLabCode = 'prj';
//$cmbLabel = txt('prj');
$cmbTableName = 'prj';
$cmbFieldNames = 'nome';
$cmbCaseSensitive = 0;
$who = 'prj';
$cmbQuery = 'prj';
$cmbPHP = 'addPrj.php';
include('build_cmb.php');

echo "<input type='hidden' name='hidmmData' value='$hidmmData' />";

$col = 'esp';
// variáveis já marcadas praquele especímene
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
echo "</div>";


echo "</div>"; // divcomum


echo "<div id='divOverlay'>
	<div id='divDialog'></div>
</div>
<div id='tooltip'></div>";

echoButtons();
?>
</form>
</body>
</html>
