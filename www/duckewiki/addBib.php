<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();
/*
 * To-do list:
 * - padronizar toda a nomenclatura de controles (id/name):
 *  - código minúsculo de 3 letras (btn=button, sel=select...)
 *  - colocar o id[nome] dos controles no alto da página
 *  - nome dos controles poderia ser o mesmo nome da coluna...? e não usar id?
 * - não consigo editar a URL na janela pop-up! Bom. Se fora do modo debug, nem mostrar a barra de URL
 * - Editora do livro?
 *
 */
$edit = getGet('edit');
if ($edit == '')
	$title = txt('nova').' '.txt('bibref');
else
	$title = txt('edit').' '.txt('bibref');
?>

<!DOCTYPE html>
<html lang='BR'>
	<head>
		<meta charset='UTF-8'>
		<title> <?php echo $title ?> </title>
		<link rel="stylesheet" type="text/css" href="css/cssDuckeWiki.css">
		<script src='js/funcoes.js'></script>
<script>
function bibtexUpd() {
	var b = document.getElementsByName('txabibtex')[0].value;
	var p1, p2;
	p1 = b.indexOf('@');
	if (p1 >= 0) {
		p2 = b.indexOf('{');
		var tipo = b.substr(p1+1,p2-p1-1); // 'article'
		p1 = b.indexOf(',',p2);
		var key0 = b.substr(p2+1,p1-p2-1); // 'de Oliveira29012014'
		b = b.substr(p1+1); // remove os dados anteriores
		var key, val, keys = [], vals = [];
		do {
			b = b.trim(); // remove espaços em branco no início (e fim) de b
			p1 = b.indexOf('=');
			key = b.substr(0,p1).trim();
			p1 = b.indexOf('{',p1);
			p2 = b.indexOf('}',p1);
			val = b.substr(p1+1,p2-p1-1).trim();
			keys.push(key.toLowerCase());
			vals.push(val);
			b = b.substr(p2+2).trim();
		} while (b != '}' && key != '');
		if (keys.length > 0) {
			var selTipo = document.getElementsByName('seltipo')[0];
			switch (tipo.toLowerCase()) {
				case 'book' : selTipo.selectedIndex = 2; break;
				case 'article' : selTipo.selectedIndex = 1; break;
			}
			// bibkey
			var txtbibkey = document.getElementsByName('txtbibkey')[0];
			txtbibkey.value = key0;
			// author
			var txtAutor = document.getElementsByName('txtAutor')[0];
			var autor = vals[keys.indexOf('author')];
			p1 = autor.indexOf(' and ');
			if (p1 >= 0) {
				txtAutor.value = autor.substr(0,p1);
				var txtAutores = document.getElementsByName('txtAutores')[0];
				txtAutores.value = autor.substr(p1+5);
			} else {
				txtAutor.value = autor;
			}
			// year
			var txtAno = document.getElementsByName('txtAno')[0];
			txtAno.value = vals[keys.indexOf('year')];
			// title
			var txtTitulo = document.getElementsByName('txtTitulo')[0];
			txtTitulo.value = vals[keys.indexOf('title')];
			// journal
			var txtJournal = document.getElementsByName('txtJournal')[0];
			txtJournal.value = vals[keys.indexOf('journal')];
		}
	}
}
function fechaLogo(id,who,texto) {
	alert(id+','+who+','+texto);
	window.opener.handlePopupResult(id,who,texto);
	clearLocalStore('frmBib');
    window.close();
}
function aoCarregar(edit) {
	if (edit == '') {
		refill('frmBib');
	}
	document.getElementsByName("txabibtex")[0].focus();
}
</script>
<?php
$tabela = 'bib';
$update = getGet('update');
$close = getGet('close');
$h1 = "<h1 style='text-align:center'>$title</h1>";
if ($edit == '') {
	emptyRow($tabela);
} else {
	updateRow($tabela,$edit);
}
$body = "<body onload='aoCarregar(\"$edit\")'>";
$divRes = '';
if (!empty($post)) {
	$v1 = $_SESSION['user_id'];
	$v2 = date('d/m/Y H:i:s'); // hoje
	$v3 = getPost('txtbibkey');
	$v4 = getPost('seltipo');
	$v5 = getPost('txtAno');
	$v6 = getPost('txtAutor');
	$v7 = getPost('txtAutores');
	$v8 = getPost('txtJournal');
	$v9 = getPost('txtTitulo');
	$v10 = getPost('txtPgs');
	$v11 = getPost('txtVol');
	$v12 = getPost('txabibtex');
	$arrPar = [];
	for ($i=1; $i<=12; $i++) {
		$arrPar[] = ${"v$i"};
	}
	//$arrPar = array($v1,$v2,$v3,$v4,$v5,$v6,$v7,$v8,$v9,$v10,$v11,$v12);
	$cols = 'addby,adddate,bibkey,tipo,ano,autor,autores,journal,title,pgs,vol,bibrec'; // deve ir numa linha só, sem espaços
	$arrCol = explode(',',$cols);
	if ($edit) {
		$q = "update $tabela set (";
		if (algumaMudou($q)) { // pelo menos uma coluna mudou -> edita
			montaQuery($q);
			$res = pg_query_params($conn,$q,[$edit]);
			if ($res) {
				$divRes = "<div id='divSucesso' style='background-color:#00FF00'>Registro atualizado com sucesso! ($q)</div>";
				updateRow($tabela,$edit);
				if ($close) {
					$body = "<body onload='fechaLogo($edit)'>";
				}
			} else {
				pg_send_query_params($conn,$q,[$edit]);
				$res = pg_get_result($conn);
				$resErr = pg_result_error($res);
				$divRes = "<div id='divSucesso' style='background-color:#FF0000'>Erro ao atualizar registro ($q): $resErr</div>";
			}
		} else {
			$divRes = "<div id='divSucesso' style='background-color:#FF0000'>Não há mudanças a atualizar!</div>";
		}
	} else { // não está editando -> insere
		switch (registroExiste($tabela)) {
			case 'f' :
				echo "Não existe<BR><BR>";
				insereUm($tabela,$close,$divRes,$body,$v9);
				break;
			case 't' :
				$divRes = "<div id='divSucesso' style='background-color:#FF0000'>Registro já existe.</div>";
				break;
			default :
				pg_send_query_params($conn,$q,$arrPar);
				$res = pg_get_result($conn);
				$resErr = pg_result_error($res);
				$divRes = "<div id='divSucesso' style='background-color:#FF0000'>Erro na query: $resErr</div>";
		}
	} // fim do insert
}
pullCfg();
echo "</head>
$body";
$loginErro = login_error($conn);
if ($loginErro) {
	exit("<p>Você não tem autorização de acesso a esta página (erro: $loginErro). Por favor, <a href='index.php' target='main'>volte para a tela inicial e faça o login</a>.</p>");
}
echoHeader();
?>

<dl>
<dt>
	<label>BibTex</label>
</dt>
<dd>
	<textarea name='txabibtex' onblur='bibtexUpd()'></textarea>
</dd>

<dt>
	<label>BibKey</label>
</dt>
<dd>
	<input type='text' name='txtbibkey' oninput='store(this)' />
</dd>

<!--dt>
	<label>
		<span style='color:red'>*</span>
	</label>
</dt-->
<?= dtlab('tipo.bib'); ?>
<dd>
	<select required name='seltipo' onchange='store(this)'>
	<option value=''>
	</option>"
	<?php
		$q = "select * from bibtipo order by tipo";
		$res = pg_query($conn,$q);
		if ($res) {
			while ($row = pg_fetch_array($res,NULL,PGSQL_NUM)) {
				if ($row[0] == $tipo) {
					echo "<option value=$row[0] selected>$row[1]</option>\n";
				} else {
					echo "<option value=$row[0]>$row[1]</option>\n";
				}
			}
			echo "</select>";
		} else {
			echo "Erro na query: $q<BR>";
		}
	?>
</dd>

<?= dtlab('bib.aut1'); ?>
<dd>
	<input required pattern='\D+' name='txtAutor' type='text' value= <?= "'$autor'" ?> onkeyup='requiredKeyUp()' oninput='store(this)' />
</dd>

<?= dtlab('bib.aut2'); ?>
<dd>
	<input name='txtAutores' type='text' value= <?= "'$autores'" ?> oninput='store(this)' />
</dd>

<?= dtlab('ano'); ?>
<dd>
	<input required pattern='\d+' name='txtAno' type='text' value= <?= "'$ano'" ?> onkeyup='requiredKeyUp()' oninput='store(this)' />
</dd>

 <?= dtlab('bib.tit'); ?>
<dd>
	<input required name='txtTitulo' type='text' value= <?= "'$title'" ?> onkeyup='requiredKeyUp()' oninput='store(this)' />
</dd>

<?= dtlab('bib.per'); ?>
<dd>
	<input name='txtJournal' type='text' value= <?= "'$journal'" ?> oninput='store(this)' />
</dd>

<?= dtlab('bib.pgs'); ?>
<dd>
	<input name='txtPgs' type='text' value= <?= "'$pgs'" ?> oninput='store(this)' />
</dd>

<dt>
	<label>
		<?= txt('bib.vol'); ?>/<?= txt('bib.ed'); ?>
	</label>
</dt>
<dd>
	<input name='txtVol' type='text' value= <?= "'$vol'"?> oninput='store(this)' />
</dd>
</dl>

<?php
echoButtons();
?>
</form>
</body>
</html>
