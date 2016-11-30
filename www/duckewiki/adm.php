<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();
echo "<!DOCTYPE html>
<html lang='BR'>
<head>
<meta charset='UTF-8'>";
$title = txt('adm');
echo "<title>$title</title>";
/* ver número de registros por tabela - ordem:

   tabela   |  n
------------+------
		tax			|
		loc			|
		esp			|
		pl			|
		det			|
						|
		key			|
		val			|
		poss		|
		var			|
		frm			|
						|
		pess		|
		espec		|
		usr			|
						|
		herb		|
		prj			|
		bib			|

<botão Analyze>

um botão para (re)contar, outro pra conferir contagem (opção de auto-corrigir) de:
- tax.espn
- tax.pln
- loc.espn
- loc.pln


*/

?>
<link rel="stylesheet" type="text/css" href="css/cssDuckeWiki.css">
<style>
td {
	border-left:1px solid black;
	border-right:1px solid black;
	padding:5px;
}
</style>
<script src='js/funcoes.js'></script>
<script type='text/javascript'>
var evtSource;
var divprg;
/** após o <body> terminar de carregar */
function aoCarregar() {
	divprg = document.getElementById('divprgbar');
}
/** formata segundos em 'XhYmZs' (horas, minutos e segundos) */
function formatHMS(sec) {
	// seconds = 2740.58
	var h = Math.floor(sec/3600); // 0
	var m = Math.floor((sec % 3600)/60); // 45 //.67
	var s = Math.round(sec % 60) // 40.58
	return h+'h'+m+'m'+s+'s';
}
/** conta o número de espécies e plantas de cada táxon (incluindo subtáxons) */
function contar(what,tot) {
	divprg.innerHTML = '0/'+tot+' (0%)';
	evtSource = new EventSource('adm_sse.php?f=reconta&tab='+what);
	evtSource.onmessage = function(e) {
		if (e.data != 'end') {
			//console.log(e.data);
			var p = e.data.indexOf('|');
			var n = e.data.substr(0,p);
			var t = e.data.substr(p+1);
			if (n > 0) {
				var tleft = t*tot/n - t;
				divprg.innerHTML = n+'/'+tot+' ('+(100*n/tot).toFixed(1)+'% - '+formatHMS(tleft)+' restantes)';
			} else {
				divprg.innerHTML = n+'/'+tot+' ('+(100*n/tot).toFixed(1)+'%)';
			}
		} else {
			evtSource.close();
		}
	};
}
/** pára a contagem, sem desfazer o que foi feito */
function parar() {
	evtSource.close();
	divprg.innerHTML = divprg.innerHTML+'<BR>-- processo interrompido --';
}
/** chama a função Analyze (maintenance operation) do PostgreSQL */
function analisa() {
	divprg.innerHTML = 'Analisando...';
	evtSource = new EventSource('adm_sse.php?analisa=1');
	evtSource.onmessage = function(e) {
		if (e.data != 'end') {
			divprg.innerHTML = 'Análise em andamento...';
		} else {
			divprg.innerHTML = 'Análise concluída.';
			evtSource.close();
		}
	};
}
/*
select var.* from var
left join key on key.id = var.key
where valt like '%,%' and key.tipo = 4

*/
function valt2valf() {
	divprg.innerHTML = '0';
	evtSource = new EventSource('adm_sse.php?val=1');
	evtSource.onmessage = function(e) {
		if (e.data != 'end') {
			if (e.data.substr(0,1) == '!') {
				console.log(e.data);
			} else {
				var p = e.data.indexOf('|');
				var p1 = e.data.indexOf('/');
				var n = e.data.substr(0,p1);
				var tot = e.data.substr(p1+1,p-p1-1);
				var t = e.data.substr(p+1);
				if (n > 0) {
					var tleft = t*tot/n - t;
					divprg.innerHTML = n+'/'+tot+' ('+(100*n/tot).toFixed(1)+'% - '+formatHMS(tleft)+' restantes)';
				} else {
					divprg.innerHTML = n+'/'+tot+' ('+(100*n/tot).toFixed(1)+'%)';
				}
				//divprg.innerHTML = n;
			}
		} else {
			evtSource.close();
		}
	};
}
function gettabscols() {
	divUpd = 'divTabsCols';
	var url = 'admtabscols.php';
	conecta(url,update);
}
</script>
<?php
$h1 = "<h1 style='text-align:center'>$title</h1>";
$user = $_SESSION['user_id'];
$body = "<body onload='aoCarregar()'>";
pullCfg();
echo "</head>
$body";
$loginErro = login_error($conn);
if ($loginErro) {
	exit("<p>Você não tem autorização de acesso a esta página (erro: $loginErro). Por favor, <a href='index.php' target='main'>volte para a tela inicial e faça o login</a>.</p>");
}
echo "$h1";
echo "<form id='frmAdm' autocomplete='off' method='post' action=''>\n";
echo "<div id='divprgbar'></div>";
echo "<dl>";
/*if (!empty($_POST['check'])) {
	$check = getPost('check');
	$pos = strpos($check,'.');
	$tab = substr($check,0,$pos);
	$n = substr($check,$pos+1);
	echo "<dt>Checando</dt><dd>$tab [$n]...</dd><BR><BR>";
	if ($tab == 'tax') {
		$q = "select t.id, t.nome,
		exists(select 1 from tax tf where tf.taxpai = t.id)::int as haschild
		from tax t";
		$res = pg_query($conn,$q);
		if ($res) {
			$rown = 0;
			while ($row = pg_fetch_array($res,NULL,PGSQL_NUM)) {
				$rown++;
				if ($rown % 100 == 0) {
					echo '.';
				}
			}
		}
	} else {
		echo "<dt>outro check</dt>";
	}
}*/
$q = "SELECT 
  nspname AS schemaname,relname,reltuples
FROM pg_class C
LEFT JOIN pg_namespace N ON (N.oid = C.relnamespace)
WHERE 
  nspname NOT IN ('pg_catalog', 'information_schema') AND
  relkind='r' 
ORDER BY reltuples DESC";
$res = pg_query($conn,$q);
if ($res) {
	$tabnomes = [];
	$tabn = [];
	$i = 0;
	while ($row = pg_fetch_array($res,NULL,PGSQL_NUM)) {
		$tabnomes[] = $row[1];
		$tabn[] = $row[2];
		//echo "<dt>$i.$row[1]</dt><dd>$row[2] <button type='button' name='check' value='$row[1].$row[2]' onclick='checa(\"$row[1]\")'>check $row[1]</button></dd>";
		$i++;
		/*if (in_array($row[1],['esp','pl','tax'])) {
			echo "<dt>$row[1]</dt><dd>$row[2] <button type='button' name='check' value='$row[1].$row[2]' onclick='checa(\"$row[1]\")'>check $row[1]</button></dd>";
		}*/
	}
	echo "<dl><dt>Tabelas</dt><dd>";
	$tabs = ['tax','loc','esp','pl','det'];
	echo "<table><tr><td>tabela</td><td>número de linhas</td><td>contar</td><td>parar</td></tr>";
	for ($ti=0; $ti<count($tabs); $ti++) {
		$n = array_search($tabs[$ti],$tabnomes);
		echo "<tr><td>$tabnomes[$n]</td>
		<td>$tabn[$n]</td>
		<td><button type='button' onclick='contar(\"$tabs[$ti]\",$tabn[$n])'>contar</button></td>
		<td><button type='button' onclick='parar()'>parar</button></td></tr>";
		
	}
	echo "</table>";
	echo "</dd></dl>";
}
?>
<div class='wrapper'>
<button type='button' onclick='analisa()'>Analyze</button>
<button type='button' onclick='valt2valf()' disabled>valt to valf</button>
<button type='button' onclick='gettabscols()'>Tabs/Cols</button>
</div>
<div id='divTabsCols' class='wrapper'></div>
</form>
</body>
</html>
