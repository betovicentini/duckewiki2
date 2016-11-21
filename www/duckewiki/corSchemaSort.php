<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
if (session_status() == PHP_SESSION_NONE) {
	sec_session_start();
}
if (isset($_GET['sort'])) {
	$sort = $_GET['sort'];
	if ($sort == 1) {
		$sort = 'nome,username';
		//$sortn = 1;
	} else {
		$sort = 'username,nome';
		//$sortn = 2;
	}
} else {
	$sort = 'nome,username';
	//$sortn = 1;
}
echo "<dt>".txt('cfg.outras')."</dt><dd><select id='selcores' onchange='getOther(this)'>"; // onchange?
$q = "select u.username,u.namef,u.namel,c.* from cor c
join usr u on u.id = c.addby
order by $sort";
$res = pg_query($conn,$q);
echo "<option value='0'>-- escolha um esquema --</option>";
while ($row = pg_fetch_array($res,NULL,PGSQL_ASSOC)) {
	$nome = "($row[namef] $row[namel])";
	if ($nome == '( )') {
		$nome = '';
	}
	$i = 0;
	$valor = '';
	foreach ($row as $k => $v) {
		if ($i++ > 6) {
			$valor.="$k=$v,";
		}
	}
	$valor = $row['id'].'号'.substr($valor,0,-1);
	if ($sort == 'nome,username') {
		echo "<option value='$valor'>$row[username]$nome|$row[nome]</option>\n";
	} else {
		echo "<option value='$valor'>$row[nome]|$row[username]$nome</option>\n";
	}
}
echo "</select><button type='button' onclick='selSchemaSort()'>&lt;-&gt;</button></dd>"; // ordenar
?>
