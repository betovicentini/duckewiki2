<?php
$file = $_GET['file'];
$linenum = $_GET['linenum'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <title>Editor</title>
  <style type="text/css" media="screen">
body {
    overflow: hidden;
    margin: 0;
    padding: 0;
    height: 100%;
    width: 100%;
    font-family: Arial, Helvetica, sans-serif, Tahoma, Verdana, sans-serif;
    font-size: 12px;
    background: rgb(14, 98, 165);
    color: white;
}
	#editor-container {
    position: absolute;
    top:  0px;
    left: 240px;
    bottom: 0px;
    right: 0px;
}

    
  </style>
<script type="text/javascript" charset="utf-8">




</script>
</head>
<body>
<div style="position:absolute;height:100%;width:240px">
<div ><br />
<?php echo "<input type='hidden' value='".$_GET['file']."' id='fname'>"; 
echo "&nbsp;&nbsp;&nbsp;&nbsp;<span style='cursor: pointer; font-size: 1.5em; font-weight: bold;' onclick=\"javascript: getbackup('".$_GET['file']."');\">".$_GET['file']."</span>"; ?></div>
</br>
<button id="save">Salvar</button>
</br>
<?php
	$fn = explode("/",$_GET['file']);
	$nf = count($fn)-1;
	$fn = $fn[$nf];
	
	$offn = explode(".",$fn);
	$noffn = count($offn)-1;
	//print_r($offn[$noffn]);
	if (strtoupper($offn[$noffn])=='JS') {
		$otipo ='javascript';
	} else {
		if (strtoupper($offn[$noffn])==='PHP') {
			$otipo ='php';
		} else { 
			$otipo = 'text';
		}
	}
	echo "<input type='hidden'  id='otipo' value='".$otipo."' >";
	//@mkdir("dev_logs/".$fn, 0755);
	$fndir = "dev_logs/".$fn;
	if (file_exists($fndir)) {
	$chdir = array_diff(scandir($fndir), array('..', '.','.htaccess','php.ini'));
	$chdir = array_values($chdir);
	rsort($chdir);
	if (count($chdir)>0) {
		echo "<h3>Modificações</h3><div><ul >";
		foreach($chdir as $arq) {
			$aa = $fndir."/".$arq;
			echo "
<li style='cursor: pointer;' onclick=\"javascript: getbackup('".$aa."');\">".$arq."</li>";
		
		}
		echo "</ul></div>";
		}
	}
?>
</div>
<br >
<div id="editor-container"><?php echo htmlspecialchars(file_get_contents($_GET['file'])); ?></div>
<script src="ace/src-min/ace.js" type="text/javascript" charset="utf-8"></script>
<script>
function getHTTPObject() {
	var http = false;
	//Use IE's ActiveX items to load the file.
	if(typeof ActiveXObject != 'undefined') {
		try {http = new ActiveXObject("Msxml2.XMLHTTP");}
		catch (e) {
			try {http = new ActiveXObject("Microsoft.XMLHTTP");}
			catch (E) {http = false;}
		}
	//If ActiveX is not available, use the XMLHttpRequest of Firefox/Mozilla etc. to load the document.
	} else if (XMLHttpRequest) {
		try {http = new XMLHttpRequest();}
		catch (e) {http = false;}
	}
	return http;
}

var saveButton = document.getElementById("save")
var editor = ace.edit("editor-container");
editor.setTheme("ace/theme/clouds");
var tipo = document.getElementById('otipo').value;
editor.session.setMode("ace/mode/"+tipo);
editor.getSession().setUseWrapMode(true);
editor.navigateTo(<?php echo $linenum+10; ?>, 0);
editor.moveCursorTo(<?php echo $linenum; ?>, 0);
editor.focus();
//editor.setHighlightActiveLine(true);
editor.on("input", function() {
	saveButton.disabled = editor.session.getUndoManager().isClean();
});
editor.on("input", function() {
	saveButton.disabled = false;
});


saveButton.addEventListener("click", function() {
			var fn = document.getElementById("fname").value;
			//var sessionData = sessionToJSON(editor.session);
			var conteudo = encodeURIComponent(editor.session.getValue());
			//var blob = new Blob([conteudo], {type: 'text/plain'});
			var http = getHTTPObject();
			var url = 'devtoolssalvacode.php';
			var params = 'file='+fn+'&conteudo='+conteudo;
			//alert(params);
			http.open("POST", url, true);
	
			//Send the proper header infomation along with the request
			http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			http.setRequestHeader("Content-length", params.length*3);
			http.setRequestHeader("Connection", "close");
			function handler() {
				if(http.readyState == 4 && http.status == 200) {
					alert(http.responseText);
					//editor.session.getUndoManager().markClean();
					saveButton.disabled = true;
				}
			}
			http.onreadystatechange = handler;
			http.send(params);
});
function getbackup(ofn) {
			var xhttp = getHTTPObject();
			var url = 'devtoolsgetback.php';
			var params = 'file='+ofn;
			xhttp.open("POST", url, true);
	
			//Send the proper header infomation along with the request
			xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp.setRequestHeader("Content-length", params.length*3);
			xhttp.setRequestHeader("Connection", "close");
			function nhandler() {
				if(xhttp.readyState == 4 && xhttp.status == 200) {
					var conteudo = decodeURIComponent(xhttp.responseText);
					//alert(conteudo);
					editor.session.setValue(conteudo);
					saveButton.disabled = false;
				}
			}
			xhttp.onreadystatechange = nhandler;
			xhttp.send(params);
}

</script>

</body>
</html>
