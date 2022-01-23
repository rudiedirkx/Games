<?php

class Model extends db_generic_model {}

class Game extends Model {
	const COLORS_TO_COMPLETE = 2;
	const COLOR_COMPLETE_ROUND = 100;

	static $_table = 'keeropkeer_games';

	public function touch() : void {
		$this->update(['changed_on' => time()]);
	}

	public function disableDice() : void {
		$dice = $this->dice_array;

		$i = array_search($_POST['color'], $dice['colors']);
		if (strlen($_POST['color']) && $i !== false) {
			unset($dice['colors'][$i]);
			$dice['colors'] = array_values($dice['colors']);
		}

		$i = array_search($_POST['number'], $dice['numbers']);
		if (strlen($_POST['number']) && $i !== false) {
			unset($dice['numbers'][$i]);
			$dice['numbers'] = array_values($dice['numbers']);
		}

		$this->update([
			'dice' => json_encode($dice),
		]);
	}

	public function allPlayersTurnReady() : bool {
		$finisheds = array_count_values(array_column($this->players, 'finished_round'));
		unset($finisheds[self::COLOR_COMPLETE_ROUND]);
		return array_keys($finisheds) == [$this->round];
	}

	public function endRound() : void {
		$this->update([
			'round' => $this->round + 1,
			'turn_player_id' => $this->getNextTurnPlayerId(),
			'dice' => null,
		]);

		if ($this->is_color_complete) {
			Player::updateAll([
				'finished_round' => self::COLOR_COMPLETE_ROUND,
			], ['game_id' => $this->id]);
			$this->update([
				'turn_player_id' => null,
			]);
		}
	}

	protected function getNextTurnPlayerId() {
		$pids = array_column($this->players, 'id');
		$i = array_search($this->turn_player_id, $pids);
		return $i === false ? $pids[array_rand($pids)] : $pids[($i + 1) % count($pids)];
	}

	protected function get_winner() {
		$players = $this->players;
		usort($players, function($a, $b) {
			return $b->score - $a->score;
		});
		return $players[0];
	}

	protected function get_is_color_complete() {
		foreach ($this->players as $player) {
			if ($player->finished_round == self::COLOR_COMPLETE_ROUND) {
				return true;
			}
		}
		return false;
	}

	protected function get_is_player_complete() {
		foreach ($this->players as $player) {
			if ($player->finished_round != self::COLOR_COMPLETE_ROUND) {
				return false;
			}
		}
		return true;
	}

	protected function get_sufficient_players() {
		return count($this->players) > 1;
	}

	protected function get_can_roll() {
		return !$this->dice;
	}

	protected function get_dice_array() {
		return json_decode($this->dice, true) ?: new stdClass;
	}

	protected function get_url() {
		return 'https://' . $_SERVER['HTTP_HOST'] . '/194.php?game=' . $this->password;
	}

	protected function relate_columns() {
		return $this->to_many(FullColumn::class, 'game_id');
	}

	protected function relate_num_columns() {
		return $this->to_count(FullColumn::$_table, 'game_id');
	}

	protected function relate_colors() {
		return $this->to_many(FullColor::class, 'game_id');
	}

	protected function relate_num_colors() {
		return $this->to_count(FullColor::$_table, 'game_id');
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

	static public function createNew(string $board, string $playerName) : Player {
		return self::$_db->transaction(function() use ($board, $playerName) {
			$gid = self::insert([
				'created_on' => time(),
				'changed_on' => time(),
				'board' => $board,
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

	static public function get(?string $password) : ?self {
		return $password ? self::first(['password' => $password]) : null;
	}
}

class Player extends Model {
	static $_table = 'keeropkeer_players';

	public function touch() : void {
		$this->update(['online' => time()]);
	}

	public function getStatus() : KeerStatus {
		if ($this->game->round == 0) {
			if (!$this->game->sufficient_players) {
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
					$label = $this->game->is_color_complete ? "End LAST turn" : "End turn";
					return new KeerStatusButton($this->game, "next-turn", $label);
				}
				else {
					return new KeerStatus($this->game, "Waiting for '{$this->game->turn_player}' to choose...");
				}
			}
			else {
				return new KeerStatus($this->game, "Waiting for '{$this->game->turn_player}' to roll...");
			}
		}
		elseif ($this->game->is_color_complete) {
			if ($this->game->is_player_complete) {
				return new KeerStatus($this->game, "GAME OVER! '{$this->game->winner}' won, with score {$this->game->winner->score}.");
			}
			else {
				return new KeerStatus($this->game, "GAME OVER! Waiting for players' last round.");
			}
		}
		else {
			return new KeerStatus($this->game, "Waiting for players to finish turn...");
		}
	}

	public function getOthersColumns() : array {
		$indexes = [];
		foreach ($this->game->columns as $column) {
			if ($column->player_id != $this->id) {
				$indexes[] = (int) $column->column_index;
			}
		}
		return $indexes;
	}

	public function getOthersColors() : array {
		$colors = [];
		foreach ($this->game->colors as $color) {
			if ($color->player_id != $this->id) {
				$colors[] = $color->color;
			}
		}
		return $colors;
	}

	public function registerFullColumns(array $columns) : void {
		$exist = array_column($this->game->columns, 'column_index');
		foreach (array_diff($columns, $exist) as $column) {
			FullColumn::insert([
				'game_id' => $this->game_id,
				'player_id' => $this->id,
				'column_index' => $column,
			]);
		}
	}

	public function registerFullColors(array $colors) : void {
		$exist = array_column($this->game->colors, 'color');
		foreach (array_diff($colors, $exist) as $color) {
			FullColor::insert([
				'game_id' => $this->game_id,
				'player_id' => $this->id,
				'color' => $color,
			]);
		}

		if (count($colors) >= Game::COLORS_TO_COMPLETE) {
			$this->update([
				'finished_round' => 100,
			]);
		}
	}

	public function getUseJokersUpdate(bool $color, bool $number) : array {
		if ($color || $number) {
			return [
				'used_jokers' => $this->used_jokers + intval($color) + intval($number),
			];
		}
		return [];
	}

	protected function get_online_ago() {
		return time() - $this->online;
	}

	protected function get_can_choose() {
		return $this->can_end_turn && ($this->game->round == 1 || $this->is_turn || !$this->game->turn_player->can_end_turn);
	}

	protected function get_can_roll() {
		return $this->game->can_roll && $this->is_turn && ($this->round == 0 || $this->can_end_turn);
	}

	protected function get_can_end_turn() {
		return $this->game->round > 0 && $this->finished_round == $this->game->round - 1;
	}

	protected function get_is_turn() {
		return $this->id == $this->game->turn_player_id;
	}

	protected function get_is_leader() {
		foreach ($this->game->players as $player) {
			return $this->id == $player->id;
		}
	}

	protected function relate_game() {
		return $this->to_one(Game::class, 'game_id');
	}

	public function __toString() {
		return $this->name ?? '?';
	}

	static public function get(?string $password) : ?self {
		return $password ? self::first(['password' => $password]) : null;
	}
}

class FullColumn extends Model {
	static $_table = 'keeropkeer_columns';
}

class FullColor extends Model {
	static $_table = 'keeropkeer_colors';
}

class KeerStatus {
	protected $game;
	protected $text;

	public function __construct(Game $game, string $text) {
		$this->game = $game;
		$this->text = $text;
	}

	public function getHash() : string {
		return sha1(get_class($this) . "$this->text:{$this->game->num_players}:{$this->game->num_columns}:{$this->game->num_colors}");
	}

	public function __toString() {
		return '<em>' . do_html($this->text) . '</em>';
	}
}

class KeerStatusButton extends KeerStatus {
	protected $id;

	public function __construct(Game $game, string $id, string $label) {
		parent::__construct($game, $label);
		$this->id = $id;
	}

	public function __toString() {
		return '<button id="' . $this->id . '">' . do_html($this->text) . '</button>';
	}
}
