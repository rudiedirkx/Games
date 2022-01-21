<?php

$columns = [
	range('A', 'O'),
	[5, 3, 3, 3, 2, 2, 2, 1, 2, 2, 2, 3, 3, 3, 5],
	[3, 2, 2, 2, 1, 1, 1, 0, 1, 1, 1, 2, 2, 2, 3],
];

$boards = [
	'gray' => [
		'color' => '#444',
		'map' => [
			'gggyyyy G bbbOyyy',
			'ogYgYyo o pBboogg',
			'BgpgggG p ppyyogg',
			'bppgoOb b ggyyoPb',
			'poooopb b ooopppp',
			'pBbPppp y YoPbbbO',
			'yybbbbp y yyggGoo',
		],
	],
	'green' => [
		'color' => '#5fb55f',
		'map' => [
			'Ogbbppp g ggYyypp',
			'ggggpyg P ggpyypy',
			'bboGyyG b ppppooy',
			'boooogg b bbboPoo',
			'bPOpboo o BYoopYO',
			'ppppbbb y yyoBggg',
			'yyyyGBy y ooggbbb',
		],
	],
	'pink' => [
		'color' => '#a50d61',
		'map' => [
			'gGooOpp p Ybbbbbp',
			'pooygGb y yyGooOp',
			'BbbPggB y pppogoo',
			'bbpppgg O oPygggg',
			'bppbbbo b boyyyyB',
			'oyggboo g booYPpy',
			'yyYgyyy g ggopppy',
		],
	],
];

return [$columns, $boards];
