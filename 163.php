<?php
// Entangled: http://entanglement.gopherwoodstudios.com/

?>
<!DOCTYPE html>
<html>

<head>
<title>ENTANGLED</title>
<style>
* {
	margin: 0;
	padding: 0;
}
#board {
	height: 567px;
	padding: 0 12px;
	float: left;
	position: relative;
	background-color: #eee;
}
#board > div {
	float: left;
}
#board > div > div {
	z-index: 2;
	opacity: 0.75;
	width: 94px;
	height: 81px;
	margin: 0 -11px;
	background: url(images/163_tile.png) no-repeat center center;
	transform: rotate(0deg);
	transition: all 200ms ease-out;
}
#board > div > div.center {
	z-index: 1;
	opacity: 1;
	background-image: url(images/163_tile_center.png);
}
#board > div > div: hover {
	opacity: 1;
}
#board > div.c2 > div,
#board > div.c6 > div {
	position: relative;
	top: 80px;
}
#board > div.c3 > div,
#board > div.c5 > div {
	position: relative;
	top: 40px;
}
#board > div.c1 > div,
#board > div.c7 > div {
	position: relative;
	top:120px;
}
</style>
</head>

<body>

<div id=board></div>

<script>
function emptyBoard() {
	var html = '';
	for (var i = 1; i <= 7; i++) {
		html += '<div class="col c' + i + '">';

		var r = i == 4 ? 7 : ( i < 4 ? 3+i : 11-i );
		for (var j = 1; j <= r; j++) {
			var c = i == 4 && j == 4 ? 'center' : 'tile';
			html += '<div data-rotated="0" class="' + c + ' r' + j + '"></div>';
		}

		html += '</div>';
	}

	document.querySelector('#board').innerHTML = html;
}

function rotateTile(tile, direction) {
	var rotated = parseInt(tile.dataset.rotated) + direction;
	var deg = rotated * 60;
	tile.style.transform = 'rotate(' + deg + 'deg)';
	tile.dataset.rotated = rotated;
}

window.onload = function() {
	emptyBoard();
	var b = document.querySelector('#board')
	b.addEventListener('wheel', function(e) {
		if ( e.target.matches('.tile') ) {
			e.preventDefault();

			if ( !e.target._transitioning ) {
				e.target._transitioning = true;
				rotateTile(e.target, e.deltaY/Math.abs(e.deltaY));
			}
		}
	}, true);
	b.addEventListener('transitionend', function(e) {
		e.target._transitioning = false;
	}, true);
};
</script>

</body>

</html>
