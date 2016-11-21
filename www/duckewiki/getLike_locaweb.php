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
	if (isset($_GET['case'])) {
		$caseSens = ($_GET['case'] > 0);
	} else {
		$caseSens = false; // ignore case by default
	}
	if (isset($_GET['coll'])) {
		$collation = $_GET['coll'];
	}
	if (substr($query,0,4) == 'pess') { // long PESS
		$q = "select id,abrev||' ('||prenome||' '''||segundonome||''' '||sobrenome||')'
		from pess
		where
		abrev ilike '%$what%' or
		prenome ilike '%$what%' or
		segundonome ilike '%$what%' or
		sobrenome ilike '%$what%'
		order by abrev,prenome";
	} else
	if ($query == 'herb') { // long HERB
		$q = "select id,sigla||' ('||nome||')'
		from herb
		where
		sigla ilike '%$what%' or
		nome ilike '%$what%'";
	} else
	if ($query == 'vern') { // long VERN
		$q = "select v.id,v.nome||' ('||l.sigla||')'
		from vern v
		join lang l on v.lang = l.id
		where
		v.nome ilike '%$what%'";
	} else
	if ($query == 'taxespec') { // long PESS
		$q = "select id,abrev||' ('||prenome||' '''||segundonome||''' '||sobrenome||')'
		from pess
		where
		abrev ilike '%$what%' or
		prenome ilike '%$what%' or
		segundonome ilike '%$what%' or
		sobrenome ilike '%$what%'
		order by abrev,prenome";
	} else
	if ($query == 'locpai' || $query == 'loc') { // long LOC
		$q = "select id,nome
		from loc
		where
		nome ilike '%$what%'
		order by nome";
	} else
	if (in_array($query,['habtax','tax','taxsin','espectax'])) {
		$q = "select id,gettax(id) nometax
		from tax
		where
		gettax(id) like '%$what%'
		order by nometax";
	} else
	if ($query == 'taxpai') { // long TAX
		$q = "select id,rank,gettax(id) nome
		from tax
		where
		nome like '%$what%'
		order by nome";
	} else
	if ($query == 'hab') { // long HAB
		$q = "select id,nome
		from hab
		where
		nome ilike '%$what%'
		order by nome";
	} else
	if ($query == 'pltag') { // long PL
		$q = "select id,pltag
		from pl
		where
		pltag ilike '%$what%'
		order by pltag";
	} else
	if ($query == 'prj') { // long PRJ
		$q = "select id,nome
		from prj
		where nome ilike '%$what%'
		order by nome";
	} else
	if ($query == 'bib') { // long BIB
		$q = "select id,title
		from bib
		where title ilike '%$what%'
		order by title";
	} else
	if ($query == 'formf') { // long KEY
		$q = "select id,pathname
		from key
		where pathname ilike '%$what%'
		order by pathname";
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
	}
	$res = pg_query($conn,$q);

	if ($multi == 'S') {
		echo "<select id='sel$who' multiple=true size=10 onclick='cmbselClick(\"$who\",\"S\")' onkeydown='cmbselKeyDown(event,\"S\")' onkeyup='cmbselKeyUp(event,\"$multi\")' ondblclick='mmselDblClick(event,\"S\")'>";
	} else {
		echo "<select id='sel$who' size=10 onclick='cmbselClick(\"$who\",\"N\")' onkeydown='cmbselKeyDown(event,\"N\")' onkeyup='cmbselKeyUp(event,\"$multi\")' ondblclick='mmselDblClick(event,\"N\")'>";
	}
	while ($row = pg_fetch_array($res,NULL,PGSQL_NUM)) {
		if ($query == 'taxpai') {
			echo "<option value='$row[0]件$row[1]' onmouseover='mmoptMouseOver(event,this,\"$query\")'>$row[2]</option>";
		} else {
			echo "<option value='$row[0]' onmouseover='mmoptMouseOver(event,this,\"$query\")'>$row[1]</option>";
		}
	}
	echo "</select>";

}
?>
