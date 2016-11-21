<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php'; // inclui definições de proxy
sec_session_start();

if (isset($_GET['nome'])) {
	//$proxy = 'proxy.inpa.gov.br:3128'; // TIRAR SE ESTIVER FORA DO INPA!!!
	//$proxyauth = 'rodrigo.dias:9875321'; // TIRAR SE ESTIVER FORA DO INPA!!!
	$nome = str_replace(' ','+',$_GET['nome']);
	$url = "http://services.tropicos.org/Name/Search?name=$nome&type=exact&apikey=b2c88eaf-66a8-42e0-9931-8c619f4eac1d&format=xml";
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_PROXY, $proxy); // TIRAR SE ESTIVER FORA DO INPA!!!
	curl_setopt($curl, CURLOPT_PROXYUSERPWD, $proxyauth); // TIRAR SE ESTIVER FORA DO INPA!!!
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$curl_response = curl_exec($curl);
	if (!$curl_response) {
		die('Erro: "' . curl_error($curl) . '" - Código: ' . curl_errno($curl));
	} else {
		$xml = new SimpleXMLElement($curl_response);
		if ($xml->Name->Error != '') {
			echo "Erro: nome [$nome] não encontrado.";
		} else {
			echo $xml->Name->NameId;
		}
	}
}
?>
