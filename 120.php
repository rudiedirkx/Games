<?php
// WORDMIX

require 'inc.functions.php';

session_start();

$sentences = [
	"My car is big.",
	"I have a bigger car than your father.",
	"I would like to do my groceries.",
	"I once saw a cat climbing a tree.",
	"That were some remarkable moves, he made.",
	"now a sentence without a capital or a dot",
	"That should be a lot harder to construct",
	"The last word in this sentence is not bitch but slut.",
];

$session = &$_SESSION['wm_user'];

if ( !isset($session['show_sentence'], $session['sentence'], $session['words'], $session['word']) ) {
	$session['sentence'] = 0;
	$session['show_sentence'] = -1;
	resetGame();
}

$word = @$session['words'][ $session['word'] ];



// RESET
if ( isset($_POST['reset']) ) {
	$session = ['sentence' => 0];
	resetGame();

	do_redirect();
}

// SUBMIT WORD
if ( isset($_POST['word']) ) {
	$session['show_sentence'] = -1;

	if ( strtolower($_POST['word']) === strtolower($word) ) {
		$session['word']++;

		if ( $session['word'] == count($session['words']) ) {
			$session['show_sentence'] = $session['sentence'];

			$session['sentence']++;
			resetGame();
		}
	}

	do_redirect();
}

?>
<html>

<head>
<meta name="viewport" content="width=device-width">
<title>WORDMIX</title>
<style>
html, body {
	margin: 0;
	padding: 0;
}
body {
	padding: 40px 50px;
}
.word {
	font-family: monospace;
	font-size: 20px;
	padding: 5px;
	line-height: 1.4;
}
</style>
</head>

<body>

<? if ($session['show_sentence'] >= 0): ?>
	<p style="color: green">Sentence done: <?= do_html($sentences[ $session['show_sentence'] ]) ?>
<? endif ?>

<form method="post" autocomplete="off">
	<p>Level <?= $session['sentence'] + 1 ?> / <?= count($sentences) ?>. Word <?= $session['word'] + 1 ?> / <?= count($session['words']) ?>:</p>
	<pre id="word" class="word" style="background-color: #eee; padding: 5px; white-space: pre-line">
		<span><?= implode('</span><span>', str_split(str_shuffle($word))) ?>
	</pre>
	<p><input class="word" name="word" autofocus size="<?= strlen($word) ?>" maxlength="<?= strlen($word) ?>" /></p>
	<button>CHECK WORD</button>
</form>

<script>
// @todo Mark used letters during typing
</script>

<hr />

<form method="post">
	<button name="reset" value="1">RESET</button>
</form>

<?php

function resetGame() {
	global $sentences, $session;

	$sentence = $sentences[ $session['sentence'] ];
	$words = explode(' ', $sentence);
	shuffle($words);
	$session['words'] = $words;
	$session['word'] = 0;
}
