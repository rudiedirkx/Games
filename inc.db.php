<?php

$db = db_mysql::open(array('user' => DB_USER, 'pass' => DB_PASS, 'db' => DB_NAME));

if ( !$db ) {
	exit('No connecto!');
}

$db->ensureSchema(require 'inc.db-schema.php');

trait WithMultiplayerPassword {
	static public function get(?string $password) : ?self {
		return $password ? self::first(['password' => $password]) : null;
	}
}

trait WithMultiplayerHistory {
	static public function addHistory(int $pid) : void {
		if (!self::inHistory($pid)) {
			$pids = self::getHistory();
			$pids[] = $pid;
			setcookie(self::HISTORY_COOKIE_NAME, implode(',', $pids), time() + 7 * 86400);
		}
	}

	static public function inHistory(int $pid) : bool {
		return in_array($pid, self::getHistory());
	}

	static public function getHistory() : array {
		if (!isset($_COOKIE[self::HISTORY_COOKIE_NAME])) return [];
		return explode(',', $_COOKIE[self::HISTORY_COOKIE_NAME]);
	}
}
