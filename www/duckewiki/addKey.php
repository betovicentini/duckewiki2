<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
require_once './model/key.php';
sec_session_start();
$edit = getGet('edit');
if ($edit == '') {
	$title = txt('nova').' '.txt('key');
} else {
	$title = txt('edit').' '.txt('key');
}

?>
<!DOCTYPE html>
<html lang='BR'>
	<head>
		<meta charset='UTF-8'>
		<title><?php $title ?> </title>
		<link rel="stylesheet" type="text/css" href="css/cssDuckeWiki.css">
		<script src='js/funcoes.js'></script>
<script>
function makealias(str) {
	var txtalias = document.getElementById('txtnomealias');
   if (txtalias.value=="") {
   var n = str.length;
   if (n>15) {
    var res = str.split(" ");
    var res2 = '';
    var r = '';
    for (i = 0; i < res.length; i++) { 
       r = res[i].substring(0, 3);
       r = r.toUpperCase();
       if (res2=='') { res2 = r } else {res2 = res2+'_'+r;}
    }
    } else { 
       var res = str.split(" ");
       res = res.join("_");
       res2 = res.toUpperCase(); 
    }
    var txtalias = document.getElementById('txtnomealias');
    txtalias.value=res2;
    }
}

function changeTipo(who) {
	var divmult = document.getElementById('divmult');
	var divunit = document.getElementById('divunit');
	var divvar = document.getElementById('divVar');
	var dlimg = document.getElementById('dlImg');
	var dldef = document.getElementById('dlDef');
	var dlnome = document.getElementById('dlNome');
	var dlclasse = document.getElementById('dlClass');
	if (who.value=="") {
	divvar.style.display = 'none';
	divmult.style.display = 'none';
	divunit.style.display = 'none';
	dlimg.style.display = 'none';
	dldef.style.display = 'none';
	dlnome.style.display = 'none';
	dlclasse.style.display = 'none';
	} else {
		dlimg.style.display = 'block';
		dldef.style.display = 'block';
		dlnome.style.display = 'block';
		dlclasse.style.display = 'block';
	}
	switch (parseInt(who.value,10)) {
		case 1 : // Classe
			divmult.style.display = 'none';
			divunit.style.display = 'none';
			document.getElementById('radmultN').checked = true;
			divvar.style.display = 'none';
			break;
		case 4 : // Quantitativo
			divmult.style.display = 'none';
			divunit.style.display = 'block';
			divvar.style.display = 'none';
			break;
		case 2: 
			divmult.style.display = 'block';
			divvar.style.display = 'block';
			divunit.style.display = 'none';
			break;
		case 9: 
			divvar.style.display = 'block';
			divunit.style.display = 'none';
			divmult.style.display = 'none';
			break;
		default :
			divvar.style.display = 'none';
			divmult.style.display = 'none';
			divunit.style.display = 'none';
	}
}
function aoCarregar(edit) {
	if (edit == '') { // senão usa os valores do id a ser editado
		refill('frmKey');
		
		
		
	}
	changeTipo(document.getElementsByName('seltipo')[0]);
	document.getElementsByName("seltipo")[0].focus();
}
</script>
<?php
$tabela = 'key';
$update = getGet('update');
$close = getGet('close');

if ($edit == '') {
	emptyRow($tabela); // cria uma variável php para cada coluna na tabela $tabela, todas vazias
} else {
	updateRow($tabela,$edit); // cria uma variável php para cada coluna na tabela $tabela, com os valores 
}

$body = "<body onload='aoCarregar(\"$edit\")'>";
$divRes = '';
if (!empty($post)) {
	$v1 = $_SESSION['user_id'];
	$v2 = date('d/m/Y H:i:s');
	
	$v3 = getPost('selClass');
	$opathname = getPost('txtnome');
	if ($v3>0) {
		$q = "select pathname from key where id=$1";
		$res = pg_query_params($conn,$q,[$v3]);
		if ($res) {
			if ($row=pg_fetch_array($res)) {
				$opathname = $row[0]." - ".$opathname;
			}
		} 
	}
	$variaveis = new Variaveis(
		$_SESSION['user_id'],
		date('d/m/Y H:i:s'),
		getPost('txakeywords'),
		getPost('radmulti'),
		getPost('selClass'),
		getPost('txadef'),
		getPost('txtnome'),
		getPost('seltipo'),
		getPost('selunit'),
		getPost('txtnomealias'),
		$opathname
	);

	$arrPar = $variaveis->getArray();
	//$arrPar = array($v1,$v2,$v3,$v4,$v5,$v6,$v7,$v8,$v9);
	$cols = 'addby,adddate,keywords,multiselect,pai,def,nome,tipo,unit,alias,pathname'; // deve ir numa linha só, sem espaços
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
		//atualizaSubTabela('sinon','taxval','taxsin','','mmValsinsStd',$divRes1);
		$MMs = explode('|',get('hidmmData'));
		//echo "MMs = ".get('hidmmData')."<BR><BR>";
		foreach ($MMs as $MM) {
			$M = explode(';',$MM);
			if (atualizaSubTabela($M,$divRes1)) {
				$divRes.=$divRes1;
			}
		}
		//$divRes.=$divRes1;
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
?>
	<dl>
		<?= dtlab('tipo.key')?>
		<dd>
			<select name='seltipo' type='hidden' value='' onchange='store(this);changeTipo(this)'>
				<option value=''></option>
				<?php
					$q = "select id,tipo from keytipo order by tipo";
					$res = pg_query($conn,$q);
					if (!isset($tipo)) {
							echo "<option value='' selected></option>";
					}
					while ($row2 = pg_fetch_array($res,NULL,PGSQL_NUM)) {
						if ($row2[0] == $tipo) {
							echo $sltxt = "selected";
						}  else { $sltxt = "";}
						echo "<option $sltxt value='$row2[0]'>$row2[1]</option>";
					}
				?>
			</select>
		</dd>
	</dl>

	<dl id='dlNome' >
		<dt><label><?= txt('nome')?> </label></dt>
		<dd><input name='txtnome' type='text' value='<?= $nome?>' oninput='store(this)' onblur='makealias(this.value)' /></dd>
		<?= dtlab('nomealias')?>
		<dd><input id='txtnomealias' name='txtnomealias' type='text' value='<?= $alias?>' oninput='store(this)' /></dd>
	</dl>
	<dl id='dlClass' >
	<dt ><label><?=txt('class')?></label></dt>
	<dd>
		<select name='selClass' type='hidden' value='' onchange='store(this)'>
			<option value=''></option>
<?php
	$q = "select pai from key where id = $1";
	$res = pg_query_params($conn,$q,[$edit]);
	$classe = pg_fetch_array($res,NULL,PGSQL_NUM)[0];

	$q = "select k1.id,k1.nome,k1.pai,k2.nome from key as k1 left join key as k2 on k1.pai=k2.id where k1.tipo = 1 order by k1.pathname";
	$res = pg_query($conn,$q);
	while ($row2 = pg_fetch_array($res,NULL,PGSQL_NUM)) {
		if (!empty($row2[3])) {
			$keylabel = $row2[3]."  ".$row2[1];
		} else { $keylabel = $row2[1];}
		if ($row2[2]) { // tem pai
			if ($row2[0] == $classe) {
				echo "<option value='$row2[0]' selected>- $keylabel</option>";
			} else {
				echo "<option value='$row2[0]'>- $keylabel</option>";
			}
		} else {
			if ($row2[0] == $classe) {
				echo "<option value='$row2[0]' style='color:#0000FF; text-shadow: 1px 1px blue;' selected>$keylabel</option>";
			} else {
				echo "<option value='$row2[0]' style='color:#0000FF; text-shadow: 1px 1px blue;'>$keylabel</option>";
			}
		}
	}
?>
		</select>
	</dd>
</dl>

<div id='divmult'>
	<dl>
		<dt><label><?=txt('selmult')?><span style='color:red'>*</span></label></dt>
	<dd>
<?php 
	$multi = $multiselect == 'S'; // assim ele tem Não como padrão
	if ($multi) {
		echo "<input type='radio' id='radmultS' name='radmulti' value='S' onclick='store(this)' checked>".txt('sim');
		echo "<input type='radio' id='radmultN' name='radmulti' value='N' onclick='store(this)'>".txt('não');
	} else {
		echo "<input type='radio' id='radmultS' name='radmulti' value='S' onclick='store(this)'>".txt('sim');
		echo "<input type='radio' id='radmultN' name='radmulti' value='N' onclick='store(this)' checked>".txt('não');
	}
?>
	</dd></dl>
</div>
<div id='divunit'>
	<dl>
		<dt><label><?=txt('unidpadr')?></label></dt>
		<dd>
			<select name='selunit' type='hidden' value='' onchange='store(this)'>
				<option value=''></option>
<?php
$q = "select * from varunit order by id";
$res = pg_query($conn,$q);
while ($row2 = pg_fetch_array($res,NULL,PGSQL_NUM)) {
	if ($row2[0] == $unit) {
		echo "<option value='$row2[0]' selected>$row2[1]</option>";
	} else {
		echo "<option value='$row2[0]'>$row2[1]</option>";
	}
}
?>

			</select>
		</dd>
	</dl>
</div>
<dl id='dlDef' >
	<dt><label><?= txt('def')?></label></dt>
	<dd><textarea name='txadef' oninput='store(this)'><?= $def?></textarea></dd>
</dl>
<div id='divVar'>
<?php
	$mmLabelH = txt('keyvar');
	$mmLabel1 = txt('valdisp');
	$mmLabel2 = txt('valsel');
	$who = 'var';
	$mmQuery = 'keyvar';
	$mmTableName = 'var';
	$mmTableLink = 'poss.val';
	include('build_mm.php');
?>
</div>
<div>
<?php echo "<input type='hidden' name='hidmmData' value='$hidmmData' />"; ?>
</div>
<!---
<dl>
	<dt><label><?=txt('keywords')?></label></dt>
	<dd><textarea name='txakeywords' oninput='store(this)'><?=$keywords?></textarea></dd>
</dl>
--->
<!--- imgicon NAO ESTA IMPLEMENTADO NEM AQUI NEM EM addVal.php -->
<dl id='dlImg' >
<dt ><label><?=txt('imgicon')?></label></dt>
<dd><input type='file' name='filIcone' accept='image/*' onchange='store(this)'></dd>
</dl>

<?= echoButtons(); ?>
</form>
</body>
</html>
