<?php

return [
	'version' => 'p195:1',
	'tables' => [
		'p195_tables' => [
			'id' => ['pk' => true],
			'created_on' => ['unsigned' => true, 'default' => 0],
			'changed_on' => ['unsigned' => true, 'default' => 0],
			'password',
			'dealer_player_id' => ['unsigned' => true, 'references' => ['p195_players', 'id', 'cascade']],
			'turn_player_id' => ['unsigned' => true, 'references' => ['p195_players', 'id', 'cascade']],
			'round' => ['unsigned' => true, 'null' => false, 'default' => 0],
			'state' => ['unsigned' => true, 'null' => false, 'default' => 0],
			'cards',
			'log',
		],
		'p195_players' => [
			'id' => ['pk' => true],
			'table_id' => ['unsigned' => true, 'null' => false, 'references' => ['p195_tables', 'id', 'cascade']],
			'password',
			'name',
			'online' => ['unsigned' => true, 'default' => 0],
			'balance' => ['type' => 'int', 'default' => 0],
			'bet' => ['type' => 'int', 'default' => 0],
			'state' => ['unsigned' => true, 'null' => false, 'default' => 0],
			'cards',
			'log',
		],
	],
];
