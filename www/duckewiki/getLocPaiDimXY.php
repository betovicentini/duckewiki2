<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();
if (!empty($_GET['dequem'])) {
	$dequem = $_GET['dequem'];
} else {
	$dequem = 'loc';
}
if (!empty($_GET['id'])) {
	$id = $_GET['id'];
	if ($dequem == 'loc') { // $id é da própria localidade
		$q = "select dimx,dimy from loc
		where id = $1";
		$res = pg_query_params($conn,$q,[$id]);
		if ($row = pg_fetch_array($res,NULL,PGSQL_NUM)) {
			if ($row[0] > 0 && $row[1] > 0) { // se esta localidade tiver DimX e DimY
				echo "S";
			} else {
				echo "N";
			}
		} else {
			echo "N";
		}
	} else { // $dequem = 'pai', quer dizer, o $id é da localidade filha, deve olhar pai dela
		$q = "select count(*)
		from locpai
		where loc = $1";
		$res = pg_query_params($conn,$q,[$id]);
		if ($row = pg_fetch_array($res,NULL,PGSQL_NUM)) {
			if ($row[0] == 1) { // só usa StartX e StartY se tiver apenas 1 pai
				$q = "select l.dimx,l.dimy,l.nome from locpai lp
				join loc l on l.id = lp.pai
				where lp.loc = $1";
				$res = pg_query_params($conn,$q,[$id]);
				if ($row = pg_fetch_array($res,NULL,PGSQL_NUM)) {
					echo "$row[0],$row[1],$row[2]<BR>";
					if ($row[0] > 0 && $row[1] > 0) { // e se este pai tiver DimX e DimY
						echo "S";
					} else {
						echo "N";
					}
				} else {
					echo "N";
				}
			} else {
				echo "N";
			}
		} else {
			echo "N";
		}
	}
} else {
	echo "N";
}
?>
