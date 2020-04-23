<?php
// WORD

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>Word</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
html {
	font-family: sans-serif;
	background-color: white;
}
html.waiting {
	background-color: black;
	-webkit-transition: background-color 2s linear;
}
body {
	margin-left: 20px;
}
#word {
	position: relative;
	display: inline-block;
	font-weight: bold;
	font-size: 2em;
	line-height: 1;
	margin: 0;
	padding: 0;
	-webkit-user-select: none;
}
#word span {
	cursor: pointer;
}
#word .letter {
	position: relative;
	display: inline-block;
	margin-right: 4px;
}
#word .switch {
	display: inline-block;
	padding: 0 2px;
}
#word:not(.moving) .switch:hover,
#word.moving .letter.moving .switch {
	background-color: yellow;
}
#word.moving .switch:hover {
	background-color: red;
}
#word .move {
	position: absolute;
	z-index: 3;
	top: -3px;
	left: -10px;
	width: 0;
	height: 0;
	display: block;
	border-top: solid 8px green;
	border-left: solid 8px transparent;
	border-right: solid 8px transparent;
}
#word > .move {
	left: auto;
	right: -11px;
}
#word:not(.moving) .move {
	display: none;
}
#word .move:hover {
	border-top-color: red;
}
</style>
</head>

<body onload="init()">

<p>We're looking for...</p>

<p id="word">?</p>

<script>
NodeList.prototype.forEach = Array.prototype.forEach;
HTMLCollection.prototype.indexOf = Array.prototype.indexOf;

const words = [
	'randomness',
	'simplicity',
	'inconspicuous',
	'careless',
	'multitude',
	'shiny',
	'nightshade',
	'forever',
	'cannibalism',
	'scrutinize',
	'professor',
	'subscriptions',
	'impossible',
	'assignment',
	'delicacies',
	'yesterday',
	'upcoming',
	'opportunity',
	'celebrity',
	'business',
	'disabled',
];

var $word, wordIndex = -1, moves = 0, moving = -1;

function init() {
	$word = document.querySelector('#word');

	chooseWord();
	getReady();

	$word.onclick = function(e) {
		var t = e.target;
		if ( t.classList.contains('switch') ) {
			// Switch letters (REPLACE)
			if ( moving >= 0 ) {
				moves++;

				var $endMark = $word.lastElementChild,
					$letterB = t.parentNode,
					$letterA = $word.children[moving];

				var $letterAMark = $letterA.nextElementSibling,
					$letterBMark = $letterB.nextElementSibling;

				$letterAMark ? $word.insertBefore($letterB, $letterAMark) : $word.appendChild($letterB);
				$letterBMark ? $word.insertBefore($letterA, $letterBMark) : $word.appendChild($letterA);

				// Move the end triangle to the end again
				$word.appendChild($endMark);

				unmoving();
			}

			// Start moving letter
			else {
				var $letter = t.parentNode,
					letterIndex = $word.children.indexOf($letter);
				moving = letterIndex;
				$letter.classList.add('moving');
				$word.classList.add('moving');
			}
		}

		// Move letter (BETWEEN)
		else if ( t.classList.contains('move') ) {
			moves++;

			// Move to the end
			if ( t.parentNode.id == 'word' ) {
				var $letter = $word.children[moving];
				$word.appendChild($letter);

				// Move the end triangle to the end again
				$word.appendChild(t);
			}

			// Place before something
			else {
				var $letter = $word.children[moving],
					$mark = t.parentNode;
				$word.insertBefore($letter, $mark);
			}

			unmoving();
		}
	};
}

function unmoving() {
	moving = -1;

	document.querySelectorAll('.moving').forEach(function(el) {
		el.classList.remove('moving');
	});

	if ( $word.textContent.trim() == words[wordIndex] ) {
		setTimeout(function() {
			alert('You win!\n\nIn '+ moves + ' moves');
			location.reload();
		}, 200);
	}
}

function chooseWord() {
	wordIndex = ~~(Math.random() * words.length);
	$word.textContent = words[wordIndex];
}

function getReady() {
	setTimeout(function() {
		document.documentElement.classList.add('waiting');
		setTimeout(function() {
			garble();

			document.documentElement.classList.remove('waiting');
		}, 2000);
	}, 1000);
}

function garble() {
	var word = words[wordIndex];
// alert(word);
	for ( var letters=[], i=0; i<word.length; i++ ) {
		letters.push(word[i]);
	}

	letters.sort(function() {
		return 0.5 - Math.random();
	});

	var garbled = letters.join('');
// alert(garbled);
	var pre = '<span class="letter"><span class="switch">',
		suf = '</span><span class="move"></span></span>';
	$word.innerHTML = pre + letters.join(suf + pre) + suf + '<span class="move"></span>';
}
</script>

</body>

</html>
