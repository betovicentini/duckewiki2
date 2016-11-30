<?php
//PEGA O SCRIPT EDITADO
$postedData = rawurldecode($_POST['conteudo']);
//$tempData = str_replace("\\", "",$postedData);
//$cleanData = json_decode($postedData);
//salva o arquivo modificado
//$osdados = htmlspecialchars_decode($cleanData);
//LE NOVAMENTE O ARQUIVO ORIGINAL
//$original = htmlspecialchars(file_get_contents($_POST['file']));
$original = file_get_contents($_POST['file']);
//$compara = abs(strcmp($postedData ,$original));
if ($postedData!=$original) {
	@mkdir("dev_logs", 0755);
	//get original file name
	$fn = explode("/",$_POST['file']);
	$nf = count($fn)-1;
	$fn = $fn[$nf];
	@mkdir("dev_logs/".$fn, 0755);
	$curd = date('Y-m-d_H:i');
	$nfn = $curd."_".$fn;
	$pnfn = "dev_logs/".$fn."/".$nfn;
	//se ainda não houver um arquivo modificado nesta data e minuto, salva um backup
	if (!file_exists($pnfn)) {
		$f = fopen($pnfn, "w+") or die("fopen failed");
		fwrite($f, file_get_contents($_POST['file']));
		fclose($f);
	}
	$f = fopen($_POST['file'], "w+") or die("fopen failed");
	fwrite($f, $postedData);
	fclose($f);
	echo "O arquivo foi salvo com sucesso";
} else {
	echo "Você não modificou nada";
}
?>
