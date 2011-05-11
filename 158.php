<?php
// Fortune's Tower

session_start();
define('S_NAME', 'ft_php');


if ( !empty($_GET['reset']) || empty($_SESSION[S_NAME]) ) {
	function getTowerCards() {
		$g_arrCards = array_merge(array(0,0,0,0),range(1,7),range(1,7),range(1,7),range(1,7),range(1,7),range(1,7),range(1,7),range(1,7));
		shuffle($g_arrCards);
		$arrCards = array();
		for ( $i=0; $i<8; $i++ ) {
			$arrCards[] = array_splice($g_arrCards, 0, $i+1);
		}
		return $arrCards;
	}
	$_SESSION[S_NAME] = array(
		'balance' => 1000,
		'cards' => getTowerCards(),
		'gatecard' => true,
		'burned' => array(),
		'nextrow' => 0, // excl this one is shown
		'basebet' => 0,
	);
	if ( !empty($_GET['reset']) ) {
		header('Location: '.$_SERVER['PHP_SELF']);
		exit;
	}
}


if ( isset($_GET['bet']) ) {
	if ( 0 === $_SESSION[S_NAME]['nextrow'] && (int)$_GET['bet'] >= 1 && (int)$_GET['bet'] <= 10 ) {
		// New round
		$_SESSION[S_NAME]['nextrow'] = 2;
		$_SESSION[S_NAME]['balance'] -= 15*(int)$_GET['bet'];
		$_SESSION[S_NAME]['cards'] = getTowerCards();
		$_SESSION[S_NAME]['burned'] = array();
		$_SESSION[S_NAME]['basebet'] = (int)$_GET['bet'];
	}
	header('Location: '.$_SERVER['PHP_SELF']);
	exit;
}

echo '<pre>';
echo buildTower($_SESSION[S_NAME]['cards'], 8);

echo "\n";
print_r($_SESSION[S_NAME]);




function buildTower( $f_arrCards, $f_iRow = null ) {
	$bGateCard = $_SESSION[S_NAME]['gatecard'];
	$arrBurned = $_SESSION[S_NAME]['burned'];
	$iRow = null !== $f_iRow ? $f_iRow : $_SESSION[S_NAME]['nextrow'];
	$szTower = '';
	$arrCards = $f_arrCards;
	foreach ( $arrBurned AS $bc ) {
		$arrCards[$bc[0]][$bc[1]] = '<b>'.$arrCards[$bc[0]][$bc[1]].'</b>';
	}
	foreach ( $arrCards AS $i => $row ) {
		$szTower .= str_repeat(' ', 1*(8-$i)-1);
		if ( $iRow > $i ) {
			if ( 0 == $i ) {
				$szTower .= $bGateCard ? '?' : ' ';
			}
			else {
				$szTower .= ( 1 == array_count_values($row) ? '<u>'.implode('</u> <u>', $row).'</u>' : implode(' ', $row) ).'  ('.array_sum($row).')';
			}
		}
		else {
			$szTower .= str_repeat(' ', $i*2+1);
		}
		$szTower .= "\n";
	}
	return $szTower;
}

?>