<?php
// INDEX

include('connect.php');

$g_iWidth		= 10;
$g_iTileWidth	= 91;
$g_iTileHeight	= 91;
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
	array('161b',	'Bioshock hacker',						'From the game: hack a machine'),
	array(162,		'Marbles',								'My very own marbles2.com ripoff'),
	array(163,		'Entangled',							'Entangled ripoff: less HTML5, more CSS3 and JS'),
	array(164,		'Machinarium I',						'Stole a fun game from Machinarium'),
	array('mpoker/poker',	'JANUS POKER',				'Multiplayer Texas Hold\'em BY Janus'),
	array('102c_test_field_analysis', 'MINESWEEPER TEST',	'A minesweeper test in JS'),
	array('create_minesweeper_field', 'CREATE MINESWEEPER FIELD FOR `MINESWEEPER TEST`', 'A minesweeper test in JS'),
);

$g_iHeight = ceil(count($g_arrGames)/$g_iWidth);
$g_iEmptyCells = $g_iHeight*$g_iWidth-count($g_arrGames);

$arrUseGames = 0 < $g_iEmptyCells ? array_merge(array_fill(0, $g_iEmptyCells, false), $g_arrGames) : $g_arrGames;
shuffle($arrUseGames);

$iMediaQueryLimit = $g_iWidth * $g_iTileWidth + 2 + 20;

?>
<!doctype html>
<html lang="en">
<!-- { server: <?=$_SERVER['SERVER_ADDR']?> } -->

<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0" />
<meta charset="utf-8" />
<title><?php echo strtoupper($_SERVER['HTTP_HOST']); ?></title>
<style>
/* A big Fuck you! to IE */
@media (min-width: 0) {
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
		background: black url(/cached/thumbs.gif) 0 0 no-repeat;
		border: 0;
	}
}

@media (max-width: <?=$iMediaQueryLimit?>px) {
	canvas {
		display: none;
	}
	#contact {
		padding: 15px 0 0;
		text-align: center;
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
	#tiles a:hover,
	#tiles a:focus	{
		background: #ccc;
	}
	#tiles a:active	{
		background: #000;
		color: #fff;
	}
}

@media (min-width: <?=$iMediaQueryLimit?>px) {
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
		width:<?php echo $g_iWidth*$g_iTileWidth; ?>px;
		height:<?php echo $g_iHeight*$g_iTileHeight; ?>px;
		margin:-<?php echo $g_iHeight*$g_iTileHeight/2; ?>px 0 0 -<?php echo $g_iWidth*$g_iTileWidth/2; ?>px;
	}
	#tiles li,
	#tiles .img {
		width:<?php echo $g_iTileWidth; ?>px;
		height:<?php echo $g_iTileHeight; ?>px;
	}
	#tiles li {
		display: block;
		float: left;
		background: #000;
		position: absolute;
	}
	#tiles.positioned li {
		-webkit-transition: all 300ms ease-out;
		-moz-transition: all 300ms ease-out;
	}
	#tiles li.empty {
		background: transparent;
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
	}
	#tiles:hover .img {
		opacity:0.75;
		-webkit-transition: -webkit-transform 80ms ease-out;
		-moz-transition: -moz-transform 80ms ease-out;
		transition: -moz-transform 80ms ease-out;

		-webkit-transform: translateZ(0);
		-moz-transform: translateZ(0);
		transform: translateZ(0);
	}
	#tiles a:hover .img {
		box-shadow:0 0 45px #fff;
		-moz-box-shadow:0 0 45px #fff;
		opacity:1.0;
		position: relative;
		z-index: 2;

		-webkit-transform: translateZ(0) scale(1.3);
		-moz-transform: translateZ(0) scale(1.3);
		transform: translateZ(0) scale(1.3);
	}
	#tiles h2, #tiles p {
		display: none;
	}

	#contact {
		position: fixed;
		left: 0;
		top: 0;
		z-index: 3;
	}
	#contact a {
		display: block;
		color: white;
		background: rgba(0, 0, 0, 0.6);
		font-size: 24px;
		line-height: 50px;
		width: 150px;
		text-align: center;
		text-decoration: none;
	}
	#contact a:not(:hover):not(:focus) {
		-webkit-transition: all 300ms linear;
		-moz-transition: all 300ms linear;
	}
	#contact a:hover,
	#contact a:focus {
		background: rgba(0, 0, 0, 1);
		line-height: 100px;
		width: 250px;
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

<canvas width="800" height="600" id="cv"></canvas>

<p id="contact"><a href="mailto:games@webblocks.nl?subject=About your awesome <?=$_SERVER['HTTP_HOST']?>...">Contact me</a></p>

<ul id="tiles" data-width="<?=$g_iWidth?>" data-height="<?=$g_iHeight?>">
<?php

$thumbs = get_thumbs_positions();
echo '<!-- ';
print_r($thumbs);
echo ' -->';

foreach ( $arrUseGames AS $i => $game ) {
	$y = floor($i / $g_iWidth);
	$x = $i % $g_iWidth;

	echo "\t".'<li data-x="'.$x.'" data-y="'.$y.'" class="item-'.($i+1).( $game ? '' : ' empty' ).'">';
	if ( $game ) {
		$ext = preg_match('/^\d+[a-f]?$/', $game[0]) ? '' : '.php';

		$bgname = '_' . str_replace('/', '_', $game[0]);
		$bgpos = isset($thumbs[$bgname]) ? $thumbs[$bgname] : $thumbs['__'];

		echo '<a title="'.$game[1].'" href="'.$game[0].$ext.'">';
		echo '<span class="img" style="background-position: 0 -' . $bgpos . 'px"></span>';
		echo '<h2>'.$game[1].'</h2>';
		echo '<p>'.$game[2].'</p>';
		echo '</a>';
	}
	echo '</li>'."\n";
}

?>
</ul>

<script src="/js/mootools_1_11.js"></script>
<script>
Array.prototype.shuffle = function() {
	this.sort(function() {
		return 0.5 - Math.random()
	})
	return this
}
function clone(obj) {
	return JSON.parse(JSON.stringify(obj))
}

(function() {

	var cd = getComputedStyle(document.getElementById('cv')).getPropertyValue('display');

	if ( cd == 'none' ) return;

	var g_draw = true,
		g_drawspeed = 500,
		g_drawtimer,
		g_crawlspeed = 300,
		g_empties = [],
		g_crawling = 0

	function log() {
		//window.console.log.apply(window.console, arguments)
	}

	function rand(b) {
		return Math.floor( Math.random() * (b+1) )
	}

	function posneg() {
		return rand(1) ? 1 : -1
	}

	// Background tiles
	var g_colours = [
		['rgb(87,11,43)', 'rgb(141,18,70)', 'rgb(135,17,67)', 'rgb(175,23,87)', 'rgb(222,51,123)'],
		['#5096CA', '#A0B5CA', '#C7D6ED', '#5C739C', '#58473F'],
		['#B58348', '#ED8806', '#ED9306', '#ED9E38', '#EDA850']
	]
	var g_useColours = g_colours[rand(g_colours.length-1)]
	var g_superRandom = 0 == rand(g_colours.length)

	function setCanvasSize() {
		var cv = document.getElementById('cv')
		cv.width = document.body.offsetWidth
		cv.height = document.body.offsetHeight
	}

	function draw(f_initial) {
		log('disable drawing')
		if ( g_draw ) {
			setTimeout(function() {
				log('enable drawing')
				g_draw = true
			}, g_drawspeed)
		}
		g_draw = false
		var cv = document.getElementById('cv')
		if ( f_initial ) {
			setCanvasSize()
		}
		var ct = cv.getContext("2d")
		var w = cv.offsetWidth, h = cv.offsetHeight
		for ( var x=0; x<w; x+=50 ) {
			for ( var y=0; y<h; y+=50 ) {
				if ( g_superRandom ) {
					ct.fillStyle = 'rgb(' + rand(255) + ', ' + rand(255) + ', ' + rand(255) + ')'
				}
				else {
					ct.fillStyle = g_useColours[rand(g_useColours.length-1)]
				}
				ct.fillRect(x, y, 50, 50)
			}
		}
	}

	Window.addEvent('load', function(e) {
		log('window.onload')

		draw(1)

		setInterval(function() {
			log('crawl')
			crawl(g_crawling++%g_empties.length)
		}, g_crawlspeed)
	})

	var lc = '', c
	Document.addEvent('mousemove', function(e) {
		log('window.onmousemove')
		c = e.clientX + ':' + e.clientY // stupid Chrome bug fires mousemove events when the mouse doesn't move
		'CANVAS' == e.target.nodeName && c != lc && g_draw && draw()
		lc = c
	})

	Window.addEvent('resize', function(e) {
		log('window.onresize')
		draw(1)
	})

	if ( innerWidth < <?=$iMediaQueryLimit?> ) {
		return
	}

	// Moving game icons
	$$$('#tiles > li').each(function( li ) {
		var empty = li.classList.contains('empty')
		if ( empty ) {
			li.remove()
			g_empties.push([parseInt(li.dataset.x), parseInt(li.dataset.y)])
		}
		else {
			position(li)
		}
	})
	setTimeout("$('tiles').addClass('positioned')", 300)
	//log(g_empties)

	function neighbour( f_coords ) {
		var options = [[0, 1], [0, -1], [1, 0], [-1, 0]], i = 0
		options.shuffle()
		//log(options)
		for ( ; i<4; i++ ) {
			var coords = clone(f_coords)
			coords[0] += options[i][0]
			coords[1] += options[i][1]
			var id = 'li[data-x="' + coords[0] + '"][data-y="' + coords[1] + '"]', el = $$$(id)[0]
			//log(id)
			if ( el ) {
				return el
			}
		}
	}

	function position(li) {
		return li.css({
			left: li.dataset.x * <?=$g_iTileWidth?>,
			top: li.dataset.y * <?=$g_iTileHeight?>
		})
	}

	function crawl( ci ) {
		if ( undefined == ci ) {
			g_empties.forEach(function( c, ci ) {
				crawl(ci)
			})
			return //log(g_empties)
		}

		var coords = clone(g_empties[ci])

		// pick a direction
		var li = neighbour(coords)
		if ( li ) {
			var licoords = [parseInt(li.dataset.x), parseInt(li.dataset.y)]

			li.dataset.x = String(coords[0])
			li.dataset.y = String(coords[1])
			position(li)

			g_empties[ci] = clone(licoords)
		}
	}

})()

Window.addEvent('load', function(e) {
	try {
		console.log((Date.now() - performance.timing.requestStart)/1000);
	} catch (ex) {}
});
</script>
</body>

</html>
