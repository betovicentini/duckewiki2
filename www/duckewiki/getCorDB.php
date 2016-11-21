<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();
if (!empty($_GET['field'])) {
	$field = $_GET['field'];
} else {
	$field = '';
}
if (!empty($_GET['cor'])) {
	$cor = $_GET['cor'];
	$q = "select rgb from cores where br = $1";
	$res = pg_query_params($conn,$q,[$cor]);
	//echo "$cor: $q<BR><BR>";
	if ($row = pg_fetch_array($res,NULL,PGSQL_NUM)) {
		//echo "2: $q<BR><BR>";
		$rgb = $row[0];
	} else {
		$rgb = '';
	}
	echo "<canvas width=50 height=20 id='cnv$field' onclick='getCor(\"$field\",\"$rgb\")'>$rgb</canvas> ";
	echo "<label id='lbl$field' onclick='lblClick(this)'>#$rgb</label>";
}
?>
