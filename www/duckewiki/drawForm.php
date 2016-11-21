<script src='cor.js'></script>
<?php
// primeiro guarda as unidades disponíveis
$q = "select * from varunit order by id";
$res = pg_query($conn,$q);
$undId = [];
$undName = [];
while ($row = pg_fetch_array($res,NULL,PGSQL_NUM)) {
	$undId[] = $row[0];
	$undName[] = $row[1];
}
// depois conta/memoriza os grupos de variáveis contíguas (pertencentes ao mesmo pai/avô)
$q = "select k.nome, ff.field, k.pai, k1.pai avo, k1.nome nomepai, k2.nome nomeavo, ff.ordem, k.pathname, k.def, k.nome, k.tipo, k.unit, k.multiselect from frm f
		left join frmf ff on ff.frm = f.id
		left join key k on k.id = ff.field
		left join key k1 on k.pai = k1.id
		left join key k2 on k1.pai = k2.id
		where f.id = $1
		order by ordem";
$res = pg_query_params($conn,$q,[$frmid]);
$paiAnt = null;
$avoAnt = null;
$fieldAnt = null;
$grupo = 0;
$grupos = [];
$fields = [];
$continua = false;
while ($row = pg_fetch_array($res,NULL,PGSQL_ASSOC)) {
	$pai = $row['pai'];
	$avo = $row['avo'];
	if ($pai == $paiAnt || $pai == $avoAnt || $avo == $paiAnt) {
		if ($paiAnt != null || $avoAnt != null) {
			if (!$continua) {
				$grupo++;
				$continua = true;
				$grupos[] = $grupo;
				$fields[] = $fieldAnt;
			}
			$grupos[] = $grupo;
			$fields[] = $row['field'];
		}
	} else {
		$continua = false;
	}
	$paiAnt = $pai;
	$avoAnt = $avo;
	$fieldAnt = $row['field'];
}
if (isset($_GET['col'])) {
	$col = $_GET['col'];
	$$col = $_GET["$col"];
}
// finalmente exibe as variáveis na ordem certa
$q = "select k.nome, ff.field, r.".$col.", k.pai, k1.pai avo, k1.nome nomepai, k2.nome nomeavo, ff.ordem, k.pathname, v.id val_id, v.valname, v.valdef, k.def, k.nome, k.tipo, u.unit, k.multiselect from frm f
left join frmf ff on ff.frm = f.id
left join key k on k.id = ff.field
left join poss p on p.key = k.id
left join val v on p.val = v.id
left join key k1 on k.pai = k1.id
left join key k2 on k1.pai = k2.id
left join varunit u on u.id = k.unit
left join var r on r.".$col." = $1 and r.key = ff.field
where f.id = $2
order by ordem, v.valname";
$res = pg_query_params($conn,$q,[$$col,$frmid]);
$grupo = null;
$grupoAnt = null;
$fieldAnt = null;
$fs = false;
$fspainome = null;
$expfs = $_SESSION['cfg.expfs'];
if ($expfs == 'S') {
	$fsAdisplay = 'none';
	$fsBdisplay = 'block';
} else {
	$fsAdisplay = 'block';
	$fsBdisplay = 'none';
}
$meses = ['',txt('mes01'),txt('mes02'),txt('mes03'),txt('mes04'),txt('mes05'),txt('mes06'),
	txt('mes07'),txt('mes08'),txt('mes09'),txt('mes10'),txt('mes11'),txt('mes12')];
echo "<BR><dl><dt><label>".txt('data')."</label>  <img src='icon/question.png' title='".txt('datafrm.tip')."'></dt>
<dd><input type='text' name='txtfrmdia' size=5 placeholder='".txt('dia')."' oninput='store(this)' onkeyup='txtdiakeyup(this,\"selfrmmes\",\"txtfrmano\")' />
<select name='selfrmmes' style='width: 150px' onchange='store(this);selmeschange(this,\"txtfrmdia\",\"txtfrmano\")'>";
for ($i=0; $i<13; $i++) {
	/*if (!empty($mes) && $mes == $i) {
		echo "<option value=$i selected>$meses[$i]</option>";
	} else {*/
		echo "<option value=$i>$meses[$i]</option>";
	//}
}
echo "</select>
<input type='text' name='txtfrmano' size=5 placeholder='".txt('ano')."' value='$ano' oninput='store(this)' onblur='txtanoblur(this,\"txtfrmdia\",\"selfrmmes\")' /></dd>
</dl>";

$cmbLabel = txt('bibref');
$cmbTip = txt('bibreffrm.tip');
$cmbTableName = 'bib';
$cmbFieldNames = 'title,autor';
$cmbCaseSensitive = 0;
$who = 'bibfrm';
$cmbQuery = 'bib';
$cmbPHP = 'addBib.php';
include('build_cmb.php');

while ($row = pg_fetch_array($res,NULL,PGSQL_ASSOC)) {
	$field = $row['field'];
	if ($field != $fieldAnt) {
		if ($fieldAnt != '') {
			echo "</dd></dl>\n";
		}
		if (in_array($field,$fields)) { // é um dos que tem fieldset
			$grupo = $grupos[array_search($field,$fields)]; // grupo/número do fieldset
			if (!$fs) { // abre um novo se não tinha
				$fspainome = $row['nomepai'];
				echo "<div id='divFrmFSa".$grupo."' style='display:$fsAdisplay'><a href='javascript:popFS($grupo,\"Frm\")'>&lt;$fspainome&gt;</a></div>";
				echo "<div id='divFrmFSb".$grupo."' style='display:$fsBdisplay'>";
				echo "<fieldset><legend><a href='javascript:unpopFS($grupo,\"Frm\")'>$fspainome</a></legend>\n";
				$fs = true;
			} else // tem um fieldset aberto
			if ($grupo != $grupoAnt) { // fecha o antigo e abre um novo se apenas mudou o grupo
				echo "</fieldset></div>\n";
				if ($row['nomeavo'] != '') {
					$fspainome = $row['nomeavo'].' - '.$row['nomepai'];
				} else {
					$fspainome = $row['nomepai'];
				}
				echo "<div id='divFrmFSa".$grupo."' style='display:$fsAdisplay'><a href='javascript:popFS($grupo,\"Frm\")'>&lt;$fspainome&gt;</a></div>";
				echo "<div id='divFrmFSb".$grupo."' style='display:$fsBdisplay'>";
				echo "<fieldset><legend><a href='javascript:unpopFS($grupo,\"Frm\")'>$fspainome</a></legend>\n"; // $fs não muda
			}
		} else // não deve ter fieldset
		if ($fs) { // se estava aberto, fecha
			echo "</fieldset></div>\n";
			$fs = false;
			$fspainome = null;
		}
		$path = $row['pathname'];
		$def = $row['def'];
		if (strpos($path,$fspainome) === 0) { // apaga o nome do fieldset do label no <dt>
			$pos2 = strlen($fspainome)+3;
			$path = substr($path,$pos2);
		}
		echo "<BR><dl><dt>$path <img src='icon/question.png' title='$def'>"; // nome
		if ($row[$col] != '') {
			//echo "<a href='javascript:jump2anchor($field)'><img src='icon/exclam.png' title='Existem valores associados a esta variável'></a>"; // nome
			$icon = 'eye.png';
			switch ($_SESSION['cfg.frmdest']) {
				case 'S' : // Self
					echo "<img src='icon/$icon' title='Existem valores associados a esta variável' style='cursor:pointer' onclick='jump2anchor($field)'>"; // nome
					break;
				case 'T' : // Tab
					echo "<img src='icon/$icon' title='Existem valores associados a esta variável' style='cursor:pointer' onclick='window.opener.jump2anchor($field,1)'>"; // nome
					break;
				case 'W' : // Window
					echo "<img src='icon/$icon' title='Existem valores associados a esta variável' style='cursor:pointer' onclick='window.opener.jump2anchor($field)'>"; // nome
					break;
				default :
					echo "<img src='icon/$icon' title='Existem valores associados a esta variável' style='cursor:pointer' onclick='window.opener.jump2anchor($field)'>"; // nome
			}
		}
		echo "</dt>\n<dd>";
	}
	$valname = $row['valname'];
	$valdef = $row['valdef'];
	$val_id = $row['val_id'];
	if ($valname == null) {
		switch ($row['tipo']) {
			case 5 :
				echo "<textarea name='txa$field'></textarea>\n";
				break;
			case 7 :
				echo "<input id='txt$field' type='text' oninput='getCorDB(this.value,\"$field\")'><!--div style='display:inline-block;border:1px solid black;width:50px;background-color:white'>&nbsp</div-->
				<div id='divcor$field' style='display:inline'>
					<canvas width=50 height=20 id='cnv$field' onclick='getCor(\"$field\")'></canvas>
					<label id='lbl' onclick='lblClick(this)'>#</label>
				</div>\n";
				break;
			default :
				echo "<input type='text' name='txt$field'> ";
				//echo $row['unit']."\n";
				echo "<select name='und$field'>";
				for ($i=0; $i<sizeof($undId); $i++) {
					if ($row['unit'] == $undName[$i]) {
						echo "<option value=$undId[$i] selected>$undName[$i]</option>";
					} else {
						echo "<option value=$undId[$i]>$undName[$i]</option>";
					}
				}
				//echo "<option>".$row['unit']."</option>";
				echo "</select> \n";
		}
	} else {
		$multi = $row['multiselect'];
		if ($multi == 'S') {
			echo "<label style='white-space:nowrap'><input type='checkbox' name='chk$field"."[]' value='$val_id'>$valname";
			if (!empty($valdef)) {
				echo " <img src='icon/question.png' title='$valdef'>";
			}
			echo "</label>\n";
		} else {
			echo "<label style='white-space:nowrap'><input type='radio' name='rad$field' value='$val_id'>$valname";
			if ($valdef != '') { // POR QUÊ ARBUSTO VAI?
				echo " <img src='icon/question.png' title='$valdef'>";
			}
			echo "</label>\n";
		}
	}
	$fieldAnt = $field;
	$grupoAnt = $grupo;
}
echo "</dd></dl>\n";
echo "<div id='divCor' style='position:absolute;display:none' onmousedown='cnvMouseDown(event,2)' onmousemove='cnvMouseMove(event)'>\n
	<canvas id='cnvGetCor'></canvas>\n
</div>\n";
?>
