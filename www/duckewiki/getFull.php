<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();
if (!empty($_GET['id'])) {
	$id = $_GET['id'];
	$q = '';
	if (!empty($_GET['query'])) {
		$query = $_GET['query'];
		switch ($query) {
			case 'loc' :
				$q = 'select getloc(id)
				from loc
				where id = $1';
				break;
		}
	}
	if ($q != '') {
		$res = pg_query_params($conn,$q,[$id]);
		if ($row = pg_fetch_array($res,NULL,PGSQL_NUM)) {
			echo $row[0];
		}
	}
}
?>
