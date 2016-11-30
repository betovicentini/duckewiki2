<?php
$file = $_GET['file'];
$definition = $_GET['definition'];

//$dd = getcwd( );
$arq = "help/devhelp.json";
$tem = file_exists($arq);
if ($tem) {
	$input = @file_get_contents($arq);
	$ohelp = json_decode($input, True);
	$kkk = @$ohelp[$file];
	if (empty($kkk) || $kkk!=$definition) {
		$ohelp[$file] = $definition;
		file_put_contents($arq, json_encode($ohelp,TRUE));
		echo "Definição salva";
	}  else {
		echo "Você não mudou nada";
	}
} else {
	$res = array($file => $definition);
	file_put_contents($arq, json_encode($res,TRUE));
	echo "Definição salva";
}
//echo $definition;

?>
