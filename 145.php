<?php
// LINX

$g_arrBoards = array(1=>
	array(
		'6            5',
		'',
		'      4',
		'     3 1',
		'      2',
		'  2       2',
		'      2',
		'     1 3',
		'',
		'      4',
		'',
		'5            6',
	),
);


$iBoard = isset($_GET['board'], $g_arrBoards[$_GET['board']]) ? $_GET['board'] : key($g_arrBoards);
$arrBoard = $g_arrBoards[$iBoard];

?>
<html>

<head> 
<title>Linx</title>
<style>
* { margin: 0; padding: 0; }
#map-table {
	margin: 20px 0 0 20px;
	border-collapse: collapse;
	box-shadow: 0 0 20px #666;
}
#map-container {

}
#map-container td {
	background-color: #999;
	padding: 0;
	border: 0;
}
#map-container a {
	display: block;
	width: 33px;
	height: 33px;
	border: solid 1px #fff;
	border-color: #aaa #888 #888 #aaa;
}
#map-container a:hover {
	border-color: #fff;
}
#map-container td.line a {
	width: 35px;
	height: 35px;
	border: 0;
	border-image: initial;
}

.pad.type-1 a {
	background-color: red;
}
.pad.type-2 a {
	background-color: orange;
}
.pad.type-3 a {
	background-color: white;
}
.pad.type-4 a {
	background-color: black;
}
.pad.type-5 a {
	background-color: #0d0;
}
.pad.type-6 a {
	background-color: blue;
}

.line {
	background: url(/images/145-lines.png);
}
.line.type-1.dir-v {
	background-position: 0 0;
}
.line.type-1.dir-h {
	background-position: -50px 0;
}
.line.type-1.dir-sw {
	background-position: -100px 0;
}
.line.type-1.dir-nw {
	background-position: -150px 0;
}
.line.type-1.dir-ne {
	background-position: -200px 0;
}
.line.type-1.dir-se {
	background-position: -250px 0;
}
.line.type-2.dir-v {
	background-position: 0 -50px;
}
.line.type-2.dir-h {
	background-position: -50px -50px;
}
.line.type-2.dir-sw {
	background-position: -100px -50px;
}
.line.type-2.dir-nw {
	background-position: -150px -50px;
}
.line.type-2.dir-ne {
	background-position: -200px -50px;
}
.line.type-2.dir-se {
	background-position: -250px -50px;
}
.line.type-3.dir-v {
	background-position: 0 -100px;
}
.line.type-3.dir-h {
	background-position: -50px -100px;
}
.line.type-3.dir-sw {
	background-position: -100px -100px;
}
.line.type-3.dir-nw {
	background-position: -150px -100px;
}
.line.type-3.dir-ne {
	background-position: -200px -100px;
}
.line.type-3.dir-se {
	background-position: -250px -100px;
}
.line.type-4.dir-v {
	background-position: 0 -150px;
}
.line.type-4.dir-h {
	background-position: -50px -150px;
}
.line.type-4.dir-sw {
	background-position: -100px -150px;
}
.line.type-4.dir-nw {
	background-position: -150px -150px;
}
.line.type-4.dir-ne {
	background-position: -200px -150px;
}
.line.type-4.dir-se {
	background-position: -250px -150px;
}
.line.type-5.dir-v {
	background-position: 0 -200px;
}
.line.type-5.dir-h {
	background-position: -50px -200px;
}
.line.type-5.dir-sw {
	background-position: -100px -200px;
}
.line.type-5.dir-nw {
	background-position: -150px -200px;
}
.line.type-5.dir-ne {
	background-position: -200px -200px;
}
.line.type-5.dir-se {
	background-position: -250px -200px;
}
.line.type-6.dir-v {
	background-position: 0 -250px;
}
.line.type-6.dir-h {
	background-position: -50px -250px;
}
.line.type-6.dir-sw {
	background-position: -100px -250px;
}
.line.type-6.dir-nw {
	background-position: -150px -250px;
}
.line.type-6.dir-ne {
	background-position: -200px -250px;
}
.line.type-6.dir-se {
	background-position: -250px -250px;
}

img.preload {
	width: 0;
	height: 0;
}
</style>
</head>

<body>

<table id="map-table">
	<tbody id="map-container">
		<?php

		for ( $y=0; $y<12; $y++ ) {
			echo '<tr>' . "\n";

			for ( $x=0; $x<14; $x++ ) {
				$tile = isset($arrBoard[$y][$x]) ? (int)trim($arrBoard[$y][$x]) : 0;

				$classes = array();
				$tile && $classes = array('pad', 'type-' . $tile);

				$class = $classes ? ' class="'.implode(' ', $classes).'"' : '';

				echo '<td data-type="' . $tile . '" data-x="' . $x . '" data-y="' . $y . '"' . $class . '><a href="#"></a></td>' . "\n";
			}

			echo '</tr>' . "\n";
		}

		?>
	</tbody>
</table>

<img class="preload" src="/images/145-lines.png" />

<script src="//code.jquery.com/jquery-latest.min.js"></script>
<script>
$.shuffle = function(arr) {
	for ( var j, x, i = arr.length; i; j = parseInt(Math.random() * i), x = arr[--i], arr[i] = arr[j], arr[j] = x );
	return arr;
}

//$(function() {
	var directions = ['v', 'h', 'sw', 'nw', 'ne', 'se']

	var type;

	$('#map-container').on('click', function(e, t) {
		t = $(e.target)

		if ( !t.is('a') ) return
		e.preventDefault()
		var cell = t.parent()

		if ( cell.hasClass('pad') ) {
			type = ~~cell.data('type')
		}
		else {
			if ( type ) {
				var d = $.shuffle(directions)[0]
				// line
				cell.addClass('line')
				// type
					.addClass('type-' + type)
				// direction
					.removeClass('dir-' + cell.data('last-dir'))
					.addClass('dir-' + d)
					.data('last-dir', d)
			}
		}
	})

	$('#map-container').on('contextmenu', function(e, t) {
		t = $(e.target)

		if ( !t.is('a') ) return
		var cell = t.parent()

		if ( cell.hasClass('line') ) {
			e.preventDefault()

			cell.removeClass('line').removeClass('dir-' + cell.data('last-dir'))
		}
	})
//})
</script>

</body>

</html>