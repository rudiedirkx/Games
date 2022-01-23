<?php

return [
	'version' => 'keeropkeer:1',
	'tables' => [
		'keeropkeer_games' => [
			'id' => ['pk' => true],
			'created_on' => ['unsigned' => true, 'default' => 0],
			'changed_on' => ['unsigned' => true, 'default' => 0],
			'password',
			'board',
			'turn_player_id' => ['unsigned' => true, 'references' => ['keeropkeer_players', 'id', 'cascade']],
			'round' => ['unsigned' => true, 'null' => false, 'default' => 0],
			'dice',
		],
		'keeropkeer_players' => [
			'id' => ['pk' => true],
			'game_id' => ['unsigned' => true, 'null' => false, 'references' => ['keeropkeer_games', 'id', 'cascade']],
			'password',
			'name',
			'online' => ['unsigned' => true, 'default' => 0],
			'finished_round' => ['unsigned' => true, 'null' => false, 'default' => 0],
			'board',
			'used_jokers' => ['unsigned' => true, 'null' => false, 'default' => 0],
			'score' => ['type' => 'int', 'null' => false, 'default' => 0],
		],
		'keeropkeer_columns' => [
			'columns' => [
				'id' => ['pk' => true],
				'game_id' => ['unsigned' => true, 'null' => false, 'references' => ['keeropkeer_games', 'id', 'cascade']],
				'column_index' => ['unsigned' => true, 'null' => false],
				'player_id' => ['unsigned' => true, 'null' => false, 'references' => ['keeropkeer_players', 'id', 'cascade']],
			],
			'indexes' => [
				'game_column_unique' => ['columns' => ['game_id', 'column_index'], 'unique' => true],
			],
		],
		'keeropkeer_colors' => [
			'columns' => [
				'id' => ['pk' => true],
				'game_id' => ['unsigned' => true, 'null' => false, 'references' => ['keeropkeer_games', 'id', 'cascade']],
				'color' => ['null' => false],
				'player_id' => ['unsigned' => true, 'null' => false, 'references' => ['keeropkeer_players', 'id', 'cascade']],
			],
			'indexes' => [
				'game_color_unique' => ['columns' => ['game_id', 'color'], 'unique' => true],
			],
		],
	],
];
