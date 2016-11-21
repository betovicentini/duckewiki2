<?php
ini_set("memory_limit","-1");
ini_set("mysql.allow_persistent","-1");
//include_once '../../includes_pl/db_connect.php';
//include_once '../../includes_pl/functions.php';
//require_once './model/am.php';
session_start();
//sec_session_start();
?>
<!DOCTYPE html>
<html lang='BR'>
	<head>
		<meta charset='UTF-8'>
		<title><?= $title ?> </title>
		<script src='funcoes.js'></script>
		<link rel="stylesheet" type="text/css" href="cssDuckeWiki.css">
		<style>
div.t {
    position: absolute;
    top: 50px;
    padding: 5px;
    background-color: lightgray;
    width: 30%;
    height: 500px; 
    overflow: scroll;
}

div.t2 {
    position: absolute;
    top: 50px;
    padding: 5px;
    background-color: white;
    margin-left: 32%;
    width: 65%;
    height: 500px; 
    overflow: scroll;
}

div.definicao {
    padding: 5px;
    background-color: lightorange;
    width: 95%;
}

div.relacionados {
    padding: 5px;
    background-color: lightblue;
    width: 95%;
}
 
div.functions {
    padding: 5px;
    background-color: lightyellow;
    width: 95%;
}

div.javascripts {
    padding: 5px;
    background-color: lightgreen;
    width: 95%;
}    
ul {
    list-style-type: none;
    /*background-color: yellow;*/
    padding: 0;
}
   
ul li:hover {
    background-color: yellow;
    cursor: pointer;
}
		</style>
		<script>
function getfilerefs(filename) {
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (xhttp.readyState == 4 && xhttp.status == 200) {
      document.getElementById("resultado").innerHTML = xhttp.responseText;
    }
  };
  var url = 'devtoolschange.php?file='+filename;
  xhttp.open("GET", url, true);
  xhttp.send();
}
function  enterdef(file) {
	var curtxt = document.getElementById("curdefinition").innerHTML;
	var newtxt = prompt("Edite a definição", curtxt);
	if (newtxt != '' && newtxt != null) { 
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
			if (xhttp.readyState == 4 && xhttp.status == 200) {
			  document.getElementById("curdefinition").innerHTML = xhttp.responseText;
			}
		};
	  var url = 'devtoolsdefin.php?file='+file+"&definition="+newtxt;
	  xhttp.open("GET", url, true);
	  xhttp.send();
	}
}

function getfunrefs(funname, isjava, arquivo) {
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (xhttp.readyState == 4 && xhttp.status == 200) {
      document.getElementById("resultado").innerHTML = xhttp.responseText;
    }
  };
  var url = 'devtoolsfuns.php?function='+funname+'&isjava='+isjava+'&arquivo='+arquivo;
  xhttp.open("GET", url, true);
  xhttp.send();

}

function pegaomodelo(funname, isfun, arquivo) {
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (xhttp.readyState == 4 && xhttp.status == 200) {
      document.getElementById("resultado").innerHTML = xhttp.responseText;
    }
  };
  var url = 'devtoolsmodels.php?function='+funname+'&isfun='+isfun+'&arquivo='+arquivo;
  xhttp.open("GET", url, true);
  xhttp.send();

}
function mostracodigo(funname) {
//alert(funname);
	var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (xhttp.readyState == 4 && xhttp.status == 200) {
      var otitulo = "Código do arquivo "+funname;
      var myWindow = window.open("", otitulo, "toolbar=yes,scrollbars=yes,resizable=yes,top=100,left=200,width=700,height=400,menubar=yes");
    myWindow.document.write(xhttp.responseText);
    }
  };
  var url = 'devtoolsmostracode.php?file='+funname;
  xhttp.open("GET", url, true);
  xhttp.send();
    
}

</script>
	</head>
<body>
<?php


$resultado = array();
$phpfiles = array();

//PEGA OS ARQUIVOS PHP NA PASTA ATUAL E EXTRAI AS INTER-REFERENCIAS
$directory = getcwd( );
$curfolder = array_diff(scandir($directory), array('..', '.','.htaccess','php.ini'));
$curfolder = array_values($curfolder);
$curfolder = preg_grep( "/.php/" , $curfolder);
asort($curfolder);
$curfolder = array_values($curfolder);
$nrefsbyfile = array_fill_keys($curfolder, 0);


//PEGA OS ARQUIVOS PHP NA PASTA includes_pl E EXTRAI AS INTER-REFERENCIAS
$pp = explode("/",$directory);
$np = count($pp)-1;
unset($pp[$np]);
$np = count($pp)-1;
unset($pp[$np]);
$pp = implode("/",$pp);
$dd = $pp."/includes_pl";
$arquivos = array_diff(scandir($dd), array('..', '.','.htaccess','php.ini'));
$arquivos = array_values($arquivos);
$arquivos = preg_grep( "/.php/" , $arquivos);
$arquivos = array_values($arquivos);
foreach($arquivos as $arq) {
	$aa = "../../includes_pl/".$arq;
	$curfolder[] = array($aa,$arq);
	$nrefsbyfile[$arq] = 0;
}

//for($i=0;$i<=1;$i++) {
//	$afile = $curfolder[$i];
//$nrefsbyfile = array_fill_keys($curfolder, 0);

foreach($curfolder as $afile) {
	if (is_array($afile)) {
		$arq = $afile[1];
		$afile = $afile[0];
	} else {$arq=$afile;}
	//$nrefsbyfile[$afile]  = 0;
	//$input = @file_get_contents($afile) or die("Could not access file: ".$afile);
	$linkedfiles = array();
	foreach($curfolder as $temfile) {
		if (is_array($temfile)) {
			$arq2 = $temfile[1];
			$temfile = $temfile[0];
		} else { $arq2 = $temfile;}
		if ($arq2<>$arq) {
			$oarq = fopen($afile, "r") or die("Cannot open file!\n"); 
			$idl =1;
			$quaislinhas = array();
			while ($linh = fgets($oarq, 1024)) { 
			    if (preg_match("/".$arq2."/i", $linh)) { 
			        $quaislinhas[] = $idl;
			    } 
			    $idl++;
			} 
			fclose($oarq); 
			//$tem = preg_match( "/".$arq2."/" , $input);
			$tem = count($quaislinhas);
			if ($tem>0) {
				//echo "Encontrei ".$arq2." no arquivo ".$arq."<br >";
				$lnn = implode(", ",$quaislinhas);
				$linkedfiles[] = array("file" => $temfile, "atlines" => $lnn);
				$nrefsbyfile[$arq2] = $nrefsbyfile[$arq2]+1;
				$nrefsbyfile[$arq] = $nrefsbyfile[$arq]+1;
			}
		}
	}
	if (count($linkedfiles)>0) {
		$resultado[$afile]['referedFiles'] = $linkedfiles;
	}
}

//PEGA ARQUIVOS COM FUNCOES E PROCURA EM QUAIS ARQUIVOS ELAS ESTAO PRESENTES
$phpFUNfiles = array("../../includes_pl/functions.php");
$functionsinfiles = array();
$thefuns = array();
$thejavas = array();
foreach($phpFUNfiles as $funfile) {
	$handle = fopen($funfile, "r");
	$funnames = array();
	if ($handle) {
		$idl = 1;
		while (($line = fgets($handle)) !== false) {
				$nn = explode(" ",$line);
				$nn = array_map("trim",$nn);
				if (strtolower($nn[0])=='function') {
					$nn2 = $nn;
		        	unset($nn2[0]);
		        	$nn2 = array_values($nn2);
		        	$nn2 = implode("",$nn2);
		        	$nn3 = explode("(",$nn2);
		        	$fname = $nn3[0];
		        	$funnames[] = $fname;
		        	$thefuns[$funfile][$fname]['atlines'] = $idl;
				}
				$idl++;
		}
		fclose($handle);
	}
	asort($funnames);
	$functionsinfiles[$funfile] = $funnames;
	//ONDE AS FUNCOES ESTAO SENDO USADAS
	foreach($curfolder as $onde) {
		if (is_array($onde)) {
			$onde = $onde[0];
		}
		//$input = @file_get_contents($onde) or die("Could not access file: ".$onde);
		$usedfunctions = array();
		foreach($funnames as $fun) {
				$pattern = $fun."\\(";
				$handle = fopen($onde, "r");
				if ($handle) {
					$idl = 1;
					$quaislinhas = array();
					while (($line = fgets($handle)) !== false) {
					    if (preg_match("/".$pattern."/i", $line)) { 
					    	$quaislinhas[] = $idl;
					    }
					    $idl++;
				    } 
				} 
				fclose($handle); 
				$nl = count($quaislinhas);
				if ($nl>0) {
					//found function fun in onde
					$naslinhas = implode(", ",$quaislinhas);
					$usedfunctions[] = array("function" => $fun, "atlines" => $naslinhas);
					$oldar = @$thefuns[$funfile][$fun]['usedby'];
					if (is_array($oldar)) {
						$oldar[] = $onde;
						//, "atlines" => $naslinhas);
					} else {
						$oldar = array($onde);
						//, "atlines" => $naslinhas);
					}
					$thefuns[$funfile][$fun]['usedby'] = $oldar;
				}
		}
		//echo "File: ".$onde."<br >";
		if (count($usedfunctions)>0) {
			$resultado[$onde]['functions'][$funfile] = $usedfunctions;
		}
	}
}


//PEGA JAVASCRIPTS
//PEGA ARQUIVOS COM FUNCOES E PROCURA EM QUAIS ARQUIVOS ELAS ESTAO PRESENTES
$dd = getcwd( );
$directory = $dd."/js/";
$arquivos = array_diff(scandir($directory), array('..', '.','.htaccess','php.ini'));
$thecurfolder = $curfolder;
foreach($arquivos as $aa) {
	$thecurfolder[] = array("js/".$aa,$aa);
}

$jsfunctionsinfiles = array();
$thejavas = array();
foreach($arquivos as $jsfile) {
	$resfiles = array_diff($arquivos,array($jsfile));
	$thefile = $directory.$jsfile;
	$handle = fopen($thefile, "r");
	$funnames = array();
	if ($handle) {
		$idl=1;
		while (($line = fgets($handle)) !== false) {
				$nn = explode(" ",$line);
				$nn = array_map("trim",$nn);
				if (strtolower($nn[0])=='function') {
					$nn2 = $nn;
		        	unset($nn2[0]);
		        	$nn2 = array_values($nn2);
		        	$nn2 = implode("",$nn2);
		        	$nn3 = explode("(",$nn2);
		        	$fname = $nn3[0];
		        	$funnames[] = $fname;
		        	$thejavas[$jsfile][$fname]['atlines'] = $idl;
				}
				$idl++;
		}
		fclose($handle);
	}
	asort($funnames);
	$jsfunctionsinfiles[$jsfile] = $funnames;
	foreach($thecurfolder as $onde) {
		if (is_array($onde)) {
			$onde = $onde[0];
			$fileonde = $onde[1];
		} else {
			$fileonde = $onde;
		}
		//if ($fileonde!=$jsfile) {
		$input = file_get_contents($onde) or die("Could not access file: ".$onde);
		//check if the file lists the jsfile
		$hasfile = preg_match("/".$jsfile."/", $input);
		if ($hasfile) {
			$usedfunctions = array();
			foreach($funnames as $fun) {
				$pattern = $fun."\\(";
				$handle = fopen($onde, "r");
				if ($handle) {
					$idl = 1;
					$quaislinhas = array();
					while (($line = fgets($handle)) !== false) {
					    if (preg_match("/".$pattern."/i", $line)) { 
					    	$quaislinhas[] = $idl;
					    }
					    $idl++;
				    } 
				} 
				fclose($handle); 
				$nl = count($quaislinhas);
				if ($nl>0) {
				//if (preg_match("/".$pattern."/", $input)) {
					//found function fun in onde
					$naslinhas = implode(", ",$quaislinhas);
					$usedfunctions[] = array("function" => $fun, "atlines" => $naslinhas);
					//$usedfunctions[] = $fun;
					$oldar = @$thejavas[$jsfile][$fun]['usedby'];
					if (is_array($oldar)) {
						$oldar[] = $onde;
					} else {
						$oldar = array($onde);
					}
					$thejavas[$jsfile][$fun]['usedby'] = $oldar;
				}
			}
			//echo "File: ".$onde."<br >";
			if (count($usedfunctions)>0) {
				$resultado[$onde]['javascripts'][$jsfile] = $usedfunctions;
			}
		}
	}
	//}
}

//pega todos os modelos
$dd = getcwd( );
$directory = $dd."/model/";
$arquivos = array_diff(scandir($directory), array('..', '.','.htaccess','php.ini'));
$arquivos = array_values($arquivos);
$arquivos = preg_grep( "/.php/" , $arquivos);
$osmodelos = array_values($arquivos);
$modelosclasses = array();
$modelosfunctions = array();
$themodels = array();
foreach($osmodelos as $omodelo) {
	$thefile = $directory.$omodelo;
	$handle = fopen($thefile, "r");
	$classes = array();
	$funcoes = array();
	if ($handle) {
		while (($line = fgets($handle)) !== false) {
				//$arquivos = preg_grep( "/.php/" , $arquivos);
				$line = trim($line);
				$nn = explode(" ",$line);
				$nn = array_map("trim",$nn);
				$ntt = $nn[0]." ".@$nn[1];
				if (strtolower($nn[0])=='class' || $ntt=='public class') {
				    $ll = str_replace("public","",$line);
				    $ll = str_replace("class","",$ll);
				    $ll = str_replace("{","",$ll);
		        	$clname = $ll;
		        	$classes[] = $clname;
		        	//$themodels['class'][$omodelo][$clname]['where'] = $omodelo;
				} else {
					if (strtolower($nn[0])=='function' || $ntt=='public function') {
						$ll = str_replace("public","",$line);
						$ll = str_replace("function","",$ll);
						$ll = explode("(",$ll);
						//$ll = array_map("trim",$ll);
						$clname = $ll[0];
						$funcoes[] = $clname;
						//$themodels['functions'][$clname]['where'] = $omodelo;
					}
				}
		}
		fclose($handle);
	}
	$modelosclasses[$omodelo] = $classes;
	$modelosfunctions[$omodelo] = $funcoes;
	foreach($curfolder as $onde) {
		if (is_array($onde)) {
			$onde = $onde[0];
		}
		$input = @file_get_contents($onde) or die("Could not access file: ".$onde);
		//check if the file lists the jsfile
		$hasfile = preg_match("/".$omodelo."/", $input);
		if ($hasfile) {
			$usedfunctions = array();
			foreach($funcoes as $fun) {
				$pattern = $fun."\\(";
				if (preg_match("/".$pattern."/", $input)) {
					//found function fun in onde
					$usedfunctions[] = $fun;
					$oldar = @$themodels['functions'][$omodelo][$fun]['usedby'];
					if (is_array($oldar)) {
						$oldar[] = $onde;
					} else {
						$oldar = array($onde);
					}
					$themodels['functions'][$omodelo][$fun]['usedby'] = $oldar;
				}
			}
			//echo "File: ".$onde."<br >";
			if (count($usedfunctions)>0) {
				$resultado[$onde]['models'][$omodelo]['functions'] = $usedfunctions;
			}
			$usedfunctions = array();
			foreach($classes as $fun) {
				$pattern = $fun."\\(";
				if (preg_match("/".$pattern."/", $input)) {
					//found function fun in onde
					$usedfunctions[] = $fun;
					$oldar = @$themodels['class'][$omodelo][$fun]['usedby'];
					if (is_array($oldar)) {
						$oldar[] = $onde;
					} else {
						$oldar = array($onde);
					}
					$themodels['class'][$omodelo][$fun]['usedby'] = $oldar;
				}
			}
			//echo "File: ".$onde."<br >";
			if (count($usedfunctions)>0) {
				$resultado[$onde]['models'][$omodelo]['class'] = $usedfunctions;
			}
		}
	}
}

//echo "<pre>";
//print_r($modelosfunctions);
//echo "</pre>";
//$directory = $dd."/js/";
$resultado['thefunctions'] = $thefuns;
$resultado['thejavas'] = $thejavas;
$resultado['themodels'] = $themodels;

//print_r($thefuns);
$dd = getcwd( );
//if (!file_exists("help")) {
//mkdir("help",0644,true);
//}
$arq ="help/file_relations.json";
file_put_contents($arq, json_encode($resultado,TRUE));

echo"
<div class='t' >
<h3>Arquivos PHP</h3>
<div style='background-color: lightblue;'>
<ul >";
foreach($curfolder as $file) {
	if (is_array($file)) {
			$fch = $file[1];
			$file = $file[0];
	} else {
		$fch = $file;
	}
	$temalgo = $nrefsbyfile[$fch];
	if ($temalgo==0) { $cor = "color: red;";} else { $cor = "color: black;"; }
echo "
<li style='cursor: pointer; $cor' onclick=\"javascript: getfilerefs('".$file."');\">".$file."</li>";
}
echo "
</ul>
</div>
<hr>
<h3>Funções PHP</h3>
<div style='background-color: lightyellow;'>
<ul>";
foreach($functionsinfiles as $kk => $asfuns) {
	$fnn = "<small>File: ".$kk."</small>";
	foreach($asfuns as $fun) {
		$isused = count($thefuns[$kk][$fun]['usedby']);
		if ($temalgo==0) { $cor = "color: red;";} else { $cor = "color: black;"; }
		echo "<li style='cursor: pointer; $cor' onclick=\"javascript: getfunrefs('".$fun."', 0 , '".$kk."');\">".$fun." [".$fnn."]</li>";
	}
}
echo "
</ul>";
echo "
</div>
<hr>
<h3>Funções Javascript</h3>
<div style='background-color: lightgreen;'>
<ul>";
foreach($jsfunctionsinfiles as $kk => $asfuns) {
	$fnn = "<small>File: ".$kk."</small>";
	foreach($asfuns as $fun) {
	$isused = @$thejavas[$kk][$fun]['usedby'];
	if (!$isused) { $cor = "color: red;";} else { $cor = "color: black;"; }
		echo "<li style='cursor: pointer;$cor' onclick=\"javascript: getfunrefs('".$fun."', 1, '".$kk."');\" >".$fun." [".$fnn."]</li>";
	}
}
echo "
</ul>";
echo "
</div>
<hr>
<h3>Modelos PHP</h3>
<div style='background-color: lightyellow;'>";
echo "<ul>";
foreach($modelosclasses as $kk => $asfuns) {
	$fnn = "<small>File: ".$kk."</small>";
foreach($asfuns as $fun) {
	$isused = @$themodels['class'][$kk][$fun]['usedby'];
	if (!$isused) { $cor = "color: red;";} else { $cor = "color: black;"; }
	echo "<li style='cursor: pointer; $cor ' onclick=\"javascript: pegaomodelo('".$fun."', 0, '".$kk."');\" >".$fun." [".$fnn."]</li>";
}
}
echo "</ul>";
echo "
</div>
<hr>
<h3>Funções de Modelos PHP</h3>
<div style='background-color: lightgreen;'>";
foreach($modelosfunctions as $kk => $asfuns) {
	$fnn = "<small>File: ".$kk."</small>";
echo "<ul>";
foreach($asfuns as $fun) {
	$isused = @$themodels['functions'][$kk][$fun]['usedby'];
	if (!$isused) { $cor = "color: red;";} else { $cor = "color: black;"; }
	echo "<li style='cursor: pointer; $cor' onclick=\"javascript: pegaomodelo('".$fun."', 1,'".$kk."');\" >".$fun." [".$fnn."]</li>";
}
echo "</ul>";
}
echo "
</div>
</div>
<div class='t2' id='resultado'>

</div>
";
//echo "<br><div ><pre>";print_r($modelosfunctions); echo "</pre></div>";


?>
</body>
</html>

