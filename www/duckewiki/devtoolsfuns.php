<?php
$function = $_GET['function'];
$isjava = $_GET['isjava'];
$arquivo = $_GET['arquivo'];

//GET REFERENCES
//$dd = getcwd( );
$arq ="help/file_relations.json";
$input = @file_get_contents($arq);
$data = json_decode($input, True);

if ($isjava==0) {
	$kkk0 = @array_keys($data['thefunctions'][$arquivo][$function]['usedby']);
	$refsfiles = @$data['thefunctions'][$arquivo][$function]['usedby'];
	$linenum = @$data['thefunctions'][$arquivo][$function]['atlines'];
	$ondesta = $arquivo;
	$oque = "da função PHP";
} else {
	$kkk0 = @array_keys($data['thejavas'][$arquivo][$function]['usedby']);
	$refsfiles = @$data['thejavas'][$arquivo][$function]['usedby'];
	$linenum = @$data['thejavas'][$arquivo][$function]['atlines'];
	$ondesta = "js/".$arquivo;
	$oque = "da função javascript";
}

//GET DEFINITION
//$dd = getcwd( );
$arq2 = "help/devhelp.json";
$tem2 = file_exists($arq2);
$bt = "<input type='button'  onclick=\"javascript: enterdef('".$function."');\" value='Editar definição' ><br >";
$thedef = "<small>arquivo: ".$ondesta."[@ line: ".$linenum."]</small>&nbsp;<input type='button'  onclick=\"javascript:mostracodigo('".$ondesta."');\" value='Mostra código'  ><br ><br >";
if ($tem2) {
	$input2 = @file_get_contents($arq2);
	$ohelp2 = json_decode($input2, True);
	$kkk = @$ohelp2[$function];
	if (!empty($kkk)) {
		$thedef .= "<span id='curdefinition' >".$kkk."</span>&nbsp;".$bt;
	} else {
		$thedef .=  "<span id='curdefinition' >Definicao do arquivo não inserida</span>&nbsp;".$bt;
	}
}  else {
		$thedef .=  "<span id='curdefinition' >Arquivo de definições ainda não gerado</span>&nbsp;".$bt;
}



$meutexto = "
<div id='definicao' class='definicao'>
<h3>Info ".$oque." ".$function."</h3>
<br />
".$thedef."
</div>";

if (@in_array($function,$kkk0)) {
	$txt = "<ul>";
	foreach($refsfiles as $ff) {
		$txt .= "<li onclick=\"javascript: getfilerefs('".$ff."');\">".$ff."</li>";
	}
	$txt .= "</ul>";
	//echo $txt;
	$meutexto = "
<div id='definicao' class='definicao'>
<h3>Info da função ".$function."</h3>
<br />
".$thedef."
</div>";
if (!empty($txt)) {
$meutexto .= "
<div > 
<h3>Arquivos que usam a função</h3>
<div id='reflist' class='relacionados' >".$txt."</div>
</div>";
	}
	
} 
echo $meutexto;


?>
