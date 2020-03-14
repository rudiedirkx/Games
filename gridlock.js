class Gridlock extends LeveledGridGame {

	reset() {
		super.reset();

		// this.styles = $('#gridlock-styles');
	}

	createField( cell, type, rv, x, y ) {
		if ( ' ' == type ) return;

		cell.data('block', type);
	}

	// createGame() {
	// 	super.createGame();
	//
	// 	document.head.append(document.el('style').attr('id', 'gridlock-styles'));
	// }

	// randomColor() {
	// 	return '#' + parseInt(Math.random() * 12).toString(16).repeat(3);
	// 	// return '#' + ('000000' + (Math.random()*0xFFFFFF<<0).toString(16)).slice(-6);
	// }

	// createdMap( rv ) {
	// 	const types = rv.map.map(row => Array.from(row)).flat(1).unique().filter(t => t != ' ');
	// 	this.styles.setText(types.map(t => {
	// 		return `td[data-block="${t}"] { background-color: ${t == 'z' ? '#c00' : this.randomColor()} }`;
	// 	}).join("\n"));
	// }

	listenControls() {
		this.listenCellClick();
		this.listenGlobalDirection();
	}

	haveWon() {
		return false;
	}

	handleCellClick( cell ) {
	}

	handleGlobalDirection( dir ) {
	}

}

class GridlockEditor extends GridGameEditor {

	cellTypes() {
		return {
			"z": 'Target block',
			"a": 'Block A',
			"b": 'Block B',
			"c": 'Block C',
			"d": 'Block D',
			"e": 'Block E',
			"f": 'Block F',
			"g": 'Block G',
			"h": 'Block H',
			"i": 'Block I',
			"j": 'Block J',
			"k": 'Block K',
		};
	}

	defaultCellType() {
		return 'z';
	}

	createCellTypeCell( type ) {
		return '<td data-block="' + type + '"></td>';
	}

	exportLevel() {
		const map = [];

		r.each(this.m_objGrid.rows, (tr, y) => {
			var row = '';
			r.each(tr.cells, (cell, y) => {
				row += cell.data('block') || ' ';
			});
			map.push(row);
		});

		const level = {map};
		return level;
	}

	formatAsPHP( level ) {
		var code = [];
		code.push('\t[');
		code.push("\t\t'map' => [");
		r.each(level.map, row => code.push("\t\t\t'" + row + "',"));
		code.push("\t\t],");
		code.push('\t],');
		code.push('');
		code.push('');
		return code;
	}

	setBlockType( cell, type ) {
		const cur = cell.data('block');
		if (cur === type) {
			cell.data('block', null);
		}
		else {
			cell.data('block', type);
		}
	}

	setType_z( cell ) {
		this.setBlockType(cell, 'z');
	}

	setType_a( cell ) {
		this.setBlockType(cell, 'a');
	}

	setType_b( cell ) {
		this.setBlockType(cell, 'b');
	}

	setType_c( cell ) {
		this.setBlockType(cell, 'c');
	}

	setType_d( cell ) {
		this.setBlockType(cell, 'd');
	}

	setType_e( cell ) {
		this.setBlockType(cell, 'e');
	}

	setType_f( cell ) {
		this.setBlockType(cell, 'f');
	}

	setType_g( cell ) {
		this.setBlockType(cell, 'g');
	}

	setType_h( cell ) {
		this.setBlockType(cell, 'h');
	}

	setType_i( cell ) {
		this.setBlockType(cell, 'i');
	}

	setType_j( cell ) {
		this.setBlockType(cell, 'j');
	}

	setType_k( cell ) {
		this.setBlockType(cell, 'k');
	}

}
