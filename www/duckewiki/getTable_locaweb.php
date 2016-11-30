<?php
/*
 * Ordenar:
 * - clicar em mais de uma coluna faz ordenação por múltiplas colunas
 * - clicar de novo na mesma coluna: 1) inverte a ordem; 2) retira a ordenação
 * Limite:
 * ok exibir o limite aplicado e o ponto de início
 * ok modificar o limite
 * ok modificar o ponto de início
 * - verificar erros prováveis quando ponto de início é colocado perto dos extremos
 * - quando muda de tabela grande para pequena, limite/offset máximo ficam em excesso
 * - não mostrar 1 a 10 se n = 0
 * 
 * ok Filtros vazios não devem ir para a URL
 * ok Quando der Enter, coloca aquele filtro (se != '') na URL e submit (se == '', tira da URL se estiver lá)
 * ok colocar mais de um filtro ao mesmo tempo
 * ok evitar o 'like' quando for campo numérico (ou melhor ainda fazer typecast para 'text')
 * 
 * - cuidado para o Session de um não pegar variáveis do outro? (Session de coisas e de plantas em comum!)
 * - sort de uma tabela não deve passar pra outra
 * - zerar o offset/limit quando mudar de tabela/filtro?
 * - tax.pai = novo id, e não o antigo?! (select * from tax where nome = 'Ficus' or id = 67 or oldfam = 67)
 * - FECHAR o datepicker quando sair pelo Tab/Esc


http://stackoverflow.com/questions/36343860/why-does-a-slight-change-in-the-search-term-slow-down-the-query-so-much

I have the following query in PostgreSQL:

select e.id, (select count(id) from imgitem ii where ii.tabid = e.id and ii.tab = 'esp') as imgs,
 e.ano, e.mes, e.dia, cast(cast(e.ano as varchar(4))||'-'||right('0'||cast(e.mes as varchar(2)),2)||'-'|| right('0'||cast(e.dia as varchar(2)),2) as varchar(10)) as data,
 pl.pltag, e.inpa, e.det, d.ano anodet, coalesce(p.abrev,'')||' ('||coalesce(p.prenome,'')||')' determinador, d.tax, coalesce(v.val,v.valf)||' '||vu.unit as altura,
 coalesce(v1.val,v1.valf)||' '||vu1.unit as DAP, d.fam, tf.nome família, d.gen, tg.nome gênero, d.sp, ts.nome espécie, d.inf, e.loc, l.nome localidade, e.lat, e.lon
from esp e
left join det d on e.det = d.id
left join tax tf on d.fam = tf.oldfam
left join tax tg on d.gen = tg.oldgen
left join tax ts on d.sp = ts.oldsp
left join tax ti on d.inf = ti.oldinf
left join loc l on e.loc = l.id
left join pess p on p.id = d.detby
left join var v on v.esp = e.id and v.key = 265
left join varunit vu on vu.id = v.unit
left join var v1 on v1.esp = e.id and v1.key = 264
left join varunit vu1 on vu1.id = v1.unit
left join pl on pl.id = e.pl
WHERE unaccent(TEXT(coalesce(p.abrev,'')||' ('||coalesce(p.prenome,'')||')')) ilike unaccent('%vicen%')

It takes 430ms to retrieve 1129 rows from a total of 9250 in esp table.

If I change the search term from %vicen% to %vicent% (adding a 't'), it takes 431ms to retrieve the same 1129 rows.

Ordering by the search column, ascending and descending, I see that all 1129 rows have exactly the same name in both cases.

Now the strange: if I change the search term from %vicent% to %vicenti% (adding an 'i'), now it takes unbelievable 24.4 seconds to retrieve the same 1129 rows!

The searched term is always in the first coalesce, i.e. coalesce(p.abrev,''). I expect the query to run slower or faster, depending on the size of the searched string, but not that much!! Anyone has any idea of what's going on?


 */
if (!isset($conn)) {
	include_once '../../includes_pl/db_connect.php';
	include_once '../../includes_pl/functions.php';
	sec_session_start();
}
if (isset($_GET['table'])) {
	$table = $_GET['table'];
}
$exportList = readMarks($table);
$selCount = count($exportList);
$mainTables = in_array($table,['checklist','especimenes','plantas','locais','locais2']);
if (isset($_GET['export'])) {
	$export = $_GET['export'];
	if (!empty($exportList)) {
		header('Content-Type: text/csv;charset=UTF-8;'); // send response headers to the browser
		$agora = date('_Y_m_d_H_i_s');
		header("Content-Disposition: attachment;filename=$table$agora.csv;");
		$fp = fopen('php://output','w') or die ('Cannot create file!');
	} else {
		$exportList = '';
	}
} else {
	$export = '';
	$exportList = '';
}
$onlycols = false;
if (isset($_GET['onlycols'])) {
	$onlycols = $_GET['onlycols'];
}
$mark = getGet('mark','');

/////////////////////////////////////////////////////
// function getFilter()
/////////////////////////////////////////////////////

function getFilter($begin) { // $begin se a cláusula está no início do WHERE, e não depois de outras cláusulas (... AND ...)
	$res = '';
	global $export;
	global $exportList;
	global $mark;
	if ($export && !$exportList) {
		return false;
	}
	global $table;
	foreach ($_GET as $key => $value) {
		if (strpos($key,'txtFilter') === 0) {
			if ($begin) {
				$res = $res.' WHERE';
				$begin = false;
			} else {
				$res = $res.' AND';
			}
			$key = substr($key,9);
			if (in_array($table,['checklist','especimenes','plantas','locais','locais2'])) {
				switch ($key) {
					case 'altura' : $key = "coalesce(v.val,v.valf)||' '||vu.unit";
						break;
					case 'ano' : $key = 'e.ano';
						break;
					case 'anodet' : $key = 'd.ano';
						break;
					case 'avô' : $key = "ttt.nome";
						break;
					case 'dap' : $key = "coalesce(v1.val,v1.valf)||' '||vu1.unit";
						break;
					case 'data' : $key = "cast(cast(e.ano as varchar(4))||'-'||right('0'||cast(e.mes as varchar(2)),2)||'-'||right('0'||cast(e.dia as varchar(2)),2) as varchar(10))";
						break;
					case 'determinador' : $key = "coalesce(p.abrev,'')||' ('||coalesce(p.prenome,'')||')'";
						break;
					case 'dia' : $key = 'e.dia';
						break;
					case 'espécie' :
						if ($table == 'especímenes') {
							$key = "ts.nome";
						} else
						if ($table == 'plantas') {
							$key = "gettax_sp(d.tax)";
						}
						break;
					case 'especímenes' : $key = "especímenes";
						break;
					case 'estado' : $key = 'getloc_uf(l.id)';
						break;
					case 'família' :
						if ($table == 'especimenes') {
							$key = "tf.nome";
						} else
						if ($table == 'plantas') {
							$key = "gettax_fam(d.tax)";
						}
						break;
					case 'gênero' :
						if ($table == 'especimenes') {
							$key = "tg.nome";
						} else
						if ($table == 'plantas') {
							$key = "gettax_gen(d.tax)";
						}
						break;
					case 'id' :
						switch ($table) {
							case 'checklist' : $key = 't.id';
								break;
							case 'especimenes' : $key = 'e.id';
								break;
							case 'plantas' : $key = 'pl.id';
								break;
							case 'locais' :
							case 'locais2' : $key = 'l.id';
								break;
						}
						break;
					case 'lat' : $key = 'e.lat';
						break;
					case 'lon' : $key = 'e.lon';
						break;
					case 'localidade' :
						switch ($table) {
							case 'locais2' :
								$key = 'getloc(l.id)';
								break;
							default :
								$key = 'l.nome';
						}
						break;
					case 'mes' : $key = 'e.mes';
						break;
					case 'município' : $key = 'getloc(l.id,8)';
						break;
					case 'pai' : $key = "tt.nome";
						break;
					case 'país' : $key = 'getloc(l.id,3)';
						break;
					case 'projeto' : $key = 'prj.nome';
						break;
					case 'rank' : $key = "r.nome";
						break;
					case 'tag' : $key = "pl.pltag";
						break;
					case 'tax' : $key = 'd.tax';
						break;
					case 'táxon' : $key = 't.nome';
						break;
					case 'tipo' : $key = 'lt.tipo';
						break;
					case 'uf' : $key = 'getloc(l.id,5)';
						break;
				}
			} else {
				$key = "$table.$key";
			}
			// modificadores do $value
			$modif = '';
			while (in_array(substr($value,0,1),['=','>','<','!'])) {
				$modif .= substr($value,0,1);
				$value = substr($value,1);
			}
			if ($modif == '') {
				$res = $res." TEXT($key) ilike '%$value%'"; // TEXT() para poder filtrar colunas numéricas
			} else {
				if ($modif == '!') { // Comparação usando <>
					if (in_array($value,['NULL','null'])) {
						$res = $res." ($key) is not null";
					} else {
						$res = $res." $key <> '$value'";
					}
				} else { // Comparação usando =, >, <, >=, <=
					if (in_array($value,['NULL','null'])) {
						$res = $res." ($key) is null";
					} else {
						$res = $res." $key $modif '$value'";
					}
				}
			}
		}
	}
	global $fIDs;
	if ($export || $mark == 'showMark' || isset($fIDs)) {
		switch ($table) {
			case 'checklist' : $key = 't.id';
				break;
			case 'especimenes' : $key = 'e.id';
				break;
			case 'locais' : $key = 'l.id';
				break;
			case 'plantas' : $key = 'pl.id';
				break;
			default : $key = 'id';
		}
		if ($begin) {
			$res = $res.' WHERE';
		} else {
			$res = $res.' AND';
		}
		if ($export) {
			$lista = implode(',',$exportList);
		} else
		if (isset($fIDs)) {
			$lista = $fIDs;
		} else {
			$lista = implode(',',readMarks($table));
		}
		$res = $res." $key in ($lista)";
	}
	return($res);
}
// function getFilter()
/////////////////////////////////////////////////////
$mostrar = false;
$direcao = 0;

/////////////////////////////////////////////////////
// MONTA QUERY
/////////////////////////////////////////////////////
if (substr($table,0,7) == 'filter:') {
	$fname = substr($table,7);
	if ($fname != '0') {
		if ($fp = fopen('usr/'.$_SESSION['user_id']."/filter/$fname","rb")) {
			$table = rtrim(fgets($fp),"\r\n");
			if (strpos($table,',') > 0) {
				echo "Erro na leitura de usr/".$_SESSION['user_id']."/filter/$fname (o formato do arquivo é inválido).";
				$table = 'tables';
			} else {
				$fIDs = rtrim(fgets($fp),"\r\n");
				fclose($fp);
				echo "$table:<BR>";
			}
		}
	} else {
		$table = 'tables';
	}
}
if ($table == 'checklist') {
	$q = "select count(*) from (with taxs as (
	select d.tax, pl.id plid, null as eid
	from pl
	left join det d on d.id = pl.det
	union all
	select d.tax, null as plid, e.id eid
	from esp e
	left join det d on d.id = e.det),
agg as (
	select
		taxs.tax,
		count(taxs.plid) plantas,
		count(taxs.eid) especímenes
	from
		taxs
	group by
		taxs.tax)
select t.id,t.nome táxon,tt.nome pai,ttt.nome avô,r.nome rank,plantas,especímenes from agg
left outer join tax t on agg.tax = t.id
left join tax tt on t.taxpai = tt.id
left join tax ttt on tt.taxpai = ttt.id
left join ranks r on r.id = t.rank";
	$q = $q.getFilter(true);
	$q = $q.") as tbchklist";
	$res = pg_query($conn,$q);
	$row = pg_fetch_row($res);
	$rowcount = $row[0];
	$q = "with taxs as (
	select d.tax, pl.id plid, null as eid
	from pl
	left join det d on d.id = pl.det
	union all
	select d.tax, null as plid, e.id eid
	from esp e
	left join det d on d.id = e.det),
agg as (
	select
		taxs.tax,
		count(taxs.plid) plantas,
		count(taxs.eid) especímenes
	from
		taxs
	group by
		taxs.tax)
select t.id,t.nome táxon,
case when (t.aut is null) then coalesce(t.nome,'')||' '||coalesce(t.auttxt,'') else t.nome||' '||p.abrev end as táxon_autor,
tt.nome pai,ttt.nome avô,r.nome rank,plantas,especímenes from agg
left outer join tax t on agg.tax = t.id
left join tax tt on t.taxpai = tt.id
left join tax ttt on tt.taxpai = ttt.id
left join ranks r on r.id = t.rank
left join pess p on p.id = t.aut";
	$q = $q.getFilter(true);
	$mostrar = true;
} else
if ($table == 'especimenes') {
	$q = "select count(*) from (select e.id, e.ano, e.mes, e.dia, cast(cast(e.ano as varchar(4))||'-'||right('0'||cast(e.mes as varchar(2)),2)||'-'||
 right('0'||cast(e.dia as varchar(2)),2) as varchar(10)) as data, e.inpa, e.det, d.ano anodet,
 coalesce(p.abrev,'')||' ('||coalesce(p.prenome,'')||')' determinador, d.tax,
 coalesce(v.val,v.valf)||' '||vu.unit as altura,
 coalesce(v1.val,v1.valf)||' '||vu1.unit as DAP,
 d.fam, tf.nome família, d.gen, tg.nome gênero, d.sp, ts.nome espécie, d.inf, e.loc, l.nome localidade
from esp e
left join det d on e.det = d.id
left join tax tf on d.fam = tf.oldfam
left join tax tg on d.gen = tg.oldgen
left join tax ts on d.sp = ts.oldsp
left join tax ti on d.inf = ti.oldinf
left join loc l on e.loc = l.id
left join pess p on p.id = d.detby
left join var v on v.esp = e.id and v.key = 265
left join varunit vu on vu.id = v.unit
left join var v1 on v1.esp = e.id and v1.key = 264
left join varunit vu1 on vu1.id = v1.unit
left join pl on pl.id = e.pl";
	$q = $q.getFilter(true).') as tbespecimenes';
	$res = pg_query($conn,$q);
	$row = pg_fetch_row($res);
	$rowcount = $row[0]; //, getloc_uf(l.id) estado
	$q = "select e.id, (select count(id) from imgitem ii where ii.tabid = e.id and ii.tab = 'esp') as imgs, e.ano, e.mes, e.dia, cast(cast(e.ano as varchar(4))||'-'||right('0'||cast(e.mes as varchar(2)),2)||'-'||
 right('0'||cast(e.dia as varchar(2)),2) as varchar(10)) as data, pl.pltag, e.inpa, e.det, d.ano anodet,
 coalesce(p.abrev,'')||' ('||coalesce(p.prenome,'')||')' determinador, d.tax,
 coalesce(v.val,v.valf)||' '||vu.unit as altura,
 coalesce(v1.val,v1.valf)||' '||vu1.unit as DAP,
 d.fam, tf.nome família, d.gen, tg.nome gênero, d.sp, ts.nome espécie, d.inf, e.loc, l.nome localidade, e.lat, e.lon
from esp e
left join det d on e.det = d.id
left join tax tf on d.fam = tf.oldfam
left join tax tg on d.gen = tg.oldgen
left join tax ts on d.sp = ts.oldsp
left join tax ti on d.inf = ti.oldinf
left join loc l on e.loc = l.id
left join pess p on p.id = d.detby
left join var v on v.esp = e.id and v.key = 265
left join varunit vu on vu.id = v.unit
left join var v1 on v1.esp = e.id and v1.key = 264
left join varunit vu1 on vu1.id = v1.unit
left join pl on pl.id = e.pl";
	$q = $q.getFilter(true);
	$mostrar = true;
} else
if ($table == 'plantas') {
	$filtro = getFilter(true);
	if ($filtro == '') {
		// se não tiver filtro
		$q = "select count(*) from (select pl.pltag tag from pl left join det d on pl.det = d.id";
		$q = $q.$filtro.") as tbplantas";
	} else {
		// com filtro tem que permitir tax
		$q = "select count(*) from (select pl.id, pl.pltag tag, t.nome táxon, r.nome rank, gettax_fam(d.tax) família, gettax_gen(d.tax) gênero, gettax_sp(d.tax) espécie, l.nome localidade
from pl
left join det d on pl.det = d.id
left join loc l on pl.loc = l.id
left join tax t on t.id = d.tax
left join ranks r on r.id = t.rank
left join pess p on p.id = d.detby
left join prj on prj.id = pl.prj";
		$q = $q.$filtro.') as tbplantas';
	}
	$res = pg_query($conn,$q);
	$row = pg_fetch_row($res);
	$rowcount = $row[0];
	$q = "select pl.id, (select count(id) from imgitem ii where ii.tabid = pl.id and ii.tab = 'pl') as imgs, pl.pltag tag, t.id tax, t.nome táxon, r.nome rank, gettax_fam(d.tax) família, gettax_gen(d.tax) gênero, gettax_sp(d.tax) espécie, l.nome localidade, d.ano anodet,
 coalesce(p.abrev,'')||' ('||coalesce(p.prenome,'')||')' determinador, pl.lat, pl.lon, pl.alt, prj.nome projeto
from pl
left join det d on pl.det = d.id
left join loc l on pl.loc = l.id
left join tax t on t.id = d.tax
left join ranks r on r.id = t.rank
left join pess p on p.id = d.detby
left join prj on prj.id = pl.prj";
	$q = $q.getFilter(true);
	$mostrar = true;
} else
if ($table == 'locais') {
	$q = "select count(*) from (with locs as (
	select pl.loc, pl.id plid, null as eid from pl
		union all
		select e.loc, null as plid, e.id eid from esp e),
agg as (
	select
		locs.loc,
		count(locs.plid) plantas,
		count(locs.eid) especímenes
	from
		locs
	group by
		locs.loc)
select l.id from agg
left outer join loc l on agg.loc = l.id
where l.id > 0";
	$q = $q.getFilter(false);
	$q = $q.") as tblocais";
	$res = pg_query($conn,$q);
	$row = pg_fetch_row($res);
	$rowcount = $row[0];
	$q = "with locs as (
	select pl.loc, pl.id plid, null as eid from pl
		union all
		select e.loc, null as plid, e.id eid from esp e),
agg as (
	select
		locs.loc,
		count(locs.plid) plantas,
		count(locs.eid) especímenes
	from
		locs
	group by
		locs.loc)
select l.id,l.nome localidade,getloc(l.id,8) município,getloc(l.id,5) UF,getloc(l.id,3) país,l.lat,l.lon,lt.tipo,plantas,especímenes from agg
left outer join loc l on agg.loc = l.id
left join loctipo lt on l.tipo = lt.id
where l.id > 0";
	$q = $q.getFilter(false);
	$mostrar = true;
} else
if ($table == 'locais2') {
	$q = "select count(*) from (with locs as (
	select pl.loc, pl.id plid, null as eid from pl
		union all
		select e.loc, null as plid, e.id eid from esp e),
agg as (
	select
		locs.loc,
		count(locs.plid) plantas,
		count(locs.eid) especímenes
	from
		locs
	group by
		locs.loc)
select l.id from agg
left outer join loc l on agg.loc = l.id
where l.id > 0";
	$q = $q.getFilter(false);
	$q = $q.") as tblocais";
	$res = pg_query($conn,$q);
	$row = pg_fetch_row($res);
	$rowcount = $row[0];
	$q = "with locs as (
	select pl.loc, pl.id plid, null as eid from pl
		union all
		select e.loc, null as plid, e.id eid from esp e),
agg as (
	select
		locs.loc,
		count(locs.plid) plantas,
		count(locs.eid) especímenes
	from
		locs
	group by
		locs.loc)
select l.id,getloc(l.id) localidade,l.lat,l.lon,lt.tipo,plantas,especímenes from agg
left outer join loc l on agg.loc = l.id
left join loctipo lt on l.tipo = lt.id
where l.id > 0";
	$q = $q.getFilter(false);
	$mostrar = true;
} else
if ($table == 'frm') {
	$q = "select count(*) from $table where addby = ".$_SESSION['user_id']." or shared = 'S'";
	$q = $q.getFilter(true);
	$res = pg_query($conn,$q);
	$row = pg_fetch_row($res);
	$rowcount = $row[0];
	$q = "select * from $table where addby = ".$_SESSION['user_id']." or shared = 'S'";
	$q = $q.getFilter(true);
	$sorted = 'id';
	$mostrar = true;
} else
if ($table == 'msg') {
	$q = "select count(*) from msg";
	$q = $q.getFilter(true);
	$res = pg_query($conn,$q);
	$row = pg_fetch_row($res);
	$rowcount = $row[0];
	$q = "select msg.id,msg.msgfrom,msg.msgto,usr.namef,usr.namel,usr.username,usr.pess,usr.email,usr.accesslevel,
	msg.adddate,msg.thread,msg.lida,msg
	from msg,usr
	where usr.id = msg.msgfrom and
	(msg.msgfrom = ".$_SESSION['user_id']." or 
	msg.msgto = ".$_SESSION['user_id'].")";
	$q = $q.getFilter(true);
	//$sorted = 'thread';
	$mostrar = true;
} else
if ($table != 'none' && $table != 'tables') {
	$q = "select count(*) from $table";
	$q = $q.getFilter(true);
	$res = pg_query($conn,$q);
	$row = pg_fetch_row($res);
	$rowcount = $row[0];
	$q = "select * from $table";
	$q = $q.getFilter(true);
	$sorted = 'id';
	$mostrar = true;
}
// monta query - fim
/////////////////////////////////////////////////////
if (isset($_GET['sort'])) {
	$sorted = $_GET['sort'];
}
if (isset($_GET['sort2'])) {
	$sort = $_GET['sort2'];
}
if ($export && !$exportList) {
	$mostrar = false;
}
if ($mostrar) { // daqui vai até o final
	$desc = false;
	$direcao = 2;
	if (isset($sorted)) { // se tiver especifica a ordem
		if ($sorted != 'none') {
			if (substr($sorted,0,1) == '!') {
				$sorted = substr($sorted,1);
				$desc = true;
				$direcao = 1;
				$q = $q." ORDER BY $sorted desc";
			} else {
				$q = $q." ORDER BY $sorted";
				//echo "$q<BR><BR>";
			}
		} else // $sorted == 'none'
		if ($table == 'checklist') {
			$q = $q." order by táxon"; // entra aqui?
		}
	} else {
		if ($table == 'checklist') {
			$q = $q." order by táxon";
		}
		$sorted = 'none';
	}
	/////////////////////////////////////////////////////
	// LÊ CONFIGURAÇÃO
	/////////////////////////////////////////////////////
	$qCols = "select * from cfg where usr = $1";
	$res = pg_query_params($conn,$qCols,[$_SESSION['user_id']]);
	if ($res) {
		if ($row = pg_fetch_array($res,NULL,PGSQL_ASSOC)) {
			$colunas = [];
			foreach ($row as $key => $value) {
				switch ($table) {
					case 'checklist' :
						if (strpos($key,'tchk') === 0) {
							$colunas[substr($key,4)] = $value;
						}
						break;
					case 'especimenes' :
						if (strpos($key,'tesp') === 0) {
							$colunas[substr($key,4)] = $value;
						}
						break;
					case 'plantas' :
						if (strpos($key,'tpl') === 0) {
							$colunas[substr($key,3)] = $value;
						}
						break;
					case 'locais' :
						if (strpos($key,'tloc') === 0) {
							$colunas[substr($key,4)] = $value;
						}
						break;
				}
			}
		}
	}
	// lê configuração
	/////////////////////////////////////////////////////
	// onlyColunas - faz menu para selecionar colunas
	if ($onlycols) {
		$q = $q." LIMIT 1"; // é possível ir mais rápido com LIMIT 0?
		$res = pg_query($conn,$q);
		if ($res) {
			if ($row = pg_fetch_array($res,NULL,PGSQL_ASSOC)) {
				$cols = implode(',',$colunas);
				echo "[$cols]";
				foreach ($row as $key => $value) {
					if ($colunas[$key] == 'S') {
						echo "<input type='checkbox' id='chk$key' checked>$key<BR>";
					} else {
						echo "<input type='checkbox' id='chk$key'>$key<BR>";
					}
				}
			}
		}
	// onlycolunas
	/////////////////////////////////////////////////////
	} else { // ! $onlycols, daqui vai até o final
		/////////////////////////////////////////////////////
		// ITENS MARCADOS
		/////////////////////////////////////////////////////
		if (in_array($mark,['','showMark'])) {
			if (isset($limit)) {
				$q = $q." LIMIT $limit"; // se tiver especifica o limite de linhas a mostrar
			}
			if (isset($offset)) {
				$q = $q." OFFSET $offset"; // se tiver especifica o número de linhas a pular (paginação)
			}
		}
		// mostra o sql
		if ($_SESSION['user_id'] == $dev_userid) {
			echo "$q<BR>";
		}
		$res = pg_query($conn,$q); // executa a query que desenhará a tabela
		
		if (!in_array($mark,['','showMark'])) { // se tiver que marcar/desmarcar...
			if ($mark == 'unmarkAll') { // apaga o arquivo
				if (file_exists('usr/'.$_SESSION['user_id']."/mark/$table.txt")) {
					unlink('usr/'.$_SESSION['user_id']."/mark/$table.txt");
				}
			} else {
				$IDs = pg_fetch_all_columns($res); // pega todos os valores da coluna 0 (id)
				$oldMarks = readMarks($table);
				if ($mark == 'mark') { // marca
					$IDs = array_merge($IDs,$oldMarks);
					$IDs = array_unique($IDs);
				} else
				if ($mark == 'unmark') { // desmarca
					$IDs = array_diff($oldMarks,$IDs);
				}
				$selCount = count($IDs);
				sort($IDs);
				$fp = fopen('usr/'.$_SESSION['user_id']."/mark/$table.txt","wb"); // e salva
				fwrite($fp,implode(',',$IDs));
				fclose($fp);
			}
		} else { // daqui vai até o final
			$IDs = readMarks($table); //...senão só lê as marcas
			$colcount = pg_num_fields($res);
			if (!$export) { // se exportar o cabeçalho é outro
				////////////////////////////////////////////////////////////////
				// DESENHA A TABELA - Cabeçalho - nomes das colunas+botões
				////////////////////////////////////////////////////////////////
				echo "<table id='tblMain' border='1'><tr id='tr1Main' style='background-color:#".$_SESSION['cfg.cortbh']."' onMouseMove='row1MouseMove(event)' onMouseDown='row1MouseDown(event)' onMouseUp='row1MouseUp(event)' onContextMenu='showMenu(event,\"$table\");return false;'>";
				echo "<td>Edit</td>";
				echo "<td>Img</td>";
				for ($i=0; $i<$colcount; $i++) { // botões
					$colname = pg_field_name($res,$i);
					if ($colname != 'imgs') {
						if ($sort == $colname || $sort == "!$colname") {
							echo "<td><button type='button' id='btnCol_$colname' name='sort' value='!$colname' onclick='btnColClick(this,$direcao,\"$table\")'><strong>$colname</strong></button></td>\n";
						} else {
							echo "<td><button type='button' id='btnCol_$colname' name='sort' value='$colname' onclick='btnColClick(this,1,\"$table\")'>$colname</button></td>\n";
						}
					}
				}
				echo "</tr>";
				// Cabeçalho da tabela - filtros
				echo "<tr id='tr2Main' style='background-color:#".$_SESSION['cfg.cortbh']."'>";
				echo "<td><button type='button' id='btnSelect' onclick='mnuSelect(event,$rowcount,\"$table\",\"$sort\")'>*</button></td>";
				echo "<td>&nbsp;</td>";
				$filtros = array();
				for ($i=0; $i<$colcount; $i++) {
					$colname = pg_field_name($res,$i);
					if ($colname != 'imgs') {
						$filterName = "txtFilter$colname";
						if (isset($_GET[$filterName])) {
							//$filterValue = addslashes($_GET[$filterName]);
							//$filterValue = str_replace("'","\'",$_GET[$filterName]);
							$filterValue = $_GET[$filterName];
							$filtros[$colname] = $filterValue;
							echo "<td><input id='txtFilter$colname' name='txtFilter$colname' type='text' class='shorter' onkeypress='txtFilterKeyPress(event,\"$sort\",".(3-$direcao).")' value='$filterValue' /></td>\n";
						} else {
							echo "<td><input id='txtFilter$colname' name='txtFilter$colname' type='text' class='shorter' onkeypress='txtFilterKeyPress(event,\"$sort\",".(3-$direcao).")' /></td>\n"; // $sort carrega o !, $sorted não
						}
					}
				}
				echo "</tr>";
			} else {
				$cols = [];
				for ($i=0; $i<$colcount; $i++) { // botões
					$cols[] = pg_field_name($res,$i);
				}
				fputcsv($fp,$cols,'	'); // cabeçalho para exportação
			}
			////////////////////////////////////////////////////////////////
			// DESENHA A TABELA - Corpo - linhas de dados
			////////////////////////////////////////////////////////////////
			while ($row = pg_fetch_array($res,NULL,PGSQL_ASSOC)) {
				if (!$export) {
					if ($table == 'msg' && $row['lida'] == 'N') {
						echo "<tr style='color:#F00'>";
					} else {
						echo "<tr>";
					}
					echo "<td><span style='white-space:nowrap'>";
					echo "<input type='checkbox' name='chk$row[id]' onclick='chkClick(this,event,\"$table\")' ";
					if (in_array($row['id'],$IDs)) {
						echo 'checked ';
					}
					echo "/>";
					$l = 16;
					switch ($table) {
						case 'checklist' :
							echo "<img src='icon/edit.png' height=$l width=$l style='cursor:pointer' onclick='taxEdit($row[id])' title='Editar registro'>";
							echo "<img src='icon/mobot.png' height=$l width=$l style='cursor:pointer' onclick='taxMobot(\"$row[táxon]\")'>";
							break;
						case 'especimenes' :
							echo "<img src='icon/edit.png' height=$l width=$l style='cursor:pointer' onclick='espEdit($row[id])' title='Editar registro'>";
							break;
						case 'plantas' :
							echo "<img src='icon/edit.png' height=$l width=$l style='cursor:pointer' onclick='plEdit($row[id])' title='Editar registro'>";
							break;
						case 'locais' :
							echo "<img src='icon/edit.png' height=$l width=$l style='cursor:pointer' onclick='locEdit($row[id])' title='Editar registro'>";
							break;
						case 'frm' :
							echo "<img src='icon/edit.png' height=$l width=$l style='cursor:pointer' onclick='anyEdit(\"$table\",$row[id])' title='Editar registro'>";
							echo " <img src='icon/dupl.png' style='cursor:pointer' onclick='duplFrm($row[id],\"$row[nome]\")' title='Duplicar registro'>";
							if ($row['addby'] == $_SESSION['user_id']) {
								echo " <img src='icon/redx.png' style='cursor:pointer' onclick='eraseRec(\"$table\",$row[id])' title='Excluir registro'>";
							}
							break;
						case 'msg' :
							if ($row['msgto'] == $_SESSION['user_id']) {
								echo "<img src='icon/edit.png' height=$l width=$l style='cursor:pointer' onclick='replyMsg($row[thread],$row[msgfrom])' title='Responder mensagem'>";
							}
							break;
						default :
							echo "<img src='icon/edit.png' height=$l width=$l style='cursor:pointer' onclick='anyEdit(\"$table\",$row[id])' title='Editar registro'>";
					}
					echo "</span></td>";
					if (isset($row['imgs']) && $row['imgs'] > 0) {
						echo "<td><img src='icon/img.png' style='cursor:pointer' onclick='callAdd(\"img.php?tab=$table&tabid=$row[id]\",1000,0,0,1)'></td>";
					} else {
						echo "<td>&nbsp;</td>";
					}
					foreach($row as $key => $cell) {
						if ($key != 'imgs') {
							if ($cell == null) {
								echo "<td>&nbsp;</td>"; // insere um espaço em branco (selecionável) se for nulo
							} else {
								// INSERIR lang, offset, limit e sort CORRETOS ABAIXO !!
								if ($table == 'checklist' && $key == 'especímenes') {
									$id = $row['id'];
									echo "<td><a href='main.php?lang=BR&offset=0&limit=100&table=especimenes&sort=id&txtFiltertax==$id'>$cell</a></td>";
								} else
								if ($table == 'checklist' && $key == 'plantas') {
									$id = $row['id'];
									echo "<td><a href='main.php?lang=BR&offset=0&limit=100&table=plantas&sort=id&txtFiltertax==$id'>$cell</a></td>";
								} else
								if ($table == 'especimenes' && $key == 'localidade') {
									$loc = $row['loc'];
									echo "<td><a href='main.php?table=locais2&txtFilterid==$loc'>$cell</a></td>";
								} else {
									echo "<td>$cell</td>";
								}
							}
						}
					}
					echo "</tr>\n";
					if ($table == 'msg' && $row['msgto'] == $_SESSION['user_id'] && $row['lida'] == 'N') { // se é pra mim e não tinha sido lida (ainda)...
						$qUpd = "update msg set lida = 'S' where id = $row[id]"; // ...agora foi lida
						$resUpd = pg_query($conn,$qUpd);
						if ($resUpd) {
							// sucesso
						} else {
							// falha
						}
					}
					// DESENHA A TABELA - Corpo - linhas de dados
					////////////////////////////////////////////////////////////////
				} else {
					fputcsv($fp,$row,'	'); // if $export
				}
			} // fim do while
			////////////////////////////////////////////////////////////////
			// DESENHA A TABELA - Rodapé
			////////////////////////////////////////////////////////////////
			if (!$export) {
				echo "</table>";
			} else {
				fclose($fp);
			}
		}
	}
}
?>
