<?php
	include_once '../../includes_pl/db_connect.php';
	include_once '../../includes_pl/functions.php';
	sec_session_start();
	/*
	 * To-do list:
	 * - padronizar toda a nomenclatura de controles (id/name):
	 *  - código minúsculo de 3 letras (btn=button, sel=select...)
	 *  - colocar o id[nome] dos controles no alto da página
	 *  - nome poderia ser o mesmo nome da coluna...? e não usar id?
	 * - não consigo editar a URL na janela pop-up! Bom. Se fora do modo debug, nem mostrar a barra de URL
	 * 
	 * 
	 * Agrupar (coluna Grupo em formf)
	 * 
	 */
	$edit = getGet('edit');
	if ($edit == '') {
		$title = txt('novo').' '.txt('frm');
	} else {
		$title = txt('edit').' '.txt('frm');
	}
?>

<!DOCTYPE html>
<html lang='BR'>
	<head>
		<meta charset='UTF-8'>
		<title><?=$title?></title>
		<link rel="stylesheet" type="text/css" href="cssDuckeWiki.css">
		<script src='funcoes.js'></script>
<script>
function agrupa() {
	var i, comecou=false, parou=false, descontinuo=false, first=-1, last;
	var sel = document.getElementById('stdvar');
	for (i=0; i<sel.length; i++) {
		if (sel.options[i].selected) {
			if (first < 0) {
				first = i;
			}
			last = i;
			if (parou) {
				descontinuo = true;
			} else {
				comecou = true;
			}
		} else {
			if (comecou) {
				parou = true;
			}
		}
	}
	var update=false;
	if (descontinuo) {
		alert('Não é possível agrupar uma seleção descontínua. Selecione itens numa única sequência.');
	} else
	if (first == last) {
		alert('Não é possível agrupar um único item. Selecione mais de um item numa sequência única.');
	} else {
		var grp = prompt("Digite o nome do grupo");
		if (grp == '') {
			for (i=0; i<sel.length; i++) {
				if (sel.options[i].selected) {
					setGrp(sel[i],''); // exclui o grupo
					update = true;
				}
			}
		} else
		if (grp) {
			for (i=0; i<sel.length; i++) {
				if (sel.options[i].selected) {
					setGrp(sel[i],grp);
					update = true;
				}
			}
		}
	}
	if (update) {
		updateStd('var');
		store(sel);
	}
}
function fechaLogo(id,who,texto) {
	window.opener.handlePopupResult(id,who,texto);
    window.close();
}
function aoCarregar(edit) {
	if (edit == '') {
		refill('frmForm');
	}
	document.getElementsByName("txtnome")[0].focus();
}
</script>
		<?php
			$tabela = 'frm';
			$update = getGet('update');
			$close = getGet('close');
			if ($edit == '') {
				emptyRow($tabela);
			} else {
				updateRow($tabela,$edit);
			}
			$divRes = '';
			if (!empty($post)) {
				/*$subTabela = 'formf';
				$subLkpField = 'form';
				$subFields = 'field,ordem';
				$subOrder = 'ordem';
				$subWho = 'var';*/
				echo 'hidmmData: '.get('hidmmData').'<BR><BR>';
				$v1 = $_SESSION['user_id'];
				$v2 = date('d/m/Y H:i:s');
				$v3 = get('txtnome');
				$v4 = get('radShared');
				$v5 = get('chkHab') == 'on' ? 'S' : 'N';
				$arrPar = [];
				for ($i=1; $i<=5; $i++) {
					$arrPar[] = ${"v$i"};
				}
				//$arrPar = array($v1,$v2,$v3,$v4,$v5);
				$cols = 'addby,adddate,nome,shared,hab'; // deve ir numa linha só, sem espaços
				$arrCol = explode(',',$cols);
				if ($edit) {
					$q = "update $tabela set (";
					if (algumaMudou($q)) { // pelo menos uma coluna mudou -> edita
						$arr2 = montaQuery($q);
						$res = pg_query_params($conn,$q,[$edit]);
						if ($res) {
							add2dwh($arr2,$divRes); // add to data warehouse
							updateRow($tabela,$edit);
							if ($close) {
								$body = "<body onload='fechaLogo($edit)'>";
							}
						} else {
							pg_send_query_params($conn,$q,[$edit]);
							$res = pg_get_result($conn);
							$resErr = pg_result_error($res);
							$divRes = "<div id='divSucesso' style='background-color:#FF0000'>Erro ao atualizar registro ($q): $resErr</div>";
						}
					} else {
						$divRes = "<div id='divSucesso' style='background-color:#FF0000'>Não há mudanças a atualizar!</div>";
					}
					/*if (atualizaSubTabela($subTabela,$subLkpField,$subFields,$subOrder,"mmVal$subWho"."Std",$divRes1)) {
						$divRes = $divRes1;
					}*/
					$MMs = explode('|',get('hidmmData'));
					foreach ($MMs as $MM) {
						$M = explode(';',$MM);
						if (atualizaSubTabela($M,$divRes1)) {
							$divRes.=$divRes1;
						}
					}
				} else { // não está editando -> insere
					switch (registroExiste($tabela)) {
						case 'f' :
							echo "Não existe<BR><BR>";
							insereUm($tabela,$close,$divRes,$body);
							break;
						case 't' :
							$divRes = "<div id='divSucesso' style='background-color:#FF0000'>Registro já existe.</div>";
							break;
						default :
							pg_send_query_params($conn,$q,$arrPar);
							$res = pg_get_result($conn);
							$resErr = pg_result_error($res);
							$divRes = "<div id='divSucesso' style='background-color:#FF0000'>Erro na query: $resErr</div>";
					}
				} // fim do insert
			}
			pullCfg();
		?>
	</head>
	<body onload='aoCarregar(<?="$edit"?>)'>
		<?php
			$loginErro = login_error($conn);
			if ($loginErro) {
				exit("<p>Você não tem autorização de acesso a esta página (erro: $loginErro). Por favor, <a href='index.php' target='main'>volte para a tela inicial e faça o login</a>.</p>");
			}
			echoHeader();
/*		<h1 style='text-align:center'><?=$title?></h1>
		<?=$divRes?>
		<form id='frmForm' autocomplete='off' method='post' action=''>*/
		?>

			<dl>
				<dt>
					<label>
						<?=txt('nome')?>
					</label>
				</dt>
				<dd>
					<input name='txtnome' type='text' value=<?="'$nome'"?> oninput='store(this)' />
				</dd>
			</dl>

			<?php
				$mmLabelH = txt('keys');
				$mmLabel1 = txt('keydisp');
				$mmLabel2 = txt('keysel');
				$who = 'var';
				$mmQuery = 'frmf';
				$mmTableName = 'key';
				$mmTableLink = 'frmf.field';
				$mmExtraField = 'grp';
				include('build_mm.php');
			?>

			<dl>
				<dt>
					<label>
						<?=txt('frmuso')?>
					</label>
				</dt>
				<dd>
					<?php
						if ($shared == 'N') {
							echo "<input type='radio' name='radShared' value='N' checked onclick='store(this)'>".txt('frmpess');
							echo " <input type='radio' name='radShared' value='S' onclick='store(this)'>".txt('frmcomp');
						} else
						if ($shared == 'S') {
							echo "<input type='radio' name='radShared' value='N' onclick='store(this)'>".txt('frmpess');
							echo " <input type='radio' name='radShared' value='S' checked onclick='store(this)'>".txt('frmcomp');
						} else {
							echo "<input type='radio' name='radShared' value='N' onclick='store(this)'>".txt('frmpess');
							echo " <input type='radio' name='radShared' value='S' onclick='store(this)'>".txt('frmcomp');
						}
					?>
				</dd>

				<dt>
					<label>
						<?= txt('frmhab') ?>
					</label>
				</dt>
				<dd>

					<!--
					if () {
						echo "";
					} else {
						echo "<input type='checkbox' name='chkHab' onclick='store(this)' />";
					}
					-->
					<input type='checkbox' name='chkHab' <?= ($hab == 'S') ? checked : " " ?> onclick='store(this)' />
				</dd>
			</dl>

<input type='hidden' name='hidmmData' value=<?="'$hidmmData'"?> />

			<?php
				echoButtons();
			?>
		</form>
<menu type="context" id="mnu1">
  <menuitem label="Agrupar (Ctrl+Enter)" onclick="agrupa()"></menuitem>
</menu>
</body>
</html>
