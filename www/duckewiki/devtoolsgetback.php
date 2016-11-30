<?php
//PEGA O SCRIPT EDITADO
$afile = rawurldecode($_POST['file']);
echo rawurlencode(file_get_contents($afile)); 
?>
