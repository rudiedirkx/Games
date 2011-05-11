<?php
// INDEX

include('connect.php');

$g_iWidth		= 10;
$g_iTileWidth	= 90;
$g_iTileHeight	= 90;
$g_szImageDir	= 'images/';
$g_iTdBorder	= 3;
$g_szBgColor	= '#000000';
$g_szBorderColor= '#ffffff';
$g_szEmptyCell	= '';

$g_arrGames = array(
	array(101,		'BLACKJACK',							'The Blackjack where 21 pays double pay and the dealer stops at 17 and goes at 16'),
	array('101c',	'BLACKJACK',							'You know Jack... Rules inside!'),
	array(102,		'MINESWEEPER',							'Default field of 9*9 containing 10 mines! The faster you play, the better your score'),
	array(104,		'BLACKBOX -PHP',						'Find the Atoms by sending beams into the field (PHP+Ajax based)'),
	array(141,		'BLACKBOX -Javascript',					'Find the Atoms by sending beams into the field (JavaScript based)'),
	array(105,		'CARRACE',								'Race a bitmap car with your cursor. You need a steady hand and a good mouse for this'),
	array(106,		'JAVACAVE',								'You\'re a lonely snake trying to make his way home... Dont bump the walls, use your spacebar'),
	array(107,		'TRAPPED',								'You\'re a dot and your trapped. Typical, right? Get outta there'),
	array(108,		'DEFEND YOUR CASTLE',					'For the real die-harders! This game keeps you up at night. It does get bored after a few hours and you might get handinjuries'),
	array(109,		'GRIDLOCK',								'Get the coloured fcker out of the grid. Do so by moving all others'),
//	array(113,		'GANGWARS',								'Multiplayer game. Probably not running now. Check it out though (not yet finished)'),
//	array('pornstars/index',		'PORNSTARS',							'Best multiplayer game ever! 98% by me! This game hasnt got anything to do with porn, as it does with stars'),
//	array(114,		'BLACKNOVA',							'Multiplayer game. Probably not running now. Check it out though'),
	array(110,		'CUMULARI ABSOLUTUS',					'A mathematical problem. I want the sum of the absolute values of all 25 fields to be 1... Impossible?'),
	array(111,		'STICK',								'Make as much money as possible within a set or unset amount of days. Whats your tcatics?'),
	array(115,		'MASTERMIND',							'Its old, but its stil good! Its a braincracker'),
	array(116,		'CIJFERS',								'Thats me playing. Try it out, might be fun'),
	array(117,		'QUIZ',									'For the brainiacs among us. A few questions you can bust your head on'),
	array(139,		'THE BOX -OneTarget',					'As old as the world, but not as easy. Get all boxes in that one corner'),
	array(140,		'THE BOX -MoreTargets',					'As old as the world, but not as easy. Every box has one place to go to. Its not that easy'),
	array(119,		'PICROSS',								'Tricky puzzle. You can make it as big as you want though'),
	array(120,		'WORDMIX',								'Just a few mixed words and a sentence. Get it right and you wiiiiiin'),
	array(121,		'POKER',								'Play poker on your own'),
	array(136,		'SUDOKU -DoItYourself',					'Make your own sudoku or try one of the earlier'),
	array(137,		'KIDS SUDOKU',							'Make your own very easy sudoku and play it'),
	array(666,		'BOOTYCALL',							'The game where every guy, no matter how young or old, has fun with'),
	array(123,		'STEPPING STONES',						'Eliminate all stones but one, to reach the ultimate score: 1'),
//	array(135,		'STEPPING STONES -JavaScript',			'Eliminate all stones but one, to reach the ultimate score: 1'),
	array(124,		'SAFE CRACKING',						'Crack the safe by choosing the four right numbers'),
	array(125,		'HOW FAST ARE YOU?',					'Very simple JS game. Hit the field as soon as possible when it turns red!'),
	array(126,		'SOME COLOURS',							'Practically all possible HTML RGB colours'),
	array(132,		'MULTIPLAYER POKER',					'Play Poker Texas HOld\'em with 3 other people'),
//	array(142,		'MP TEXAS HOLD\'EM v2',					'Play Poker Texas Hold\'em with up to 9 other people'),
	array(133,		'DOPEWARS',								'Deal drugs to make lots and lots of moneeehy'),
	array(127,		'NUMBER GUESSING',						'Try to guess the number in as few tries as possible'),
	array(128,		'HOW COMPUTER CREATED EARTH',			'The story about God, Earth, the creation and those )#$)(@!#& computers'),
	array(131,		'PHP POKER TEXAS HOLD\'EM TEST',		'A test to benchmark my PHP PokerTexasHoldem class'),
	array('131b',	'TEXAS HOLD\'EM TEST',					'A test to benchmark my new PokerTexasHoldem class'),
	array(134,		'TICK TACK TOE',						'The classic'),
	array(143,		'ABALONE',								'Work your way with the balls'),
	array(145,		'LINX',									'!!NEW!!'),
	array(146,		'SLITHER',								'!!NEW!!'),
	array(147,		'QUICK CLICKER',						'!!NEW!!'),
	array(148,		'EINSTEIN\'S RIDDLE',					'Are you in the top 2% smartest people in the world? Can you solve the puzzle?'),
	array(149,		'SHIFTER',								'Think first, shift later'),
	array(150,		'SWITCHBOARD',							'Connect the relais to make all lights light up'),
	array(151,		'TOWERS OF HANOI',						'8 disks, 3 towers, make the moves'),
	array(152,		'Fibonacci',							''),
	array(153,		'F1 racer',								''),
	array(154,		'Maze',									''),
//	array(155,		'?',									''),
	array(156,		'Tetravex',								'The Ubuntu ripoff'),
	array(157,		'Fortune\'s Tower - JS',				'It\'s a Fable II Pub game'),
	array(158,		'Fortune\'s Tower - PHP',				'It\'s a Fable II Pub game'),
	array(159,		'Atomix',								'The Ubuntu ripoff'),
	array(160,		'Pixelus',								'A Zylom game'),
	array(161,		'Bioshock hacker',						'From the game: hack a machine'),
	array(162,		'Marbles',								'My very own marbles2.com ripoff'),
	array(163,		'Entangled',							'Entangled ripoff: less HTML5, more CSS3 and JS'),
	array('mpoker/poker',	'JANUS POKER',				'Multiplayer Texas Hold\'em BY Janus'),
	array('102c_test_field_analysis', 'MINESWEEPER TEST',	'A minesweeper test in JS'),
	array('create_minesweeper_field', 'CREATE MINESWEEPER FIELD FOR `MINESWEEPER TEST`', 'A minesweeper test in JS'),
);

$g_iHeight = ceil(count($g_arrGames)/$g_iWidth);
$g_iEmptyCells = $g_iHeight*$g_iWidth-count($g_arrGames);

$arrUseGames = 0 < $g_iEmptyCells ? array_merge(array_fill(0, $g_iEmptyCells, false), $g_arrGames) : $g_arrGames;
shuffle($arrUseGames);

?>
<!DOCTYPE html> 
<html lang="en"> 

<head>
<title><?php echo strtoupper($_SERVER['HTTP_HOST']); ?></title>
<style>
* { margin:0; padding:0; }
body, html { background-color:#111; width:100%; height:100%; }
canvas { position:fixed; top:0; left:0; z-index:1; }
#tiles { -webkit-box-shadow:0 0 150px #000; -moz-box-shadow:0 0 150px #000; z-index:2; list-style:none; border:solid 1px #fff; overflow:visible; position:absolute; left:50%; top:50%; width:<?php echo $g_iWidth*$g_iTileWidth; ?>px; height:<?php echo $g_iHeight*$g_iTileHeight; ?>px; margin:-<?php echo $g_iHeight*$g_iTileHeight/2; ?>px 0 0 -<?php echo $g_iWidth*$g_iTileWidth/2; ?>px; }
#tiles li, #tiles img { width:<?php echo $g_iTileWidth; ?>px; height:<?php echo $g_iTileHeight; ?>px; }
#tiles li { display:block; float:left; background-color:#000; position:relative; }
#tiles img { border:0; position:absolute; }
#tiles:hover img { opacity:0.75;  filter:alpha(opacity=75); -webkit-transition: all 100ms ease-out; }
#tiles a:hover img { box-shadow:0 0 45px #fff; -moz-box-shadow:0 0 45px #fff; opacity:1.0; filter:alpha(opacity=100); top:-15px; left:-15px; z-index:3; width:120px; height:120px; }
</style>
<script> 
function rand(b) {
//	return 0;
	return Math.floor( Math.random() * (b+1) );
}
var g_drawspeed = 2000, g_drawtimer;
var g_colours = [
	['rgb(87,11,43)', 'rgb(141,18,70)', 'rgb(135,17,67)', 'rgb(175,23,87)', 'rgb(222,51,123)'],
	['#5096CA', '#A0B5CA', '#C7D6ED', '#5C739C', '#58473F'],
	['#B58348', '#ED8806', '#ED9306', '#ED9E38', '#EDA850']
];
var g_useColours = g_colours[rand(g_colours.length-1)];
var g_superRandom = 0 == rand(g_colours.length);
function setCanvasSize() {
	var cv = document.getElementById('cv');
	cv.width = document.body.offsetWidth;
	cv.height = document.body.offsetHeight;
}
function draw(f_initial) {
	clearTimeout(g_drawtimer);
	var cv = document.getElementById('cv');
	if ( f_initial ) {
		setCanvasSize();
	}
	var ct = cv.getContext("2d");
	var w = cv.offsetWidth, h = cv.offsetHeight;
	for ( var x=0; x<w; x+=50 ) {
		for ( var y=0; y<h; y+=50 ) {
			if ( g_superRandom ) {
				ct.fillStyle = 'rgb(' + rand(255) + ', ' + rand(255) + ', ' + rand(255) + ')';
			}
			else {
				ct.fillStyle = g_useColours[rand(g_useColours.length-1)];
			}
			ct.fillRect(x, y, 50, 50);
		}
	}
	g_drawtimer = setTimeout(draw, g_drawspeed);
}
</script> 
</head>

<body onload="draw(1);" onclick="draw();" onresize="draw(1);"> 

<canvas style="" width="800" height="600" id="cv"></canvas> 

<ul id="tiles">
<?php

foreach ( $arrUseGames AS $i => $game ) {
	if ( $game ) {
		$img = $g_szImageDir . '_' . str_replace('/', '_', $game[0]) . '.gif';
		$file = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . ( '/' == substr($g_szImageDir, 0, 1) ? '' : dirname($_SERVER['PHP_SELF']).'/' ) . $img;
	}
	echo "\t".'<li class="item-'.($i+1).'">'.( $game ? '<a title="'.$game[1].'" href="'.$game[0].'"><img alt="'.$game[1].'" src="'.( file_exists($file) ? $img : $g_szImageDir.'__.gif' ).'" /></a>' : '&nbsp;' ).'</li>'."\n";
}

?>
</ul>

</body>

</html>
