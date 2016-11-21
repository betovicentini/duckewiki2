<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();
$table = getGet('table');
$name = getGet('name');
// pega os valores selecionados
$marks = readMarks($table);

if (count($marks) > 0 && !file_exists('usr/'.$_SESSION['user_id']."/filter/$name")) {
	$fp = fopen('usr/'.$_SESSION['user_id']."/filter/$name","wb"); // 'b' não é só pra binário ??
	fwrite($fp,"$table\n");
	fwrite($fp,implode(',',$marks));
	fwrite($fp,"\n");
	fclose($fp);
	echo "Sucesso ao salvar $name!";
} else {
	if (count($marks) == 0) {
		echo "Não há registros a salvar!";
	} else
	if (file_exists('usr/'.$_SESSION['user_id']."/filter/$name")) {
		echo "Filtro '$name' já existe!";
	} else {
		echo "Erro ao salvar $name!";
	}
}
?>
