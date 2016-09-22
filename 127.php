<doctype html>
<html>

<head>
<title>Guess my number from 1-100</title>
</head>

<body>

<p>Guess the number (0 - 100):</p>
<form>
	<input id="number" type="number" style="width: 3em" autofocus />
	<button id="guess">Guess</button>
	<button id="reset">Restart</button>
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
				return alert("You win!\n\nIn " + this.game.guesses + " guesses.\n\nPlay another round?");
			}

			alert("You're too " + (off > 0 ? "low" : "high") + ".");
		},
		win: function() {
			$number.value = '';
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

$reset.addEventListener('click', function(e) {
	e.preventDefault();
});
</script>

</body>

</html>
