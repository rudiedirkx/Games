<?php

$columns = [
	range('A', 'O'),
	[5, 3, 3, 3, 2, 2, 2, 1, 2, 2, 2, 3, 3, 3, 5],
	[3, 2, 2, 2, 1, 1, 1, 0, 1, 1, 1, 2, 2, 2, 3],
];

$boards = [
	'gray' => [
		'color' => '#233c3f',
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
	'yellow' => [
		'color' => '#ffcd00',
		'text' => '#555',
		'map' => [
			'pggYyyy o ppgggbY',
			'pgpppyy o ppggbbb',
			'bPpbbGg g ggPbOBp',
			'oooobbb B gYybppp',
			'ybboopp p bbbPpoo',
			'yyBggoO y yobyyyo',
			'GyygGoo y Ooooygg',
		],
	],
	'purple' => [
		'color' => '#56368b',
		'map' => [
			'yyOooGb b byyppgg',
			'obbbppp P pyYbpPo',
			'oobbYpO o oBbbpbB',
			'oGyyyyo g obbObbp',
			'gggPyog g Yooooop',
			'Bgpggog y gppyyyp',
			'pppgbby y ggggGyy',
		],
	],
	'orange' => [
		'color' => '#e97252',
		'map' => [
			'OoYoppP g oPggggg',
			'boooGpg g YppGbyy',
			'ybbggpg y yyppbyO',
			'yYbyyyy B byypooo',
			'pygBbGy o bbbbobb',
			'gggpbbb o ggOyyBb',
			'gpppooo p poooPpp',
		],
	],
	'blue' => [
		'color' => '#46a8d5',
		'map' => [
			'ppggyyY g gppppoo',
			'OppBbGy g gPgYpoo',
			'bOobggg p oggggGy',
			'bbooogp p ooooBby',
			'gbbpppb B bbObyyG',
			'ggYypbb y yybboyb',
			'yyyyPoo o Pyybopp',
		],
	],
];

return [$columns, $boards];
