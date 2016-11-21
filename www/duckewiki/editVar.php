<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();
$loginErro = login_error($conn);
if ($loginErro) {
	exit("<p>Você não tem autorização de acesso a esta página. Por favor, <a href='index.php' target='main'>volte para a tela inicial e faça o login</a>.</p>");
}
$varid = getGet('varid');
	// abre a janela de inserir novo valor
	$val = getGet('val');
	$q = "select v.addby,v.adddate,v.key,k.nome,v.val,v.valf,v.valt,v.unit,p.val poss,l.valname,k.pathname,k.tipo tip,kt.tipo,k.multiselect
	from var v
	left join poss p on p.key = v.key
	left join key k on k.id = v.key
	left join keytipo kt on kt.id = k.tipo
	left join val l on l.id = p.val
	where v.id = $1
	order by valname";
	$res = pg_query_params($conn,$q,[$varid]);
	if ($res) {
		if ($row = pg_fetch_array($res,NULL,PGSQL_ASSOC)) {
			$tipo = $row['tip'];
			$keyname = $row['pathname'];
			echo "($varid) ";
			switch ($tipo) {
				case 2 : // Variavel|Categoria
					if ($row['multiselect'] == 'S') {
						if (strpos($val,';') !== false) {
							$vals = explode(';',$val);
						} else {
							$vals = [$val];
						}
						$ctrl = 'chkposs';
						echo "$keyname: ";
						if (in_array($row['poss'],$vals)) {
							echo "<label style='white-space:nowrap'><input type='checkbox' name='chk$row[key]' value='$row[poss]' checked>$row[valname]</label>\n";
						} else {
							echo "<label style='white-space:nowrap'><input type='checkbox' name='chk$row[key]' value='$row[poss]'>$row[valname]</label>\n";
						}
						while ($row = pg_fetch_array($res,NULL,PGSQL_ASSOC)) {
							if (in_array($row['poss'],$vals)) {
								echo "<label style='white-space:nowrap'><input type='checkbox' name='chk$row[key]' value='$row[poss]' checked>$row[valname]</label>\n";
							} else {
								echo "<label style='white-space:nowrap'><input type='checkbox' name='chk$row[key]' value='$row[poss]'>$row[valname]</label>\n";
							}
						}
					} else {
						$ctrl = 'selposs';
						echo "$keyname: <select id='$ctrl'>";
						if ($row['poss'] == $val) {
							echo "<option value='$row[poss]' selected>$row[valname]</option>";
						} else {
							echo "<option value='$row[poss]'>$row[valname]</option>";
						}
						while ($row = pg_fetch_array($res,NULL,PGSQL_ASSOC)) {
							if ($row['poss'] == $val) {
								echo "<option value='$row[poss]' selected>$row[valname]</option>";
							} else {
								echo "<option value='$row[poss]'>$row[valname]</option>";
							}
						}
						echo "</select>";
					}
					break;
				default :
					if (strlen($val) < 20) {
						$ctrl = 'txtvar';
						echo "$keyname: <input type='text' id='$ctrl' value='$val' onkeyup='varKeyUp(event,$varid,\"$ctrl\")' />";
					} else {
						$ctrl = 'txavar';
						echo "$keyname: <textarea id='$ctrl' onkeyup='varKeyUp(event,$varid,\"$ctrl\")'>$val</textarea>";
					}
					break;
			}
		}
	}
	echo "<BR><button id='btnSave' type='button' onclick='btnSaveVarClick($varid,\"$ctrl\")'>Salvar</button>
	<button id='btnCancel' type='button' onclick='btnCancelVarClick()'>Cancelar</button>";
?>
