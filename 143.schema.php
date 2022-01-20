<?php

return [
	'version' => 'abalone:4',
	'tables' => [
		'abalone_games' => [
			'id' => ['pk' => true],
			'turn' => ['null' => false, 'default' => 'white'],
			'password' => ['null' => false],
			'last_move',
		],
		'abalone_players' => [
			'id' => ['pk' => true],
			'game_id' => ['unsigned' => true],
			'password' => ['null' => false],
			'color' => ['null' => false, 'default' => 'white'],
			'online' => ['unsigned' => true, 'default' => 0],
			'ip',
		],
		'abalone_balls' => [
			'id' => ['pk' => true],
			'player_id' => ['unsigned' => true],
			'x' => ['unsigned' => true],
			'y' => ['unsigned' => true],
			'z' => ['unsigned' => true],
		],
	],
];
