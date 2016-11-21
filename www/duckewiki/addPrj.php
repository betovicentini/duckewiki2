<?php
	include_once '../../includes_pl/db_connect.php';
	include_once '../../includes_pl/functions.php';
	sec_session_start();
	/*
	 * To-do list:
	 * - padronizar toda a nomenclatura de controles (id/name):
	 *  - código minúsculo de 3 letras (btn=button, sel=select, txt=input type text, txa=textarea, div=div,
	 *   Enter clicar no botão de inserir coletor extra...
	 * - ... e depois navegar até deixar visível o que entrou
	 *   clicar nos coletores extras para ampliar/reduzir...
	 * - ... mas mantendo as seleções/posições?
	 * - filtrar coletores disponíveis
	 * - planta mostrar tag + [duas últimas localidades] (se planta não marcada, deixa em branco)
	 * - quando mudar o coletor selecionado, tirá-lo da lista de coletores extras selecionados (se estiver lá)
	 * - F5 não pode apagar o que já foi inserido!! (cookies?)
	 * - como buscar plantas marcadas? Por tag? Por id?
	 * - depois modificar para automatizar tudo a partir de arquivos texto?
	 * - picker pra data pra não ter problema de digitar na ordem errada
	 * - procurar na lista de outros coletores disponíveis pelo prenome
	 * - quando clicar em Add1Esp na tela principal, ativar a janela se já estiver aberta (ou abrir outra? Evitar abrir + de 1)
	 * - janela do popup está sem barra de rolagem
	 * - quando digitar um gênero, mostrar também as espécies/subespécies?
	 * ---------em casa
	 * - conferir Nova Ident contradições ex: Muita confiança + cf.
	 * - conferir Nova Amostra contradições ex: Lat negativa e Norte
	 * - adicionar coordenadas em UTM
	 * - FECHAR o datepicker quando sair pelo Tab/Esc
	 * -
	 * ------------------
	 * - form precisa ter id E name?
	 * Using an ID rather than a name to identify and obtain a reference to a form is generally preferable.
	 * http://www.dyn-web.com/tutorials/forms/references.php
	 *
	 *   nomes/ids diferentes para cada form, para poder salvar no localStorage
	 * - filtrar especímenes por id está dando "id ambíguo"
	 * - editar não está mostrando valores nos cmb
	 *
	 */
	$edit = getGet('edit');
	if ($edit == '') {
		$title = "Novo Projeto";
	} else {
		$title = "Editar Projeto";
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
/*function modoEdit() {
	document.body.style.background = '#D0B060';
	document.getElementById('btnSave').style.background = 'red';
}*/
/*function handlePopupResult(id,who='',texto='') {
	var val = document.getElementsByName('val'+who)[0];
	val.value = id;
	store(val); // precisa guardar senão perde no refill do aoCarregar()
	if (who == 'det') {
		var spn = document.getElementById('spn'+who);
		spn.innerHTML = texto;
		store(spn);
	} else
	if (['col','loc','hab','prj'].indexOf(who) >= 0) {
		var txt = document.getElementById('txt'+who);
		txt.value = texto;
		store(txt);
	}
	modoEdit(); // muda a cor do fundo, habilita o botão "Salvar"...
}*/
function fechaLogo(id,who,texto) {
	window.opener.handlePopupResult(id,who,texto);
    window.close();
}
/*function refillDivSpan() {
	// preencher de novo o div_mm e o span_det
}*/
function aoCarregar(edit) {
	if (edit == '') {
		refill('frmPrj');
	}
	document.getElementById("txtnome").focus();
	//modoEdit();
}
</script>
		<?php
			$tabela = 'prj';
			$update = getGet('update');
			$close = getGet('close');
			if ($edit == '') {
				emptyRow($tabela);
			} else {
				updateRow($tabela,$edit);
			}
			$divRes = '';
			if (!empty($post)) {
				$v1 = $_SESSION['user_id'];
				$v2 = date('d/m/Y H:i:s');
				$v3 = get('txtnome');
				$v4 = get('txturl');
				$v5 = get('txtfinanc');
				$v6 = get('txtprocs');
				$v7 = get('txtlogo');
				$v8 = get('valfrmmrf');
				$v9 = get('valfrmhab');
				$arrPar = [];
				for ($i=1; $i<=9; $i++) {
					$arrPar[] = ${"v$i"};
				}
				//$arrPar = array($v1,$v2,$v3,$v4,$v5,$v6,$v7,$v8,$v9);
				$cols = 'addby,adddate,nome,url,financ,procs,logo,morffrm,habfrm'; // deve ir numa linha só, sem espaços
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
					} else { // nada mudou na tabela principal
						$divRes = "<div id='divSucesso' style='background-color:#FF0000'>Não há mudanças a atualizar!</div>";
					}
					atualizaSubTabela($subTabela,$subLkpField,$subFields,$subOrder,"mmVal$subWho"."Std",$divRes1);
					//atualizaSubTabela('espcols','esp','col,ordem','ordem','mmValcolsStd',$divRes1);
					$divRes.=$divRes1;
				} else { // não está editando -> insere
					switch (registroExiste($tabela)) {
						case 'f' :
							echo "Não existe<BR><BR>";
							insereUm($tabela,$close,$divRes,$body);
							/*$res = tentaInserir($tabela,$q);
							echo "$q<BR>";
							if ($res) {
								$newID = pg_fetch_array($res,NULL,PGSQL_NUM)[0];
								$mmValStd = get("mmVal$subWho"."Std");
								if ($mmValStd != '') {
									$itens = explode(';',$mmValStd);
									$q1 = "insert into $subTabela (addby,adddate,$subLkpField,$subFields) values ";
									$ordem = 1;
									foreach ($itens as $item) {
										$q1.="($v1,'$v2',$newID,$item,$ordem),";
										$ordem++;
									}
									$q1 = substr($q1,0,-1).";";
									echo "query1: $q1<BR><BR>";
									$res1 = pg_query($conn,$q1);
									if ($res1) {
										$divRes = "<div id='divSucesso' style='background-color:#00FF00'>Registro inserido com sucesso!</div>";
										if ($close) {
											$body = "<body onload='fechaLogo($newID,\"prj\",\"$v3\")'>";
										}
									} else {
										pg_send_query($conn,$q1);
										$res1 = pg_get_result($conn);
										$resErr = pg_result_error($res1);
										$divRes = "<div id='divSucesso' style='background-color:#FF0000'>Erro ao inserir coletores extras: $resErr</div>";
									}
								} else {
									$divRes = "<div id='divSucesso' style='background-color:#00FF00'>Registro inserido com sucesso!</div>";
									if ($close) {
										$body = "<body onload='fechaLogo($newID,\"prj\",\"$v3\")'>";
									}
								}
							} else {
								pg_send_query_params($conn,$q,$arrPar);
								$res = pg_get_result($conn);
								$resErr = pg_result_error($res);
								$divRes = "<div id='divSucesso' style='background-color:#FF0000'>Erro ao inserir registro: $resErr</div>";
							}*/
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
	<head>
	<body onload='aoCarregar(<?= "$edit" ?>)'>
		<?php
			$loginErro = login_error($conn);
			if ($loginErro) {
				exit("<p>Você não tem autorização de acesso a esta página (erro: $loginErro). Por favor, <a href='index.php' target='main'>volte para a tela inicial e faça o login</a>.</p>");
			}
			echoHeader();
/*		<h1 style='text-align:center'><?= $title ?></h1>
		<?= $divRes ?>
		<form id='frmPrj' autocomplete='off' method='post' action=''>*/
		?>

			<dl>
				<dt>
					<label>Nome</label>
				</dt>
				<dd>
					<input name='txtnome' type='text' size=10 value=<?="'$nome'"?> oninput='store(this)' />
				</dd>

				<dt>
					<label>URL</label>
				</dt>
				<dd>
					<input name='txturl' type='text' size=10 value=<?="'$url'"?> oninput='store(this)' />
				</dd>

				<dt>
					<label>Financiador</label>
				</dt>
				<dd>
					<input name='txtfinanc' type='text' size=10 value=<?="'$financ'"?> oninput='store(this)' />
				</dd>

				<dt>
					<label>Processos</label>
				</dt>
				<dd>
					<input name='txtprocs' type='text' size=10 value=<?="'$procs'"?> oninput='store(this)' />
				</dd>
			</dl>
<!--// logo
// equipe:-->
			<?php
				$mmLabelH = 'Equipe';
				$mmLabel1 = 'Pessoas disponíveis';
				$mmLabel2 = 'Pessoas selecionadas';
				$who = 'equipe';
				$mmQuery = 'pess3';
				$mmTableLink = 'prjpess.pess';
				include('build_mm.php');

				/*$cmbLabel = 'Formulário morfologia';
				$cmbTableName = 'form';
				$cmbFieldNames = 'nome';
				$cmbCaseSensitive = 0;
				$who = 'frmmrf';
				$cmbQuery = '';
				$cmbPHP = 'addForm.php';
				include('build_cmb.php');

				$cmbLabel = 'Formulário hábitat';
				$cmbTableName = 'form';
				$cmbFieldNames = 'nome';
				$cmbCaseSensitive = 0;
				$who = 'frmhab';
				$cmbQuery = '';
				$cmbPHP = 'addForm.php';
				include('build_cmb.php');*/

				echoButtons();
			?>
		</form>
	</body>
</html>
