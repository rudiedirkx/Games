<?php

define( 'S_NAME', '142b_mppoker_v2' );

$t_arrUser = db_select('', '');
User::$user = new User($t_arrUser[0]);
define( 'USER_ID', (int)$t_arrUser[0]['id'] );





class User {
	public $id = -1;
	public $username = '';
	public $balance = -1;
	public function __construct($f_arrUser) {
		$this->id		= (int)$f_arrUser['id'];
		$this->username	= $f_arrUser['username'];
		$this->balance	= $f_arrUser['balance'];
		// .. more?
	}
	public static $user = null; # type User
	public static function logincheck() {
		if ( defined('USER_ID') && is_int(USER_ID) ) {
			return self::$user;
		}
		if ( empty($_SESSION[S_NAME]) || !isset($_SESSION[S_NAME]['user_id'], $_SESSION[S_NAME]['secret'], $_SESSION[S_NAME]['ip']) || !count($arrUser=db_select('mpp_users', 'id = '.(int)$_SESSION[S_NAME]['user_id'].' AND secret = \''.addslashes($_SESSION[S_NAME]['secret']).'\' AND login_ip = \''.addslashes($_SESSION[S_NAME]['ip']).'\'')) || $_SERVER['REMOTE_ADDR'] !== $_SESSION[S_NAME]['ip'] ) {
			return false;
		}
		self::$user = new self($arrUser[0]);
		define( 'USER_ID', (int)$arrUser[0]['id'] );
		return self::$user;
	}
}

class Player extends User {
	public $user = null; # type User
	public $tables = array(); # type PokerTable[]
	
}

class PokerTable {
	public $players = array(); # type Player[]
	public $round = null; # type Round
	
}

class Round {
	public $table = null; # type PokerTable;
	public $pools = array(); # type Pool[]
	
}

class Pool {
	public $round = null; # type Round
	
}

?>