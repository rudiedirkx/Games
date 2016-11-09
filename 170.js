mahjong = {};

// debug //
mahjong._debug = function(msg) {
	var pre = document.createElement('pre');
	pre.textContent = msg;
	document.body.appendChild(pre);
};
// debug //

mahjong.colors = {"111":"white","000":"black","100":"red","010":"green"};

mahjong.IMPORT_SCALE_X = 2;
mahjong.IMPORT_SCALE_Y = 3;

mahjong.Board = function Board() {
	this.levels = [];
	this.allTiles = [];

	this.addTile = function(tile) {
		tile.board = this;

		this.allTiles.push(tile);

		if (!this.levels[tile.level]) {
			this.levels[tile.level] = [];
		}

		this.levels[tile.level].push(tile);
	};

	this.assignValues = function() {
		if (this.allTiles.length % 2 != 0) {
			throw new Error('Number of tiles must be a multiple of 2.');
		}

		var range = 9;

		var values = [];
		for (var i = 0; i < this.allTiles.length/2; i++) {
			var value = (i % range) + 1;
			values.push(value);
			values.push(value);
		}

		this.assign(this.allTiles, values);
	};

	this.assign = function(tiles, values) {
		values.sort(function(a, b) {
			return Math.random() > 0.5 ? -1 : 1;
		});

		for (var i = 0; i < values.length; i++) {
			tiles[i].value = values[i];
		}
	};

	this.shuffle = function() {
		var values = this.activeValues();
		var tiles = this.activeTiles();

		this.assign(tiles, values);
	};

	this.activeTiles = function() {
		return this.allTiles.filter(mahjong.Tile.enabled);
	};

	this.activeValues = function() {
		return this.activeTiles().map(mahjong.Tile.value);
	};

	this.activeTilesOnTop = function() {
		return this.allTiles.filter(mahjong.Tile.enabled).filter(mahjong.Tile.onTop);
	};

	this.activeValuesOnTop = function() {
		return this.activeTilesOnTop().map(mahjong.Tile.value);
	};

	this.moves = function() {
		var values = this.activeValuesOnTop().reduce(function(values, value) {
			(values[value]) ? (values[value]++) : (values[value] = 1);
			return values;
		}, {});

		var moves = 0;
		for (var value in values) {
			moves += Math.floor(values[value] / 2);
		}

		return moves;
	};
};

mahjong.Board.fromList = function(list) {
	var board = new mahjong.Board;
	for (var i = 0; i < list.length; i++) {
		board.addTile(list[i]);
	}

	return board;
};

mahjong.Board.serialize = function(list) {
	return list.map(function(tile) {
		return [tile.x, tile.y, tile.level];
	});
};
mahjong.Board.unserialize = function(list) {
	return list.map(function(tile) {
		return new mahjong.Tile(tile[0], tile[1], tile[2]);
	});
};

mahjong.Tile = function Tile(x, y, level) {
	this.x = x;
	this.y = y;
	this.level = level;

	this.levelUp = function() {
		return this.board.levels[ this.level + 1 ];
	};

	this.isOnTop = function() {
		// The tiles of 1 level up
		var levelUp = this.levelUp();
		if (!levelUp) return true;

		// Check for every tile if it covers this one
		for (var i = 0; i < levelUp.length; i++) {
			var tile = levelUp[i];
			if (!tile.disabled && tile.covers(this)) {
				return false;
			}
		}

		return true;
	};

	this.covers = function(tile) {
		// `this` must be lower than `tile`
		if (tile.level >= this.level) return false;

		// X & Y offset must be <= 1
		return Math.abs(this.x - tile.x) <= 1 && Math.abs(this.y - tile.y) <= 1;
	};

	this.rect = function() {
		return [
			this.x * (SQUARE_W+MARGIN),
			this.y * (SQUARE_H+MARGIN),
			(SQUARE_W+MARGIN) * TILE_W - 1,
			(SQUARE_H+MARGIN) * TILE_H - 1
		];
	};

	this.draw = function(ctx, color) {
		var rect = this.rect();
		ctx.fillStyle = color;
		ctx.fillRect.apply(ctx, rect);

		if (this.value) {
			ctx.font = '16px sans-serif';
			ctx.fillStyle = '#fff';
			ctx.textAlign = 'left';
			ctx.textBaseline = 'top';
			ctx.fillText(String(this.value), rect[0] + 5, rect[1] + 5);
		}
	};

	this.hitBy = function(x, y) {
		var rect = this.rect();
		return x > rect[0] && x < rect[0] + rect[2] && y > rect[1] && y < rect[1] + rect[3];
	};

};

mahjong.Tile.enabled = function(tile) {
	return !tile.disabled;
};

mahjong.Tile.onTop = function(tile) {
	return tile.isOnTop();
};

mahjong.Tile.value = function(tile) {
	return tile.value;
};

mahjong.draw = function(canvas, board) {
	var ctx = canvas.getContext('2d');
	ctx.canvas.width = ctx.canvas.width;

	for (var level = 0; level < board.levels.length; level++) {
		var tiles = board.levels[level];
		var L = (12 - 3*level).toString(16);
		var color = '#' + L + L + L;

		for (var i = 0; i < tiles.length; i++) {
			var tile = tiles[i];

			// Don't draw disabled tiles
			if (tile.disabled) continue;

			tile.draw(ctx, color);
		}
	}
};

mahjong.target = function(board, x, y) {
	var L = board.levels.length;
	while (L--) {
		var tiles = board.levels[L];
		for (var i = 0; i < tiles.length; i++) {
			var target = tiles[i];
			if (!target.disabled && target.hitBy(x, y)) {
				return target;
			}
		}
	}
};

mahjong.pixels = function(src) {
	// console.time('pixels');

	return new Promise(function(resolve) {
		var img = new Image;
		img.src = src;
		img.onload = function(e) {
			var canvas = document.createElement('canvas');
			var w = img.width;
			var h = img.height;
			canvas.width = w;
			canvas.height = h;
			var ctx = canvas.getContext('2d');
			ctx.drawImage(img, 0, 0);
			var pixels = ctx.getImageData(0, 0, w, h).data;

			var level = 0;
			var image = [[]];
			for (var i = 0; i < pixels.length; i+=4) {
				var key = String(Math.round(pixels[i]/255)) + String(Math.round(pixels[i+1]/255)) + String(Math.round(pixels[i+2]/255));
				var px = mahjong.colors[ key ];

				var y = Math.floor(i/4/w);
				var x = i/4 - y*w;

				if (x == 0) {
					level = 0;
				}

				if (px == 'red') {
					level++;

					if (image[level] == null) {
						image[level] = [];
					}
				}
				else {
					if (image[level][y] == null) {
						image[level][y] = [];
					}

					image[level][y].push(px);
				}
			}

			// console.timeEnd('pixels');
			resolve(image);
		};
	});
};

mahjong.tiles = function(pixels) {
	// console.time('tiles');

	return new Promise(function(resolve) {
		var board = new mahjong.Board;

		for (var level = 0; level < pixels.length; level++) {

			var rows = pixels[level];
			for (var y = 0; y < rows.length; y++) {

				var cols = rows[y];
				for (var x = 0; x < cols.length; x++) {

					var px = cols[x];
					if (px == 'green') {
						board.addTile(new mahjong.Tile(x/mahjong.IMPORT_SCALE_X, y/mahjong.IMPORT_SCALE_Y, level));
					}
				}
			}
		}

		// console.timeEnd('tiles');
		resolve(board);
	});
};
