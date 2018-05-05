class Tetravex extends GridGame {

	constructor(solution, available) {
		super(solution);

		this.m_objSource = available;
		this.m_iSize = 0;
	}

	getScore() {
		return {
			...super.getScore(),
			level: this.m_iSize,
		};
	}

	createGame() {
		setTimeout(() => {
			this.m_objSource.on('dragstart', e => e.preventDefault());
			this.m_objGrid.on('dragstart', e => e.preventDefault());
		});

		$('#size-selector').on('change', (e) => {
			if ( e.subject.value ) {
				this.startGame(parseInt(e.subject.value));
			}
		});
	}

	startGame( size ) {
		this.reset();

		this.m_iSize = size;
		$('#size-selector').value = size;

		const grid = this.createMap(size);
		this.fillTable(size, this.m_objSource, grid);
		this.fillTable(size, this.m_objGrid);
	}

	restartGame() {
		this.startGame(this.m_iSize);
	}

	fillTable( size, table, grid ) {
		var html = '';
		for (var y = 0; y < size; y++) {
			html += '<tr>';
			for (var x = 0; x < size; x++) {
				var tile = grid && grid[y] && grid[y][x] || '';
				var src = tile ? 'src="' + this.createTileSource(tile) + '"' : '';
				html += '<td><img data-tile="' + tile + '" ' + src + ' /></td>';
			}
			html += '</tr>';
		}
		table.setHTML(html);
	}

	createTileSource( tile ) {
		// @todo Inline SVG
		return '?image=' + tile;
	}

	randomTile() {
		return Math.floor(Math.random() * 10);
	}

	createMap( size ) {
		const grid = [];
		var row, cell;
		for (var y = 0; y < size; y++) {
			row = [];
			for (var x = 0; x < size; x++) {
				cell = [
					y > 0 ? grid[y-1][x][2] : this.randomTile(),
					this.randomTile(),
					this.randomTile(),
					x > 0 ? row[x-1][1] : this.randomTile(),
				].join('');
				row.push(cell);
			}
			grid.push(row);
		}

		const tiles = this.flatten(grid);
		tiles.sort(this.shuffle);
		tiles.sort(this.shuffle);

		return this.chunk(tiles, size);
	}

	shuffle() {
		return Math.random() > 0.5 ? -1 : 1;
	}

	flatten( grid ) {
		return [].concat.apply([], grid);
	}

	chunk( list, size ) {
		const grid = [];
		while ( list.length > 0 ) {
			grid.push(list.splice(0, size));
		}
		return grid;
	}

	haveWon() {
		return this.m_objGrid.getElements('img[data-tile=""]').length == 0;
	}

	getSelected() {
		return this.m_objGrid.getElement('.selected') || this.m_objSource.getElement('.selected')
	}

	select( cell ) {
		this.unselect();
		cell.addClass('selected');
	}

	getTile( cell ) {
		return cell.firstElementChild.data('tile');
	}

	shift( direction ) {
		this.unselect();

		// @todo
	}

	tilesMatch( sourceTile, dirNum, neighborTile ) {
		const sourceConnector = sourceTile[dirNum];
		const neighborConnector = neighborTile[(dirNum+2)%4];
		return neighborConnector === sourceConnector;
	}

	validMove( sourceCell, targetCell ) {
		if ( targetCell.closest('#available') ) return true;

		const targetCoord = this.getCoord(targetCell);
		const sourceImg = sourceCell.firstElementChild;
		const sourceTile = sourceImg.data('tile');

		return !this.dir4Coords.some((offset, i) => {
			var cell = this.getCell(targetCoord.add(offset));
			var tile = cell && cell.firstElementChild.data('tile');
			return cell != sourceCell && tile && !this.tilesMatch(sourceTile, i, tile);
		});
	}

	moveTo( sourceCell, targetCell ) {
		if ( !this.validMove(sourceCell, targetCell) ) return;

		const targetImg = targetCell.firstElementChild;
		const sourceImg = sourceCell.firstElementChild;

		targetCell.append(sourceImg);
		sourceCell.append(targetImg);

		this.setMoves(this.m_iMoves + 1);
		this.winOrLose();

		this.unselect();
	}

	unselect() {
		this.m_objGrid.getElements('.selected').removeClass('selected');
		this.m_objSource.getElements('.selected').removeClass('selected');
	}

	listenControls() {
		this.listenCellClick();
		this.listenCellClick(this.m_objSource);
		this.listenGlobalDirection();
	}

	handleCellClick( cell ) {
		if ( this.m_bGameOver ) return this.restartGame();

		var selected = this.getSelected();
		if ( this.getTile(cell) ) {
			this.startTime();
			this.select(cell);
		}
		else if ( selected ) {
			this.startTime();
			this.moveTo(selected, cell);
		}
	}

	handleGlobalDirection( direction ) {
		if ( this.m_bGameOver ) return;

		this.shift(direction);
	}

}
