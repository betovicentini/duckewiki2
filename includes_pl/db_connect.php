<?php
include_once 'psl-config.php';   // As functions.php is not included  (acho que não precisava mais do functions.php)
$conn = pg_connect('host='.HOST.' dbname='.DATABASE.' user='.USER.' password='.PASSWORD) or die("Erro ao conectar!");
pg_set_error_verbosity($conn,PGSQL_ERRORS_VERBOSE); // PGSQL_ERRORS_TERSE PGSQL_ERRORS_DEFAULT
