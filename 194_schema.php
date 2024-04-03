<?php

return [
	'version' => 'keeropkeer:3',
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
			// 'see_all' => ['unsigned' => true, 'null' => false, 'default' => 0],
			'flags' => ['unsigned' => true, 'null' => false, 'default' => 0],
			'max_rounds' => ['unsigned' => true, 'null' => false, 'default' => 0],
		],
		'keeropkeer_players' => [
			'id' => ['pk' => true],
			'game_id' => ['unsigned' => true, 'null' => false, 'references' => ['keeropkeer_games', 'id', 'cascade']],
			'password',
			'name',
			'online' => ['unsigned' => true, 'default' => 0],
			'finished_round' => ['unsigned' => true, 'null' => false, 'default' => 0],
			'board',
			'used_jokers',
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
	'updates' => [
		'keeropkeer-1' => function($db) {
			$db->update('keeropkeer_games', [
				'flags' => Game::FLAG_HIDE_SCORES | Game::FLAG_SEE_ALL,
			], [
				'see_all' => 1,
			]);
		},
		'keeropkeer-2' => function($db) {
			$db->query("
				alter table keeropkeer_players
				change used_jokers used_jokers varchar(255) null default null
			");
			$db->query("
				update keeropkeer_players
				set used_jokers = substr('________', 1, cast(used_jokers as unsigned))
			");
		},
	],
];
