<?php

return [
	'version' => 'games:4',
	'tables' => [
		'scores' => [
			'id' => ['pk' => true],
			'game' => ['null' => false],
			'level' => ['unsigned' => true],
			'ip' => ['null' => false],
			'utc' => ['unsigned' => true],
			'time' => ['unsigned' => true, 'null' => false],
			'score' => ['type' => 'int'],
			'moves' => ['unsigned' => 'int'],
			'more',
		],
	],
];
