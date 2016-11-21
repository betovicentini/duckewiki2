<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();
if (isset($_GET['mesomicro'])) {
	$mesomicro = $_GET['mesomicro'];
}
/*
 * Incluir também os pontos só com lat,lon
 */
?>
<!DOCTYPE html>
<html>
<head>
<title>Mapa</title>
<meta name='viewport' content='initial-scale=1.0'>
<meta charset='utf-8'>
<style>
html, body {
	width: 100%;
	height: 100%;
	margin: 0;
	padding: 0;
}
</style>
<script>
var pts = [], ptsBackup;
var esps = {};
var pls = {};
var markers = [];
var infoWindow, infoWindows = {};
var details = {};
function openDivs(who) { // pula mesomicro (mas pula outros também ?? )
	var i, j, k;
	var box = document.getElementById('box'+who);
	box.style.display = 'block';
	var boxF = box.children;
	var boxFF, boxFFF;
	for (i=0; i<boxF.length; i++) {
		if (boxF[i].tagName.toLowerCase() == 'div') {
			boxF[i].style.display = 'block';
			boxFF = boxF[i].children;
			if (boxFF.length > 0) {
				if (boxFF[0].tagName.toLowerCase() == 'div') {
					boxFF[0].style.display = 'block';
					boxFF = boxFF[0].children;
					for (j=0; j<boxFF.length; j++) {
						if (boxFF[j].tagName.toLowerCase() == 'div') {
							boxFF[j].style.display = 'block';
							boxFFF = boxFF[j].children;
							for (k=0; k<boxFFF.length; k++) {
								if (boxFFF[k].tagName.toLowerCase() == 'div') {
									boxFFF[k].style.display = 'block';
								}
							}
						}
					}
				}
			}
		}
	}
}
function mostra(who) {
	if (who == 0) { // se clicar no *
		var i, divs = document.getElementById('txt').getElementsByTagName('div');
		// se o primeiro divbox está aberto...
		if (divs[2].style.display == 'block') {
			for (i=2; i<divs.length; i++) {
				if (divs[i].id.substr(0,3) == 'box') { // ... fecha todos
					divs[i].style.display = 'none';
				}
			}
		} else {
			for (i=2; i<divs.length; i++) {
				if (divs[i].id.substr(0,3) == 'box') { // ... senão abre todos
					divs[i].style.display = 'block';
				}
			}
		}
	} else { // se não clicou no * (inverte só aquele)
		var div = document.getElementById('box'+who);
		if (div.style.display == 'none') {
			openDivs(who);
		} else {
			div.style.display = 'none';
		}
	}
}
/** retorna o path de um local */
function getLocPath(loc) {
	var i;
	for (i=0; i<pts.length; i++) {
		if (pts[i].id == loc) {
			return pts[i].path;
		}
	}
	return '';
}
/** pega o marker para um local. Se aquele local não tiver, pega de um dos ancestrais do local */
function getLocMarker(loc) {
	var i, marker = null;
	while (marker == null && loc > 0) {
		for (i=0; i<markers.length; i++) { // procura um marcador para o local atual (loc)
			if (markers[i].infoWindowIndex == loc) {
				marker = markers[i];
				break;
			}
		}
		if (marker == null) { // se não encontrou um marcador...
			for (i=0; i<pts.length; i++) { // substitui loc pelo pai de loc [PEGANDO APENAS O PRIMEIRO PAI !! ]
				if (pts[i].id == loc) {
					loc = pts[i].pai;
					break;
				}
			}
		}
	}
	return marker;
}
/** mostra no mapa as informações de um especímene ou planta */
function clickEspPl(who,what,loc) {
  var i, marker, content, detail;
  marker = getLocMarker(loc);
  if (typeof infoWindows[marker.infoWindowIndex] !== 'undefined') {
		//detail = details[what+who.innerHTML];
		detail = details[what+who.title];
		var fam = detail.fam;
		if (fam != '') {
			fam = fam.toUpperCase()+'<BR>';
		}
		var tax = detail.tax;
		if (tax != '') {
			tax = '<i>'+tax+'</i>';
		}
		if (detail.aut != '') {
			tax = tax+' '+detail.aut;
		}
		if (fam != '' || tax != '') {
			tax = tax+'<hr>';
		}
		var col = '';
		if (detail.abrev != '' || detail.num != '') {
			col = '<strong>'+detail.abrev+' # '+detail.num+'</strong><BR>';
		}
		var cols = detail.cols;
		if (cols != '') {
			cols = '&nbsp;& ' + cols + '<BR>';
		}
		var data = detail.ano+detail.mes+detail.dia;
		if (data != '') {
			data = '<strong>Coletada em '+data+'</strong>';
		}
		if (col != '' || cols != '' || data != '') {
			data = data+'<hr>';
		}
		var local = getLocPath(detail.loc);
		if (local != '') {
			local = local+'<hr>';
		}
		var hab = detail.hab;
		if (hab != '') {
			hab = hab+'<hr>';
		}
		var notas = detail.notas;
		if (notas != '') {
			notas = '<strong>Notas:</strong><BR>'+notas+'<hr>';
		}
		// primeiro muda o conteúdo
		content = '<div>'+fam+tax+col+cols+data+local+hab+notas+detail.prj+'</div>';
		infoWindows[marker.infoWindowIndex].setContent(content);
		// depois mostra
		infoWindows[marker.infoWindowIndex].open(map,marker);
	}
}
/** função recursiva para tentar pegar lat;lon de um ponto ancestral */
function llPais(pai) {
	var i;
	for (i=0; i<ptsBackup.length; i++) {
		if (ptsBackup[i].id == pai) {
			if (ptsBackup[i].lat != null && ptsBackup[i].lon != null) {
				return ptsBackup[i].lat+';'+ptsBackup[i].lon;
			} else {
				if (ptsBackup[i].pai > 0) {
					return llPais(ptsBackup[i].pai);
				} else {
					return 'null;null';
				}
			}
		}
	}
	return 'null;null';
}
/** executada após terminar de carregar a página (<body onload>) */
function aoCarregar() {
	var i, j, lista, divs, div, indent='10px', latlon, pos, label;
	var divTxt = document.getElementById('txt');
	var boxPai,pl;
	if (typeof loadPts === 'function') {
		loadPts();
		div = document.createElement('div');
		div.id = 'divAll';
		div.innerHTML = "<a href='javascript:mostra(0)'>*</a>";
		divTxt.appendChild(div);
	}
	var mesomicro;
	var printMesoMicro = <?php echo $mesomicro; ?>;
	var pais = [0], pais1;
	ptsBackup = pts.slice();
	do {
		pais1 = [];
		for (i=0; i<pts.length; i++) {
			if (pais.indexOf(pts[i].pai) >= 0) { // coloca só os filhos de pais
				pais1.push(pts[i].id);
				mesomicro = [6,7].indexOf(pts[i].tid) >= 0;
				// cria um div
				div = document.createElement('div');
				div.id = 'div'+pts[i].id+'_'+pts[i].pai;
				// tenta puxar lat,lon dos pais, se não tiver (completando o que já começou no php)
				if (pts[i].lat == null || pts[i].lon == null) {
					latlon = llPais(pts[i].pai);
					pos = latlon.indexOf(';');
					pts[i].lat = latlon.substr(0,pos);
					pts[i].lon = latlon.substr(pos+1);
				}
				// título do div (primeira linha do innerHTML)
				if (mesomicro) {
					if (printMesoMicro > 0) {
						//div.innerHTML = pts[i].nome+' ('+pts[i].id+')';
						//div.innerHTML = pts[i].nome;
						div.innerHTML = "<a href='javascript:mostra(\""+pts[i].id+'_'+pts[i].pai+"\")'>"+pts[i].nome+"</a>";
					}
				} else {
					//div.innerHTML = "<a href='javascript:mostra(\""+pts[i].id+'_'+pts[i].pai+"\")'>"+pts[i].nome+" ("+pts[i].id+'_'+pts[i].pai+") "+pts[i].lat+"|"+pts[i].lon+"</a>"; // com o nome do local
					div.innerHTML = "<a href='javascript:mostra(\""+pts[i].id+'_'+pts[i].pai+"\")'>"+pts[i].nome+"</a>"; // com o nome do local
				}
				// cria uma caixa (pra ficar dentro do div)
				var divBox = document.createElement('div');
				divBox.id = 'box'+pts[i].id+'_'+pts[i].pai;
				// ESPECÍMENES
				if (pts[i].id in esps) {
					// cria a lista dos esp daquele loc
					lista = esps[pts[i].id].split('|');
					var t = '';
					for (j=0; j<lista.length; j++) {
						if (details['esp'+lista[j]].abrev != '' && details['esp'+lista[j]].num != '') {
							label = details['esp'+lista[j]].abrev+' '+details['esp'+lista[j]].num;
						} else {
							label = lista[j];
						}
						t = t + "<span style='color:black;text-shadow:2px 0 0 yellow;cursor:pointer' onclick='clickEspPl(this,\"esp\","+pts[i].id+")' title='"+lista[j]+"'>" +
							//lista[j] + "</span>, ";
							label + "</span>, ";
					}
					t = t.substr(0,t.length-2);
					divBox.innerHTML = t;
				}
				// PLANTAS
				if (pts[i].id in pls) {
					lista = pls[pts[i].id].split('|');
					var t = '';
					for (j=0; j<lista.length; j++) {
						if (details['pl'+lista[j]].abrev != '' && details['pl'+lista[j]].num != '') {
							label = details['pl'+lista[j]].abrev+' '+details['pl'+lista[j]].num;
						} else {
							label = lista[j];
						}
						t = t + "<span style='color:black;text-shadow:2px 0 0 lightgreen;cursor:pointer' onclick='clickEspPl(this,\"pl\","+pts[i].id+")' title='"+lista[j]+"'>" +
							//lista[j] + "</span>, ";
							label + "</span>, ";
					}
					t = t.substr(0,t.length-2);
					if (divBox.innerHTML == '') {
						divBox.innerHTML = t;
					} else {
						divBox.innerHTML = divBox.innerHTML + "<BR>" + t;
					}
				}
				divBox.style.display = 'none';
				if (mesomicro) {
					divBox.style.backgroundColor = 'rgba(0,0,0,0)';
					divBox.style.border = '0px solid black';
				} else {
					divBox.style.backgroundColor = 'rgba(0,0,0,0.05)';
					divBox.style.border = '0px solid black';
				}
				divBox.style.paddingLeft = indent;
				// se tem pai
				div.appendChild(divBox);
				if (pts[i].pai > 0) {
					divs = document.getElementById('txt').getElementsByTagName('div');
					for (j=0; j<divs.length; j++) {
						if (divs[j].id.indexOf('box'+pts[i].pai) == 0) {
							boxPai = divs[j];
							if (boxPai == null) {
							} else {
								if (mesomicro) {
									boxPai.style.paddingLeft = '0px';
								} else {
									boxPai.style.paddingLeft = indent;
								}
								boxPai.appendChild(div);
							}
						}
					}
				} else { // não tem pai (país)
					divTxt.appendChild(div);
				}
				pts.splice(i--,1);
			}
		}
		pais = pais1;
	} while (pts.length > 0);
	pts = ptsBackup.slice();
}
function winKeyDown(e) {
	if (e.keyCode == 106) { // *
		mostra(0);
	}
}
window.addEventListener('keydown', winKeyDown, false);
<?php
$latC = null;
$lonC = null;
$lat = null;
$lon = null;
$tipo = null;
$latMin = 1000;
$latMax = -1000;
$lonMin = 1000;
$lonMax = -1000;
$iMrk = 1;
if (!empty($_GET['tax'])) {
	$tax = $_GET['tax'];
	// guarda informações de todos os locais que abrigam descendentes daquele táxon ($1) (via esp ou pl)
	$q = "with recursive locpais as (
		select l.id, l.nome, l.tipo tid, lt.tipo, lp.pai, l.lat, l.lon
		from loc l
		left join locpai lp on lp.loc = l.id
		left join loctipo lt on lt.id = l.tipo
		where l.id in (select loc from (select loc
			from esp e
			join det d on d.id = e.det
			where d.tax = any(gettaxfilhos($1))
			union
			select loc
			from pl
			join det d on d.id = pl.det
			where d.tax = any(gettaxfilhos($1))) as locais
			where loc is not null)
		union
		select l.id, l.nome, l.tipo tid, lt.tipo, lp.pai, l.lat, l.lon
		from loc l
		left join locpai lp on lp.loc = l.id
		left join loctipo lt on lt.id = l.tipo
		join locpais p on (l.id = p.pai)
	)
	select distinct id,pai,nome,getloc(id) path,tid,tipo,lat,lon from locpais order by id,nome";
	$res = pg_query_params($conn,$q,[$tax]);
	echo "function loadPts() {\n"; // \tvar pt,esp;\n";
	if ($res) {
		$mrksLat = [];
		$mrksLon = [];
		$mrksLab = [];
		$mrksTit = [];
		$mrksId = [];
		$iMrk = 0;
		$llPts = [];
		while ($row = pg_fetch_array($res,NULL,PGSQL_ASSOC)) {
			$id = $row['id'];
			$pai = empty($row['pai']) ? 0 : $row['pai'];
			$tid = $row['tid'];
			$tipo = $row['tipo'];
			$nome = $row['nome'];
			$path = $row['path'];
			$lat = empty($row['lat']) ? 'null' : $row['lat'];
			$lon = empty($row['lon']) ? 'null' : $row['lon'];
			if ($lat == 'null' || $lon == 'null') {
				if (array_key_exists($pai,$llPts)) {
					/*if ($pai == 11905) {
						echo "\n\n\n// pai = 11905\n\n\n";
					}*/
					$llPai = $llPts[$pai];
					$pos = strpos($llPai,';');
					$lat = substr($llPai,0,$pos);
					$lon = substr($llPai,$pos+1);
				}
			}
			$llPts[$id] = "$lat;$lon";
			if ($lat == 'null') {
				$lon = 'null';
			}
			if ($lon == 'null') {
				$lat = 'null';
			}
			if ($lat != 'null') {
				if ($lat < $latMin) {
					$latMin = $lat;
				}
				if ($lat > $latMax) {
					$latMax = $lat;
				}
			} 
			if ($lon != 'null') {
				if ($lon < $lonMin) {
					$lonMin = $lon;
				}
				if ($lon > $lonMax) {
					$lonMax = $lon;
				}
			}
			$mrksLat[] = $lat;
			$mrksLon[] = $lon;
			$mrksLab[] = '';
			$mrksTit[] = $nome;
			$mrksId[] = $id;
			$iMrk++;
			/*echo "\tpt = {id:$id,pai:$pai,tipo:$tid,nome:'$nome',lat:$lat,lon:$lon};\n";
			echo "\tpts.push(pt);\n";*/
			echo "\tpts.push({id:$id,pai:$pai,tid:$tid,tipo:'$tipo',nome:'$nome',path:'$path',lat:$lat,lon:$lon});\n";
		}
		echo "// min-max: $latMin $latMax , $lonMin $lonMax\n";
		if ($latMin < 1000 && $lonMin < 1000) {
			$latC = ($latMin+$latMax)/2;
			$lonC = ($lonMin+$lonMax)/2;
		}
		$tipo = 'tax';
	}
	// especímenes por local
	$q = "select e.loc,string_agg(e.id::text,'|') esps from esp e
	join det d on e.det = d.id
	where d.tax = any(gettaxfilhos($1)) and
	e.loc is not null
	group by e.loc
	order by e.loc";
	$res = pg_query_params($conn,$q,[$tax]);
	if ($res) {
		while ($row = pg_fetch_array($res,NULL,PGSQL_ASSOC)) {
			$loc = $row['loc'];
			$esps = $row['esps'];
			echo "\tesps[$loc] = '$esps';\n";
		}
	}
	// plantas por local
	$q = "select pl.loc,string_agg(pl.id::text,'|') pls from pl
	join det d on pl.det = d.id
	where d.tax = any(gettaxfilhos($1)) and
	pl.loc is not null
	group by pl.loc
	order by pl.loc";
	$res = pg_query_params($conn,$q,[$tax]);
	if ($res) {
		while ($row = pg_fetch_array($res,NULL,PGSQL_ASSOC)) {
			$loc = $row['loc'];
			$pls = $row['pls'];
			echo "\tpls[$loc] = '$pls';\n";
		}
	}
	// detalhes dos especímenes
	$q = "select e.id,e.loc,getespnotas(e.id) notas,h.nome habitat,e.lat,e.lon,e.dia,e.mes,e.ano,gettax(t.id) taxon,getaut(t.id) autor,gettax_fam(t.id) fam,p.abrev,e.num,string_agg(p1.abrev,',') cols,prj.nome proj from esp e
	join det d on e.det = d.id
	join tax t on t.id = d.tax
	join pess p on p.id = e.col
	left join espcols ec on ec.esp = e.id
	left join pess p1 on p1.id = ec.col
	left join hab h on h.id = e.hab
	left join prj on prj.id = e.prj
	where d.tax = any(gettaxfilhos($1)) and
	e.loc is not null
	group by e.id,e.loc,getespnotas(e.id),h.nome,e.lat,e.lon,e.dia,e.mes,e.ano,gettax(t.id),getaut(t.id),gettax_fam(t.id),p.abrev,e.num,prj.nome";
	$res = pg_query_params($conn,$q,[$tax]);
	if ($res) {
		while ($row = pg_fetch_array($res,NULL,PGSQL_ASSOC)) {
			$id = $row['id'];
			$loc = $row['loc'];
			$notas = $row['notas'];
			$hab = $row['habitat'];
			$lat = empty($row['lat']) ? 'null' : $row['lat'];
			$lon = empty($row['lon']) ? 'null' : $row['lon'];
			$dia = $row['dia'];
			switch (strlen($dia)) {
				case 1 : $dia = "/0$dia"; break;
				case 2 : $dia = "/$dia"; break;
			}
			$mes = $row['mes'];
			switch (strlen($mes)) {
				case 1 : $mes = "/0$mes"; break;
				case 2 : $mes = "/$mes"; break;
			}
			$ano = $row['ano'];
			$taxon = $row['taxon'];
			$aut = $row['autor'];
			$fam = $row['fam'];
			$abrev = $row['abrev'];
			$num = $row['num'];
			$cols = $row['cols'];
			$prj = $row['proj'];
			echo "\tdetails['esp$id'] = {id:$id,loc:$loc,notas:'$notas',hab:'$hab',lat:$lat,lon:$lon,dia:'$dia',mes:'$mes',ano:'$ano',tax:'$taxon',aut:'$aut',fam:'$fam',abrev:'$abrev',num:'$num',cols:'$cols',prj:'$prj'};\n";
		}
	}
	// detalhes das plantas
	$q = "select pl.id,pl.loc,getplnotas(pl.id) notas,h.nome habitat,pl.lat,pl.lon,pl.dia,pl.mes,pl.ano,gettax(t.id) taxon,getaut(t.id) autor,gettax_fam(t.id) fam,p.abrev,pl.num,string_agg(p1.abrev,',') cols,prj.nome proj from pl
	join det d on pl.det = d.id
	join tax t on t.id = d.tax
	left join pess p on p.id = pl.col
	left join plcols pc on pc.pl = pl.id
	left join pess p1 on p1.id = pc.col
	left join hab h on h.id = pl.hab
	left join prj on prj.id = pl.prj
	where d.tax = any(gettaxfilhos($1)) and
	pl.loc is not null
	group by pl.id,pl.loc,getplnotas(pl.id),h.nome,pl.lat,pl.lon,pl.dia,pl.mes,pl.ano,gettax(t.id),getaut(t.id),gettax_fam(t.id),p.abrev,pl.num,prj.nome";
	$res = pg_query_params($conn,$q,[$tax]);
	if ($res) {
		while ($row = pg_fetch_array($res,NULL,PGSQL_ASSOC)) {
			$id = $row['id'];
			$loc = $row['loc'];
			$notas = $row['notas'];
			$hab = $row['habitat'];
			$lat = empty($row['lat']) ? 'null' : $row['lat'];
			$lon = empty($row['lon']) ? 'null' : $row['lon'];
			$dia = $row['dia'];
			switch (strlen($dia)) {
				case 1 : $dia = "/0$dia"; break;
				case 2 : $dia = "/$dia"; break;
			}
			$mes = $row['mes'];
			switch (strlen($mes)) {
				case 1 : $mes = "/0$mes"; break;
				case 2 : $mes = "/$mes"; break;
			}
			$ano = $row['ano'];
			$taxon = $row['taxon'];
			$aut = $row['autor'];
			$fam = $row['fam'];
			$abrev = $row['abrev'];
			$num = $row['num'];
			$cols = $row['cols'];
			$prj = $row['proj'];
			echo "\tdetails['pl$id'] = {id:$id,loc:$loc,notas:'$notas',hab:'$hab',lat:$lat,lon:$lon,dia:'$dia',mes:'$mes',ano:'$ano',tax:'$taxon',aut:'$aut',fam:'$fam',abrev:'$abrev',num:'$num',cols:'$cols',prj:'$prj'};\n";
		}
	}
	echo "}\n"; // fim de loadPts()
} // fim do if 'tax'
echo "</script>\n";
echo "</head>
<body onload='aoCarregar()'>
<div id='map' style='width:70%;height:100%;float:left'></div>
<div id='txt' style='width:30%;height:100%;float:right;padding:5px;box-sizing:border-box;overflow-y:scroll'>";
if (!empty($_GET['loc'])) {
	$loc = $_GET['loc'];
	$q = "with recursive locpais as (
		select l.id, l.nome, l.tipo tid, lt.tipo, lp.pai, 1 as profund, l.lat, l.lon
		from loc l
		left join locpai lp on lp.loc = l.id
		left join loctipo lt on lt.id = l.tipo
		where l.id = $1
		union
		select l.id, l.nome, l.tipo tid, lt.tipo, lp.pai, profund+1, l.lat, l.lon
		from loc l
		left join locpai lp on lp.loc = l.id
		left join loctipo lt on lt.id = l.tipo
		join locpais p on (l.id = p.pai)
	)
	select distinct id,nome,lat,lon,profund from locpais order by profund";
	$res = pg_query_params($conn,$q,[$loc]);
	if ($res) {
		$nomes = '';
		$mrksLat = [];
		$mrksLon = [];
		$mrksLab = [];
		$mrksTit = [];
		$mrksId = [];
		while ($row = pg_fetch_array($res,NULL,PGSQL_ASSOC)) { // gazetteer -> ... -> país
			if ($row['lat'] != null && $row['lon'] != null) {
				$id = $row['id'];
				$vermelho = false;
				if ($lat == null && $lon == null) {
					$lat = $row['lat'];
					$lon = $row['lon'];
					$vermelho = true;
				}
				$latM = $row['lat'];
				$lonM = $row['lon'];
				if ($latM < $latMin) {
					$latMin = $latM;
				}
				if ($latM > $latMax) {
					$latMax = $latM;
				}
				if ($lonM < $lonMin) {
					$lonMin = $lonM;
				}
				if ($lonM > $lonMax) {
					$lonMax = $lonM;
				}
				if ($vermelho) {
					$nomes .= "$iMrk <span style='color:red;font-weight:bold'>$row[nome]</span><BR><span style='color:gray;font-family:sans-serif;font-size:14px'>&nbsp;&nbsp;&nbsp; lat: $latM &nbsp;&nbsp;&nbsp; lon: $lonM</span><BR>\n";
				} else {
					$nomes .= "$iMrk $row[nome]<BR><span style='color:gray;font-family:sans-serif;font-size:14px'>&nbsp;&nbsp;&nbsp; lat: $latM &nbsp;&nbsp;&nbsp; lon: $lonM</span><BR>\n";
				}
				// para inserir google marker - coletando dados
				$mrksLat[] = $latM;
				$mrksLon[] = $lonM;
				$mrksLab[] = $iMrk;
				$mrksTit[] = $row['nome'];
				$mrksId[] = $id;
				$iMrk++;
			} else {
				$nomes .= "$row[nome]<BR>";
			}
		}
		echo $nomes;
		echo "// min-max: $latMin $latMax , $lonMin $lonMax\n";
		if ($latMin < 1000 && $lonMin < 1000) {
			$latC = ($latMin+$latMax)/2;
			$lonC = ($lonMin+$lonMax)/2;
		}
		$tipo = 'loc';
	}
}
if (empty($_GET['tax']) && empty($_GET['loc'])) {
	if (!empty($_GET['lat'])) {
		$latC = $_GET['lat'];
	}
	if (!empty($_GET['lon'])) {
		$lonC = $_GET['lon'];
	}
	$nome = '';
	$tipo = 'll';
}

// terminou de coletar os dados
// initMap

echo "</div>
<script>
var map, marker;
function initMap() { // tipo = $tipo, latC = $latC, lonC = $lonC\n";
echo "\tvar divMap = document.getElementById('map');\n";
if ($tipo == 'll') { // apenas 1 lat/lon
	echo "\tvar div = document.getElementById('txt');\n";
	echo "\tdiv.style.width='0px';\n";
	echo "\tdivMap.style.width='100%';\n";
}
if ($latC != null && $lonC != null) {
	echo "\tvar pixelWidth = divMap.clientWidth;
	var GLOBE_WIDTH = 256;\n"; // a constant in Google's map projection
	if ($tipo != 'll') {
		echo "\tvar angle = ".($lonMax-$lonMin).";\n";
	} else {
		echo "\tvar angle = 1;\n";
	}
	echo "\tif (angle < 0) {
		angle += 360;
	}
	var zoom = Math.round(Math.log(pixelWidth * 360 / angle / GLOBE_WIDTH) / Math.LN2);
	var ponto = {lat: $latC, lng: $lonC};
	map = new google.maps.Map(document.getElementById('map'), {
		center: ponto,
		zoom: zoom-1
	});\n";
}
if ($tipo == 'll') { // apenas 1 lat/lon
	echo "\tmarker = new google.maps.Marker({
		position: ponto,
		map: map,
		title: '$lat, $lon'
	});\n";
} else { // vários lat/lon (via loc ou tax)
	$conta = 0;
	for ($i=0; $i<$iMrk-1; $i++) {
		echo "// i = $i, mrksLat[$i] = $mrksLat[$i], mrksLon[$i] = $mrksLon[$i]\n";
		if ($mrksLat[$i] != 'null' && $mrksLon[$i] != 'null') {
			//var markers = [];
			echo "\t// tem coordenada:
		contentString = '<div id=\"div$mrksId[$i]\">'+
		'<h1 id=\"firstHeading$mrksId[$i]\" class=\"firstHeading\">$mrksId[$i] $mrksTit[$i]</h1>'+
		'<div id=\"divCont$mrksId[$i]\">'+
		'<p>Conteúdo.</p>'+
		'</div>'+
		'</div>';
		// strings.push(contentString);
		infoWindow = new google.maps.InfoWindow({
			content: contentString,
			maxWidth: 400
		});\n";
			echo "\tponto = {lat: $mrksLat[$i], lng: $mrksLon[$i]};\n";
			echo "\tmarker = new google.maps.Marker({
		position: ponto,
		label: '$mrksLab[$i]',
		map: map,
		title: '$mrksTit[$i]',
		//infoWindowIndex: $conta
		infoWindowIndex: $mrksId[$i]
	});
	marker.addListener('click', function(event) {
		//console.log(event.latLng);
		//alert(event.latLng);
		map.panTo(event.latLng);
		//map.setZoom(6);
		//infowindow.open(map, marker);
		console.log(this.infoWindowIndex);
		console.log(infoWindows.length);
		//infoWindows[this.infoWindowIndex].open(map,this);
		infoWindows[this.infoWindowIndex].open(map,this);
	});
	//infoWindows.push(infoWindow);
	infoWindows[$mrksId[$i]] = infoWindow;
	markers.push(marker);\n";
			$conta++;
		} else {
			//echo "\t// coordenada nula\n";
		}
	}
}
echo "}
</script>\n";
if (in_array($tipo,['ll','loc','tax'])) {
	echo "<script src='https://maps.googleapis.com/maps/api/js?key=AIzaSyBg2lg4dAtElufHbhu1YsnhbuJK0mUCBZM&callback=initMap'
	async defer></script>\n";
}
?>
</body>
</html>
