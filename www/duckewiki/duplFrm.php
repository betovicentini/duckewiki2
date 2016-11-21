<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();
$id = getGet('id');
$nome = getGet('newname');
// duplica o formulário, obtendo o novo id
$q = "select * from frm
where id = $1";
$res = pg_query_params($conn,$q,[$id]);
if ($res) {
	if ($row = pg_fetch_array($res,NULL,PGSQL_ASSOC)) {
		$shared = $row['shared'];
		$hab = $row['hab'];
		$q = "insert into frm (addby,adddate,nome,shared,hab) values (".
			$_SESSION['user_id'].",'".date('d/m/Y H:i:s')."','$nome',$1,$2) returning id";
		$res = pg_query_params($conn,$q,[$shared,$hab]);
		if ($res) { // inseriu com sucesso
			$newID = pg_fetch_array($res,NULL,PGSQL_NUM)[0];
			// duplica os campos do formulário, usando o novo id
			$q = "select field from frmf
			where frm = $1
			order by ordem";
			$res = pg_query_params($conn,$q,[$id]);
			if ($res) {
				$fields = [];
				$ordem = 0;
				$q = "insert into frmf (addby,adddate,frm,field,ordem) values ";
				while ($row = pg_fetch_array($res,NULL,PGSQL_NUM)) {
					$ordem++;
					$q .= "(".$_SESSION['user_id'].",'".date('d/m/Y H:i:s')."',$newID,$row[0],$ordem),";
				}
				if ($ordem > 0) {
					$q = substr($q,0,-1).";";
					$res = pg_query($conn,$q);
					if ($res) {
						echo "Sucesso [$q]<BR>";
					} else {
						echo "Falha [$q]<BR>";
					}
				}
			}
		}
	}
}
?>
