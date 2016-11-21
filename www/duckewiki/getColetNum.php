<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();
if (!empty($_GET['id'])) {
	$id = $_GET['id'];
	$q = "select max(num) from esp
	where col = $1";
	$res = pg_query_params($conn,$q,[$id]);
	if ($row = pg_fetch_array($res,NULL,PGSQL_NUM)) {
		$num = $row[0]+1;
		//echo "<dl><dt><label>".txt('num1')."</label></dt><dd><input name='txtnum' type='text' size=10 value='$num' oninput='store(this)' />";
		echo "<dl>".dtlab('num1')."<dd><input required name='txtnum' type='text' size=10 value='$num' oninput='store(this)' />";
		echo "<label> ".txt('num2')." </label> <img src='icon/question.png'><div class='tooltip'>".txt('num2.tip')."</div><input name='txtnummax' type='text' size=10 oninput='store(this)' onkeydown='txtnummaxkeyup(event)' />";
		echo "</dd></dl>";
	}
}
?>
