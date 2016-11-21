<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();
if (!empty($_GET['pl'])) {
	$pl = $_GET['pl'];
	$q = "select pl.id,pl.pltag,pl.loc,l.nome localidade,pl.lat,pl.lon,pl.det,d.tax,gettax(d.tax) taxon,pl.prj,pl.hab,h.nome habitat
	from pl
	join loc l on l.id = pl.loc
	join hab h on h.id = pl.hab
	join det d on d.id = pl.det
	where pl.id=$1";
	$res = pg_query_params($conn,$q,[$pl]);
	if ($row = pg_fetch_array($res,NULL,PGSQL_ASSOC)) {
		//echo "<dl style='background-color:lightgreen'><dt>".txt('loc')."</dt><dd>$row[nome]</dd></dl>";
		$arr = array('loctxt' => $row['localidade'],
								'detval' => $row['det'],
								'dettxt' => $row['taxon'],
								'habtxt' => $row['habitat']);
		echo json_encode($arr);
		/*echo json_encode("{\"locval\":\"$row[loc]\",\"loctxt\":\"$row[localidade]\",
		\"detval\":\"$row[det]\",
		\"habval\":\"$row[hab]\",\"habtxt\":\"$row[habitat]\"}");*/
	}
} else {
	/*$edit = $_GET['edit'];
	$loc = $_GET['loc'];*/
	/*$cmbLabel = txt('loc');
	$cmbTableName = 'loc';
	$cmbFieldNames = 'nome';
	$cmbCaseSensitive = 0;
	$who = 'loc';
	$cmbQuery = 'loc';
	$cmbPHP = 'addLoc.php';
	include('build_cmb.php');*/
}
?>
