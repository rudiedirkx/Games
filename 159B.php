<?php
// ATOMIX | BUILDER

$title = 'ATOMIX';
$javascript = 'atomix';
$bodyClass = 'atomix';
$types = [
	'wall' => 'Wall',
	'H' => ['Hydrogen', ['data-atom' => 'H']],
	'O' => ['Oxygen', ['data-atom' => 'O']],
	'C' => ['Carbon', ['data-atom' => 'C']],
];
$jsClass = 'Atomix';

require 'gridgame-builder.php';
