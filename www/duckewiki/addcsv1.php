<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();
if (!empty($_GET['fname'])) {
	$fname = $_GET['fname'];
	echo "Arquivo: $fname<BR>";
} else {
	echo 'Arquivo não encontrado!';
	exit;
}
function getNth($l,$n,$sep='	') { // separado por tabs
	$i = 0;
	$l = explode($sep,$l);
	foreach ($l as $c) {
		if ($i == $n) {
			return $c;
		}
		$i++;
	}
}
if (!empty($_GET['std'])) {
	//ex. de std: am.esp件am.tipo件esp.country
	$std = explode('件',$_GET['std']);
	$cols = [];
	$BD = [];
	foreach ($std as $item) {
		echo "$item<BR>";
		$item = explode('号',$item);
		$cols[] = $item[0];
		$BD[] = $item[1];
		switch ($item[1]) {
			case 'esp.col':
				$coletor = $item[0];
				break;
		}
		/*$pos = strpos($item,'.');
		$t = substr($item,0,$pos);
		if ($t == $tab) {
			$col = substr($item,$pos+1);
			$pos = strpos($col,'|');
			$col = substr($col,0,$pos);
			$cols[] = $col; // guarda aqui as colunas já selecionadas daquela tabela (não devem aparecer mais)
		}*/
	}
	echo "coletor = $coletor<BR>";
	print_r($cols);
	$csv = file($fname);
	$head = explode('	',$csv[0]);
	$ncols = [];
	$i = 0;
	foreach ($head as $col) {
		if (in_array($col,$cols)) {
			$ncols[$col] = $i;
		}
		$i++;
	}
	print_r($ncols);
	if (!empty($coletor)) {
		$ncoletor = $ncols[$coletor];
	}
	echo "<BR>ncoletor = $ncoletor<BR>";
	echo "<table style='margin-left:auto;margin-right:auto'>";
	$coletores = [];
	foreach ($csv as $line) {
		$coletores[] = getNth($line,$ncoletor);
	}
	$coletores = array_unique($coletores);
	sort($coletores);
	// para cada um da lista de coletores do arquivo csv
	foreach ($coletores as $coletor) {
		$q = "select id from pess where abrev = $1";
		$res = pg_query_params($conn,$q,[$coletor]);
		if ($res) {
			$colID[$coletor] = '';
			while ($row = pg_fetch_array($res,NULL,PGSQL_NUM)) {
				$colID[$coletor] = $colID[$coletor].$row[0].';';
			}
			$colID[$coletor] = substr($colID[$coletor],0,-1); // tira o último ;
			if ($colID[$coletor] == '') {
				// não encontrou abreviatura idêntica
				$candidatos = [];
				// procura pelo sobrenome
				$pos = strpos($coletor,',');
				if ($pos !== false) {
					$lastName = substr($coletor,0,$pos);
					$q = "select id from pess where sobrenome = $1";
					$res = pg_query_params($conn,$q,[$lastName]);
					if ($res) {
						while ($row = pg_fetch_array($res,NULL,PGSQL_NUM)) {
							$candidatos[] = $row[0];
						}
					}
				}
				// procura por proximidade (mínima distância Levenshtein)
				$q = "select id,abrev,levenshtein_less_equal($1,abrev,10)
				from pess
				where levenshtein_less_equal($1,abrev,10) < 6
				order by levenshtein_less_equal($1,abrev,10)";
					$res = pg_query_params($conn,$q,[$coletor]);
					if ($res) {
						$i=0;
						while ($row = pg_fetch_array($res,NULL,PGSQL_NUM)) {
							echo "<tr><td>para '$coletor' $i: [$row[0]] $row[1] (levenshtein: $row[2]) <button>atualizar</button> <button>unir</button></td></tr>";
							$i++;
						}
						echo "<tr><td>$coletor</td></tr>";
					}
				
			}
		}
	}
	echo "</table>";
	print_r($coletores);
}
?>
