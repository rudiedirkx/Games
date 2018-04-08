class Mastermind extends Game {

	constructor() {
		super();

		this.m_objTable = $('#table');

		this.m_arrColors = ['black', 'white', 'green', 'red', 'yellow', 'blue'];

		this.m_objColorSelector = this.createColorSelector();
		this.m_fnSelectColor = () => 0;
	}

	createColorSelector() {
		var el = $('#color-selection');

		el.setHTML(this.m_arrColors.map((color) => {
			return '<li data-color="' + color + '" style="background-color: ' + color + '"></li>';
		}).join(''));

		return el;
	}

	startGame() {
		document.body.removeClass('gameover');

		this.m_objTable.getElements('tbody tr')
			.removeClass('active').removeClass('done')
			.first().addClass('active');
		this.m_objTable.getElements('tbody td').data('color', null).attr('style', null);
		this.m_objTable.getElements('.score').empty();

		var colors = this.m_arrColors.slice();

		var cells = this.m_objTable.getElements('.unknown-colors td').each((cell) => {
			colors.sort((a, b) => Math.random() > 0.5 ? 1 : -1);
			var color = colors.shift();
			cell.removeClass('open').data('color', color).attr('style', null);
		});
	}

	selectColor( cell ) {
		if ( this.m_bGameOver ) return this.startGame();

		this.m_fnSelectColor = (color) => {
			cell.data('color', color).css('background-color', color);
		};
		document.body.classList.add('selecting-color');
	}

	checkSelection() {
		if ( this.m_bGameOver ) return this.startGame();

		this.startTime();

		var activeRow = this.m_objTable.getElement('tr.active');
		var colors = this.getSelection(activeRow);

		var missing = colors.some((color) => !color);
		if ( missing ) {
			return alert('You must select 4 colors');
		}

		var uniqueColors = colors.unique();
		if ( uniqueColors.length != colors.length ) {
			return alert('The 4 colors must be unique');
		}

		var [goodPositions, goodColors] = this.getScore(colors);
		var asterisks = [];
		for (var i = 0; i < colors.length; i++) {
			if ( i < goodPositions ) asterisks.push('position');
			else if ( i < goodColors ) asterisks.push('color');
		}
		activeRow.getElement('.score').setHTML(asterisks.map((clas) => {
			return '<span class="' + clas + '">*</span>';
		}).join(' '));

		var nextRow = activeRow.getNext();
		activeRow.removeClass('active').addClass('done');
		nextRow.addClass('active');

		this.winOrLose();
	}

	getScore( haveColors ) {
		var mustColors = this.getMustColors();
		var goodPositions = mustColors.reduce((num, color, i) => num + Number(color == haveColors[i]), 0);
		var goodColors = mustColors.reduce((num, color) => num + Number(haveColors.includes(color)), 0);
		return [goodPositions, goodColors];
	}

	getSelection( tr ) {
		return tr.getElements('td').map((cell) => cell.data('color') || '');
	}

	getMustColors() {
		return this.getSelection(this.m_objTable.getElement('.unknown-colors'));
	}

	win() {
		super.win();

		this.m_objTable.getElements('.unknown-colors td').each((cell) => {
			cell.addClass('open').css('background-color', cell.data('color'));
		});

		this.m_objTable.getElements('tr.active').removeClass('active');

		document.body.addClass('gameover');
	}

	haveWon() {
		var iter = this.m_objTable.getElements('tbody tr.done').last();
		if ( !iter ) return false;

		var haveColors = this.getSelection(iter);
		var mustColors = this.getMustColors();

		return haveColors.join(',') == mustColors.join(',');
	}

	listenControls() {
		this.m_objTable.on('click', 'tbody tr.active td', (e) => {
			this.selectColor(e.subject);
		});

		this.m_objColorSelector.on('click', 'li[data-color]', (e) => {
			this.m_fnSelectColor(e.subject.data('color'));
			document.body.classList.remove('selecting-color');
		});

		this.m_objTable.getElement('.submit button').on('click', (e) => {
			e.preventDefault();

			this.checkSelection();
		});
	}

}
