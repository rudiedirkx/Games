<?php
// LINX

header('Content-type: text/html; charset="utf-8"');

//type = singular | symmetric | multiple
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
	array(
		'xx      xx',
		'xx      xx',
		'    12',
		'',
		'  3    4',
		'  4    3',
		'',
		'    21',
		'xx      xx',
		'xx      xx',
	),
	array(
		'type' => 'singular',
		'map' => array(
			'',
			'        x',
			'        x',
			'   xxx  x',
			'   xxx  x',
			'   xxx  x',
			' 1 xxx2 x3',
			'   xxx  x',
			'   xxx  x',
			'   xxx  x',
			'        x',
			'        x',
			'',
		),
		'max' => 52,
		'best' => 43,
	),
	array(
		'type' => 'multiple',
		'map' => array(
			'        ',
			' 1    2 ',
			'        ',
			'        ',
			'        ',
			'  2  1  ',
			' 1    2 ',
			'        ',
		),
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
	<p>[<?=$iBoard?>] | <a href="?board=<?=$iBoard+1?>">Next board</a></p>
</div>

<div class="status">
	<p id="message">Nothing going on down here...</p>
</div>

<div class="game">
	<div id="map-container" class="m">
		<?php

		$board = board($arrBoard);
		$map = $board->map;

		for ( $y=0; $y<$board->rows; $y++ ) {
			echo '<div class="row">' . "\n";

			for ( $x=0; $x<$board->cols; $x++ ) {
				$tile = isset($map[$y][$x]) ? trim($map[$y][$x]) : '';

				$classes = array('cell');
				if ( 'x' == $tile ) {
					$classes[] = 'na';
				}
				else if ( $tile ) {
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

<img class="preload" src="/images/145-lines.png" alt="preloading lines sprite" />

<script src="//code.jquery.com/jquery-latest.js"></script>
<script>var LEVEL = <?=$iBoard?>, TYPE = '<?=$board->type?>'</script>
<script src="/145.js"></script>

</body>

</html>
<?php

function board( $arrBoard ) {
	isset($arrBoard['map']) || $arrBoard = array('map' => $arrBoard);
	$type = strtolower(@$arrBoard['type']);
	$map = $arrBoard['map'];

	$singular = 'singular' == $type;
	$multiple = 'multiple' == $type;

	return (object)array(
		'type' => $type,
		'map' => $map,
		'cols' => max(array_map('strlen', $map)),
		'rows' => count($map),
		'singular' => $singular,
		'multiple' => $multiple,
		'symmetric' => !$singular && !$multiple,
	);
}


