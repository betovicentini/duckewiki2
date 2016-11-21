<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();
$tab = getGet('tab');
$id = getGet('id');
// salva e exclui primeiro as sub-tabelas vinculadas
switch ($tab) {
	case 'frm' :
		$q = "select * from frmf
		where frm = $1";
		$res = pg_query_params($conn,$q,[$id]);
		if ($res) {
			$ids = [];
			$qDWH = "insert into dwh (tab,tabid,col,val,addby,adddate) values ";
			while ($row = pg_fetch_array($res,NULL,PGSQL_ASSOC)) {
				echo "id: $row[id], ";
				$ids[] = $row['id'];
				foreach ($row as $key => $value) {
					if (!in_array($key,['id','addby','adddate'])) {
						if ($value == '') {
							$qDWH.="('frmf','$row[id]','$key',NULL,'$row[addby]','$row[adddate]'),";
						} else {
							$qDWH.="('frmf','$row[id]','$key',$value,'$row[addby]','$row[adddate]'),";
						}
					}
				}
			}
			if (sizeof($ids) > 0) { // pelo menos 1 subitem a ser salvo/excluído
				$qDWH = substr($qDWH,0,-1).";";
				$resDWH = pg_query($conn,$qDWH);
				if ($resDWH) {
					echo "sucesso em DWH [$qDWH]<BR>";
					// só exclui neste caso
					$q = "delete from frmf where id in (".implode(',',$ids).")";
					$res = pg_query($conn,$q);
					if ($res) {
						echo "sucesso ao excluir [$q]<BR>";
					} else {
						echo "falha ao excluir [$q]<BR>";
					}
				} else {
					echo "falha em DWH [$qDWH]<BR>";
				}
			} else {
				echo "não há subitens a excluir<BR>";
			}
		}
		break;
}
// salva o registro a ser excluído em dwh
$q = "select * from $tab where id = $1";
$res = pg_query_params($conn,$q,[$id]);
if ($res) {
	$qDWH = "insert into dwh (tab,tabid,col,val,addby,adddate) values ";
	if ($row = pg_fetch_array($res,NULL,PGSQL_ASSOC)) {
		foreach ($row as $key => $value) {
			if (!in_array($key,['id','addby','adddate'])) {
				if ($value == '') {
					$qDWH.="('$tab','$id','$key',NULL,'$row[addby]','$row[adddate]'),";
				} else {
					$qDWH.="('$tab','$id','$key','$value','$row[addby]','$row[adddate]'),";
				}
			}
		}
	}
	$qDWH = substr($qDWH,0,-1).";";
	$resDWH = pg_query($conn,$qDWH);
	if ($resDWH) {
		echo "sucesso em DWH [$qDWH]<BR>";
		// só exclui neste caso
		$q = "delete from $tab where id = $1";
		$res = pg_query_params($conn,$q,[$id]);
		if ($res) {
			echo "sucesso ao excluir [$q]<BR>";
		} else {
			echo "falha ao excluir [$q,$tab,$id]<BR>";
		}
	} else {
		echo "falha em DWH [$qDWH]<BR>";
	}
}
?>
