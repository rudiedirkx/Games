<?php

require 'inc.env.php';
require 'vendor/autoload.php';

if (!is_local()) {
	ini_set('display_errors', 0);
}

if (php_sapi_name() !== 'cli') {
	if (empty($_COOKIE['games'])) {
		setcookie('games', $_COOKIE['games'] = get_random(40), strtotime('+2 years'), '/');
	}
	elseif ( rand(0, 9) == 0 ) {
		setcookie('games', $_COOKIE['games'], strtotime('+2 years'), '/');
	}
}
