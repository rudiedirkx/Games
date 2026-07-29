<?php

[$columns, $boards] = require '191_levels.php';

$columns[1][7] = 2; // First time col H gets 2 points in kok2, not 1 like kok1

$boards = array_filter($boards, function($board) {
	return !empty($board['gold']);
});

$rowRewards = ['die', 'bomb', 'heart', 'bomb', 'die', 'heart', 'bomb'];

$dieRows = count(array_filter($rowRewards, function($r) {
	return $r == 'die';
}));
$goldCount = count(reset($boards)['gold'] ?? []);
$uses = 1 + $goldCount + 1 + $dieRows;

$config = [
	'rowRewards' => $rowRewards,
	'hearts' => 5,
	'uses' => $uses,
	'jokers' => 6,
];

return [$columns, $boards, $config];
