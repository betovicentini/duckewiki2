<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();
if (isset($_GET['what'])) {
	$what = $_GET['what'];
}
if ($what != '') {
	if (!empty($_GET['query'])) {
		$query = $_GET['query'];
	}
	if (isset($_GET['who'])) {
		$who = $_GET['who'];
	}
	if (isset($_GET['m'])) {
		$multi = $_GET['m'];
	} else {
		$multi = 'i'; // indeterminado, não deveria acontecer nunca
	}
	if (isset($_GET['coll'])) {
		$collation = $_GET['coll'];
	}
	//echo "$query<BR><BR>";
	if (substr($query,0,4) == 'pess') { // long PESS
	//abrev||' ('||prenome||' '||segundonome||' '||sobrenome||')'
		$q = "select id,concat(abrev,' (',prenome,' ',segundonome,' ',sobrenome,')')
		from pess
		where
		unaccent(abrev) ilike unaccent('%$what%') or
		unaccent(prenome) ilike unaccent('%$what%') or
		unaccent(segundonome) ilike unaccent('%$what%') or
		unaccent(sobrenome) ilike unaccent('%$what%')
		order by abrev,prenome";
	} else
	if ($query == 'herb') { // long HERB
		//sigla||' ('||nome||')'
		$q = "select id,concat(sigla,' (',nome,')')
		from herb
		where
		unaccent(sigla) ilike unaccent('%$what%') or
		unaccent(nome) ilike unaccent('%$what%')";
	} else
	if ($query == 'vern') { // long VERN
		$q = "select v.id,v.nome||' ('||l.sigla||')'
		from vern v
		join lang l on v.lang = l.id
		where
		unaccent(v.nome) ilike unaccent('%$what%')";
	} else
	if ($query == 'taxespec') { // long PESS
		//		abrev||' ('||prenome||' '||segundonome||' '||sobrenome||')'
		$q = "select id,concat(abrev,' (',prenome,' ',segundonome,' ',sobrenome,')')
		from pess
		where
		unaccent(abrev) ilike unaccent('%$what%') or
		unaccent(prenome) ilike unaccent('%$what%') or
		unaccent(segundonome) ilike unaccent('%$what%') or
		unaccent(sobrenome) ilike unaccent('%$what%')
		order by abrev,prenome";
	} else
	if ($query == 'locpai' || $query == 'loc') { // long LOC
		$q = "select id,nome
		from loc
		where
		unaccent(nome) ilike unaccent('%$what%')
		order by nome";
	} else
	if ($query == 'keyvar') { // long VAR
		$q = "select id,valname
		from val
		where
		unaccent(valname) ilike unaccent('%$what%')
		order by valname";
	} else
	if (in_array($query,['habtax','tax','taxsin','espectax'])) {
		$q = "select id,gettax(id) nometax
		from tax
		where
		unaccent(gettax(id)) ilike unaccent('%$what%')
		order by nometax";
	} else
	if ($query == 'taxpai') { // long TAX
		
//		$q = "select id,rank,gettax(id) nome
//		from tax
//		where
//		unaccent(gettax(id)) ilike unaccent('%$what%')
//		order by nome";
		$q = "select 
		t1.id,
		t1.rank,
		gettax(t1.id) nome
		from tax as t1 
		left join tax as t2 on t1.taxpai=t2.id
		where
		unaccent(concat(t2.nome,' ',t1.nome)) ilike unaccent('%$what%') 
		order by nome";
		
	} else
	if ($query == 'hab') { // long HAB
		$q = "select id,nome
		from hab
		where
		unaccent(nome) ilike unaccent('%$what%')
		order by nome";
	} else
	if ($query == 'pltag') { // long PL
		$q = "select id,pltag
		from pl
		where
		unaccent(pltag) ilike unaccent('%$what%')
		order by pltag";
	} else
	if ($query == 'prj') { // long PRJ
		$q = "select id,nome
		from prj
		where unaccent(nome) ilike unaccent('%$what%')
		order by nome";
	} else
	if ($query == 'bib') { // long BIB
		$q = "select id,title
		from bib
		where unaccent(title) ilike unaccent('%$what%')
		order by title";
	} else
	if ($query == 'frmf') { // long KEY
		$q = "select id,pathname
		from key
		where unaccent(pathname) ilike unaccent('%$what%')
		order by pathname";
	} else
	if ($query == 'esp') {
		$q = "select e.id,concat(p.abrev,' ',e.num) numcol
		from esp e
		left join pess p on p.id = e.col
		where unaccent(concat(p.abrev,' ',e.num)) ilike unaccent('%$what%')
		order by numcol";
	} else
	if ($query == 'pl') {
		$q = "select pl.id,concat(p.abrev,' ',pl.pltag) numcol
		from pl
		left join pess p on p.id = pl.col
		where unaccent(concat(p.abrev,' ',pl.pltag)) ilike unaccent('%$what%')
		order by numcol";
	} else
	if ($query == 'varquant') {
		$q = "select id,pathname
		from key
		where tipo = 4 and pathname ilike '%$what%'";
	} else
	if ($query == 'varquali') {
		$q = "select id,pathname
		from key
		where tipo = 2 and pathname ilike '%$what%'";
	} else
	if ($query == 'varcsv') {
		$q = "select id,pathname
		from key
		where pathname ilike '%$what%'";
	}
	$res = pg_query($conn,$q);
	
	if ($query == 'varcsv') {
		echo "<select id='sel$who' size=10 onchange='selColChange(this)'>";
	} else
	if ($multi == 'S') {
		echo "<select id='sel$who' multiple=true size=10 onclick='cmbselClick(\"$who\",\"S\")' onkeydown='cmbselKeyDown(event,\"S\")' onkeyup='cmbselKeyUp(event,\"$multi\")' ondblclick='mmselDblClick(event,\"S\")'>";
	} else {
		echo "<select id='sel$who' size=10 onclick='cmbselClick(\"$who\",\"N\")' onkeydown='cmbselKeyDown(event,\"N\")' onkeyup='cmbselKeyUp(event,\"$multi\")' ondblclick='mmselDblClick(event,\"N\")'>";
	}
	while ($row = pg_fetch_array($res,NULL,PGSQL_NUM)) {
		if ($query == 'taxpai') {
			echo "<option value='$row[0]件$row[1]' onmouseover='mmoptMouseOver(event,this,\"$query\")'>$row[2]</option>";
		} else
		if ($query == 'varcsv') {
			echo "<option value='$row[0]'>$row[1]</option>";
		} else {
			echo "<option value='$row[0]' onmouseover='mmoptMouseOver(event,this,\"$query\")'>$row[1]</option>";
		}
	}
	echo "</select>";
}
?>
