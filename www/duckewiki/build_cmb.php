<?php
/* Variáveis a conferir:
 * colspan=4
 * presença ou não do botão
 * case-sensitive ou não
 * 
 * Erros:
 * clicar não está adicionando no cmb puro (ex: Amostra coletada/Coletor)
 * Esc (no txt ou no sel) fechar o select
 * 
 * $cmbField != $who ???
 * 
Exemplo de uso:
<?php
x	$cmbTR = true;             // se cria uma linha na tabela ou não
x	$cmbColSpan = 1;           // quantas colunas o input deve ocupar
x	$cmbSeparaLabel = false;   // (só usado para $cmbTR = false) se o label fica numa coluna diferente do input
	$cmbLabel = 'Coletor';     // descrição do label
x	$cmbField = 'col';			// coluna do banco de dados ligada ao cmb
x	$cmbNome = 'coletor';      // nome do campo a ser passado ao POST (e recebido dele) (pode ser formado de $cmbWho)
x	$cmbValue = 'Zé da Silva'; // valor do campo a ser passado ao POST (e recebido dele) (pode ser formado de $cmbWho)
x	$hidValue = 0;             // valor do index do campo passado ao POST (e recebido dele) (pode ser formado de $cmbWho)
	$cmbTableName = 'pess';    // tabela a buscar no bd
	$cmbFieldNames = 'abreviacao,prenome'; // campos a buscar/mostrar no db
	$cmbFieldNames = 'nome,{rank=taxpairank}'; // rank será buscado, mas exibido no input-hidden 'taxpairank'
x	$cmbMulti = 'S' | 'N';
x	$cmbCaseSensitive = 0;		// se diferencia maiúsculas/minúsculas
	$who = 'Col';				// Sufixo do nome dos controles/elementos (botões, inputs, selects, divs)
	$cmbW = 500;				// largura (width) da janela de inserir novo item
	$cmbH = 500;				// altura (height) da janela de inserir novo item
	$cmbPHP = 'addPessoa.php';	// arquivo .php para inserir novo item
	$cmbTitle = 'Adicionar uma pessoa'; // título da janela de inserir novo item
	include('build_cmb.php');	// chama esta função
?>
 */
if ($edit == '' && empty($add)) {
	if (!empty($post)) {
		$cmbValue = getPost("txt$who"); // ok
		$hidValue = getPost("val$who");
	} else
	if (!empty($hidValue)) { // se decidir o valor fora daqui (quem faz isso?)
		// tratar { } em $cmbFieldNames
		$q = "select $cmbFieldNames from $cmbTableName where id = $1";
		$numFields = count(explode(',',$cmbFieldNames));
		$res = pg_query_params($conn,$q,[$hidValue]);
		if ($res) {
			$rowF = pg_fetch_array($res,NULL,PGSQL_NUM);
			switch ($numFields) {
				case 1: $cmbValue = $rowF[0];break;
				case 2: $cmbValue = $rowF[0].' ['.$rowF[1].']';break;
				case 3: $cmbValue = $rowF[0].' ('.$rowF[1].')'.' ['.$rowF[2].']';break;
				default: $cmbValue = $rowF[0];
			}
		} else {
			$cmbValue = '';
		}
	} else {
		$hidValue = '';
		$cmbValue = '';
	}
} else {
	if ($who == 'dettax') {
		$hidValue = $tax;
	} else {
		//if (isset($who) && isset($$who)) {
		//echo $who.','.$taxpai;
		if (!empty($$who)) {
			$hidValue = $$who;
		} else {
			// no caso de formulário anexo a um addX
			$hidValue = '';
		}
	}
	if ($hidValue != '') {
		$q = "select $cmbFieldNames from $cmbTableName where id = $1";
		$numFields = count(explode(',',$cmbFieldNames));
		$res = pg_query_params($conn,$q,[$hidValue]);
		if ($res) {
			$rowF = pg_fetch_array($res,NULL,PGSQL_NUM);
			switch ($numFields) { // talvez seja melhor copiar o modelo de $mmCollation?
				case 1: $cmbValue = $rowF[0];break;
				case 2:
					if ($who == 'taxpai') {
						$cmbValue = $rowF[0];
						$hidValue = "$hidValue|$rowF[1]";
					} else {
						$cmbValue = $rowF[0].' ['.$rowF[1].']';
					}
					break;
				case 3: $cmbValue = $rowF[0].' ('.$rowF[1].')'.' ['.$rowF[2].']';break;
				default: $cmbValue = $rowF[0];
			}
		} else {
			$cmbValue = '';
		}
	} else {
		$cmbValue = '';
	}
}
if (!empty($cmbLabCode)) {
	$cmbLabel = txt($cmbLabCode);
	$cmbTip = txt("$cmbLabCode.tip");
}
if (empty($cmbTip)) {
	$cmbTip = '';
} else {
	//$cmbTip = " <img src='icon/question.png' title='$cmbTip'>";
	$cmbTip = " <img src='icon/question.png'><div class='tooltip'>$cmbTip</div>";
}
if (empty($cmbFIXO)) {
	echo "<dl>";
	echo "<dt><label>$cmbLabel";
	/*
	if (isset($cmbNeed) && $cmbNeed) {
		echo "<span style='color:red'>*</span>";
		$cmbNeed = false; // para não cair nos próximos
	}
	*/
	echo "$cmbTip</label></dt>";
	echo "<dd>";
	if (isset($cmbOnInput) && $cmbOnInput != '') {
		echo "<input id='val$who' name='val$who' value='$hidValue' type='hidden' oninput='$cmbOnInput' />";
		$cmbOnInput = '';
	} else {
		echo "<input id='val$who' name='val$who' value='$hidValue' type='hidden' oninput='store(this)' />";
	}
	if (isset($cmbNeed) && $cmbNeed) {
		echo "<input required id='txt$who' name='txt$who' value='$cmbValue' type='text' onkeyup='cmbtxtKeyUp(event,\"N\",\"$cmbQuery\")' onkeydown='cmbtxtKeyDown(event)' onfocus='cmbtxtFocus(event)' oninput='store(this)' />";
		$cmbNeed = false; // para não cair nos próximos
	} else {
		echo "<input id='txt$who' name='txt$who' value='$cmbValue' type='text' onkeyup='cmbtxtKeyUp(event,\"N\",\"$cmbQuery\")' onkeydown='cmbtxtKeyDown(event)' onfocus='cmbtxtFocus(event)' oninput='store(this)' />";
	}
	if ($cmbPHP != '') {
		if (!isset($cmbW)) {
			$cmbW = 800;
		}
		if (!isset($cmbH)) {
			$cmbH = 0;
		}
		echo "<button type='button' id='btn$who' onclick='callAdd(\"$cmbPHP?from=$who\",$cmbW,$cmbH,1,1,\"$who\",\"$cmbTableName\",\"$cmbFieldNames\")'>+</button>";
	}
	echo "<div id='div$who'>";
	echo "</div>";
	echo "</dd>";
	echo "</dl>\n";
} else {
	echo "<dl style='background-color:lightgreen'><dt><label>$cmbLabel</label>$cmbTip</dt><dd>$cmbValue</dd></dl>";
	unset($cmbFIXO); // para não afetar os próximos cmb
}
$cmbTip = ''; // para não afetar o próximo
$cmbLabCode = ''; // para não afetar o próximo
?>
