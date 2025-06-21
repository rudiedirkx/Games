<?php
// INDEX

// header('HTTP/1.1 500 Uptime test');
// echo "Error 500 for uptime monitor test\n";
// exit;

require __DIR__ . '/inc.bootstrap.php';

$g_iWidth		= 13;
$g_iTileWidth	= 91;
$g_iTileHeight	= 91;
$g_szImageDir	= 'images/';
$g_iTdBorder	= 3;
$g_szBgColor	= '#000000';
$g_szBorderColor= '#ffffff';
$g_szEmptyCell	= '';

$g_arrGames = require __DIR__ . '/inc.index.php';

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

$thumbs = get_thumbs_positions();
$thumbsRange = count($thumbs) - 1;

?>
<!doctype html>
<html lang="en">

<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="theme-color" content="#333" />
<link rel="icon" type="image/png" href="favicon-128.png" sizes="128x128" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
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
#tiles {
	--thumbs-total: <?= count($thumbs) ?>;
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
	}
	#tiles li:not(.empty) {
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
	#tiles:hover .img,
	#tiles:focus-within .img {
		opacity: 0.65;
	}
	#tiles a:hover .img,
	#tiles a:focus .img {
		box-shadow: 0 0 45px #fff;
		opacity: 1.0;
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

foreach ( $g_arrUseGames AS $i => $game ) {
	$y = floor($i / $g_iWidth);
	$x = $i % $g_iWidth;

	echo "\t".'<li data-x="'.$x.'" data-y="'.$y.'" class="item-'.($i+1).( $game ? '' : ' empty' ).'">';
	if ( $game ) {
		$bgname = '_' . str_replace('/', '_', $game[0]);
		$bgpos = isset($thumbs[$bgname]) ? $thumbs[$bgname] : $thumbs['__'];

		$index = $bgpos / THUMB_SIZE;
		$bgposY = round(100 / $thumbsRange * $index, 3);

		echo '<a data-bgname="' . $bgname . '" title="'.$game[1].'" href="' . $game[0] . '.php">';
		echo '  <span class="img" style="background-position: 0 ' . $bgposY . '%; x-background-position: 0 calc(100% / ' . $thumbsRange . ' * ' . $index . ')"></span>';
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

<?php if (DA_URL): ?>
	<script src="<?= DA_URL ?>"></script>
<?php endif ?>
</body>

</html>
