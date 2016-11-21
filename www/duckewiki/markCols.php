<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();
$table = getGet('table');
$cols = getGet('cols');
$vals = getGet('vals');
/*print_r($cols);
echo "<BR>$table<BR>";
print_r($vals);*/
if ($vals != '') {
	switch ($table) {
		case 'checklist' :
			$tbl = 'chk';
			break;
		case 'especimenes' :
			$tbl = 'esp';
			break;
		case 'plantas' :
			$tbl = 'pl';
			break;
		case 'locais' :
			$tbl = 'loc';
	}
	$cols = explode(',',$cols);
	foreach ($cols as $key => $col) {
		$cols[$key] = "t$tbl$col";
	}
	$cols = implode(',',$cols);
	$q = "update cfg set ($cols) = ($vals) where usr = $1";
	$res = pg_query_params($conn,$q,[$_SESSION['user_id']]);
	if ($res) {
		echo "Colunas selecionadas com sucesso!";
	} else {
		echo "Erro ao selecionar colunas!";
	}
}
?>
