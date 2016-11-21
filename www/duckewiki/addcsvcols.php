<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();
if (isset($_GET['tabela'])) {
	$tab = $_GET['tabela'];
	$cols = [];
	if (!empty($_GET['std'])) {
		//ex. de std: am.esp件am.tipo件esp.country
		$std = explode('件',$_GET['std']);
		foreach ($std as $item) {
			$item = explode('号',$item)[1];
			$pos = strpos($item,'.');
			$t = substr($item,0,$pos);
			if ($t == $tab) {
				$col = substr($item,$pos+1);
				$pos = strpos($col,'|');
				$col = substr($col,0,$pos);
				$cols[] = $col; // guarda aqui as colunas já selecionadas daquela tabela (não devem aparecer mais)
			}
		}
		$cols[] = "id";
		$cols[] = "addby";
		$cols[] = "adddate"; // essas três colunas também não devem aparecer
	}
	echo "Colunas<BR>
	<select id='selCol' size=8 onchange='selColChange(this)'>";
	$q = "SELECT
		cols.column_name,
		(
			SELECT
				pg_catalog.col_description(c.oid, cols.ordinal_position::int)
			FROM
				pg_catalog.pg_class c
			WHERE
				c.oid = (SELECT '$tab'::regclass::oid)
				AND c.relname = cols.table_name
		) AS column_comment
		FROM
			information_schema.columns cols
		WHERE
			cols.table_name = '$tab'
			AND cols.table_schema = 'public';";
	$res = pg_query($conn,$q);
	if ($res) {
		$colunas = [];
		while ($row = pg_fetch_array($res,NULL,PGSQL_NUM)) {
			if (!in_array($row[0],$cols)) {
				$colunas[$tab.$row[0]] = txt("ALIAS:$tab.$row[0]");
			}
		}
		asort($colunas); // põe em ordem alfabética
		foreach ($colunas as $key => $value) {
			echo "<option value='$key'>$value</option>";
		}
	}
	echo "</select>";
}
?>
