<?php

class Model extends db_generic_model {}

trait HasCards {
	protected function get_cards_array() {
		return $this->cards ? array_map(fn($n) => intval($n), explode(',', $this->cards)) : [];
	}

	protected function get_cards_objects() {
		return array_map(fn($n) => new Card($n), $this->cards_array);
	}
}

trait HasPassword {
	static public function get(?string $password) : ?self {
		return $password ? self::first(['password' => $password]) : null;
	}
}

class Table extends Model {
	use HasPassword, HasCards;

	const STATE_IDLE = 0;
	const STATE_SB = 1;
	const STATE_BB = 2;
	const STATE_PREFLOP = 3;
	const STATE_FLOP = 4;
	const STATE_TURN = 5;
	const STATE_RIVER = 6;
	const STATES = [
		self::STATE_IDLE => 'idle',
		self::STATE_SB => 'posting small blind',
		self::STATE_BB => 'posting big blind',
		self::STATE_PREFLOP => 'pre-flop',
		self::STATE_FLOP => 'flop',
		self::STATE_TURN => 'turn',
		self::STATE_RIVER => 'river',
	];

	static $_table = 'p195_tables';

	public function printCards() : string {
		return implode(', ', array_slice($this->cards_objects, 0, $this->show_cards)) ?: '-';
	}

	protected function finishRound(array $log) : void {
		$this->update([
			'state' => self::STATE_IDLE,
			'dealer_player_id' => $turn = $this->getNextPlayerId($this->dealer_player_id),
			'turn_player_id' => $turn,
			'log' => json_encode($log),
		]);
	}

	public function showdown() : void {
		$scores = array_map([PokerTexasHoldem::class, 'score'], array_column($this->active_players, 'all_open_card_objects', 'id'));
// print_r($scores);
		$winner = max($scores);
// var_dump($winner);
		$winners = array_keys($scores, $winner);
// print_r($winners);

		$pot = $this->pot;
		$winnings = floor($pot / count($winners));
		foreach ($this->players as $plr) {
			$w = in_array($plr->id, $winners);
			$update = [];
			if ($w) {
				$update = [
					'balance' => $plr->balance + $winnings,
					'log' => "Won showdown [money:$winnings] with [hand:$winner]",
				];
			}
			elseif (isset($scores[$plr->id])) {
				$score = $scores[$plr->id];
				$update = ['log' => "Lost showdown with [hand:$score]"];
			}
			$plr->update($update + [
				'bet' => 0,
			]);
		}

		$this->finishRound([
			'winners' => $winners,
			'hand' => $winner,
			'pot' => $pot,
		]);
	}

	public function winByDefault(int $pid) : void {
		$winnings = $this->pot;
		foreach ($this->players as $plr) {
			$w = $pid == $plr->id;
			$update = $w ? [
				'balance' => $plr->balance + $winnings,
				'log' => "Won [money:$winnings] by default",
			] : [];
			$plr->update($update + [
				'bet' => 0,
				// 'cards' => null,
			]);
		}

		$this->finishRound([
			'winners' => [$pid],
			'state' => (int) $this->state,
			'pot' => $winnings,
		]);
	}

	public function nextBettingState() : void {
		$this->update([
			'state' => $this->state + 1,
			'turn_player_id' => $this->getNextActivePlayerId($this->dealer_player_id),
		]);
		foreach ($this->active_players as $plr) {
			$plr->update([
				'state' => Player::STATE_UNBET,
			]);
		}
	}

	public function maybeEndBetting() : void {
		$bets = array_column($this->active_players, 'bet', 'id');
// print_r($bets);
		$states = array_column($this->active_players, 'state');
// print_r($states);

		if (count($bets) == 1) {
			// Finish round
			$this->winByDefault(key($bets));
		}
		elseif (max($bets) == min($bets) && !in_array(Player::STATE_UNBET, $states)) {
			if ($this->state == Table::STATE_RIVER) {
				$this->showdown();
			}
			else {
				$this->nextBettingState();
			}
		}
		else {
			$this->nextTurn();
		}
	}

	public function getMaxBet() : int {
		return max(array_column($this->players, 'bet'));
	}

	public function isBettingState() : bool {
		return in_array($this->state, [
			self::STATE_PREFLOP,
			self::STATE_FLOP,
			self::STATE_TURN,
			self::STATE_RIVER,
		]);
	}

	public function dealAll() : void {
		$cards = range(0, 51);
		shuffle($cards);

		$this->update([
			'cards' => implode(',', array_slice($cards, 0, 5)),
		]);
		foreach ($this->players as $i => $plr) {
			$plr->update([
				'state' => Player::STATE_UNBET,
				'cards' => implode(',', array_slice($cards, 5 + 2 * $i, 2)),
			]);
		}
	}

	public function nextTurn(array $updates = []) : void {
		$this->update($updates + [
			'turn_player_id' => $this->getNextActivePlayerId($this->turn_player_id),
			'changed_on' => time(),
		]);
	}

	public function getNextActivePlayerId(int $from) : int {
		$take = false;
		foreach ($this->players as $plr) {
			if ($take && $plr->is_active) {
				return $plr->id;
			}
			elseif ($plr->id == $from) {
				$take = true;
			}
		}
		foreach ($this->active_players as $plr) {
			return $plr->id;
		}
	}

	public function getNextPlayerId(int $from) : int {
		$ids = $this->player_ids;
		$index = array_search($from, $ids);
		return $ids[($index + 1) % count($ids)];
	}

	public function addPlayer(string $playerName) : Player {
		return Player::create([
			'table_id' => $this->id,
			'online' => time(),
			'password' => get_random(),
			'name' => trim($playerName),
			'balance' => 1000,
		]);
	}

	public function touch() : void {
		$this->update(['changed_on' => time()]);
	}

	protected function get_has_showdowned() {
		return $this->state == Table::STATE_IDLE && $this->winning_hand;
	}

	protected function get_winning_state() {
		return $this->log_array['state'] ?? Table::STATE_RIVER;
	}

	protected function get_winning_hand() {
		return $this->log_array['hand'] ?? null;
	}

	protected function get_log_array() {
		return json_decode($this->log, true) ?: [];
	}

	protected function get_show_cards() {
		return self::showNumCards($this->state == 0 ? $this->winning_state : $this->state);
	}

	protected function get_pot() {
		return array_sum(array_column($this->players, 'bet'));
	}

	protected function get_state_label() {
		return self::STATES[$this->state];
	}

	protected function get_sb_player_id() {
		return $this->getNextPlayerId($this->dealer_player_id);
	}

	protected function get_bb_player_id() {
		return $this->getNextPlayerId($this->sb_player_id);
	}

	protected function get_active_player_ids() {
		return array_column($this->active_players, 'id');
	}

	protected function get_player_ids() {
		return array_column($this->players, 'id');
	}

	protected function get_active_players() {
		return array_values(array_filter($this->players, function(Player $plr) {
			return $plr->is_active;
			// return $plr->balance >= 0 && $plr->bet >= 0;
		}));
	}

	protected function get_raise() {
		return $this->big_blind;
	}

	protected function get_small_blind() {
		return 10;
	}

	protected function get_big_blind() {
		return 20;
	}

	protected function get_url() {
		return 'https://' . $_SERVER['HTTP_HOST'] . '/195.php?table=' . $this->password;
	}

	protected function relate_turn_player() {
		return $this->to_one(Player::class, 'turn_player_id');
	}

	protected function relate_dealer_player() {
		return $this->to_one(Player::class, 'dealer_player_id');
	}

	protected function relate_sb_player() {
		return $this->to_one(Player::class, 'sb_player_id');
	}

	protected function relate_bb_player() {
		return $this->to_one(Player::class, 'bb_player_id');
	}

	protected function relate_num_players() {
		return $this->to_count(Player::$_table, 'table_id');
	}

	protected function relate_players() {
		return $this->to_many(Player::class, 'table_id')->order("id asc");
	}

	static public function showNumCards(int $state) : int {
		switch ($state) {
			// case Table::STATE_IDLE:
			// 	return 5; // $this->winning_hand ? 5 : 0;

			case Table::STATE_FLOP:
				return 3;

			case Table::STATE_TURN:
				return 4;

			case Table::STATE_RIVER:
				return 5;
		}

		return 0;
	}

	static public function createNew(string $playerName) : Player {
		return self::$_db->transaction(function() use ($playerName) {
			$table = self::create([
				'created_on' => time(),
				'changed_on' => time(),
				'password' => get_random(),
			]);

			$player = $table->addPlayer($playerName);

			$table->update([
				'dealer_player_id' => $player->id
			]);

			return $player;
		});
	}
}

class Player extends Model {
	use HasPassword, HasCards;

	const STATE_UNBET = 0;
	const STATE_BET = 1;
	const STATE_FOLDED = 2;
	const STATES = [
		self::STATE_UNBET => 'unbet',
		self::STATE_BET => 'bet',
		self::STATE_FOLDED => 'folded',
	];

	static $_table = 'p195_players';

	public function printCards() : string {
		$html = [
			implode(' ', array_slice($this->cards_objects, 0, $this->show_cards)),
		];
		if ($this->table->state > Table::STATE_PREFLOP) {
			$html[] = '| ' . PokerTexasHoldem::readable_hand(PokerTexasHoldem::score($this->all_open_card_objects));
		}
		return implode(' ', $html);
	}

	public function fold() : void {
		$max = $this->table->getMaxBet();
		$this->update([
			'state' => Player::STATE_FOLDED,
			'log' => "Folded at [money:$max]",
		]);
	}

	protected function bet(int $add, bool $manual, string $log) : void {
		$update1 = $manual ? ['state' => self::STATE_BET] : [];
		$update2 = $add ? [
			'balance' => $this->balance - $add,
			'bet' => $this->bet + $add,
		] : [];
		$this->update($update1 + $update2 + [
			'log' => $log,
		]);
	}

	public function manualBet(int $add, string $log) : void {
		$this->bet($add, true, $log);
	}

	public function mandatoryBet(int $add, string $log) : void {
		$this->bet($add, false, $log);
	}

	public function betTo(int $total, string $log) : void {
		$this->manualBet($total - $this->bet, $log);
	}

	public function getStatus() : PokerStatus {
		switch ($this->table->state) {
			case Table::STATE_IDLE:
				if ($this->is_dealer) {
					$which = $this->table->round == 0 ? "first" : "new";
					return new PokerStatusAction($this->table, 'start', "Start $which round");
				}
				return new PokerStatus($this->table, "Waiting for '{$this->table->dealer_player}' to start new round...");

			case Table::STATE_SB:
				if ($this->is_sb) {
					return new PokerStatusAction($this->table, 'sb', "Post small blind");
				}
				return new PokerStatus($this->table, "Waiting for '{$this->table->sb_player}' to post small blind...");

			case Table::STATE_BB:
				if ($this->is_bb) {
					return new PokerStatusAction($this->table, 'bb', "Post big blind");
				}
				return new PokerStatus($this->table, "Waiting for '{$this->table->bb_player}' to post big blind...");

			case Table::STATE_PREFLOP:
			case Table::STATE_FLOP:
			case Table::STATE_TURN:
			case Table::STATE_RIVER:
				if ($this->is_turn) {
					if ($this->bet < ($max = $this->table->getMaxBet())) {
						$actions = ['call' => "Call $ $max"];
					}
					else {
						$actions = ['check' => "Check"];
					}
					$raise = $max + $this->table->raise;
					return new PokerStatusActions($this->table, $actions + [
						'raise' => "Raise to $ $raise",
						'fold' => "Fold",
					]);
				}
				elseif ($this->state == self::STATE_FOLDED) {
					return new PokerStatus($this->table, "Waiting for this round to end...");
				}
				return new PokerStatus($this->table, "Waiting for '{$this->table->turn_player}' to bet...");
		}

		return new PokerStatus($this->table, "Eh..?");
	}

	public function touch() : void {
		$this->update(['online' => time()]);
	}

	protected function get_log_markup() {
		if (!$this->log) return '';
		$text = $this->log;
		$text = preg_replace_callback('#\[money:(\d+)\]#', function($match) {
			return '$ ' . $match[1];
		}, $text);
		$text = preg_replace_callback('#\[hand:([\d\.]+[a-z]*)\]#', function($match) {
			return PokerTexasHoldem::readable_hand($match[1]);
		}, $text);
		return $text;
	}

	protected function get_all_open_card_objects() {
		return array_map(fn($n) => new Card($n), [
			...array_slice($this->table->cards_array, 0, $this->table->show_cards),
			...array_slice($this->cards_array, 0, $this->show_cards),
		]);
	}

	protected function get_show_cards() {
		return $this->table->state == Table::STATE_IDLE || $this->table->state > Table::STATE_BB ? 2 : 0;
	}

	protected function get_state_label() {
		return self::STATES[$this->state];
	}

	protected function get_is_active() {
		return in_array($this->state, [Player::STATE_UNBET, Player::STATE_BET]);
	}

	protected function get_is_turn() {
		return $this->table->turn_player_id == $this->id;
	}

	protected function get_roles() {
		$roles = [];
		if ($this->is_dealer) $roles[] = 'Dlr';
		if ($this->is_sb) $roles[] = 'SB';
		if ($this->is_bb) $roles[] = 'BB';
		return $roles;
	}

	protected function get_is_dealer() {
		return $this->table->dealer_player_id == $this->id;
	}

	protected function get_is_sb() {
		return $this->table->sb_player_id == $this->id;
	}

	protected function get_is_bb() {
		return $this->table->bb_player_id == $this->id;
	}

	protected function get_online_ago() {
		return time() - $this->online;
	}

	protected function relate_table() {
		return $this->to_one(Table::class, 'table_id');
	}

	public function __toString() {
		return $this->name ?? '?';
	}
}

class PokerStatus {
	protected $table;
	protected $text;

	public function __construct(Table $table, string $text) {
		$this->table = $table;
		$this->text = $text;
	}

	public function getHash() : string {
		return sha1(get_class($this) . "$this->text:{$this->table->num_players}:{$this->table->round}:{$this->table->state}:{$this->table->turn_player_id}");
	}

	public function shouldReload() : bool {
		return false;
	}

	public function __toString() {
		return '<em>' . do_html($this->text) . '</em>';
	}
}

class PokerStatusActions extends PokerStatus {
	protected $actions;

	public function __construct(Table $table, array $actions) {
		parent::__construct($table, implode(' / ', $actions));
		$this->actions = $actions;
	}

	public function shouldReload() : bool {
		return true;
	}

	public function __toString() {
		$html = [];
		foreach ($this->actions as $action => $text) {
			$html[] = '<button name="action" value="' . $action . '">' . do_html($text) . '</button>';
		}
		return implode(' ', $html);
	}
}

class PokerStatusAction extends PokerStatusActions {
	public function __construct(Table $table, string $action, string $label) {
		parent::__construct($table, [$action => $label]);
	}
}
