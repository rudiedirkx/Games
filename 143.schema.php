<?php

return array(
	'abalone_games' => array(
		'id' => array('pk' => true),
		'turn' => array('null' => false, 'default' => 'white'),
		'password' => array('null' => false),
	),
	'abalone_players' => array(
		'id' => array('pk' => true),
		'game_id' => array('unsigned' => true),
		'username' => array('null' => false),
		'password' => array('null' => false),
		'color' => array('null' => false, 'default' => 'white'),
	),
	'abalone_balls' => array(
		'id' => array('pk' => true),
		'player_id' => array('unsigned' => true),
		'x' => array('unsigned' => true),
		'y' => array('unsigned' => true),
		'z' => array('unsigned' => true),
	),
);


