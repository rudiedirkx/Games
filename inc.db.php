<?php

require DB_LIB_PATH . '/db_mysql.php';
$db = db_mysql::open(array('user' => DB_USER, 'pass' => DB_PASS, 'db' => DB_NAME));

if ( !$db ) {
	exit('No connecto!');
}
