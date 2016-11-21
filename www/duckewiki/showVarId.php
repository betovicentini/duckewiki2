<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();
$loginErro = login_error($conn);
if ($loginErro) {
	exit("<p>Você não tem autorização de acesso a esta página. Por favor, <a href='index.php' target='main'>volte para a tela inicial e faça o login</a>.</p>");
}
$varid = getGet('varid');
$newval = getGet('newval');
// localiza o registro a ser modificado
$q = "select v.id,v.addby,v.adddate,v.datavar,v.val,l.valname,v.valf,v.valt,v.key,k.tipo,k.multiselect,k.unit u1,v.unit u2,
	vu1.unit unit1, vu2.unit unit2
	from var v
	left join key k on k.id = v.key
	left join val l on l.id = v.val
	left join varunit vu1 on vu1.id = k.unit
	left join varunit vu2 on vu2.id = v.unit
	where v.id = $1";
$res = pg_query_params($conn,$q,[$varid]);
if ($res) {
	if ($row = pg_fetch_array($res,NULL,PGSQL_ASSOC)) {
		// salva os valores anteriores em dwh
		$coluna = getVarColWrite($row); // descobre a coluna com os dados
		$atual = $row[$coluna];
		$datavar = $row['datavar'];
		if ($row['unit2'] != '') {
			$unit = $row['unit2'];
		} else {
			$unit = $row['unit1'];
		}
		$qDWH = "insert into dwh (tab,col,val,addby,adddate) values
		('var','$coluna','$atual',$row[addby],'$row[adddate]')";
		$resDWH = pg_query($conn,$qDWH);
		if ($resDWH) {
			// salvou os valores anteriores
			// agora modifica os atuais
			$v1 = $_SESSION['user_id'];
			$v2 = date('d/m/Y H:i:s');
			$atual = getVar($varid);
			$q = "update var set (addby,adddate,$coluna) = ($1,$2,$3) where id = $4";
			$res = pg_query_params($conn,$q,[$v1,$v2,$newval,$varid]);
			if ($res) {
				if ($datavar == '') {
					echo getVar($varid)." $unit <span style='color:white;font-size:80%'>[$v1@$v2]</span> <img src='icon/edit.png' width=16 height=16 style='cursor:pointer' onclick='editVar($varid,\"$newval\")'>";
				} else {
					echo getVar($varid)." $unit <span style='color:white;font-size:80%'>[$v1@$v2|$datavar]</span> <img src='icon/edit.png' width=16 height=16 style='cursor:pointer' onclick='editVar($varid,\"$newval\")'>";
				}
			} else {
				pg_send_query_params($conn,$q,[$v1,$v2,$newval,$varid]);
				$res = pg_get_result($conn);
				$resErr = pg_result_error($res);
				echo "Erro ao inserir registro! (query: $q | $v1, $v2, $newval, $varid) ($resErr)";
			}
		} else {
			echo "Erro ao fazer backup! (query: $qDWH)";
		}
	} else {
		echo "Erro ao ler valores anteriores! (query: $q)";
	}
} else {
	echo "Erro ao localizar valores anteriores! (query: $q = $varid)";
}
?>
