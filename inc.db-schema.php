<?php

return [
	'version' => 'games:5',
	'tables' => [
		'scores' => [
			'id' => ['pk' => true],
			'game' => ['null' => false],
			'level' => ['unsigned' => true],
			'ip' => ['null' => false],
			'utc' => ['unsigned' => true],
			'time' => ['unsigned' => true, 'null' => false],
			'score' => ['type' => 'float'],
			'moves' => ['unsigned' => 'int'],
			'more',
			'cookie',
		],
	],
];
