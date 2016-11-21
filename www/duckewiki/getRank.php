<?php
if (!isset($conn)) {
	include_once '../../includes_pl/db_connect.php';
	include_once '../../includes_pl/functions.php';
	sec_session_start();
}
if (isset($_GET['rankpai'])) {
	$rankPai = $_GET['rankpai'];
} else {
	$rankPai = substr($hidValue,strpos($hidValue,'件')+1);
}
$q = "select * from ranks where id > $rankPai order by id";
switch ($rankPai) {
	case 10 : $rankFilho = 50; break; // kingdom -> superclass
	case 50 : $rankFilho = 60; break; // superclass -> class
	case 60 : $rankFilho = 100; break; // class -> order
	case 100 : $rankFilho = 140; break; // order -> família
	case 140 : $rankFilho = 180; break; // família -> gênero
	case 180 : $rankFilho = 220; break; // gênero -> espécie
	case 220 : $rankFilho = 230; break; // espécie -> subespécie
	default  : $rankFilho = 300;
}
echo "<dl>".dtlab('rank')."<dd><select required name='selrank' onchange='store(this);showMorf(this)'>
<option value=''></option>";
$res = pg_query($conn,$q);
while ($row = pg_fetch_array($res,NULL,PGSQL_NUM)) {
	if ($row[0] == $rankFilho) {
		echo "<option value='$row[0]' selected>$row[1]</option>";
	} else {
		echo "<option value='$row[0]'>$row[1]</option>";
	}
}
echo "</select></dd></dl>\n";
?>
