<?php
/*include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();
if (isset($_GET['esp'])) {
	$edit = $_GET['esp'];
}*/
//$edit = getGet('esp');
/* TO-DO
 * 
 * Corrigir o CSS para alinhar verticalmente o <dt> com o <dd>
 * 
 */
$expfs = $_SESSION['cfg.expfs'];
if ($expfs == 'S') {
	$fsAdisplay = 'none';
	$fsBdisplay = 'block';
} else {
	$fsAdisplay = 'block';
	$fsBdisplay = 'none';
}
//$col = getGet('col');
//echo "$"."col: [$col]";
$qSort = "select count(v.id), coalesce(k4.nome,k3.nome,k2.nome,k1.nome) ancestral from var v
join key k on k.id = v.key
left join val l on l.id = v.val
left join key k1 on k1.id = k.pai
left join key k2 on k2.id = k1.pai
left join key k3 on k3.id = k2.pai
left join key k4 on k4.id = k3.pai
where ".$col." = $1
group by ancestral
order by ancestral";
$resSort = pg_query_params($conn,$qSort,[$edit]); // $edit vem da janela que o chamou (esp, loc...)
if ($resSort) {
	$sortCount = [];
	$sortName = [];
	while ($rowSort = pg_fetch_array($resSort,NULL,PGSQL_NUM)) {
		$sortCount[] = $rowSort[0];
		$sortName[] = $rowSort[1];
	}
	$q = "select v.id, v.addby, usr.namef, usr.namel, pess.abrev, v.adddate, datavar, key, k.nome knome, k.tipo, k.multiselect, k.pathname path, k.def, val, l.valname vnome, valf, valt, v.unit u1, k.unit u2, u1.unit un1, u2.unit un2 from var v
	join key k on k.id = v.key
	left join val l on l.id = v.val
	left join varunit u1 on u1.id = v.unit
	left join varunit u2 on u2.id = k.unit
	left join usr on usr.id = v.addby
	left join pess on pess.id = usr.pess
	where ".$col." = $1
	order by k.pathname";
	$res = pg_query_params($conn,$q,[$edit]);
	if ($res) {
		$sortN = 0;
		$fsAberto = false;
		$lenLeg = 0;
		echo "<hr>\n";
		if ($expfs == 'S') {
			echo "<span id='spnVarFS'><a href='javascript:unpopFS(-1,\"Var\",\"".txt('var.expall')."\",\"".txt('var.hidall')."\")'>&lt;".txt('var.hidall')."&gt;</a></span>";
		} else {
			echo "<span id='spnVarFS'><a href='javascript:popFS(-1,\"Var\",\"".txt('var.hidall')."\",\"".txt('var.expall')."\")'>&lt;".txt('var.expall')."&gt;</a></span>";
		}
		while ($row = pg_fetch_array($res,NULL,PGSQL_ASSOC)) {
			if ($sortN < count($sortName)) {
				if (strpos($row['path'],$sortName[$sortN]) === 0) { // sortName[N] encontrado no início de path
					if ($fsAberto) {
						echo "</fieldset></div>";
						$fsAberto = false;
						$lenLeg = 0;
					}
					if ($sortCount[$sortN] > 1) {
						$lenLeg = strlen($sortName[$sortN])+3;
						echo "<div id='divVarFSa".$sortN."' style='display:$fsAdisplay'><a href='javascript:popFS($sortN,\"Var\",\"".txt('var.hidall')."\",\"".txt('var.expall')."\")'>&lt;$sortName[$sortN]&gt;</a></div>";
						echo "<div id='divVarFSb".$sortN."' style='display:$fsBdisplay'>";
						echo "<fieldset><legend><a href='javascript:unpopFS($sortN,\"Var\",\"".txt('var.expall')."\",\"".txt('var.hidall')."\")'>$sortName[$sortN]</a></legend>";
						$fsAberto = true;
					}
					$sortN++;
				}
			}
			$id = $row['id'];
			$path = substr($row['path'],$lenLeg);
			// nome da variável (key.nome)
			echo "<dl><dt><a name='$row[key]'><label>$path <img src='icon/question.png' title='$row[def]'>:</label></a></dt>\n<dd><div id='divVar$id'>";
			$atual = '';
			$unidade = '';
			// $atual + $unidade
			// se for categórico, buscou da tabela val
			if ($row['vnome'] != '') { // se tiver, mostra a variável escolhida
				$atual = $row['vnome'];
			} else
			if ($row['valt'] != '') { // senão, se tiver, mostra o texto escolhido
				if ($row['tipo'] == 2 && $row['multiselect'] == 'S') {
					// criar função para separar 'id1;id2;id3' em 'valor1; valor2; valor3'? PHP ou SQL?
					// PHP:
					$atual = getMultiVal($row['valt']);
				} else {
					$atual = $row['valt'];
				}
			} else
			if ($row['valf'] != '') { // senão, se tiver, mostra o valor em var.valf
				$atual = $row['valf'];
				$unidade = $row['un2'];
			} else {									// senão, mostra o valor em var.val
				$atual = $row['val'];
				$unidade = $row['un2'];
			}
			// $val
			if ($row['valt'] != '') { // se tiver, val recebe o texto escolhido
				$val = $row['valt'];
			} else {				// senão, recebe o valor em var.val
				$val = $row['val'];
			}
			// echo <dd>
			echo $atual;
			if (!in_array($unidade,['','num'])) {
				echo " $unidade";
			}
			echo "<span style='color:white;font-size:80%'>";
			if ($row['namef'] != '' or $row['namel'] != '') {
				echo ' ['.$row['namef'].' '.$row['namel'];
			} else
			if ($row['abrev'] != '') {
				echo ' ['.$row['abrev'];
			} else {
				echo ' ['.$row['addby'];
			}
			$data = $row['adddate'];
			if (strpos($data,' 00:00:00') !== false) {
				$data = substr($data,0,-9);
			}
			echo "@$data";
			if ($row['datavar'] != '') {
				echo "|$row[datavar]";
			}
			$nome = $row['path'];
			echo "]</span> <img src='icon/edit.png' width=16 height=16 style='cursor:pointer' onclick='editVar($id,\"$val\")'></div></dd></dl>\n";
		}
		if ($fsAberto) {
			echo "</fieldset></div>";
		}
		echo "<hr>\n";
	}
}
?>
