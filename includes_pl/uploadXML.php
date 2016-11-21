<?php
include_once 'db_connect.php';
include_once 'functions.php';
sec_session_start();
$xml = file_get_contents('php://input');
$username = htmlentities($_SESSION['username']);
$filename = "$plantasfullpath/gps/".date('Y-m-d_H-i-s')."($username).xml"; // por que não aceita caminho relativo ??
$f = fopen($filename, 'w') or die('Erro ao tentar criar arquivo!');
if (fwrite($f, $xml)) { // mas não salva nem o usuário, nem o nome original do arquivo !! (Precisa?)
	if (fclose($f)) {
		echo 'Arquivo GPX salvo com sucesso.<BR>';
		// se salvou arquivo, usa o mesmo para inserir também no banco de dados
		// CONSEGUIR UM JEITO DE SALVAR NO BANCO DE DADOS, MESMO QUE O ARQUIVO NÃO TENHA SIDO SALVO COM SUCESSO ??!!
		$linha = "ogr2ogr -append -f PostgreSQL PG:\"host=".HOST." dbname=".DATABASE." user=".USER." password=".PASSWORD."\" $filename 2>&1";
		system($linha,$retval);
		echo "ogr2ogr retornou $retval [$filename]<BR>"; // retornando 2 ??
	} else {
		echo 'Erro ao tentar fechar arquivo!';
	}
} else {
	echo 'Erro ao tentar escrever arquivo!';
}
?>
