<?php

class Model extends db_generic_model {}

class Game extends Model {
	static $_table = 'abalone_games';

	protected function get_last_move() {
		$move = Move::first("game_id = ? order by id desc", [$this->id]);
		return $move ? $move->move_array : null;
	}

	protected function relate_moves() {
		return $this->to_many(Move::class, 'game_id')->order('id asc');
	}

	static public function newGame() {
		return self::$_db->transaction(function() {
			$turn = rand(0, 1) ? 'white' : 'black';
			$gid = Game::insert([
				'turn' => $turn,
				'password' => '',
			]);

			$balls = Abalone::initialBalls();

			$login = null;
			foreach ( array('white', 'black') AS $color ) {
				$password = Player::password();
				$pid = Player::insert([
					'game_id' => $gid,
					'password' => $password,
					'color' => $color,
				]);

				if ($color == $turn) {
					$login = $password;
				}

				Ball::insertAll(array_map(function(string $coord) use ($pid) {
					$data = array_combine(array('x', 'y', 'z'), explode(':', $coord));
					$data['player_id'] = $pid;
					return $data;
				}, $balls[$color]));
			}

			return $login;
		});
	}
}

class Player extends Model {
	static $_table = 'abalone_players';

	protected function relate_balls_left() {
		return $this->to_count(Ball::$_table, 'player_id');
	}

	protected function relate_game() {
		return $this->to_one(Game::class, 'game_id');
	}

	protected function get_opponent() {
		return Player::first([
			'game_id' => $this->game_id,
			"id <> $this->id",
		]);
	}

	static public function password() {
		$password = '';
		while (strlen($password) < 30) {
			$password .= rand();
		}
		$password = preg_replace('#[^a-z0-9]#i', '', base64_encode($password));
		$password = substr($password, rand(0, strlen($password) - 10), 10);
		return strtoupper($password);
	}
}

class Ball extends Model {
	static $_table = 'abalone_balls';
}

class Move extends Model {
	static $_table = 'abalone_moves';

	protected function get_move_array() {
		return json_decode($this->move, true);
	}
}
