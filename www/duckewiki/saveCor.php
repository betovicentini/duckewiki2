<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();
if (isset($_GET['nome'])) {
	$nome = $_GET['nome'];
}
if (isset($_GET['col'])) {
	$cols = explode(',',$_GET['col']);
}
if (isset($_GET['cor'])) {
	$cores = explode(',',$_GET['cor']);
}
if (!empty($nome) && !empty($cols) && !empty($cores)) {
	$q = "insert into cor (addby,adddate,nome,";
	foreach($cols as $col) {
		$q.="$col,";
	}
	$q = substr($q,0,-1).") values (".$_SESSION['user_id'].",'".date('d/m/Y H:i:s')."','$nome',";
	$i = 1;
	foreach($cores as $cor) {
		$q.='$'.$i++.',';
	}
	$q = substr($q,0,-1).');';
	$res = pg_query_params($conn,$q,$cores);
	if ($res) {
		//echo "sucesso [$q]";
		echo "0$nome";
	} else {
		//echo "falha1 [$q]";
		echo "falha1 [$q]: ".pg_last_error($conn);
	}
} else {
	echo "falha2";
}
?>
