<?php
// INDEX

require __DIR__ . '/inc.bootstrap.php';

$g_iWidth		= 13;
$g_iTileWidth	= 91;
$g_iTileHeight	= 91;
$g_szImageDir	= 'images/';
$g_iTdBorder	= 3;
$g_szBgColor	= '#000000';
$g_szBorderColor= '#ffffff';
$g_szEmptyCell	= '';

$g_arrGames = array(
	['101',				'BLACKJACK',									'The Blackjack where 21 pays double pay and the dealer stops at 17 and goes at 16'],
	['101b',			'CARD COUNTING',								'Learn yourself to count cards'],
	['101c',			'BLACKJACK',									'You know Jack... Rules inside!'],
	['102',				'MINESWEEPER',									'Default field of 9*9 containing 10 mines! The faster you play, the better your score'],
	['102c_analyze',	'Minesweeper analyzer',							'A minesweeper test in JS'],
	['102d_create',		'Create Minesweeper maps',						'A minesweeper test in JS'],
	['104',				'BLACKBOX - PHP',								'Find the Atoms by sending beams into the field'],
	['141',				'BLACKBOX - JS',								'Find the Atoms by sending beams into the field'],
	['187',				'BLACKBOX - GRIDGAME',							'Find the Atoms by sending beams into the field'],
	['105',				'CARRACE',										'Race a bitmap car with your cursor. You need a steady hand and a good mouse for this'],
	['106',				'JAVACAVE',										"You're a lonely snake trying to make his way home... Dont bump the walls, use your spacebar"],
	['107',				'TRAPPED',										"You're a dot and your trapped. Typical, right? Get outta there"],
	['108',				'DEFEND YOUR CASTLE',							'For the real die-harders! This game keeps you up at night. It does get bored after a few hours and you might get handinjuries'],
	['109',				'GRIDLOCK (Flash)',								'Get the coloured fcker out of the grid. Do so by moving all others'],
	['188',				'GRIDLOCK (JS)',								'From Flash'],
	['110',				'CUMULARI ABSOLUTUS',							'A mathematical problem. I want the sum of the absolute values of all 25 fields to be 1... Impossible?'],
	['111',				'STICK',										'Make as much money as possible within a set or unset amount of days. Whats your tcatics?'],
	['115',				'MASTERMIND',									'Its old, but its stil good! Its a braincracker'],
	['116',				'CIJFERS',										'Thats me playing. Try it out, might be fun'],
	['117',				'QUIZ',											'For the brainiacs among us. A few questions you can bust your head on'],
	['119',				'PICROSS',										'Tricky puzzle. You can make it as big as you want though'],
	['119B',			'PICROSS - Editor',								'Level editor for PICROSS'],
	['120',				'WORDMIX',										'Just a few mixed words and a sentence. Get it right and you wiiiiiin'],
	['121',				'POKER',										'Play poker on your own'],
	['123',				'Stepping stones',								'Eliminate all stones but one, to reach the ultimate score: 1'],
	['123B',			'Stepping stones - Editor',						'Create your own Steppin stones level'],
	['124',				'SAFE CRACKING',								'Crack the safe by choosing the four right numbers'],
	['125',				'HOW FAST ARE YOU?',							'Very simple JS game. Hit the field as soon as possible when it turns red!'],
	['127',				'NUMBER GUESSING',								'Try to guess the number in as few tries as possible'],
	['128',				'HOW COMPUTER CREATED EARTH',					'The story about God, Earth, the creation and those )#$)(@!#& computers'],
	['131',				"PHP POKER TEXAS HOLD'EM TEST",					'A test to benchmark my PHP PokerTexasHoldem class'],
	['131b',			"TEXAS HOLD'EM TEST",							'A test to benchmark my new PokerTexasHoldem class'],
	// ['132',				'MULTIPLAYER POKER',							"Play Poker Texas Hold'em with 3 other people"],
	// ['133',				'DOPEWARS',										'Deal drugs to make lots and lots of moneeehy'],
	['134',				'TIC TAC TOE',									'The classic'],
	['134b',			'TIC TAC TOE v2',								'Gridgame version of TTT'],
	// ['136',				'SUDOKU -DoItYourself',							'Make your own sudoku or try one of the earlier'],
	// ['137',				'KIDS SUDOKU',									'Make your own very easy sudoku and play it'],
	['139',				'THE BOX - One Target',							'Get all boxes in that one corner'],
	['140',				'THE BOX - More Targets',						'Every box has one place to go to. Not as easy as One Target'],
	['140B',			'THE BOX - Editor',								'Create your own The Box level'],
	// ['142',			"MP TEXAS HOLD'EM v2",							"Play Poker Texas Hold'em with up to 9 other people"],
	['143',				'ABALONE',										'Work your way with the balls'],
	['145',				'LINX',											'!!NEW!!'],
	['146',				'SLITHER',										'Connect the slither'],
	['146b',			'CANVAS SLITHER',								'Connect the slither. Mobile optimized'],
	['147',				'QUICK CLICKER',								'!!NEW!!'],
	['148',				"EINSTEIN'S RIDDLE",							'Are you in the top 2% smartest people in the world? Can you solve the puzzle?'],
	['149',				'SHIFTER',										'Think first, shift later'],
	['150',				'SWITCHBOARD',									'Connect the relais to make all lights light up'],
	['151',				'TOWERS OF HANOI',								'8 disks, 3 towers, make the moves'],
	// ['152',			'Fibonacci',									''],
	['153',				'F1 Racer',										'Click-race a circuit'],
	['153B',			'F1 Racer - Editor',							'Create your own F1 Racer level'],
	['154',				'Maze',											''],
	['156',				'Tetravex',										'The Ubuntu ripoff'],
	['157',				"Fortune's Tower - JS",							"It's a Fable II Pub game"],
	['158',				"Fortune's Tower - PHP",						"It's a Fable II Pub game"],
	['159',				'Atomix',										'Create molecules from atoms'],
	['159B',			'Atomix - Editor',								'Create your own Atomix level'],
	['160',				'Pixelus',										'Get the pixels to their targets'],
	['160B',			'Pixelus - Editor',								'Create your own Pixelus level'],
	['161',				'Bioshock hacker',								'From the game: hack a machine'],
	['162',				'Marbles',										'My very own marbles2.com ripoff'],
	['163',				'Entangled',									'Entangled ripoff: less HTML5, more CSS3 and JS'],
	['164',				'Machinarium I',								'Stole a fun game from Machinarium'],
	['164B',			'Machinarium I - Editor',						'Create your own Machinarium I level'],
	['165',				'Machinarium II',								'Stole another fun game from Machinarium'],
	['165B',			'Machinarium II - Editor',						'Create your own Machinarium II level'],
	['167',				'Filling',										'Trying to make separate areas by size...'],
	['168',				'Flood',										'Flood the board by propagating a color. You start at the top left'],
	['169',				'Rectangles',									''],
	['170',				'Mahjong',										'Mahjong Solitaire in CANVAS'],
	['170B',			'Mahjong - Editor',								'Build a Mahjong Solitaire map!'],
	['171',				'Traffic',										'Very simple traffic simulation'],
	['172',				'Word',											'Reorder the letters to make a word'],
	['173',				'Mamono sweeper',								'HTML version of the Flash game'],
	['174',				'Mondrian puzzle',								'Mondrian square puzzle'],
	['175',				"Knight's Minesweeper",							'Minesweeper with different detecting neighbors'],
	['176',				'Memory',										'You know memory. Play against yourself'],
	['177',				'Squarescape',									'As seen on and stolen from the Play store'],
	['177B',			'Squarescape - Editor',							'Create your own Squarescape level'],
	['178',				'Laser',										'As seen on and stolen from the Play store'],
	['178B',			'Laser - Editor',								'Create your own Laser level'],
	['179',				'Pythagorea',									'As seen on and stolen from the Play store'],
	['181',				'ZHOR',											'As seen on and stolen from the Play store'],
	['181B',			'ZHOR - Editor',								'Create your own ZHOR level'],
	['183',				'Fallout Hacking Helper',						'Cheat with Fallout hacking'],
	['184',				'BLOCK 3x Solver',								'Because BLOCK 3x is too hard...'],
	['185',				'Tetris Solver',								'Because Tetris grids are boring, but necessary'],
	['186',				'0h h1',										'From Android'],
);

$g_iHeight = ceil(count($g_arrGames)/$g_iWidth);
$g_iEmptyCells = $g_iHeight*$g_iWidth-count($g_arrGames);
$g_arrEmptyCells = array();
while ( count($g_arrEmptyCells) < $g_iEmptyCells ) {
	$loc = rand(0, $g_iHeight * $g_iWidth - 1);
	$g_arrEmptyCells[$loc] = $loc;
}

$g_arrUseGames = array();
foreach ($g_arrGames as $i => $game) {
	if ( isset($g_arrEmptyCells[$i]) ) {
		$g_arrUseGames[] = null;
	}
	$g_arrUseGames[] = $game;
}
while (count($g_arrUseGames) < $g_iHeight * $g_iWidth) {
	$g_arrUseGames[] = null;
}

$iMediaQueryLimit = $g_iWidth * $g_iTileWidth + 2 + 20;

?>
<!doctype html>
<html lang="en">

<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="theme-color" content="#333" />
<meta charset="utf-8" />
<title><?= strtoupper($_SERVER['HTTP_HOST']) ?></title>
<style>
* { margin:0; padding:0; }
body,
html {
	width: 100%;
	height: 100%;
	font-family: sans-serif;
}
canvas {
	position:fixed;
	top:0;
	left:0;
	z-index: 1;
}
#tiles .img {
	width: 91px;
	height: 91px;
	display: block;
	background: black url('<?= html_asset('cached/thumbs.png') ?>') 0 0 no-repeat;
	background-size: 100% auto;
	border: 0;
}

@media (max-width: <?= $iMediaQueryLimit ?>px) {
	canvas {
		display: none;
	}
	#tiles {
		padding-bottom: 15px;
	}
	#tiles li {
		display: block;
		margin-top: 15px;
	}
	#tiles li.empty {
		display: none;
	}
	#tiles .img {
		float: left;
		margin-right: 15px;
	}
	#tiles a {
		display: block;
		background: #eee;
		padding: 15px;
		min-height: 91px;
		color: #000;
		text-decoration: none;
	}
	#tiles a:hover	{
		background: #ccc;
	}
	#tiles a:active	{
		background: #000;
		color: #fff;
	}
}

@media (min-width: <?= $iMediaQueryLimit ?>px) {
	body {
		background: #000;
	}
	#tiles {
		box-shadow:0 0 150px #000;
		z-index: 2;
		list-style:none;
		border:solid 1px #fff;
		overflow:visible;
		position:absolute;
		left:50%;
		top:50%;
		width:<?= $g_iWidth*$g_iTileWidth ?>px;
		height:<?= $g_iHeight*$g_iTileHeight ?>px;
		margin:-<?= $g_iHeight*$g_iTileHeight/2 ?>px 0 0 -<?= $g_iWidth*$g_iTileWidth/2 ?>px;
	}
	#tiles li,
	#tiles .img {
		width:<?= $g_iTileWidth ?>px;
		height:<?= $g_iTileHeight ?>px;
	}
	#tiles li {
		display: block;
		float: left;
		background: #000;
	}
	#tiles.positioned li {
		-webkit-transition: all 300ms ease-out;
		-moz-transition: all 300ms ease-out;
	}
	#tiles a {
		display: block;
	}
	#tiles .img {
		display:block;
		border:0;
		position:absolute;
		color: #fff;
		font-size: 20px;
		font-family: Arial;

		-webkit-transition: width 80ms ease-out, height 80ms ease-out, margin 80ms ease-out;
		-moz-transition: width 80ms ease-out, height 80ms ease-out, margin 80ms ease-out;
		transition: width 80ms ease-out, height 80ms ease-out, margin 80ms ease-out;

		-webkit-transform: translateZ(0);
		-moz-transform: translateZ(0);
		transform: translateZ(0);
	}
	#tiles:hover .img {
		opacity: 0.75;
	}
	#tiles a:hover .img,
	#tiles a:focus .img {
		box-shadow: 0 0 45px #fff;
		opacity: 1.0;
		position: relative;
		z-index: 2;

		width: 121px;
		height: 121px;
		margin: -15px 0 0 -15px;
	}
	#tiles h2, #tiles p {
		display: none;
	}
}

.clearfix:after,
#tiles a:after,
#tiles:after {
	content: "";
	display: block;
	clear: both;
	height: 0;
	visibility: hidden;
}
</style>
</head>

<body>
<canvas width="100" height="100" id="cv"></canvas>

<ul id="tiles" data-width="<?= $g_iWidth ?>" data-height="<?= $g_iHeight ?>">
<?php

$thumbs = get_thumbs_positions();
$thumbsRange = count($thumbs) - 1;

foreach ( $g_arrUseGames AS $i => $game ) {
	$y = floor($i / $g_iWidth);
	$x = $i % $g_iWidth;

	echo "\t".'<li data-x="'.$x.'" data-y="'.$y.'" class="item-'.($i+1).( $game ? '' : ' empty' ).'">';
	if ( $game ) {
		$bgname = '_' . str_replace('/', '_', $game[0]);
		$bgpos = isset($thumbs[$bgname]) ? $thumbs[$bgname] : $thumbs['__'];

		$index = $bgpos / THUMB_SIZE;
		$bgposY = round(100 / $thumbsRange * $index, 3);

		echo '<a title="'.$game[1].'" href="' . $game[0] . '.php">';
		echo '  <span class="img" style="background-position: 0 ' . $bgposY . '%; background-position: 0 calc(100% / ' . $thumbsRange . ' * ' . $index . ')"></span>';
		echo '  <h2>'.$game[1].'</h2>';
		echo '  <p>'.$game[2].'</p>';
		echo '</a>';
	}
	echo '</li>'."\n";
}

?>
</ul>

<script src="/js/rjs-custom.js"></script>
<script>
(function() {

	var cd = getComputedStyle(document.getElementById('cv')).display;

	if ( cd == 'none' ) return;

	var DRAW_SPEED = 300;
	var DRAW_SIZE_X = 35;
	var DRAW_SIZE_Y = 35;
	var DRAW_COLORS = ['#5096CA', '#A0B5CA', '#C7D6ED', '#5C739C', '#58473F'];

	var body = document.body;
	var cv = document.getElementById('cv')
	var ct = cv.getContext('2d')

	var g_draw = true;
	var g_empties = [];

	function rand(b) {
		return Math.floor( Math.random() * (b+1) )
	}

	function draw() {
		if ( g_draw ) {
			setTimeout(function() {
				g_draw = true;
			}, DRAW_SPEED);
			g_draw = false;
		}
		else {
			return;
		}

		if (cv.width != body.offsetWidth || cv.height != body.offsetHeight) {
			cv.width = body.offsetWidth;
			cv.height = body.offsetHeight;
		}

		var w = cv.width;
		var h = cv.height;

		for ( var x=0; x<w; x+=DRAW_SIZE_X ) {
			for ( var y=0; y<h; y+=DRAW_SIZE_Y ) {
				ct.fillStyle = DRAW_COLORS[rand(DRAW_COLORS.length-1)];
				ct.fillRect(x, y, DRAW_SIZE_X, DRAW_SIZE_Y);
			}
		}
	}

	draw();

	cv.on('mousemove', function(e) {
		draw();
	});

	window.on('resize', function(e) {
		draw();
	});

	window.on('load', function(e) {
		setTimeout(function() {
			console.debug((performance.timing.domContentLoadedEventEnd - performance.timing.navigationStart) + ' ms');
		}, 100);
	});

})();
</script>
</body>

</html>
