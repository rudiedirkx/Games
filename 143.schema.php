<?php

return [
	'version' => 'abalone:6',
	'tables' => [
		'abalone_games' => [
			'id' => ['pk' => true],
			'turn' => ['null' => false, 'default' => 'white'],
			'password' => ['null' => false],
			'created_on' => ['unsigned' => true, 'default' => 0],
		],
		'abalone_players' => [
			'id' => ['pk' => true],
			'game_id' => ['unsigned' => true, 'references' => ['abalone_games', 'id', 'cascade']],
			'password' => ['null' => false],
			'color' => ['null' => false, 'default' => 'white'],
			'online' => ['unsigned' => true, 'default' => 0],
			'ip',
		],
		'abalone_balls' => [
			'id' => ['pk' => true],
			'player_id' => ['unsigned' => true, 'references' => ['abalone_players', 'id', 'cascade']],
			'x' => ['unsigned' => true],
			'y' => ['unsigned' => true],
			'z' => ['unsigned' => true],
		],
		'abalone_moves' => [
			'id' => ['pk' => true],
			'game_id' => ['unsigned' => true, 'references' => ['abalone_games', 'id', 'cascade']],
			'player_id' => ['unsigned' => true, 'references' => ['abalone_players', 'id', 'cascade']],
			'move',
		],
	],
];
