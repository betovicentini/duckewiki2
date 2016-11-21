<?php
/* Erros?
 * clicar não está adicionando no ex-(c m b) puro (ex: Amostra coletada/Coletor)
 * Esc (no txt ou no sel) fechar o select
 * 
 * 
 * 
 * NÃO COMEÇAR A EDITAR QUANDO DIGITA NO cmb (a não ser não-multi), e sim quando adiciona/remove/altera na tabela da direita
 * 
Exemplo de uso:
	$mmLabelH = 'Outros coletores'; // '<BR>(clique para selecionar|ocultar)' adicionado depois
	$mmLabel1 = 'Coletores disponíveis';
	$mmLabel2 = 'Coletores selecionados';
	-- $mmExpanded = false;		// começa expandido? -- saiu
	$who = 'cols';			// sufixo dos controles
	$mmTableName = 'pess';
	$mmFieldNames = 'abrev,prenome';
	$mmTableLink = 'espcols.col';
	-- $mmCollation = '* [*]';
	include('build_mm.php');	// chama esta função
*/
if (!empty($_POST)) { // se está enviando inserção ou edição
	$mmTxtSrc = getPost("txt$who");
	$mmHidSrc = getPost("val$who");
	$mmTxtStd = explode('; ',getPost("mmTxt$who"."Std"));
	$mmValStd = explode('; ',getPost("mmVal$who"."Std"));
	if (sizeof($mmTxtStd) == 1 && $mmTxtStd[0] == '') {
		$mmTxtStd = [];
		$mmValStd = [];
	}
} else
if (!empty($edit)) { // se está começando a editar
	$mmHidSrc = '';
	$mmTxtSrc = '';
} else { // está começando a inserir
	$mmHidSrc = '';
	$mmTxtSrc = '';
}

// lidas no EDIT, mostradas no controle fechado
if ($mmQuery == 'pess1') { // lidas em addEsp
	$q = "select ec.col,p.abrev||' ('||p.prenome||')'
	from espcols ec
	join pess p on p.id = ec.col
	where
	ec.esp = $1
	order by ec.ordem";
} else
if ($mmQuery == 'pess2') { // lidas em addPl
	$q = "select pc.col,p.abrev||' ('||p.prenome||')'
	from plcols pc
	join pess p on p.id = pc.col
	where
	pc.pl = $1
	order by pc.ordem";
} else
if ($mmQuery == 'pess3') { // lidas em addPrj
	$q = "select pp.pess,p.abrev||' ('||p.prenome||')'
	from prjpess pp
	join pess p on p.id = pp.pess
	where
	pp.prj = $1
	order by pp.ordem";
} else
if ($mmQuery == 'herb') {
	$q = "select eh.herb||'号'||eh.tomb,h.sigla||' ('||eh.tomb||')号'||h.nome
	from espherb eh
	join herb h on h.id = eh.herb
	where
	eh.esp = $1";
} else
if ($mmQuery == 'vern') {
	$q = "select ev.vern,v.nome||' ('||l.sigla||')'
	from espvern ev
	join vern v on v.id = ev.vern
	join lang l on v.lang = l.id
	where
	ev.esp = $1";
} else
if ($mmQuery == 'taxespec') { // USAR coalesce() SEMPRE QUE UM NULL PUDER INVALIDAR TODA A STRING !!
	$q = "select te.espec,coalesce(p.abrev,'')||' ('||coalesce(p.prenome,'')||')'
	from taxespec te
	join pess p on p.id = te.espec
	where
	te.tax = $1";
} else
if ($mmQuery == 'espectax') {
	$q = "select t.id,gettax(t.id)
	from taxespec te
	join tax t on t.id = te.tax
	where te.espec = $1";
} else
if ($mmQuery == 'taxsin') {
	$q = "select sin,gettax(sin)
	from taxsin
	where
	tax = $1";
} else
if ($mmQuery == 'locpai') {
	$q = "select lp.pai, l.nome||'号'||getloc(lp.pai)
	from locpai lp
	join loc l on l.id = lp.pai
	where
	lp.loc = $1";
} else
if ($mmQuery == 'keyvar') {
	$q = "select v.id,v.valname
	from poss p
	join val v on v.id = p.val
	where
	p.key = $1";
} else
if ($mmQuery == 'habtax') {
	$q = "select ht.tax, gettax(ht.tax)
	from habtax ht
	where
	ht.hab = $1";
} else
if ($mmQuery == 'frmf') {
	$q = "select concat(k.id,'号'||ff.grp),concat(k.pathname,' ['||ff.grp||']'),ff.ordem
	from frmf ff
	join key k on k.id = ff.field
	where ff.frm = $1
	order by ff.ordem";
}

// PARTE DAS SUBTABELAS (como passá-las para o POST -> $hidmmData)
$mmTableLinkCol = substr($mmTableLink,strpos($mmTableLink,'.')+1); // depois do .
$mmTableLinkTab = substr($mmTableLink,0,strpos($mmTableLink,'.')); // antes do .
$qOrdem = "select count(*) from information_schema.columns
where table_name=$1 and column_name='ordem'";
$res = pg_query_params($conn,$qOrdem,[$mmTableLinkTab]);
$existeOrdem = 0;
if ($res) {
	$row2 = pg_fetch_array($res,NULL,PGSQL_NUM);
	if ($row2[0] == 1) { // existe a coluna 'ordem'
		$existeOrdem = 1;
	}
}
//echo "query: $q [$mmFieldPai]<BR><BR>";
if (!empty($mmFieldPai)) {
	$hidData = "$mmTableLinkTab;$mmFieldPai;$mmTableLinkCol";
	$mmFieldPai = ''; // para não afetar os próximos
} else {
	$hidData = "$mmTableLinkTab;$tabela;$mmTableLinkCol";
}
//echo "hidData: $hidData<BR><BR>";
if ($existeOrdem) {
	//$hidData = "$hidData,ordem;ordem";
	$hidData = "$hidData;ordem";
} else {
	$hidData = "$hidData;";
}
$hidData = "$hidData;$who";
if (!empty($mmExtraField)) {
	$hidData = "$hidData;$mmExtraField";
	$mmExtraField = ''; // para não afetar os próximos
}
if (isset($hidmmDataN)) {
	$hidmmDataN++;
	$hidmmData = "$hidmmData|$hidData";
} else {
	$hidmmDataN = 1;
	$hidmmData = $hidData;
}

$mmValStd = [];
$mmTxtStd = [];
//echo "q_mm: $q; edit = $edit<BR><BR>";
$res = pg_query_params($conn,$q,[$edit]);
if ($res) {
	while ($row2 = pg_fetch_array($res,NULL,PGSQL_NUM)) {
		$mmValStd[] = $row2[0];
		$mmTxtStd[] = $row2[1];
	}
}
/*print_r($mmValStd);
echo "<BR><BR>";
print_r($mmTxtStd);
echo "<BR><BR>";*/
// início do controle
echo "<dl>\n<dt>";

if (!empty($mmLabCode)) {
	$mmLabelH = txt($mmLabCode);
	//$mmTip = " <img src='icon/question.png' title='".txt("$mmLabCode.tip")."'>";
	$mmTip = " <img src='icon/question.png'><div class='tooltip'>".txt("$mmLabCode.tip")."</div>";
} else {
	$mmTip = '';
}

// "coluna de cabeçalho" (Header - divH)
//echo "<div id='divHA$who' style='display:none'><label><a href='javascript:mmExpand(\"$who\",0)'>$mmLabelH<BR>(".txt('clickhid').")</a></label></div>";
//echo "<div id='divHF$who'><label><a href='javascript:mmExpand(\"$who\",1)'>$mmLabelH<BR>(".txt('clicksel').")</a></label></div>";
echo "<div id='divHA$who' style='display:none'><label><a href='javascript:mmExpand(\"$who\",0)'>$mmLabelH</a></label>$mmTip</div>";
echo "<div id='divHF$who'><label><a href='javascript:mmExpand(\"$who\",1)'>$mmLabelH</a></label>$mmTip</div>";
$mmLabCode = ''; // para não afetar o próximo

// primeira coluna
echo "</dt>\n<dd><div id='divF$who' style='text-align:left'>";
if ($edit) { // aqui (divF) tem que inserir os valores a editar
	$texto = '';
	foreach ($mmTxtStd as $txtstd) {	// deve repetir depois em mmExpand
		$pos = mb_strpos($txtstd,'号',0,'UTF-8'); // mostra parte oculta por 号 como o title de um label
		if ($pos > 0) {
			$txtstdA = mb_substr($txtstd,0,$pos,'UTF-8');
			$txtstdB = mb_substr($txtstd,$pos+1,null,'UTF-8');
			$texto .= "<label title='$txtstdB'>$txtstdA</label>; ";
		} else {
			$texto .= "$txtstd; ";
		}
	}
	echo substr($texto,0,-2);
}
echo "</div>";
echo "<div id='divA$who' style='display:none'><table><tr><td>";
echo "<input id='val$who' name='val$who' value='$mmHidSrc' type='hidden' oninput='store(this)' />";
echo "<label>$mmLabel1</label>";
echo "<BR>";
//if (isset($mmTableName) && isset($mmFieldNames)) { // NÃO USA MAIS $mmTableName NEM $mmFieldNames !!
echo "<input id='txt$who' name='txt$who' value='$mmTxtSrc' type='text' onkeyup='cmbtxtKeyUp(event,\"S\",\"$mmQuery\")' onkeydown='cmbtxtKeyDown(event)' onfocus='cmbtxtFocus(event)' oninput='store(this)' />";
echo "<div id='div$who'>";
echo "</div>";
echo "\n</td>";

// segunda coluna
echo "\n<td>";
echo "<button type='button' id='btn$who"."Add' onclick='mmAdd(\"$who\")'> &gt;&gt; </button><BR>";
echo "<button type='button' id='btn$who"."Rem' onclick='mmRem(\"$who\")'> &lt;&lt; </button>";
echo "</td>\n";

// terceira coluna
echo "<td>";
echo "<label>$mmLabel2</label><BR>";
echo "<input id='mmVal$who"."Std' name='mmVal$who"."Std' type='hidden' value='".implode(';',$mmValStd)."' />";
echo "<input id='mmTxt$who"."Std' name='mmTxt$who"."Std' type='hidden' value='".implode(';',$mmTxtStd)."' />";
if ($who == 'var') { // apenas para stdvar em addFrm.php
	echo "<select id='std$who' name='std$who' size=9 multiple=true onkeydown='mmselStdKeyDown(event)' ondblclick='mmselStdDblClick(event)' contextmenu='mnu1'>";
} else {
	echo "<select id='std$who' name='std$who' size=9 multiple=true onkeydown='mmselStdKeyDown(event)' ondblclick='mmselStdDblClick(event)'>";
}
if ($edit) {
	for ($i=0; $i<sizeof($mmTxtStd); $i++) {
		$pos = mb_strpos($mmTxtStd[$i],'号',0,'UTF-8'); // mostra parte oculta por 号 como o title de um label
		if ($pos > 0) {
			$txtstdA = mb_substr($mmTxtStd[$i],0,$pos,'UTF-8');
			$txtstdB = mb_substr($mmTxtStd[$i],$pos+1,null,'UTF-8');
			echo "<option value='$mmValStd[$i]' title='$txtstdB'>$txtstdA</option>";
		} else {
			echo "<option value='$mmValStd[$i]'>$mmTxtStd[$i]</option>";
		}
	}
}
echo "</select></td>\n";

// quarta coluna
echo "<td>";
echo "<button type='button' id='btn$who"."Up' onclick='mmselStdUp(\"$who\")'> ^ </button><BR>";
echo "<button type='button' id='btn$who"."Down' onclick='mmselStdDown(\"$who\")'> v </button>";
echo "</td>\n</tr></table></div></dd></dl>";
?>
