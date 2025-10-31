<?php
	   define("PG_DB"  , "test_nu");
	   define("PG_HOST", "103.29.189.33"); 
	   define("PG_USER", "nu");
	   define("PG_PORT", "5432"); 
	   #define("PG_PASS", "adminagi");
	   define("PG_PASS", "P@ssw0rd"); 

$con = pg_connect("dbname=".PG_DB." host=".PG_HOST." port=".PG_PORT." password=".PG_PASS." user=".PG_USER) or die("Can't Connect Server");

pg_query("SET client_encoding = 'utf-8'");

?>
