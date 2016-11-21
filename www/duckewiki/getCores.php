<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();
if (!empty($_GET['id'])) {
	$id = $_GET['id'];
	$q = "select * from cfg
	where id=$1";
	$res = pg_query_params($conn,$q,[$id]);
	if ($row = pg_fetch_array($res,NULL,PGSQL_ASSOC)) {
		foreach($row as $key => $value) {
			if (substr($key,0,3) == 'cor') {
				$key = substr($key,3);
				echo "$key=$value\n";
			}
		}
	}
}
?>
