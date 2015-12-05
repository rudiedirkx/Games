<?php

class PokerTexasHoldem {

	// public $cards = array();

	public $values = array();
	public $num_values = array();

	public $suits = array();
	public $suit = '';

	/**
	 *
	 */
	public function __construct( $f_arrCards ) {
		// $this->cards = $f_arrCards;

		$num_suits = array();
		foreach ( $f_arrCards AS $objCard ) {
			// Create List<Int value>
			$this->values[] = $objCard->pth;

			// Create Hash{Int value: Int count}
			@$this->num_values[$objCard->pth]++;

			// Create List<String suit>
			$this->suits[] = $objCard->suit;

			// Create Hash{String suit: Int count}
			@$num_suits[$objCard->suit]++;
		}

		// Make sure higher come first
		arsort($this->values);

		// Find the suit with 5 cards
		foreach ($num_suits as $suit => $num) {
			if ($num >= 5) {
				$this->suit = $suit;
				break;
			}
		}
	}

	/**
	 *
	 */
	static public function winnerCardsAndSuit( $f_fHand ) {
		$szDetails = substr((string)$f_fHand, 2);
		$arrFiveCards = array();
		$szSuit = null;

		switch ( (int)$f_fHand ) {
			case 0: // hi card
			case 5: // flush
				$arrFiveCards = array((int)substr($szDetails, 0, 2), (int)substr($szDetails, 2, 2), (int)substr($szDetails, 4, 2), (int)substr($szDetails, 6, 2), (int)substr($szDetails, 8, 2));
				if ( 5 == (int)$f_fHand ) {
					$szSuit = substr($szDetails, 10);
				}
			break;
			case 1: // pair
				$arrFiveCards = array((int)substr($szDetails, 0, 2), (int)substr($szDetails, 0, 2), (int)substr($szDetails, 2, 2), (int)substr($szDetails, 4, 2), (int)substr($szDetails, 6, 2));
			break;
			case 2: // 2 pair
				$arrFiveCards = array((int)substr($szDetails, 0, 2), (int)substr($szDetails, 0, 2), (int)substr($szDetails, 2, 2), (int)substr($szDetails, 2, 2), (int)substr($szDetails, 4, 2));
			break;
			case 3: // 3 of a kind
				$arrFiveCards = array((int)substr($szDetails, 0, 2), (int)substr($szDetails, 0, 2), (int)substr($szDetails, 0, 2), (int)substr($szDetails, 2, 2), (int)substr($szDetails, 4, 2));
			break;
			case 4: // straight
				$s = (int)substr($szDetails, 0, 2);
				for ( $i=$s; $i>$s-5; $i-- ) {
					$arrFiveCards[] = $i;
				}
			break;
			case 6: // full house
				$arrFiveCards = array((int)substr($szDetails, 0, 2), (int)substr($szDetails, 0, 2), (int)substr($szDetails, 0, 2), (int)substr($szDetails, 2, 2), (int)substr($szDetails, 2, 2));
			break;
			case 7: // 4 of a kind
				$arrFiveCards = array((int)substr($szDetails, 0, 2), (int)substr($szDetails, 0, 2), (int)substr($szDetails, 0, 2), (int)substr($szDetails, 0, 2), (int)substr($szDetails, 2, 2));
			break;
			case 8:

			break;
			case 9:

			break;
		}

		$arrFiveCards = array_count_values($arrFiveCards);
		if ( isset($arrFiveCards[1]) ) {
			$arrFiveCards[14] = $arrFiveCards[1];
			unset($arrFiveCards[1]);
		}

		return array($arrFiveCards, $szSuit);
	}

	/**
	 *
	 */
	static public function readable_hand( $f_fHand ) {
		$arrCardsText = array(2 => 'Twos', 'Threes', 'Fours', 'Fives', 'Sixes', 'Sevens', 'Eights', 'Nines', 'Tens', 'Jacks', 'Queens', 'Kings', 'Aces');
		$arrCardsShort = array(2 => '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K', 'A');
		$x = explode('.', (string)$f_fHand, 2);
		$szExtra = isset($x[1]) ? $x[1] : '';
		$szHand = '';

		switch ( (int)$x[0] ) {
			case 9:
				$szHand = 'Royal Flush of '.ucfirst(strtolower($szExtra)).'';
			break;

			case 8:
				$szHand = 'Straight Flush of '.ucfirst(strtolower(substr($szExtra, 2))).' - '.$arrCardsText[(int)substr($szExtra, 0, 2)].' high';
			break;

			case 7:
				$szHand = 'Four Of A Kind - '.$arrCardsText[(int)substr($szExtra, 0, 2)].'';
			break;

			case 6:
				$szHand = 'Full House - '.$arrCardsText[(int)substr($szExtra, 0, 2)].' over '.$arrCardsText[(int)substr($szExtra, 2, 2)].'';
			break;

			case 5:
				$szHand = 'Flush of '.ucfirst(strtolower(substr($szExtra, 10))).' - '.$arrCardsText[(int)substr($szExtra, 0, 2)].' high';
			break;

			case 4:
				$szHand = 'Straight - '.$arrCardsText[(int)substr($szExtra, 0, 2)].' high';
			break;

			case 3:
				$szHand = 'Three Of A Kind - '.$arrCardsText[(int)substr($szExtra, 0, 2)].'';
			break;

			case 2:
				$szHand = 'Two Pairs - '.$arrCardsText[(int)substr($szExtra, 0, 2)].' and '.$arrCardsText[(int)substr($szExtra, 2, 2)].'';
			break;

			case 1:
				$szHand = 'One Pair of '.$arrCardsText[(int)substr($szExtra, 0, 2)].'';
			break;

			case 0:
			default:
				$arrKickers = array($arrCardsShort[(int)substr($szExtra, 0, 2)]);
				if ( 0 < ($c=(int)substr($szExtra, 2, 2)) ) {
					$arrKickers[] = $arrCardsShort[$c];
				}
				if ( 0 < ($c=(int)substr($szExtra, 4, 2)) ) {
					$arrKickers[] = $arrCardsShort[$c];
				}
				if ( 0 < ($c=(int)substr($szExtra, 6, 2)) ) {
					$arrKickers[] = $arrCardsShort[$c];
				}
				if ( 0 < ($c=(int)substr($szExtra, 8, 2)) ) {
					$arrKickers[] = $arrCardsShort[$c];
				}
				$szHand = 'High Cards '.implode(', ', $arrKickers).'';
			break;
		}

		return $szHand;
	}

	/**
	 * 9
	 */
	public function royal_flush() {
		$fn = __FUNCTION__;
		if ( isset($this->$fn) ) {
			return $this->$fn;
		}

		$szStraigtFlush = $this->straight_flush();
		if ( null !== $szStraigtFlush && '14' === substr($szStraigtFlush, 0, 2) ) {
			$this->$fn = substr($szStraigtFlush, 2);
			return $this->$fn;
		}
	}

	/**
	 * 8
	 */
	public function straight_flush() {
		$fn = __FUNCTION__;
		if ( isset($this->$fn) ) {
			return $this->$fn;
		}

		if ( null === ($szSuit=$this->flush(true)) ) {
			return;
		}

		$arrCards = array();
		foreach ( $this->values AS $iCard => $iValue ) {
			if ( $szSuit == $this->suits[$iCard] ) {
				$arrCards[] = $iValue;
			}
		}

		if ( null !== ($szHiCard=$this->straight($arrCards)) ) {
			$this->$fn = $szHiCard.$szSuit;
			return $this->$fn;
		}
	}

	/**
	 * 7
	 */
	public function four_of_a_kind() {
		$fn = __FUNCTION__;
		if ( isset($this->$fn) ) {
			return $this->$fn;
		}

		foreach ( $this->num_values AS $iValue => $iAmount ) {
			if ( 4 <= $iAmount ) {
				$szExtra = self::padleft($iValue);
				foreach ( $this->values AS $v ) {
					if ( $v != $iValue ) {
						$szExtra .= self::padleft($v);
						$this->$fn = $szExtra;
						return $szExtra;
					}
				}
			}
		}
	}

	/**
	 * 6
	 */
	public function full_house() {
		$fn = __FUNCTION__;
		if ( isset($this->$fn) ) {
			return $this->$fn;
		}

		if ( null !== ($szThreeOfAKind=$this->three_of_a_kind($v)) ) {
			$bck = $this->num_values;
			unset($this->num_values[$v]);
			if ( null !== ($szPair=$this->one_pair($v)) ) {
				$this->num_values = $bck;
				$this->$fn = substr($szThreeOfAKind, 0, 2).substr($szPair, 0, 2);
				return $this->$fn;
			}
			$this->num_values = $bck;
		}
	}

	/**
	 * 5
	 */
	public function flush($f_bSimple = false) {
		$fn = __FUNCTION__;
		if ( isset($this->$fn) ) {
			return $this->$fn;
		}

		if ( $this->suit ) {
			$szSuit = $this->suit;
			if ( $f_bSimple ) {
				return $szSuit;
			}

			$szExtra = '';
			foreach ( $this->values AS $iCard => $iValue ) {
				if ( $szSuit == $this->suits[$iCard] && 10 > strlen($szExtra) ) {
					$szExtra .= self::padleft($iValue);
				}
				if ( 10 <= strlen($szExtra) ) {
					break;
				}
			}
			$this->$fn = $szExtra.$szSuit;
			return $this->$fn;
		}
	}

	/**
	 * 4
	 */
	public function straight($f_arrValues = null) {
		$fn = __FUNCTION__;
		if ( isset($this->$fn) ) {
			return $this->$fn;
		}

		$arrValues = is_array($f_arrValues) ? $f_arrValues : array_keys($this->num_values);
		if ( 5 > count($arrValues) ) {
			// Not even 5 different cards
			return;
		}

		for ( $i=0; $i<=count($arrValues)-5; $i++ ) {
			// loop next 5 cards
			$iHiCard = $iPrevValue = $arrValues[$i];
			$bOk = true;
			for ( $j=$i+1; $j<$i+5; $j++ ) {
				if ( $arrValues[$j] != $iPrevValue-1 ) {
					$bOk = false;
					break;
				}
				$iPrevValue = $arrValues[$j];
			}
			if ( $bOk ) {
				$this->$fn = self::padleft($iHiCard);
				return $this->$fn;
			}
		}

		# ace to 5
		if ( in_array(14, $arrValues) && in_array(2, $arrValues) && in_array(3, $arrValues) && in_array(4, $arrValues) && in_array(5, $arrValues) ) {
			$this->$fn = '05';
			return $this->$fn;
		}
	}

	/**
	 * 3
	 */
	public function three_of_a_kind(&$f_pv = null) {
		$fn = __FUNCTION__;
		if ( isset($this->$fn) ) {
			return $this->$fn;
		}

		foreach ( $this->num_values AS $iValue => $iAmount ) {
			if ( 3 <= $iAmount ) {
				$f_pv = $iValue;
				$szExtras = self::padleft($iValue);
				foreach ( $this->values AS $v ) {
					if ( $iValue != $v && 6 > strlen($szExtras) ) {
						$szExtras .= self::padleft($v);
					}
					if ( 6 <= strlen($szExtras) ) {
						break;
					}
				}
				$this->$fn = $szExtras;
				return $szExtras;
			}
		}
	}

	/**
	 * 2
	 */
	public function two_pair(&$v1 = null, &$v2 = null) {
		$fn = __FUNCTION__;
		if ( isset($this->$fn) ) {
			return $this->$fn;
		}

		$szExtras = '';
		$iVal1 = $iVal2 = 0;
		foreach ( $this->num_values AS $iValue => $iAmount ) {
			if ( 2 <= $iAmount && 4 > strlen($szExtras) ) {
				$szExtras .= self::padleft($iValue);
				if ( $iVal1 == 0 ) { $iVal1 = $iValue; }
				else { $iVal2 = $iValue; }
			}
		}

		if ( 4 == strlen($szExtras) ) {
			foreach ( $this->values AS $v ) {
				if ( $iVal1 != $v && $iVal2 != $v ) {
					$szExtras .= self::padleft($v);
					break;
				}
			}
			$this->$fn = $szExtras;
			return $szExtras;
		}
	}

	/**
	 * 1
	 */
	public function one_pair(&$f_pv = null) {
		$fn = __FUNCTION__;
		if ( isset($this->$fn) ) {
			return $this->$fn;
		}

		foreach ( $this->num_values AS $iValue => $iAmount ) {
			if ( 2 <= $iAmount ) {
				$f_pv = $iValue;
				$szExtras = self::padleft($iValue);
				foreach ( $this->values AS $v ) {
					if ( $iValue != $v && 8 > strlen($szExtras) ) {
						$szExtras .= self::padleft($v);
					}
					if ( 8 <= strlen($szExtras) ) {
						break;
					}
				}
				$this->$fn = $szExtras;
				return $szExtras;
			}
		}
	}

	/**
	 *
	 */
	public function _score() {
		$arrCheckingOrder = array(
			9 => 'royal_flush',
			8 => 'straight_flush',
			7 => 'four_of_a_kind',
			6 => 'full_house',
			5 => 'flush',
			4 => 'straight',
			3 => 'three_of_a_kind',
			2 => 'two_pair',
			1 => 'one_pair',
		);

		$iScore = 0;
		foreach ( $arrCheckingOrder AS $iHandValue => $szCall ) {
			if ( null !== ($szExtra = $this->$szCall = call_user_func(array($this, $szCall))) ) {
				$iScore = $iHandValue;
				break;
			}
		}

		if ( 1 > $iScore ) {
			// high card
			$iScore = 0;
			$szExtra = '';
			foreach ( $this->values AS $v ) {
				if ( 10 > strlen($szExtra) ) {
					$szExtra .= self::padleft($v);
				}
				if ( 10 <= strlen($szExtra) ) {
					break;
				}
			}
		}

		return $iScore . '.' . $szExtra;
	}

	/**
	 *
	 */
	static public function padleft($s) {
		return str_pad((string)$s, 2, '0', STR_PAD_LEFT);
	}

	/**
	 *
	 */
	static public function score($cards, &$object = null) {
		$object = new self($cards);
		return $object->_score();
	}

}
