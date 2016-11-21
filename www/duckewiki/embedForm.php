<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();
$frmid = getGet('frmid');
$esp = getGet('esp');
if ($frmid != '') {
	include 'drawForm.php';
}
?>
