<?php

require_once("json_php".(int)PHP_VERSION.".php");

$g_arrMaps = array(
	array(
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1, 2, 1, 1, 2,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1, 2, 0, 0, 1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1, 4, 1, 0, 1, 1, 2, 1, 2,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1, 1, 0, 0, 0, 0, 0, 1, 2,-1,-1,-1,-1),
		array(-1,-1,-1,-1, 2, 0, 0, 0, 1, 1, 1, 1,-1,-1,-1,-1),
		array(-1,-1,-1,-1, 2, 1, 2, 1, 2,-1, 2, 1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
	),
	array(
		array(-1,-1,-1,-1, 1, 0, 0, 0, 0, 1,-1,-1,-1,-1, 1, 0),
		array(-1,-1,-1,-1, 1, 0, 0, 0, 0, 1, 1, 2,-1,-1, 2, 0),
		array(-1,-1,-1,-1, 4, 2, 1, 0, 0, 0, 0, 1,-1,-1, 1, 0),
		array(-1,-1,-1,-1,-1,-1, 1, 0, 0, 0, 0, 1, 2, 2, 1, 0),
		array(-1,-1, 2, 2, 3, 3, 3, 2, 1, 0, 0, 0, 0, 0, 0, 0),
		array( 1, 1, 1, 0, 0, 1,-1,-1, 2, 2, 2, 1, 0, 0, 0, 0),
		array( 0, 0, 0, 0, 0, 1, 2, 3, 3,-1,-1, 3, 1, 0, 0, 0),
		array( 1, 1, 1, 0, 0, 0, 0, 1,-1,-1,-1,-1, 1, 0, 0, 0),
		array(-1,-1, 2, 0, 1, 1, 1, 1,-1,-1, 4,-1, 2, 0, 0, 0),
		array( 2,-1, 2, 0, 1,-1,-1,-1,-1,-1,-1,-1, 1, 0, 0, 0),
		array( 3, 3, 2, 0, 1,-1,-1,-1,-1,-1,-1, 1, 1, 0, 0, 0),
		array(-1,-1, 3, 2, 1,-1,-1,-1,-1,-1,-1, 1, 0, 1, 1, 1),
		array( 5,-1,-1,-1,-1,-1, 2,-1,-1,-1,-1, 2, 1, 1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1, 1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1, 1),
	),
	array(
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1, 2, 1, 1, 1, 1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1, 2, 1, 1, 0, 0, 1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1, 1, 0, 0, 0, 0, 1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1, 2, 1, 0, 0, 1, 1,-1, 1, 1, 2,-1,-1,-1,-1,-1),
		array(-1,-1, 3, 2, 1, 2,-1,-1, 1, 0, 1,-1, 1, 2,-1,-1),
		array(-1,-1,-1, 2,-1,-1,-1,-1, 1, 0, 1, 1, 1, 2,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1, 2, 0, 0, 0, 0, 2, 2,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1, 1, 0, 0, 1, 1, 2,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1, 2, 1, 1, 1,-1, 3, 2,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
	),
	array(
		array( 0, 0, 0, 1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array( 0, 0, 0, 1, 1, 2,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array( 0, 0, 0, 0, 0, 1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array( 0, 0, 0, 1, 1, 1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array( 0, 0, 0, 1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array( 0, 0, 0, 1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array( 1, 1, 1, 1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1, 4, 7,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
	),
	array(
		array(-1, 1, 1,-1, 1, 1,-1, 1, 1,-1, 1, 0, 1, 1, 1, 0, 0, 0, 0, 0, 0, 1,-1, 1, 0, 1,-1,-1,-1,-1),
		array( 1, 1, 1, 1, 1, 1, 1, 1, 2, 2, 2, 1, 3,-1, 2, 1, 1, 1, 0, 0, 0, 1, 1, 1, 0, 1,-1,-1,-1,-1),
		array( 0, 0, 0, 0, 1, 1, 1, 0, 1,-1, 1, 1,-1,-1, 2, 1,-1, 2, 1, 1, 0, 0, 1, 1, 2, 2,-1,-1,-1,-1),
		array( 0, 0, 1, 1, 2,-1, 1, 0, 1, 1, 1, 1, 2, 2, 1, 1, 2, 3,-1, 3, 3, 2, 2,-1, 3,-1,-1,-1,-1,-1),
		array( 0, 0, 1,-1, 2, 1, 1, 1, 2, 2, 1, 0, 0, 0, 0, 0, 1,-1, 3,-1,-1,-1, 3, 3,-1,-1,-1,-1,-1,-1),
		array( 0, 0, 1, 1, 1, 0, 1, 2,-1,-1, 2, 0, 0, 0, 1, 2, 3, 2, 2, 3,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array( 0, 0, 0, 0, 0, 0, 1,-1, 4,-1, 2, 0, 0, 1, 3,-1,-1, 2, 2, 3,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array( 0, 0, 0, 0, 0, 0, 2, 2, 3, 1, 1, 0, 0, 1,-1,-1, 5,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array( 1, 1, 0, 0, 1, 1, 2,-1, 1, 0, 0, 0, 0, 1, 4,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1, 3, 2, 1, 2,-1, 3, 1, 1, 1, 1, 1, 1, 1, 4,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1, 3, 1, 1, 1,-1, 1, 1,-1, 3,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1, 1, 1,-1, 1, 1, 1,-1,-1,-1,-1,-1,-1,-1, 2,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1, 2,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
	),
	array(
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1, 4,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1, 1, 2, 1, 2,-1, 2,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1, 1, 0, 0, 1, 1, 2,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1, 3, 1, 0, 0, 0, 2,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1, 1, 0, 0, 0, 2,-1,-1,-1, 2,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1, 1, 0, 0, 0, 1, 1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1, 2, 1, 1, 0, 0, 1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1, 2, 1, 1, 1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1, 3,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1, 2,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
	),
	array(
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1, 1, 1, 1, 2, 2, 4,-1, 2, 1, 1, 1, 0, 0, 0, 1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1, 2, 0, 0, 0, 0, 1,-1, 1, 0, 0, 0, 0, 0, 0, 1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1, 3, 1, 1, 0, 0, 1,-1, 2, 1, 2, 2, 2, 2,-1, 1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1, 1, 0, 0, 1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1, 2, 0, 0, 1, 2,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1, 2, 0, 1, 1, 3,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1, 2, 1, 2,-1, 4,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
	),
	array(
		array( 0, 0, 0, 0, 0, 0, 1,-1,-1, 2, 2,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array( 0, 0, 0, 1, 1, 1, 1, 3, 4,-1, 3,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array( 0, 0, 0, 1,-1, 1, 0, 1,-1, 3,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array( 0, 0, 0, 2, 2, 2, 0, 1, 1, 2, 2,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array( 1, 1, 0, 1,-1, 1, 1, 1, 2, 1, 3,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1, 2, 0, 2, 3, 3, 2,-1, 2,-1, 2,-1,-1,-1,-1,-1,-1,-1,-1,-1, 3, 2, 1, 1,-1,-1,-1,-1,-1,-1),
		array(-1, 2, 0, 1,-1,-1, 2, 1, 2, 2, 3,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1, 1, 0, 1,-1,-1,-1, 3, 3, 2),
		array( 1, 1, 1, 2, 4, 3, 3, 2, 2, 2,-1, 2, 2,-1,-1,-1,-1,-1,-1,-1, 3, 1, 1, 2,-1,-1, 3,-1, 1, 0),
		array( 0, 0, 1,-1, 3,-1, 5,-1,-1, 3, 2, 1, 2, 3,-1,-1,-1,-1,-1,-1, 2, 1, 1,-1, 3, 3,-1, 2, 1, 0),
		array( 0, 0, 2, 2, 4,-1,-1,-1, 5,-1, 3, 1, 2,-1, 3,-1,-1, 3, 2, 2,-1, 2, 2, 2,-1, 2, 1, 1, 0, 0),
		array( 0, 0, 1,-1, 2, 2, 3, 2, 3,-1, 3,-1, 3, 3,-1, 2, 2,-1, 1, 2, 3,-1, 1, 1, 1, 1, 0, 0, 0, 0),
		array( 0, 0, 1, 1, 1, 0, 0, 1, 2, 3, 3, 2, 2,-1, 2, 1, 1, 1, 2, 2,-1, 2, 1, 0, 0, 1, 1, 1, 1, 1),
		array( 0, 0, 1, 1, 1, 0, 1, 2,-1, 2,-1, 1, 2, 2, 2, 0, 0, 0, 1,-1, 3, 2, 0, 0, 0, 1,-1, 1, 1,-1),
		array( 0, 0, 2,-1, 4, 3, 3,-1, 2, 2, 1, 1, 1,-1, 1, 1, 1, 1, 1, 2,-1, 1, 1, 1, 1, 2, 2, 2, 2, 2),
		array( 0, 0, 2,-1,-1,-1,-1, 2, 1, 0, 0, 0, 1, 1, 1, 1,-1, 2, 1, 2, 1, 1, 1,-1, 2, 3,-1, 3, 2,-1),
		array( 0, 0, 1, 3,-1,-1, 3, 1, 0, 0, 0, 0, 0, 0, 0, 1, 1, 2,-1, 1, 0, 0, 1, 2,-1, 3,-1, 3,-1, 2),
	),
	array(
		array( 1,-1, 1, 0, 0, 0, 1, 1, 2,-1,-1, 1, 0, 0, 0, 0, 1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array( 1, 1, 1, 0, 1, 2, 4,-1, 3, 2, 2, 1, 0, 0, 1, 1, 2, 3,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array( 0, 1, 1, 2, 2,-1,-1,-1, 2, 0, 0, 0, 0, 0, 1,-1, 1, 2,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array( 0, 1,-1, 2,-1, 3, 3, 2, 1, 1, 1, 1, 1, 2, 3, 2, 1, 1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array( 0, 1, 1, 2, 1, 1, 0, 0, 0, 1,-1, 1, 1,-1,-1, 1, 1, 2,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array( 1, 2, 3, 2, 1, 1, 1, 1, 0, 2, 2, 2, 1, 2, 2, 1, 1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array( 1,-1,-1,-1, 3, 2,-1, 1, 0, 1,-1, 1, 0, 0, 0, 0, 1, 3,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array( 1, 2, 4,-1, 3,-1, 2, 1, 0, 1, 1, 1, 0, 0, 1, 1, 1, 2,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array( 0, 0, 1, 2, 3, 2, 1, 0, 0, 1, 1, 2, 1, 1, 1,-1, 2, 2,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array( 0, 0, 0, 2,-1, 2, 0, 0, 1, 2,-1, 3,-1, 1, 1, 2,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array( 0, 0, 0, 2,-1, 2, 0, 1, 2,-1, 4,-1, 3, 2, 1, 3,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array( 0, 0, 0, 1, 1, 1, 0, 2,-1, 3, 3,-1, 3, 2,-1, 3,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array( 0, 0, 1, 2, 2, 1, 0, 3,-1, 3, 1, 1, 2,-1, 3,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array( 1, 2, 3,-1,-1, 2, 1, 2,-1, 3, 1, 1, 2, 2,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1, 5,-1, 2, 2, 3,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
		array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
	),
);

$arrMap = isset($_GET['map'], $g_arrMaps[$_GET['map']]) ? $g_arrMaps[$_GET['map']] : reset($g_arrMaps);
if ( isset($_POST['field']) && is_array($_POST['field']) )
{
	$arrMap = $_POST['field'];
	foreach ( $arrMap AS $k => $m ) $arrMap[$k] = array_map(create_function('$f','return (int)$f;'), $m);
}

$g_arrSides = array(count($arrMap), count($arrMap[0]));

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>

<head> 
<title>MS 2c - Test - Board Analysis</title>
<script type="text/javascript" src="general_1_2_1.js"></script>
<script type="text/javascript">
<!--//

var Minesweeper = {
	m_arrImgs : {
		'-1' : 'images/dicht.gif',
		'0' : 'images/open_0.gif',
		'1' : 'images/open_1.gif',
		'2' : 'images/open_2.gif',
		'3' : 'images/open_3.gif',
		'4' : 'images/open_4.gif',
		'5' : 'images/open_5.gif',
		'6' : 'images/open_6.gif',
		'7' : 'images/open_7.gif',
		'8' : 'images/open_8.gif',
		'm' : 'images/open_m.gif',
		'x' : 'images/open_x.gif',
		'f' : 'images/flag.gif',
		'w' : 'images/open_w.gif',
		'?' : 'images/qmark.gif'
	},

	m_arrBoard : <?php echo str_replace("],[", "],\n\t\t[", JSON::encode($arrMap)); ?>,

	m_arrSides : <?php echo JSON::encode($g_arrSides); ?>,

	m_arrKnownMines : {},

	m_arrDefiniteNoNoMines : {},

	mf_SaveAllMines : function() {
		iKnownMines = sizeof(Minesweeper.m_arrKnownMines);
		Minesweeper.mf_SaveAllMinesThisRound();
		if ( sizeof(Minesweeper.m_arrKnownMines) > iKnownMines ) {
			Minesweeper.mf_EliminateFields();
			return Minesweeper.mf_SaveAllMines();
		}
		return true;
	},

	mf_SaveAllMinesThisRound : function() {
		for ( var i=0; i<Minesweeper.m_arrBoard.length; i++ ) {
			for ( var j=0; j<Minesweeper.m_arrBoard[i].length; j++ ) {
				szId = 'tile_' + i + '_' + j;
				if ( $(szId) && 'number' == typeof Minesweeper.m_arrBoard[i][j] && 0 < Minesweeper.m_arrBoard[i][j] ) {
					Minesweeper.mf_AnalyseOneField( [i, j], Minesweeper.m_arrBoard[i][j] );
				}
			}
		}
		return true;
	},

	mf_AnalyseOneField : function( f_arrCoords, f_szTile ) {
		szTile = f_szTile;
		arrSurrounders = Minesweeper.mf_GetSurroundingTiles( f_arrCoords, false );
		iClosedTiles = Minesweeper.mf_CountClosedTiles( arrSurrounders );
		if ( szTile == iClosedTiles ) {
			for ( L in arrSurrounders ) {
				c = arrSurrounders[L];
				id = 'tile_' + c[0] + '_' + c[1];
				if ( -1 == Minesweeper.mf_GetTile(c) && "undefined" == typeof Minesweeper.m_arrDefiniteNoNoMines[id] ) {
					// save mine in KnownMines object
					Minesweeper.m_arrKnownMines[id] = true;
				}
			}
		}
		return true;
	},

	mf_EliminateFields : function() {
		for ( var i=0; i<Minesweeper.m_arrBoard.length; i++ ) {
			for ( var j=0; j<Minesweeper.m_arrBoard[i].length; j++ ) {
				id = 'tile_' + i + '_' + j;
				if ( $(id) && 'number' == typeof Minesweeper.m_arrBoard[i][j] && 0 < Minesweeper.m_arrBoard[i][j] ) {
					Minesweeper.mf_EliminateFieldsAround( [i, j], Minesweeper.m_arrBoard[i][j] );
				}
			}
		}
		return true;
	},

	mf_EliminateFieldsAround : function( f_arrCoords, f_szTile ) {
		szTile = f_szTile;
		if ( "number" != typeof szTile ) {
			return true;
		}
		arrSurrounders = Minesweeper.mf_GetSurroundingTiles( f_arrCoords, false );
		iMinesInSurrounders = 0;
		for ( L in arrSurrounders ) {
			c = arrSurrounders[L];
			id = 'tile_'+c[0]+'_'+c[1];
			if ( "undefined" != typeof Minesweeper.m_arrKnownMines[id] ) {
				iMinesInSurrounders++;
			}
		}
		if ( szTile == iMinesInSurrounders ) {
			// All mines collected! So eliminate other surrounding fields!
			for ( L in arrSurrounders ) {
				c = arrSurrounders[L];
				id = 'tile_'+c[0]+'_'+c[1];
				if ( Minesweeper.mf_GetTile(c) == -1 && "undefined" == typeof Minesweeper.m_arrKnownMines[id] ) {
					Minesweeper.m_arrDefiniteNoNoMines[id] = true;
				}
			}
		}
	},

	mf_MarkSavedMines : function() {
		for ( szTileId in Minesweeper.m_arrKnownMines ) {
			$(szTileId).src = Minesweeper.m_arrImgs['f'];
		}
		return true;
	},

	mf_MarkNonoMines : function() {
		for ( szTileId in Minesweeper.m_arrDefiniteNoNoMines ) {
			$(szTileId).src = Minesweeper.m_arrImgs['w'];
		}
		return true;
	},

	mf_CountClosedTiles : function( f_objTiles ) {
		iClosedTiles = 0;
		for ( L in f_objTiles ) {
			c = f_objTiles[L];
			id = 'tile_'+c[0]+'_'+c[1];
			if ( -1 == Minesweeper.mf_GetTile(c) && "undefined" == typeof Minesweeper.m_arrDefiniteNoNoMines[id] ) {
				iClosedTiles++;
			}
		}
		return iClosedTiles;
	},

	mf_GetCoords : function( f_objTile ) {
		c = f_objTile.id.split("_");
		return [ c[1], c[2] ];
	},

	mf_GetSurroundingTiles : function( f_arrCoords, f_bReturnTiles ) {
		if ( "undefined" == typeof f_bReturnTiles ) {
			f_bReturnTiles = true;
		}
		arrSurrounders = {};
		var s = {t:[-1,0],tr:[-1,1],r:[0,1],br:[1,1],b:[1,0],bl:[1,-1],l:[0,-1],tl:[-1,-1]};
		for ( d in s ) {
			c = [ parseInt(f_arrCoords[0])+s[d][0], parseInt(f_arrCoords[1])+s[d][1] ];
			t = Minesweeper.mf_GetTile(c);
			if ( "number" == typeof t ) arrSurrounders[d] = f_bReturnTiles ? t : c;
		}
		return arrSurrounders;
	},

	mf_GetTile : function( f_arrCoords ) {
		i = f_arrCoords[0];
		j = f_arrCoords[1];
		if ( "undefined" != typeof Minesweeper.m_arrBoard[i] && "undefined" != typeof Minesweeper.m_arrBoard[i][j] ) {
			return Minesweeper.m_arrBoard[i][j];
		}
		return false;
	}

}; // END Class Minesweeper

var sizeof = function( f_arrSource )
{
	if ( "undefined" != typeof f_arrSource.length )
	{
		// Length is defined
		return f_arrSource.length;
	}

	if ( !f_arrSource || "object" != typeof f_arrSource )
	{
		// It's not a JS object, so no properties to count
		return false;
	}

	// It's an object (manual keys) so gotta count
	iLength = 0;
	for ( x in f_arrSource )
	{
		iLength++;
	}
	return iLength;
};

//-->
</script>
<style type="text/css">
* {
	margin			: 0;
	padding			: 0;
}
p {
	margin			: 5px 0;
}
div#board {
	font-size		: 1px;
	line-height		: 10%;
}
div#board img {
	width			: 16px;
	height			: 16px;
	border			: 0;
	margin			: 0;
	padding			: 0;
}
div.br {
	margin			: 0;
	clear			: both;
}
</style>
</head>

<body>

<select style="margin:10px;margin-bottom:0;" onchange="if(this.value){document.location='?map='+this.value;}"><?php

for ( $i=0; $i<count($g_arrMaps); $i++ )
{
	echo '<option value="'.$i.'"'.( $arrMap == $g_arrMaps[$i] ? ' selected="selected"' : '' ).'>Map '.(1+$i).' ('.count($g_arrMaps[$i][0]).'*'.count($g_arrMaps[$i]).')</option>';
}

?></select>

<div style="width:<?php echo 16*$g_arrSides[1]+26; ?>px;border:solid 1px #777;margin:10px;">
<div style="width:<?php echo 16*$g_arrSides[1]+6; ?>px;border:solid 10px #bbb;">
<div style="width:<?php echo 16*$g_arrSides[1]; ?>px;border-style:solid;border-width:3px;border-color:#777 #eee #eee #777;" id="board">
<?php

$arrTiles = array(-1 => 'dicht');
for ( $i=0; $i<$g_arrSides[0]; $i++ )
{
	for ( $j=0; $j<$g_arrSides[1]; $j++ )
	{
		$iTile = $arrMap[$i][$j];
		$szTile = isset($arrTiles[$iTile]) ? $arrTiles[$iTile] : 'open_'.$iTile;
		echo '<img id="tile_'.$i.'_'.$j.'" src="images/'.$szTile.'.gif" title="'.$i.','.$j.'" />';
	}
	echo '<div class="br"></div>';
}

?>
</div>
</div>
</div>

<div style="margin:10px;margin-top:0;">
<p>
	<input type="button" value="SaveAllMines()" onclick="Minesweeper.mf_SaveAllMines();" /><br/>
	<input type="button" value="MarkSavedMines()" onclick="Minesweeper.mf_MarkSavedMines();" />
</p>

<p>
	<input type="button" value="SaveAllMines() + MarkSavedMines()" onclick="Minesweeper.mf_SaveAllMines();Minesweeper.mf_MarkSavedMines();" />
</p>

<p>
	<input type="button" value="MarkNonoMines()" onclick="Minesweeper.mf_MarkNonoMines();" />
</p>

<!--<div id="debug" style="border:solid 1px #000;height:200px;overflow:scroll;"></div>-->
</div>

</body>

</html>