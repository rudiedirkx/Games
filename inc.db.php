<?php

try {
	$db = db_mysql::open(array('user' => DB_USER, 'pass' => DB_PASS, 'db' => DB_NAME));
	// $db = db_sqlite::open(array('database' => __DIR__ . '/games.sqlite3'));
	$db->connect();
}
catch (db_exception $ex) {
	exit('No connecto!');
}

$db->ensureSchema(require 'inc.db-schema.php');

class MultiPlayerException extends Exception {
	const NAME_INVALID = 101;
	const NAME_EXISTS = 102;

	public function __construct(int $code, ?string $message = null) {
		parent::__construct($message ?? self::getLabel($code), $code);
	}

	static public function getLabel(int $code) : string {
		switch ($code) {
			case self::NAME_INVALID:
				return "Invalid name. No special characters allowed.";

			case self::NAME_EXISTS:
				return "Name already exists. If you joined before, use the PLAY link below.";
		}
		return "Error # $code?";
	}
}

trait WithMultiplayerPlayers {
	abstract function relate_players();

	public function validateName(string $name) {
		if (preg_match('#[^\w \-]#', trim($name))) {
			throw new MultiPlayerException(MultiPlayerException::NAME_INVALID);
		}
	}

	public function validateUniqueName(string $name) {
		$players = $this->players;
		$class = get_class(reset($players));

		$exists = $class::first([
			'game_id' => $this->id,
			'name' => trim($name),
		]);
		if ($exists) {
			throw new MultiPlayerException(MultiPlayerException::NAME_EXISTS);
		}
	}
}

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
