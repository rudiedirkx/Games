<?php

require __DIR__ . '/inc.bootstrap.php';
require __DIR__ . '/inc.db.php';

$ip = get_ip();
$local = is_debug_ip();

$ok = function($line) use ($local) {
	return 'OK' . ( $local ? $line : '' );
};

$referrer = trim(@$_SERVER['HTTP_REFERER']);
if ( !$referrer ) {
	exit($ok(__LINE__));
}

$referrerHost = parse_url($referrer, PHP_URL_HOST);
$selfHost = $_SERVER['HTTP_HOST'];
if ( $referrerHost !== $selfHost ) {
	exit($ok(__LINE__));
}

$game = (int) trim(parse_url($referrer, PHP_URL_PATH), '/');
if ( !$game ) {
	exit($ok(__LINE__));
}

$data = $_POST;
if ( !$data ) {
	exit($ok(__LINE__));
}

$columns = array_intersect_key($data, array_flip(['level', 'score', 'time', 'moves']));

$columns['game'] = $game;
$columns['ip'] = $ip;
$columns['utc'] = time();
$columns['cookie'] = $_COOKIE['games'] ?? null;

$more = array_diff_key($data, $columns);
$columns['more'] = json_encode((object) $more);

$db->insert('scores', $columns);

exit($ok(__LINE__));
