<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();
$table = getGet('table');
$mark = getGet('mark');
$unmark = getGet('unmark');
// pega os valores anteriores
$oldMarks = readMarks($table);
/*if (file_exists('usr/'.$_SESSION['user_id']."/mark/$table.txt")) {
	$oldMarks = file('usr/'.$_SESSION['user_id']."/mark/$table.txt");
	if (!empty($oldMarks)) {
		$oldMarks = explode(',',$oldMarks[0]);
	} else {
		$oldMarks = [];
	}
} else {
	$oldMarks = [];
}*/
// elimina
if ($unmark) {
	$oldMarks = array_diff($oldMarks,explode(',',$unmark));
}
// adiciona
if ($mark) {
	$oldMarks = array_merge(explode(',',$mark),$oldMarks);
	$oldMarks = array_unique($oldMarks);
	sort($oldMarks);
}
// e salva
$selCount = count($oldMarks);
$fp = fopen('usr/'.$_SESSION['user_id']."/mark/$table.txt","wb");
fwrite($fp,implode(',',$oldMarks));
fclose($fp);
echo "$selCount,Sucesso ao";
if ($mark) {
	$mark = str_replace(',',', ',$mark);
	echo " marcar $mark";
	if ($unmark) {
		echo " e";
	}
}
if ($unmark) {
	$unmark = str_replace(',',', ',$unmark);
	echo " desmarcar $unmark";
}
echo ".";
?>
