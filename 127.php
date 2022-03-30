<?php
// NUMBER GUESSING

require __DIR__ . '/inc.bootstrap.php';

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>Guess my number from 1-100</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<? include 'tpl.onerror.php' ?>
<style>
* {
	font-size: 19px;
}
input, button {
	padding-top: 5px;
	padding-bottom: 5px;
}
input {
	width: 6em;
	text-align: center;
}
</style>
</head>

<body>

<p>Guess the number (0 - 100):</p>
<form>
	<input id="number" type="number" autofocus />
	<button id="guess">Guess</button>
</form>

<script>
var game = {
	number: -1,
	guesses: 0,
	reset: function() {
		this.guesses = 0;
		this.number = -1;
	},
	start: function() {
		if (this.number == -1) {
			this.number = this.random();
		}
	},
	random: function() {
		return Math.floor(Math.random() * 101);
	},
	guess: function(guess) {
		this.start();
		this.guesses++;

		if (guess == this.number) {
			return 0;
		}
		return guess < this.number ? 1 : -1;
	},
	ui: {
		guess: function(number) {
			var off = this.game.guess(number);
			if (off == 0) {
				this.win();
				this.alert("You win!\n\nIn " + this.game.guesses + " guesses.\n\nPlay another round?");
			}
			else {
				this.alert("You're too " + (off > 0 ? "low" : "high") + ".");
			}
			$number.select();
		},
		win: function() {
			$number.value = '';
		},
		alert: function(msg) {
			document.activeElement.blur();
			setTimeout(function() {
				alert(msg);
			}, 50);
		},
	},
};
game.ui.game = game;

var $form = document.querySelector('form');
var $number = document.querySelector('#number');
var $reset = document.querySelector('#reset');

$form.addEventListener('submit', function(e) {
	e.preventDefault();

	game.ui.guess($number.value);
});
</script>

</body>

</html>
