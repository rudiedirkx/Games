<?php

$db = db_mysql::open(array('user' => DB_USER, 'pass' => DB_PASS, 'db' => DB_NAME));

if ( !$db ) {
	exit('No connecto!');
}

$db->ensureSchema(require 'inc.db-schema.php');
