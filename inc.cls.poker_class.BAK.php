<?php

$cards = $STATIC['cards'] = array("","h.2","h.3","h.4","h.5","h.6","h.7","h.8","h.9","h.10","h.11","h.12","h.13","h.14","c.2","c.3","c.4","c.5","c.6","c.7","c.8","c.9","c.10","c.11","c.12","c.13","c.14","d.2","d.3","d.4","d.5","d.6","d.7","d.8","d.9","d.10","d.11","d.12","d.13","d.14","s.2","s.3","s.4","s.5","s.6","s.7","s.8","s.9","s.10","s.11","s.12","s.13","s.14");
$kleuren = $STATIC['kleuren'] = array("h"=>"hearts","c"=>"clubs","d"=>"diamonds","s"=>"spades");
$cardnames = $STATIC['cardnames'] = array("","10","2","3","4","5","6","7","8","9","10","J","Q","K","A");

$STATIC['hands'] = Array(
	0 => "HIGH_CARD",
	1 => "ONE_PAIR",
	2 => "TWO_PAIRS",
	3 => "THREE_OF_A_KIND",
	4 => "STRAIGHT",
	5 => "FLUSH",
	6 => "FULL_HOUSE",
	7 => "FOUR_OF_A_KIND",
	8 => "STRAIGHT_FLUSH",
	9 => "ROYAL_FLUSH"
);
foreach ( $STATIC['hands'] AS $val => $name )
{
	define( "HANDS_" . $name, $val );
}



function make_readable_hand( $hand )
{
	global $STATIC;

	if ( "BLUF" == strtoupper($hand) )
	{
		return "NO-SHO";
	}

	$arrTextCards = Array(	"2" => "Twos",
							"3" => "Threes",
							"4" => "Fours",
							"5" => "Fives",
							"6" => "Sixes",
							"7" => "Sevens",
							"8" => "Eights",
							"9" => "Nines",
							"10"=> "Tens",
							"J" => "Jacks",
							"Q" => "Queens",
							"K" => "Kings",
							"A" => "Aces");

	if ( !is_numeric(substr($hand,-1,1)) )
	{
		$SUIT = substr($hand, -1, 1);
		$hand = substr($hand, 0, -1);
	}

	$iType = floor($hand);
	$szType = $out = str_replace("_", " ", $STATIC['hands'][$iType]);
	$szDetails = substr((STRING)($hand-$iType), 2);

	$arrDetails[1] = $STATIC['cardnames'][(INT)substr($szDetails,0,2)];
	if ( 2 < strlen($szDetails) ) $arrDetails[2] = $STATIC['cardnames'][(INT)substr($szDetails,2,2)];
	if ( 4 < strlen($szDetails) ) $arrDetails[3] = $STATIC['cardnames'][(INT)substr($szDetails,4,2)];
	if ( 6 < strlen($szDetails) ) $arrDetails[4] = $STATIC['cardnames'][(INT)substr($szDetails,6,2)];
	if ( 8 < strlen($szDetails) ) $arrDetails[5] = $STATIC['cardnames'][(INT)substr($szDetails,8,2)];

	switch ( $iType )
	{
		case HANDS_HIGH_CARD:
			$out .= ': ' . implode(", ", $arrDetails);
		break;

		case HANDS_ONE_PAIR:
			$out .= ' of '.$arrTextCards[$arrDetails[1]]." (with ".$arrDetails[2].", ".$arrDetails[3]." and ".$arrDetails[4].")";
		break;

		case HANDS_TWO_PAIRS:
			$out .= ' - '.$arrTextCards[$arrDetails[1]]." and ".$arrTextCards[$arrDetails[2]]." (with a ".$arrDetails[3]." kicker)";
		break;

		case HANDS_THREE_OF_A_KIND:
			$out .= ' - '.$arrTextCards[$arrDetails[1]]." (with ".$arrDetails[2]." and ".$arrDetails[3].")";
		break;

		case HANDS_STRAIGHT:
			$out .= ' with '.$arrDetails[1].' hi-card';
		break;    

		case HANDS_FLUSH:
			$out .= ' of '.strtoupper($STATIC['kleuren'][$SUIT]).' with ' . implode(", ", $arrDetails);
		break;

		case HANDS_FULL_HOUSE:
			$out .= ' ('.$arrTextCards[$arrDetails[1]]." over ".$arrTextCards[$arrDetails[2]].")";
		break;

		case HANDS_FOUR_OF_A_KIND:
			$out .= ' of '.$arrTextCards[$arrDetails[1]]." (with ".$arrDetails[2].")";
		break;

		case HANDS_STRAIGHT_FLUSH:
			$out .= ' of '.strtoupper($STATIC['kleuren'][$SUIT]).' with '.$arrDetails[1].' hi-card';
		break;

		case HANDS_ROYAL_FLUSH:
			$out .= ' of '.strtoupper($STATIC['kleuren'][$SUIT]).'';
		break;
	}
	return $out;
}

function return_hand( $arrCards )
{
	// Init `final hand`-array
	$arrMatchingHands = Array( );

	if ( empty($arrCards) || !is_array($arrCards) )
	{
		trigger_error("THIS FUNCTION NEEDS AN ARRAY", E_USER_ERROR);
	}

	$tmp_cards = Array( );
	foreach ( $arrCards AS $card )
	{
		list($suit,$value) = explode(".", $card, 2);
		$tmp['suit'] = $suit;
		$tmp['value'] = $value;
		$tmp_cards[] = $tmp;
	}
	$arrCardsPerCard = $tmp_cards;
	// print_r( $arrCardsPerCard );

	// sort by suit/value
	$tmp_2d_cards = flip_2d_array($arrCardsPerCard);

	// Save suits
	$arrSuits = $tmp_2d_cards['suit'];
	asort($arrSuits);

	// Save values
	$arrValues = $tmp_2d_cards['value'];
	asort($arrValues);

	// print_r( $arrSuits );
	// print_r( $arrValues );

	// Count suits per suit
	$arrNumSuits['c'] = $arrNumSuits['d'] = $arrNumSuits['h'] = $arrNumSuits['s'] = 0;
	foreach ( $arrSuits AS $suit )
	{
		$arrNumSuits[$suit]++;
	}
	// print_r( $arrNumSuits );

	// Count values per value
	foreach ( $arrValues AS $value )
	{
		if ( empty($arrNumValues[$value]) )	$arrNumValues[$value] = 1;
		else								$arrNumValues[$value]++;
	}
	// print_r( $arrNumValues );


	/** CHECK FOR FLUSH **/
	$result_flush = poker_check_for_flush( $arrNumValues, $arrNumSuits, $tmp_2d_cards );
	// var_dump( $result_pairs );
	if ( FALSE !== $result_flush && (!isset($arrMatchingHands[0]) || $result_flush[0] > $arrMatchingHands[0]) )
	{
		$arrMatchingHands = $result_flush;
	}


	/** CHECK FOR FULL-HOUSE **/
	$result_full_house = poker_check_for_full_house( $arrNumValues, $arrNumSuits );
	// var_dump( $result_pairs );
	if ( FALSE !== $result_full_house && (!isset($arrMatchingHands[0]) || $result_full_house[0] > $arrMatchingHands[0]) )
	{
		$arrMatchingHands = $result_full_house;
	}


	/** CHECK FOR STRAIGHT **/
	$result_straight = poker_check_for_straight( $arrNumValues, $arrNumSuits );
	// var_dump( $result_pairs );
	if ( FALSE !== $result_straight && (!isset($arrMatchingHands[0]) || $result_straight[0] > $arrMatchingHands[0]) )
	{
		$arrMatchingHands = $result_straight;
	}


	/** CHECK FOR PAIRS **/
	$result_pairs = poker_check_for_pairs( $arrNumValues, $arrNumSuits );
	// var_dump( $result_pairs );
	if ( FALSE !== $result_pairs && (!isset($arrMatchingHands[0]) || $result_pairs[0] > $arrMatchingHands[0]) )
	{
		$arrMatchingHands = $result_pairs;
	}


	/** CHECK FOR THREE-OF-A-KIND **/
	$result_three_of_a_kind = poker_check_for_three_of_a_kind( $arrNumValues, $arrNumSuits );
	// var_dump( $result_three_of_a_kind );
	if ( FALSE !== $result_three_of_a_kind && (!isset($arrMatchingHands[0]) || $result_three_of_a_kind[0] > $arrMatchingHands[0]) )
	{
		$arrMatchingHands = $result_three_of_a_kind;
	}


	/** CHECK FOR FOUR-OF-A-KIND **/
	$result_four_of_a_kind = poker_check_for_four_of_a_kind( $arrNumValues, $arrNumSuits );
	// var_dump( $result_four_of_a_kind );
	if ( FALSE !== $result_four_of_a_kind && (!isset($arrMatchingHands[0]) || $result_four_of_a_kind[0] > $arrMatchingHands[0]) )
	{
		$arrMatchingHands = $result_four_of_a_kind;
	}




	/** ROYAL FLUSH **/
	// Covered in FLUSH area :)




	/** CHECK FOR HIGH-CARD (if all other fails) **/
	if ( !isset($arrMatchingHands[0]) )
	{
		// HIGH-CARD!
		krsort($arrNumValues, SORT_NUMERIC); // Get the 5 highest cards

		unset($tmpHand);
		$tmpHand[0] = HANDS_HIGH_CARD;
		foreach ( $arrNumValues AS $value => $num )
		{
			if ( 5 >= count($tmpHand) )
			{
				$tmpHand[] = $value;
			}
		}
		// 5 highest cards
		// $OWNED_HANDS_HIGH_CARD = TRUE;
		$arrMatchingHands = $tmpHand;
	}

// print_r( $arrMatchingHands );

	$hands2compare = '';
	for ( $i=1; $i<count($arrMatchingHands); $i++ )
	{
		if ( 10 == $arrMatchingHands[$i] ) $arrMatchingHands[$i] = 1;
		if ( !is_numeric($arrMatchingHands[$i]) )
		{
			$hands2compare .= $arrMatchingHands[$i];
		}
		else
		{
			$hands2compare .= str_pad($arrMatchingHands[$i], 2, '0', STR_PAD_LEFT);
		}
	}
	$hands2compare = (FLOAT)$arrMatchingHands[0] . '.' . $hands2compare;
	// Return FLOAT to compare to other player(s)
	return $hands2compare;

	// Return ARRAY with hand and kickers
	return( $arrMatchingHands );

} // END return_hand( )



function poker_check_for_pairs( $arrNumValues, $arrNumSuits )
{
	// Maybe there isn't even one pair
	if ( in_array(2, $arrNumValues) )
	{
		// At least ONE pair
		krsort($arrNumValues, SORT_NUMERIC); // highest cards first, in case of three pairs :)
		foreach ( $arrNumValues AS $value => $num )
		{
			if ( 2 == $num )
			{
				$arrPairs[] = $value;
			}
		}

		// Get three or one kicker(s)
		$tmp_arrNumValues = $arrNumValues;
		unset( $tmp_arrNumValues[$arrPairs[0]] );

		if ( 1 == count($arrPairs) )
		{
			// ONE PAIR
			unset($tmpHand);
			$tmpHand[0] = HANDS_ONE_PAIR;
			$tmpHand[1] = $arrPairs[0];
			// Add kickers
			foreach ( $tmp_arrNumValues AS $value => $num )
			{
				if ( 5 > count($tmpHand) )
				{
					$tmpHand[] = $value;
				}
			}
			// a pair is of (one) same value
			// $OWNED_HANDS_ONE_PAIR = TRUE;
			$arrMatchingHands = $tmpHand;
		}
		else if ( 2 == count($arrPairs) OR 3 == count($arrPairs) )
		{
			// TWO PAIRS
			unset($tmpHand);
			$tmpHand[0] = HANDS_TWO_PAIRS;
			$tmpHand[1] = $arrPairs[0];
			$tmpHand[2] = $arrPairs[1];
			// Add kickers
			unset( $tmp_arrNumValues[$arrPairs[1]] );
			foreach ( $tmp_arrNumValues AS $value => $num )
			{
				if ( 4 > count($tmpHand) )
				{
					$tmpHand[] = $value;
				}
			}
			// two pairs have two values
			// $OWNED_HANDS_TWO_PAIRS = TRUE;
			$arrMatchingHands = $tmpHand;
		}
		return $arrMatchingHands;

	} // if ( PAIRS >= 1 )

	// Fall-through, no matches found
	return FALSE;

} // END poker_check_for_pairs( )


function poker_check_for_three_of_a_kind( $arrNumValues, $arrNumSuits )
{
	if ( in_array(3, $arrNumValues) )
	{
		// THREE-OF-A-KIND
		krsort($arrNumValues, SORT_NUMERIC); // highest cards first, in case of two three-of-a-kind's
		foreach ( $arrNumValues AS $value => $num )
		{
			if ( 3 == $num )
			{
				$arrThrees[] = $value;
			}
		}

		unset($tmpHand);
		$tmpHand[0] = HANDS_THREE_OF_A_KIND;
		$tmpHand[1] = $arrThrees[0];

		// Get & Add kickers (2)
		$tmp_arrNumValues = $arrNumValues;
		unset( $tmp_arrNumValues[$arrThrees[0]] );
		// print_r( $tmp_arrNumValues );
		foreach ( $tmp_arrNumValues AS $value => $num )
		{
			if ( 4 > count($tmpHand) )
			{
				$tmpHand[] = $value;
			}
		}

		// there is only ONE three-of-a-kind, with all same values
		// $OWNED_HANDS_THREE_OF_A_KIND = TRUE;
		$arrMatchingHands = $tmpHand;

		return $arrMatchingHands;

	} // if ( THREE-OF-A-KIND )

	// Fall-through, no matches found
	return FALSE;

} // END poker_check_for_thee_of_a_kind( )

function poker_check_for_four_of_a_kind( $arrNumValues, $arrNumSuits )
{
	if ( in_array(4, $arrNumValues) )
	{
		// FOUR-OF-A-KIND
		unset($tmpHand);
		$tmpHand[0] = HANDS_FOUR_OF_A_KIND;
		$tmpHand[1] = array_search(4, $arrNumValues);

		// Get & Add kicker
		$tmp_arrNumValues = $arrNumValues;
		unset( $tmp_arrNumValues[$tmpHand[1]] );
		krsort($tmp_arrNumValues);
// print_r( $tmp_arrNumValues );
		reset( $tmp_arrNumValues );
		$tmpHand[] = key($tmp_arrNumValues);

		// there is only ONE four-of-a-kind, with all same values
		// $OWNED_HANDS_FOUR_OF_A_KIND = TRUE;
		$arrMatchingHands = $tmpHand;

		return $arrMatchingHands;

	} // if ( FOUR-OF-A-KIND )

	// Fall-through, no matches found
	return FALSE;

} // END poker_check_for_four_of_a_kind( )

function poker_check_for_straight( $arrNumValues, $arrNumSuits )
{
	if ( 5 <= count($arrNumValues) )
	{
		$arrDifCards = array_keys($arrNumValues); // count is 5, 6 of 7
		rsort($arrDifCards, SORT_NUMERIC);

// print_r( $arrNumValues );

		// FIRST LOOP (1/3)
		$is_straight = TRUE;
		$straight_highcard = $arrDifCards[0];
		for ( $i=0; $i<5; $i++ )
		{
			if ( !isset($last_card) || $last_card == $arrDifCards[$i]+1 )
			{
				// het kan nog
			}
			else
			{
				$is_straight = FALSE;
				break;
			}
			$last_card = $arrDifCards[$i];
		}

		// SECOND LOOP (2/3)
		if ( !$is_straight )
		{
			$is_straight = TRUE;
			$straight_highcard = $arrDifCards[1];
			for ( $i=1; $i<6; $i++ )
			{
				if ( !isset($last_card) || $last_card == $arrDifCards[$i]+1 )
				{
					// het kan nog
				}
				else
				{
					$is_straight = FALSE;
					break;
				}
				$last_card = $arrDifCards[$i];
			}
		}

		// THIRD LOOP (3/3)
		if ( !$is_straight )
		{
			$is_straight = TRUE;
			$straight_highcard = $arrDifCards[2];
			for ( $i=2; $i<7; $i++ )
			{
				if ( !isset($last_card) || $last_card == $arrDifCards[$i]+1 )
				{
					// het kan nog
				}
				else
				{
					$is_straight = FALSE;
					break;
				}
				$last_card = $arrDifCards[$i];
			}
		}

		if ( !$is_straight )
		{
			if ( in_array(14, $arrDifCards) && 
				 in_array(2, $arrDifCards) && 
				 in_array(3, $arrDifCards) && 
				 in_array(4, $arrDifCards) && 
				 in_array(5, $arrDifCards) )
			{
				unset($tmpHand);
				$tmpHand[0] = HANDS_STRAIGHT;
				$tmpHand[1] = 5;
				// a STRAIGHT has only one highest card
				// $OWNED_HANDS_STRAIGHT = TRUE;
				$arrMatchingHands = $tmpHand;

				return $arrMatchingHands;
			}
		}

		if ( $is_straight )
		{
			unset($tmpHand);
			$tmpHand[0] = HANDS_STRAIGHT;
			$tmpHand[1] = $straight_highcard;
			// a STRAIGHT has only one highest card
			// $OWNED_HANDS_STRAIGHT = TRUE;
			$arrMatchingHands = $tmpHand;

			return $arrMatchingHands;
		}

	} // if ( ~~ STRAIGHT )

	// Fall-through, no matches found
	return FALSE;

} // END poker_check_for_straight( )

function poker_check_for_full_house( $arrNumValues, $arrNumSuits )
{
	if ( in_array(2, $arrNumValues) && in_array(3, $arrNumValues) )
	{
		unset($tmpHand);

		// There IS a Full House, but with what cards :)
		$tmpHand[0] = HANDS_FULL_HOUSE;

		// Get the `three-cards`
		$tmpHand[1] = array_search(3, $arrNumValues);

		// Search for the `two-cards`s
		$tmp_twocards = array( ); // will be filled with one or two values
		foreach ( $arrNumValues AS $value => $num )
		{
			if ( 2 == $num ) $tmp_twocards[] = $value;
		}
		if ( 1 == count($tmp_twocards) ) $tmpHand[2] = $tmp_twocards[0];
		else
		{
			// Sort the two `two-cards`s by highest value first and return that one
			rsort($tmp_twocards, SORT_NUMERIC);
			reset($tmp_twocards);
			$tmpHand[2] = current($tmp_twocards);
		}
		// a FULL HOUSE contains two different values
		// $OWNED_HANDS_FULL_HOUSE = TRUE;
		$arrMatchingHands = $tmpHand;

		return $arrMatchingHands;
		
	} // if ( FULL_HOUSE )

	return FALSE;

} // END poker_check_for_full_house( )

function poker_check_for_flush( $arrNumValues, $arrNumSuits, $tmp_2d_cards )
{
	if ( in_array(5, $arrNumSuits) || in_array(6, $arrNumSuits) || in_array(6, $arrNumSuits) )
	{
		// >> Get five highest cards in this suit!
		// Get suit
		foreach ( $arrNumSuits AS $tmpSuit => $num )
		{
			if ( 5 <= $num )
			{
				$_suit = $tmpSuit;
				break;
			}
		}
		// Delete cards in array who are not of this suit
		$tmp_tmp_2d_cards = $tmp_2d_cards;
		$arrKeepCards = Array( ); // Will be at least 5 in size (5-7)
		foreach ( $tmp_tmp_2d_cards['suit'] AS $key => $suit )
		{
			if ( $_suit == $suit )
			{
				$arrKeepCards[] = $tmp_tmp_2d_cards['value'][$key];
			}
		}
		// $arrKeepCards contains cards of one suit << check this array for a straight and we got STRAIGHT_FLUSH covered
		rsort($arrKeepCards, SORT_NUMERIC);
		$arrFlushCards = array_flip($arrKeepCards);
		foreach ( $arrFlushCards AS $value => $num )
		{
			$arrFlushCards[$value] = 1;
		}
		// print_r( $arrKeepCards2 );


		/** CHECK FOR STRAIGHT FLUSH **/
		$result_straight_flush = poker_check_for_straight( $arrFlushCards, $arrNumSuits );
		if ( FALSE !== $result_straight_flush )
		{
			unset($tmpHand);
			if ( 14 == $arrKeepCards[0] )
			{
				/** ROYAL STRAIGHT FLUSH **/
				$tmpHand[0] = HANDS_ROYAL_FLUSH;
			}
			else
			{
				/** STRAIGHT FLUSH **/
				$tmpHand[0] = HANDS_STRAIGHT_FLUSH;
				// Highest card in this simple straight ;)
				$tmpHand[1] = $arrKeepCards[0];
			}
			$tmpHand[] = $_suit;
			$arrMatchingHands = $tmpHand;

			return $arrMatchingHands;
		}


		unset($tmpHand);
		$tmpHand[0] = HANDS_FLUSH;
		for ( $i=0; $i<5; $i++ )
		{
			$tmpHand[] = $arrKeepCards[$i];
		}
		$tmpHand[] = $_suit;
		// a FLUSH has only one value-variant
		// $OWNED_HANDS_FLUSH = TRUE;
		$arrMatchingHands = $tmpHand;

		return $arrMatchingHands;

	} // if ( FLUSH )

	return FALSE;

} // END poker_check_for_flush( )











function flip_2d_array( $arr )
{
	$arrRetval = Array( );
	foreach ( $arr AS $szNewLevelTwo => $arrElements )
	{
		foreach ( $arrElements AS $szNewLevelOne => $szElementContent )
		{
			$arrRetval[$szNewLevelOne][$szNewLevelTwo] = $szElementContent;
		}
	}
	return $arrRetval;

} // END flip_2d_array( )

?>