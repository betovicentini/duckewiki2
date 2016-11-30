<?php
$file = $_GET['file'];
//$what = $_GET['what'];

//GET DEFINITION IF EXISTS

//$dd = getcwd( );
$arq = "help/devhelp.json";
$tem = file_exists($arq);
$bt = "<br><span id='defbut'><input type='button'  onclick=\"javascript: enterdef('".$file."');\" value='Editar definição'  ></span>&nbsp;<input type='button'  onclick=\"javascript:mostracodigo('".$file."',2);\" value='Editar o código'  >";
if ($tem) {
	//echo "nnnn testando aqui".$tem;

	$input = @file_get_contents($arq);
	$ohelp = json_decode($input, True);
	$kkk = @$ohelp[$file];
	//echo "nnnn testando aqui".$tem;
	if (!empty($kkk)) {
		$thedef = "<span id='curdefinition' >aaaa ".$kkk."</span>&nbsp;".$bt;
	} else {
		$thedef =  "<span id='curdefinition' >Definicao do arquivo não inserida</span>&nbsp;".$bt;
	}
}  else {
		$thedef =  "<span id='curdefinition' >Arquivo de definições ainda não gerado</span>&nbsp;".$bt;
}



//GET REFERENCES
//$dd = getcwd( );
//$arq = $dd."/temp/esquema.json";
$arq ="help/file_relations.json";
$input = @file_get_contents($arq);
$data = json_decode($input, True);
$kkk = @array_keys($data[$file]);
if (is_array($kkk)) {
$relatedfiles = "";
if (in_array("referedFiles",$kkk)) {
	$txt = "<ul>";
	$refsfiles = $data[$file]['referedFiles'];
	foreach($refsfiles as $ff) {
		$off = $ff['file'];
		$linen = $ff['atlines'];
		$txt .= "<li onclick=\"javascript: getfilerefs('".$off."');\">".$off." <small>[@ lines: ".$linen."]</small></li>";
	}
	$txt .= "</ul>";
	$relatedfiles = $txt;
}
$relatedfuns = "";
if (in_array("functions",$kkk)) {
	$txt = "<ul>";
	$refsfiles = $data[$file]['functions'];
	foreach($refsfiles as $kk => $ff) {
		foreach($ff as $fun) {
			$off = $fun['function'];
			$linen = $fun['atlines'];
		$txt .= "<li onclick=\"javascript: getfunrefs('".$off."',0,'".$kk."');\">".$off." [<small>".$kk."][@ lines: ".$linen."]</small></li>";
		}
	}
	$txt .= "</ul>";
	$relatedfuns =  $txt;
}

$relatedjavas = "";
if (in_array("javascripts",$kkk)) {
	$txt = "<ul>";
	$refsfiles = $data[$file]['javascripts'];
	foreach($refsfiles as $kk => $ff) {
		foreach($ff as $fun) {
			$off = $fun['function'];
			$linen = $fun['atlines'];
		$txt .= "<li onclick=\"javascript: getfunrefs('".$off."',1,'".$kk."');\">".$off." [<small>".$kk."][@ lines: ".$linen."]</small></li>";
		}
	}
	$txt .= "</ul>";
	$relatedjavas = $txt;
}

} 
else {
	$resposta = "Não há referências";
}

$meutexto = "
<div id='definicao' class='definicao'>
<h3>Info do arquivo ".$file."</h3><br>
".$thedef."</div>";
if (!empty($relatedfiles)) {
$meutexto .= "
<hr>
<div> 
<h3>Arquivos incluidos</h3>
<div id='reflist'  class='relacionados'>".$relatedfiles."</div>
</div>
";
}
if (!empty($relatedjavas)) {
$meutexto .= "
<hr>
<div> 
<h3>Javascripts usados pelo arquivo</h3>
<div id='javascriptlist' class='javascripts' >".$relatedjavas."</div>         
</div>";
}

if (!empty($relatedfuns)) {
$meutexto .= "
<hr>
<div> 
<h3>Funções usadas pelo arquivo</h3>
<div id='functionslist' class='functions'> ".$relatedfuns."</div>
</div>
";
}
echo $meutexto;

?>
