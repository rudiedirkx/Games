<?php
// MEMORY

require __DIR__ . '/inc.bootstrap.php';

$width = 5;
$height = 4;

$cards = call_user_func_array('array_merge', array_fill(0, 2, range('A', chr($width*$height/2+64))));
shuffle($cards);

?>
<style>
table {
	border-spacing: 10px;
}
td {
	padding: 0;
	background-color: #aaa;
}
a {
	display: block;
	width: 150px;
	height: 150px;
	font-size: 100px;
	line-height: 150px;
	text-align: center;
	text-decoration: none;
	color: white;
}
a:not(.open) {
	opacity: 0;
}
</style>
<?php

echo '<table><tr>';
foreach ( $cards as $i => $card ) {
	if ( $i > 0 && $i%$width == 0 ) {
		echo '</tr><tr>';
	}
	echo '<td><a href>' . $card . '</a></td>';
}
echo '</tr></table>';

?>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script>
var start = Date.now();
var lastOpen;
var clicks = 0;
[].forEach.call(document.querySelectorAll('a'), function(a) {
	a.addEventListener('click', function(e) {
		e.preventDefault();
		if ( this.classList.contains('open') ) return;

		clicks++;

		if ( lastOpen && this.textContent == lastOpen.textContent ) {
			lastOpen = null;
			this.classList.add('open');

			if ( document.querySelectorAll('a:not(.open)').length == 0 ) {
				Game.saveScore({
					time: Math.round((Date.now() - start) / 1000),
					moves: clicks,
				});
				setTimeout(function() {
					alert("You win!\n\n" + clicks + " clicks");
					setTimeout(function() {
						location.reload();
					}, 50);
				}, 50);
			}
			return;
		}

		if ( lastOpen ) {
			lastOpen.classList.remove('open');
		}
		this.classList.add('open');
		lastOpen = this;
	}, true);
});
</script>
