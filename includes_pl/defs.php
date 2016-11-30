<?php
$plantasfullpath = getcwd();
$dev_userid = 72;  //isso porque tinha o id do Rodrigo em alguns scripts e substitui por essa variavel global
$pathtoplantas2 = '../duckewiki';
//$pathtoplantas = '../html/duckewiki'; // no Mac substituir pelo caminho completo ($plantasfullpath)
//$sessionsavepath = "$plantasfullpath/sessions";
// Mac ONLY:
$sessionsavepath = '/Library/WebServer/Documents/duckewiki2/www/duckewiki/sessions';
//$pathtoplantas = getcwd();
$pathtoplantas = '../Documents/duckewiki2/www/duckewiki';

#INPA ONLY
$proxy = 'proxy.inpa.gov.br:3128'; // como estava antes
//$proxy = 'tcp://proxy.inpa.gov.br:3128';
//$proxyauth = base64_encode('rodrigo.dias:9875321');
//$proxyauth = 'rodrigo.dias:9875321'; // como estava antes
$proxyauth = 'alberto.vicentini:ViC@702431'; // como estava antes

?>
