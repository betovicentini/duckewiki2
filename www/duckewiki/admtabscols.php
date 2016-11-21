<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';
sec_session_start();
$q = "select table_name from information_schema.tables 
where table_schema = 'public'
order by table_name";
$res = pg_query($conn,$q);
if ($res) {
	echo "<textarea style='height:500px'>";
	while ($row = pg_fetch_array($res,NULL,PGSQL_NUM)) {
		echo "$row[0]=\n";
		$q1 = "select column_name
		from information_schema.columns
		where table_schema = 'public' and table_name = $1
		order by ordinal_position";
		$res1 = pg_query_params($conn,$q1,[$row[0]]);
		if ($res1) {
			while ($row1 = pg_fetch_array($res1,NULL,PGSQL_NUM)) {
				if (!in_array($row1[0],['id','addby','adddate'])) {
					echo "$row[0].$row1[0]=\n";
				}
			}
		}
	}
	echo "</textarea>";
}
?>
