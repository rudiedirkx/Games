<?php
// FALLOUT HACKING

require __DIR__ . '/inc.bootstrap.php';

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>Fallout Hacking Helper</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<script>window.onerror = function(e) { alert(e); };</script>
<style>
tr.possible .likeness {
	background-color: #afa;
}
tr.not-possible .likeness {
	background-color: #faa;
}

.word input {
	width: 100px;
	text-transform: uppercase;
}
.likeness input {
	width: 30px;
	text-align: center;
}

tr.added button,
tr.checked button {
	visibility: hidden;
}
tr.added .checked,
tr.checked .added {
	display: none;
}

#ta-container {
	position: relative;
}
textarea,
#ta-output {
	font-family: monospace;
	text-transform: uppercase;
	font-size: 16px;
	height: 24em;
}
#ta-output {
	position: absolute;
	top: 3px;
	left: 3px;
	pointer-events: none;
}
</style>
</head>

<body>

<div hidden>
	<table>
		<thead>
			<tr>
				<th>Word</th>
				<th>Sim.</th>
				<th></th>
			</tr>
		</thead>
		<tbody></tbody>
	</table>

	<template>
		<tr>
			<td class="word"><input type="text" /></td>
			<td class="likeness">
				<input type="number" class="added" />
				<output class="checked"></output>
			</td>
			<td>
				<button class="add">Add</button>
				<button class="check">Check</button>
			</td>
		</tr>
	</template>

	<hr />
</div>

<div id="ta-container">
	<textarea rows="8" cols="20" placeholder="AWORD 0
OTHER 0
CHECK"><? /* GOOD 1
FOOD 0
KANT 1
GAVE 3
GATE */ ?></textarea>
	<div id="ta-output"></div>
</div>

<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script>
class FalloutHackingHelper {
	constructor() {
		this.table = $('tbody');
		this.template = $('template').content;

		this.textarea = $('textarea');
		this.textareaOutput = $('#ta-output');

		this.repo = null;
	}

	start() {
		this.listen();
		this.addLine();
	}

	getLastLine() {
		return this.table.rows[this.table.rows.length - 1];
	}

	disableLastLine() {
		const tr = this.getLastLine();
		tr && tr.getElements('input').prop('disabled', true);
	}

	focusLastLine() {
		const tr = this.getLastLine();
		tr && tr.getElement('input').focus();
	}

	addLine() {
		this.disableLastLine();

		const tr = document.importNode(this.template, true);
		this.table.append(tr);

		this.focusLastLine();
	}

	getWord(tr) {
		return tr.getElement('.word input').value;
	}

	getLikeness(tr) {
		return parseFloat(tr.getElement('.likeness input').value);
	}

	makeRepo() {
		this.repo = new FalloutHackingHelperRepo();
		const addeds = this.table.getElements('tr.added');
		addeds.forEach(tr => this.repo.add(new FalloutHackingHelperWord(this.getWord(tr), this.getLikeness(tr))));
	}

	checkLines() {
		this.makeRepo();

		const checkeds = this.table.getElements('tr.checked');
		checkeds.forEach(tr => this.checkLine(tr));
	}

	checkLine(checkTR) {
		const checkWord = this.getWord(checkTR);
		const possible = this.repo.check(checkWord);

		checkTR.toggleClass('possible', possible);
		checkTR.toggleClass('not-possible', !possible);
	}

	alert(message) {
		setTimeout(() => alert(message), 60);
	}

	validateWordLength(tr) {
		if ( this.repo ) {
			if ( this.getWord(tr).length != this.repo.length() ) {
				this.alert('Wrong: word length');
				return false;
			}
		}

		return true;
	}

	validateLikeness(tr) {
		if ( isNaN(this.getLikeness(tr)) ) {
			this.alert('Wrong: need likeness');
			return false;
		}

		return true;
	}

	validateNeedAdded() {
		if ( this.table.rows.length < 2 ) {
			this.alert('Wrong: must add knowns first');
			return false;
		}

		return true;
	}

	validateLineForAdd(tr) {
		return this.validateWordLength(tr) && this.validateLikeness(tr);
	}

	validateLineForCheck(tr) {
		return this.validateWordLength(tr) && this.validateNeedAdded();
	}

	handleAdd(tr) {
		if ( this.validateLineForAdd(tr) ) {
			tr.addClass('added');
			this.checkLines();
			this.addLine();
		}
	}

	handleCheck(tr) {
		if ( this.validateLineForCheck(tr) ) {
			tr.addClass('checked');
			this.checkLines();
			this.addLine();
		}
	}

	tableListen() {
		this.table.on('keypress', 'input', e => {
			if (e.originalEvent.key == 'Enter') {
				const tr = e.target.closest('tr');
				if ( tr.getElement('.likeness input').value ) {
					this.handleAdd(tr);
				}
				else {
					this.handleCheck(tr);
				}
			}
		});

		this.table.on('click', 'button.add', e => {
			this.handleAdd(e.target.closest('tr'));
		});

		this.table.on('click', 'button.check', e => {
			this.handleCheck(e.target.closest('tr'));
		});
	}

	listen() {
		this.tableListen();
		this.textareaListen();
	}

	textareaWords() {
		const lines = this.textarea.value.trim().split(/[\r\n]+/);
		const words = lines.map(line => {
			var [word, likeness] = line.split(' ');
			likeness = likeness ? parseFloat(likeness) : null;
			return [word, likeness];
		});
		return words;
	}

	textareaCheckLines() {
		this.textareaMakeRepo();

		var html = '';
		this.textareaWords().forEach(([word, likeness]) => {
			if (likeness != null) {
				html += this.textareaAddedLine(word, likeness) + "\n";
			}
			else {
				html += this.textareaCheckLine(word) + "\n";
			}
		});
		this.textareaOutput.innerHTML = html;
	}

	textareaCheckLine(checkWord) {
		const possible = this.repo.check(checkWord);
		const color = possible ? 'green' : 'red';

		return `<span style="color: ${color}">` + checkWord + '</span><br>';
	}

	textareaAddedLine(checkWord, likeness) {
		return checkWord + ' ' + likeness + '<br>';
	}

	textareaMakeRepo() {
		this.repo = new FalloutHackingHelperRepo();

		this.textareaWords().forEach(([word, likeness]) => {
			likeness != null && this.repo.add(new FalloutHackingHelperWord(word, likeness));
		});
	}

	textareaListen() {
		this.textarea.on('input', e => {
			this.textareaCheckLines();
		});
	}
}

class FalloutHackingHelperRepo {
	constructor() {
		this.words = [];
	}

	add(word) {
		this.words.push(word);
	}

	check(checkWord) {
		checkWord = checkWord.toUpperCase();

		if (this.words.length && checkWord.length != this.length()) {
			return false;
		}

		return !this.words.some(word => {
			if ( !this.matchLikeness(checkWord, word.word, word.likeness) ) {
				return true;
			}
		});
	}

	matchLikeness(word1, word2, wantLikeness) {
		const isLikeness = word1.split('').filter((char, i) => char == word2[i]).length;
		return isLikeness == wantLikeness;
	}

	length() {
		if (this.words.length) {
			return this.words[0].word.length;
		}
	}
}

class FalloutHackingHelperWord {
	constructor(word, likeness) {
		this.word = word.toUpperCase();
		this.likeness = likeness;
	}
}

const objGame = new FalloutHackingHelper();
objGame.start();
</script>

<style>
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
	-webkit-appearance: none;
	appearance: none;
	margin: 0;
}
input[type=number] {
	-moz-appearance: textfield;
}
</style>

</body>

</html>
