<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();
/*
* pode agrupar por mais de um campo qualitativo? Neste caso o número de agrupamentos cresce rápido
* o que acontece se usuário não marcou média, range, min ou max nos quantitativos?
* variáveis quantitativas (e qualitativas) múltiplas: várias linhas ou texto separado por ;
* 	várias linhas -> facilita queries com mean, min, max...
* 	texto separado por ; -> reduz o tamanho da tabela
 */
$filtro = './usr/'.$_SESSION['user_id'].'/filter/'.getGet('filtro');
if ($fp = fopen($filtro,'r')) {
	$table = rtrim(fgets($fp),"\r\n");
	if (strpos($table,',') > 0) {
		echo "Erro na leitura de $filtro (o formato do arquivo é inválido).";
		$table = '';
	} else {
		$fIDs = rtrim(fgets($fp),"\r\n");
		//echo "$table:<BR>";
	}
	fclose($fp);
}
if ($table == '') {
	exit; // se houver erro, sai (após fechar o arquivo $fp)
}
$fields = getGet('fields');
echo "$table<BR>$fIDs<BR><BR>";
$fields = json_decode($fields,true); // converte JSON para array
print_r($fields);
// passo 1: identificar campos qualitativos marcados com "agrupar por"
$valores = [];
$fieldGrp = '';
foreach ($fields as $field) {
	if ($field['grp'] == 1) { // se está agrupado
		$fieldGrp = $field['val'];
		echo "<BR><BR>".$field['txt'];
		$q = "select distinct val, valt from var
		where $table in ($fIDs)
		and key = $1";
		$res = pg_query_params($conn,$q,[$field['val']]); // $fIDs devia ficar aqui?
		if ($res) {
			while ($row = pg_fetch_array($res,NULL,PGSQL_ASSOC)) {
				if ($row['val'] > 0) { // valor em $row[val]
					if (!in_array($row['val'],$valores)) {
						$valores[] = $row['val'];
					}
				} else { // valor em $row[valt]
					$valt = explode(';',$row['valt']);
					foreach ($valt as $v) {
						if (!in_array($v,$valores)) {
							$valores[] = $v;
						}
					}
				}
			}
			echo "<BR><BR>";
			print_r($valores);
		} else {
			// erro na query
		}
	}
}
// if (agrupado) ...
// passo 2: uma query para cada valor em $valores
foreach ($valores as $valor) {
	$q = "select string_agg(esp::text,',') from var
	where $table in ($fIDs)
	and key = $1
	and (val = $2 or valt like '%$2%')";
	$res = pg_query_params($conn,$q,[$fieldGrp,$valor]); // $fIDs devia ficar aqui?
	if ($res) {
		if ($row = pg_fetch_array($res,NULL,PGSQL_NUM)) {
			$IDsV = $row[0];
		}
		foreach ($fields as $field) {
			if ($field['val'] != $fieldGrp) {
				switch($field['tipo']) {
					case 1:
						echo $field['txt'];
						break;
					case 2:
						switch($field['tnum']) {
							case 'm' : // mean
								break;
							case 'r' : // range
								break;
							case 'i' : // min
								break;
							case 'a' : // max
								break;
						}
						//$q = 
						break;
				}
			}
		}
	} else {
		// erro na query
	}
}
?>
