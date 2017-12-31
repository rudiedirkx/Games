<?php

class Card {
	public static $__tostring = null;
	public static $tostring = 'image';
	public static $image_path = '/images/__SUIT_____SHORT__.gif';

	protected static $suits = array('clubs', 'diamonds', 'hearts', 'spades');
	protected static $names = array('ace', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'jack', 'queen', 'king');
	protected static $shortNames = array('a', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'j', 'q', 'k');

	public $id = -1;
	public $name = '';
	public $suit = '';
	public $value = -1;
	public $short = '';
	public $pth = -1;

	public function __construct($f_iCard) {
		$iCard = (int)$f_iCard%52;
		$iSuit = floor($iCard/13);
		$iName = $iCard%13;

		$arrValues = array(11, 2, 3, 4, 5, 6, 7, 8, 9, 10, 10, 10, 10);
		$arrPTHValues = array(14, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13);

		$this->id = $iCard;
		$this->suit = self::$suits[$iSuit];
		$this->name = self::$names[$iName];
		$this->value = $arrValues[$iName];
		$this->short = self::$shortNames[$iName];
		$this->pth = $arrPTHValues[$iName];
	}

	public function __tostring() {
		if ( null === self::$__tostring || !is_callable(self::$__tostring) ) {
			self::$__tostring = function($c) {
				return '<img src="/images/' . $c->suit . '_' . $c->short . '.gif" title="' . $c->fullname() . '" />';
			};
		}
		return call_user_func(self::$__tostring, $this);
	}

	public function fullname() {
		return $this->name . ' of ' . $this->suit;
	}

	public static function random() {
		return new Card(rand(0,51));
	}

	public static function named($inputs) {
		$output = array();
		foreach ((array) $inputs as $input) {
			$inputSuit = $input[0];
			$suit = 0;
			foreach (self::$suits as $value => $name) {
				if ($name[0] == $input[0]) {
					$suit = $value;
				}
			}

			$inputCard = substr($input, 1);
			$card = 0;
			foreach (self::$shortNames as $value => $name) {
				if ($name == $inputCard) {
					$card = $value;
				}
			}

			$output[] = new self($suit * 13 + $card);
		}

		return is_string($inputs) ? $output[0] : $output;
	}
}

class Deck {
	public $iNextCard = 0; # protected
	public $cards = array(); # protected
	public function __construct($f_bFill = true) {
		if ( $f_bFill ) {
			foreach ( range(0, 51) AS $iCard ) {
				array_push($this->cards, new Card($iCard));
			}
		}
	}
	public function next() {
		if ( !isset($this->cards[$this->iNextCard]) ) {
			return null;
		}
		return $this->cards[$this->iNextCard++];
	}
	public function size() {
		return (count($this->cards)-$this->iNextCard);
	}
	public function add_deck(Deck $objDeck) {
		$this->cards = array_merge($this->cards, $objDeck->cards);
		return $this;
	}
	public function add_card(Card $objCard) {
		array_push($this->cards, $objCard);
		return $this;
	}
	public function shuffle() {
		return shuffle($this->cards);
	}
	public function replenish() {
		$this->iNextCard = 0;
		$this->shuffle();
	}
	public function __tostring() {
		return implode("\n", $this->cards);
	}
}


