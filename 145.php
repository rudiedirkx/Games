<?php
// LINX

header('Content-type: text/html; charset="utf-8"');

//type = singular | symmetric | multiple
$g_arrBoards = require '145.boards.php';

$iBoard = isset($_GET['board'], $g_arrBoards[$_GET['board']]) ? $_GET['board'] : key($g_arrBoards);
$arrBoard = $g_arrBoards[$iBoard];

$board = board($arrBoard, $iBoard);

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
	<p>[<?=$iBoard?>] | <a href="?board=<?=$iBoard+1?>">Next board</a> | <a href="/145B">Do it yourself</a></p>
</div>

<div class="status">
	<p id="message">Nothing going on down here...</p>
</div>

<div class="game">
	<div id="map-container" class="m">
		<?php

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

<script src="js/rjs-custom.js"></script>
<script>
	var LEVEL = <?= (int)$iBoard ?>;
	var TYPE = '<?= $board->type ?>';
</script>
<script src="/145.js"></script>

</body>

</html>
<?php

function board( $arrBoard, &$iBoard = null ) {
	isset($arrBoard['map']) || $arrBoard = array('map' => $arrBoard);

	if ( isset($_GET['type'], $_GET['map']) ) {
		$arrBoard = $_GET;
		$iBoard = 'CUSTOM';
	}

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


