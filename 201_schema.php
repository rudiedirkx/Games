<?php

return [
	'version' => 'labyrinth:1',
	'tables' => [
		'labyrinth_games' => [
			'id' => ['pk' => true],
			'created_on' => ['unsigned' => true, 'default' => 0],
			'changed_on' => ['unsigned' => true, 'default' => 0],
			'password',
			'tiles',
			'turn_player_id' => ['unsigned' => true, 'references' => ['labyrinth_players', 'id', 'cascade']],
			'round' => ['unsigned' => true, 'null' => false, 'default' => 0],
		],
		'labyrinth_players' => [
			'id' => ['pk' => true],
			'game_id' => ['unsigned' => true, 'null' => false, 'references' => ['labyrinth_games', 'id', 'cascade']],
			'password',
			'name',
			'online' => ['unsigned' => true, 'default' => 0],
			'treasures',
			'location',
		],
	],
];
