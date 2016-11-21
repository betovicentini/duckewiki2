<?php
$function = $_GET['function'];
$isjava = $_GET['isfun'];
$arquivo = $_GET['arquivo'];

//$dd = getcwd( );
//$arq = $dd."/temp/esquema.json";
$arq ="help/file_relations.json";
$input = @file_get_contents($arq);
$data = json_decode($input, True);

if ($isjava==0) {
	$oque = "classe";
	$kkk0 = @array_keys($data['themodels']['class'][$arquivo][$function]['usedby']);
	$refsfiles = @$data['themodels']['class'][$arquivo][$function]['usedby'];
	$ondesta = $arquivo;
} 
else {
	$oque = "função de classe";
	$kkk0 = @array_keys($data['themodels']['functions'][$arquivo][$function]['usedby']);
	$refsfiles = @$data['themodels']['functions'][$arquivo][$function]['usedby'];
	$ondesta = $arquivo;
}


//GET DEFINITION
//$dd = getcwd( );
$arq2 = "help/devhelp.json";
$tem2 = file_exists($arq2);
$bt = "<input type='button'  onclick=\"javascript: enterdef('".$function."');\" value='Editar definição' ><br >";
$thedef = "<small>arquivo: ".$ondesta."</small>&nbsp;<input type='button'  onclick=\"javascript:mostracodigo('model/".$ondesta."');\" value='Mostra código'  ><br ><br >";
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
<h3>Info da ".$oque." ".$function."</h3>
<br />
".$thedef."
</div>";
//GET REFERENCES
if (@in_array($function,$kkk0)) {
	$txt = "<ul>";
	foreach($refsfiles as $ff) {
		$txt .= "<li onclick=\"javascript: getfilerefs('".$ff."');\">".$ff."</li>";
	}
	$txt .= "</ul>";
	//echo $txt;

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
