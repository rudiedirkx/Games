<?php

class Model extends db_generic_model {}

class Game extends Model {
	use WithMultiplayerPassword;

	const MAX_PLAYERS = 4;

	static $_table = 'labyrinth_games';

	protected function get_url() {
		return 'https://' . $_SERVER['HTTP_HOST'] . '/201.php?game=' . $this->password;
	}

	protected function get_is_joinable() {
		return $this->round == 0 && count($this->players) < self::MAX_PLAYERS;
	}

	protected function get_player_metas() {
		return array_map(function(Player $plr) {
			return [
				'location' => $plr->location,
			];
		}, array_values($this->players));
	}

	protected function get_turn_player_index() {
		foreach (array_values($this->players) as $i => $plr) {
			if ($plr->id == $this->turn_player_id) {
				return $i;
			}
		}
		return -1;
	}

	public function touch() : void {
		$this->update(['changed_on' => time()]);
	}

	protected function relate_turn_player() {
		return $this->to_one(Player::class, 'turn_player_id');
	}

	protected function relate_players() {
		return $this->to_many(Player::class, 'game_id')->order("id asc");
	}

	protected function relate_num_players() {
		return $this->to_count(Player::$_table, 'game_id');
	}

	static public function createNew(string $playerName) : Player {
		return self::$_db->transaction(function() use ($playerName) {
			$gid = self::insert([
				'created_on' => time(),
				'changed_on' => time(),
				'password' => get_random(),
			]);

			$pid = Player::insert([
				'game_id' => $gid,
				'online' => time(),
				'password' => get_random(),
				'name' => $playerName,
			]);

			Game::updateAll(['turn_player_id' => $pid], ['id' => $gid]);

			return Player::find($pid);
		});
	}
}

class Player extends Model {
	use WithMultiplayerPassword, WithMultiplayerHistory;

	const HISTORY_COOKIE_NAME = 'lab_pids';

	static $_table = 'labyrinth_players';

	protected function get_online_ago() {
		return time() - $this->online;
	}

	protected function get_is_turn() {
		return $this->id == $this->game->turn_player_id;
	}

	protected function get_player_index() {
		foreach (array_values($this->game->players) as $i => $plr) {
			if ($plr->id == $this->id) {
				return $i;
			}
		}
	}

	public function touch() : void {
		$this->update(['online' => time()]);
	}

	public function getStatus() : KeerStatus {
		if ($this->game->round == 0) {
			if (!$this->game->has_sufficient_players) {
				return new KeerStatus($this->game, "Waiting for players to join...");
			}
			elseif (!$this->is_turn) {
				return new KeerStatus($this->game, "Waiting for '{$this->game->turn_player}' to start game...");
			}
			else {
				return new KeerStatusButton($this->game, "roll", "Start game");
			}
		}
		elseif ($this->can_roll) {
			return new KeerStatusButton($this->game, "roll", "Roll dice");
		}
		elseif ($this->can_end_turn) {
			if ($this->game->dice) {
				if ($this->can_choose) {
					$label = $this->game->isColorComplete() ? "LAST turn" : "turn";
					return new KeerStatusButton($this->game, "next-turn", "<span class='choosing'>End $label</span><span class='not-choosing'>SKIP $label</span>");
				}
				else {
					return new KeerStatus($this->game, "Waiting for '{$this->game->turn_player}' to choose...");
				}
			}
			else {
				return new KeerStatus($this->game, "Waiting for '{$this->game->turn_player}' to roll...");
			}
		}
		elseif ($this->game->isColorComplete()) {
			if ($this->game->isPlayerComplete()) {
				return new KeerStatus($this->game, "GAME OVER! '{$this->game->winner}' won, with score {$this->game->winner->score}.");
			}
			else {
				$unready = $this->game->getUnTurnReadyPlayers();
				if (count($unready) == 1) {
					return new KeerStatus($this->game, "GAME OVER! Waiting for '" . $unready[0] . "'s last round.");
				}
				else {
					return new KeerStatus($this->game, "GAME OVER! Waiting for players' last round.");
				}
			}
		}
		else {
			$unready = $this->game->getUnTurnReadyPlayers();
			if (count($unready) == 1) {
				return new KeerStatus($this->game, "Waiting for '" . $unready[0] . "' to finish turn...");
			}
			else {
				return new KeerStatus($this->game, "Waiting for players to finish turn...");
			}
		}
	}

	protected function relate_game() {
		return $this->to_one(Game::class, 'game_id');
	}

	public function __toString() {
		return $this->name ?? '???';
	}
}
