<?php
header("Content-Type: text/event-stream");
//header('Cache-Control: no-cache'); // recommended to prevent caching of event data.
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();

if (isset($_GET['analisa'])) {
	pg_query($conn,"vacuum analyze");
} else
if (isset($_GET['val'])) {
	$q = "select v.* from var v
	left join key k on k.id = v.key
	where v.valt like '%;%' and k.tipo = 4
	order by v.id"; // tipo 4 = variável quantitativa
	$res = pg_query($conn,$q);
	if ($res) {
		$erro = 0;
		$i = 1;
		$ovhd = 10;
		$timeIni = time();
		$rowcount = pg_num_rows($res);
		while ($row = pg_fetch_array($res,NULL,PGSQL_ASSOC)) {
			$valt = explode(';',$row['valt']);
			$qUpd = "update var set valf=$1, ordem=1 where id = $2";
			$resUpd = pg_query_params($conn,$qUpd,[$valt[0],$row['id']]);
			if ($resUpd) {
				// ok
				for ($iUpd=1; $iUpd<sizeof($valt); $iUpd++) {
					$valt[$iUpd] = str_replace(',','.',$valt[$iUpd]); // troca , por .
					$qUpd = "insert into var (addby,adddate,oldmonitid,oldhabitid,bib, censo,datavar,esp,hab,pl, key,valf,loc,tax,unit, amostra,ordem) values
						($1,$2,$3,$4,$5, $6,$7,$8,$9,$10, $11,$12,$13,$14,$15, $16,$17)";
					$resUpd = pg_query_params($conn,$qUpd,[$row['addby'],$row['adddate'],$row['oldmonitid'],$row['oldhabitid'],$row['bib'],
						$row['censo'],$row['datavar'],$row['esp'],$row['hab'],$row['pl'],
						$row['key'],$valt[$iUpd],$row['loc'],$row['tax'],$row['unit'],
						$row['amostra'],$iUpd+1]);
					if ($resUpd) {
						// ok
					} else {
						// erro na query
						$erro++;
						$erroMsg = pg_last_error($conn);
						echo "data: !erro1 = $erroMsg ($i/id = $row[id])\n\n";
						ob_end_flush();
						flush();
					}
				}
			} else {
				// erro na query
				$erro++;
				$erroMsg = pg_last_error($conn);
				echo "data: !erro2 = $erroMsg ($i/id = $row[id])\n\n";
				ob_end_flush();
				flush();
			}
			if ($i % $ovhd == 0) { // reduz o overhead
				echo "data: $i/$rowcount|".(time() - $timeIni)."\n\n";
				//echo "data: $i\n\n";
				ob_end_flush();
				flush();
			}
			$i++;
		}
	}
	echo "data: número de erros: $erro\n\n";
	flush();
}
if (isset($_GET['f'])) {
	$f = $_GET['f'];
	if ($f == 'reconta') {
		if (isset($_GET['tab'])) {
			$tab = $_GET['tab'];
			if ($tab == 'tax') {
				$q = "select id from tax order by id";
				$res = pg_query($conn,$q);
				if ($res) {
					$IDs = pg_fetch_all_columns($res,0);
					$i = 0;
					$timeIni = time();
					//$IDs = array_slice($IDs,0,200);
							/*if ($i > 1000) {
								$ovhd = 20; // -> cfg
							} else {
								$ovhd = 10;
							}*/
					$ovhd = 10;
					/*foreach ($IDs as $ID) {
						$q = "update tax set nesp=getespn_tax(id),npl=getpln_tax(id) where id = $1";
						$res = pg_query_params($conn,$q,[$ID]);
						if ($res) {
							if ($i % $ovhd == 0) { // reduz o overhead
								echo "data: $i|".(time() - $timeIni)."\n\n";
								ob_end_flush();
								flush();
							}
						} else {
							$erro = pg_last_error($conn);
							echo "data: erro3 = $erro\n\n";
							ob_end_flush();
							flush();
						}
						$i++;
					}*/
					pg_query($conn,'begin;');
					foreach ($IDs as $ID) {
						if ($i > 0 && $i % $ovhd == 0) { // commit / begin
							$res = pg_query($conn,'commit;begin;');
							if ($res) {
								echo "data: $i|".(time() - $timeIni)."\n\n";
								ob_end_flush();
								flush();
							}
						} else {
							$q = "update tax set nesp=getespn_tax(id),npl=getpln_tax(id) where id = $1";
							$res = pg_query_params($conn,$q,[$ID]);
						}
						if (!$res) {
							$erro = pg_last_error($conn);
							echo "data: erro3 = $erro\n\n";
							ob_end_flush();
							flush();
						}
						$i++;
					}
					pg_query($conn,'commit;');
					echo "data: end\n\n";
					ob_end_flush();
					flush();
				} else {
					$erro = pg_last_error($conn);
					echo "data: erro4 = $erro\n\n";
					ob_end_flush();
					flush();
				}
			}
		}
	}
}
echo "data: end\n\n";
flush();
?>
