<?php
// LINX

header('Content-type: text/html; charset="utf-8"');

$g_arrBoards = array(1=>
	array(
		'',
		'   3  3 ',
		'',
		' 1  1',
		'',
	),
	array(
		' 5  6',
		'',
		'         5',
		'   6',
		'2      2',
		'',
	),
	array(
		'',
		'      4',
		'     3 1',
		'      2',
		'  2       2   ',
		'      2',
		'     1 3',
		'',
		'      4',
		'',
		'',
		'',
	),
);

$iBoard = isset($_GET['board'], $g_arrBoards[$_GET['board']]) ? $_GET['board'] : key($g_arrBoards);
$arrBoard = $g_arrBoards[$iBoard];

?>
<!doctype html>
<html lang="en">

<head> 
<meta charset="utf-8" />
<title>Linx</title>
<link rel="stylesheet" href="/145.css" />
</head>

<body>

<div class="help">
	<p>Connect the big dots. Drag a <span title="Yes, I know black and white aren't colors. But I don't care and neither do you.">colored</span> big dot to another big dot of the same color.</p>
	<p>[<?=$iBoard?>] | <a href="?board=<?=$iBoard+1?>">Next level</a></p>
</div>

<div class="status">
	<p id="message">Nothing going on down here...</p>
</div>

<div class="game">
	<div id="map-container" class="m">
		<?php

		$_cols = max(array_map('strlen', $arrBoard));
		$_rows = count($arrBoard);

		for ( $y=0; $y<$_rows; $y++ ) {
			echo '<div class="row">' . "\n";

			for ( $x=0; $x<$_cols; $x++ ) {
				$tile = isset($arrBoard[$y][$x]) ? (int)trim($arrBoard[$y][$x]) : 0;

				$classes = array('cell');
				if ( $tile ) {
					$classes[] = 'pad';
					$classes[] = 'type-' . $tile;
				}

				$class = $classes ? ' class="'.implode(' ', $classes).'"' : '';

				echo '<a href="#" data-type="' . $tile . '" data-x="' . $x . '" data-y="' . $y . '"' . $class . '></a>' . "\n";
			}

			echo '</div>' . "\n";
		}

		?>
	</div>
</div>

<img class="preload" src="/images/145-lines.png" />

<script src="//code.jquery.com/jquery-latest.js"></script>
<script src="/145.js"></script>

</body>

</html>