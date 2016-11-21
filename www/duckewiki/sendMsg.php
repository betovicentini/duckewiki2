<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();
if (isset($_GET['url'])) {
	$url = $_GET['url'];
} else {
	$url = '';
}
if (isset($_GET['msg'])) {
	$msg = $_GET['msg'];
	$usr = $_SESSION['user_id'];
	$quando = date('d/m/Y H:i:s');
	$thread = -1;
	if (isset($_GET['thread'])) {
		$thread = $_GET['thread'];
	} else {
		$q = "select max(thread) from msg";
		$res = pg_query($conn,$q);
		if ($res) {
			if ($row = pg_fetch_array($res,NULL,PGSQL_NUM)) {
				$thread = $row[0]+1;
			}
		} else {
			echo pg_last_error($conn); // falha
		}
	}
	if ($thread > 0) {
		if (isset($_GET['replyto'])) {
			$replyto = $_GET['replyto'];
		} else {
			$replyto = 66;
		}
		$q = "insert into msg (msgfrom,msgto,adddate,thread,msg,lida,url) values ($1,$2,$3,$4,$5,$6,$7);";
		$res = pg_query_params($conn,$q,[$usr,$replyto,$quando,$thread,$msg,'N',$url]);
		if ($res) { // sucesso
		} else {
			echo pg_last_error($conn); // falha
		}
	}
}
?>
