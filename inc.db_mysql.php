<?php

function db_connect($h,$u,$p,$d) {
	$c = mysql_connect($h,$u,$p) or die('<b>'.mysql_error().'</b>');
	mysql_select_db($d,$c) or die('<b>'.mysql_error().'</b>');
	return $c;
}

function db_set($c) {
	global $g_db;
	$g_db = $c;
	return $c;
}

function db_insert_id() {
	global $g_db;
	return mysql_insert_id($g_db);
}

function db_affected_rows() {
	global $g_db;
	return mysql_affected_rows($g_db);
}

function db_error() {
	global $g_db;
	return mysql_error($g_db);
}

function db_errno() {
	global $g_db;
	return mysql_errno($g_db);
}

function db_select($tbl, $where = '') {
	return db_fetch('SELECT * FROM '.$tbl.( $where ? ' WHERE '.$where : '' ).';');
}

function db_fetch($query) {
	$r = db_query($query);
	if ( !$r ) {
		return false;
	}
	$a = array();
	while ( $l = mysql_fetch_assoc($r) ) {
		$a[] = $l;
	}
	return $a;
}

function db_fetch_fields($query) {
	$r = db_query($query);
	if ( !$r ) {
		return false;
	}
	$a = array();
	while ( $l = mysql_fetch_row($r) ) {
		$a[$l[0]] = $l[1];
	}
	return $a;
}

function db_select_one($tbl, $field, $where = '') {
	$r = db_query('SELECT '.$field.' FROM '.$tbl.( $where ? ' WHERE '.$where : '' ).' LIMIT 1;');
	if ( !$r ) {
		return false;
	}
	return 0 < mysql_num_rows($r) ? mysql_result($r, 0) : false;
}

function db_max($tbl, $field, $where = '') {
	return db_select_one($tbl, 'MAX('.$field.')', $where);
}

function db_min($tbl, $field, $where = '') {
	return db_select_one($tbl, 'MIN('.$field.')', $where);
}

function db_count($tbl, $where = '') {
	return db_select_one($tbl, 'COUNT(1)', $where);
}

function db_select_by_field($tbl, $field, $where = '') {
	$r = db_query('SELECT * FROM '.$tbl.( $where ? ' WHERE '.$where : '' ).';');
	if ( !$r ) {
		return false;
	}
	$a = array();
	while ( $l = mysql_fetch_assoc($r) ) {
		$a[$l[$field]] = $l;
	}
	return $a;
}

function db_select_fields($tbl, $fields, $where = '') {
	$r = db_query('SELECT '.$fields.' FROM '.$tbl.( $where ? ' WHERE '.$where : '' ).';');
	if ( !$r ) {
		return false;
	}
	$a = array();
	while ( $l = mysql_fetch_row($r) ) {
		$a[$l[0]] = $l[1];
	}
	return $a;
}

function db_replace_into($tbl, $values) {
	foreach ( $values AS $k => $v ) {
		if ( $v === null ) {
			$values[$k] = 'NULL';
		}
		else if ( 'NOW()' == $v ) {
			$values[$k] = 'NOW()';
		}
		else {
			$values[$k] = "'".addslashes($v)."'";
		}
	}
	return db_query('REPLACE INTO '.$tbl.' (`'.implode('`,`', array_keys($values)).'`) VALUES ('.implode(",", $values).');');
}

function db_insert($tbl, $values) {
	foreach ( $values AS $k => $v ) {
		if ( $v === null ) {
			$values[$k] = 'NULL';
		}
		else if ( 'NOW()' == $v ) {
			$values[$k] = 'NOW()';
		}
		else {
			$values[$k] = "'".addslashes($v)."'";
		}
	}
	return db_query('INSERT INTO '.$tbl.' (`'.implode('`,`', array_keys($values)).'`) VALUES ('.implode(",", $values).');');
}

function db_update($tbl, $update, $where = '') {
	if ( !is_string($update) ) {
		$u = '';
		foreach ( (array)$update AS $k => $v ) {
			$u .= ','.$k.'='.( null === $v || 'NOW()' == $v ? ( null === $v ? 'NULL' : (string)$v ) : '\''.addslashes((string)$v).'\'' );
		}
		$update = substr($u, 1);
	}
	return db_query('UPDATE `'.$tbl.'` SET '.$update.( $where ? ' WHERE '.$where : '' ).';');
}

function db_delete($tbl, $where) {
	return db_query('DELETE FROM `'.$tbl.'` WHERE '.$where.';');
}

function db_query($query) {
	global $g_db, $g_iQueries;
	$r = mysql_query($query, $g_db) /*or die('ERROR"'.$query.'"<br /><b>'.mysql_error().'</b>')*/;
	if ( !isset($g_iQueries) ) {
		$g_iQueries = 1;
	}
	else {
		$g_iQueries++;
	}
	return $r;
}

?>