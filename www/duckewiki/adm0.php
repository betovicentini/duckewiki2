<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();
?>
<html lang='BR'>
<head>
<meta charset='UTF-8'>
<script type='text/javascript'>
var evtSource;
function aoCarregar() {
	var what = 'tax';
	evtSource = new EventSource('adm_sse.php?f=reconta&tab='+what);
	evtSource.onmessage = function(e) {
		alert(e.data);
	};
}
function reconta(what) {
	//var what = 'tax';
	evtSource = new EventSource('adm_sse.php?f=reconta&tab='+what);
	evtSource.onmessage = function(e) {
		alert(e.data);
		//evtSource.close();
	};
}
</script>
</head>
<body onload='aoCarregar()'>
<?php
$user = $_SESSION['user_id'];
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
	echo "<table><tr><td>tabela</td><td>número de linhas</td><td>recontagem</td></tr>";
	$n = array_search('tax',$tabnomes);
	echo "<tr><td>$tabnomes[$n]</td><td>$tabn[$n]</td><td><button onclick='reconta(\"tax\")'>recontar</button></td></tr>";
	$n = array_search('loc',$tabnomes);
	echo "<tr><td>$tabnomes[$n]</td><td>$tabn[$n]</td></tr>";
	$n = array_search('esp',$tabnomes);
	echo "<tr><td>$tabnomes[$n]</td><td>$tabn[$n]</td></tr>";
	$n = array_search('pl',$tabnomes);
	echo "<tr><td>$tabnomes[$n]</td><td>$tabn[$n]</td></tr>";
	$n = array_search('det',$tabnomes);
	echo "<tr><td>$tabnomes[$n]</td><td>$tabn[$n]</td></tr>";
	echo "</table>";
	echo "</dd></dl>";
}
?>
<div class='wrapper'>
<button type='button' onclick='analisa()'>Analyze</button>
</div>
</form>
</body>
</html>
