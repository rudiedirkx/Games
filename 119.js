g119 = {};
g119.history = [];

// Reset the current level completely
g119.reset = function(grid) {
	sessionStorage.removeItem('g119_' + g119.solution);
	g119.empty(grid);
	g119.history.length = 0;
};

g119.empty = function(grid) {
	[].forEach.call(grid.querySelectorAll('td'), function(cell) {
		cell.dataset.state = '';
	});

	[].forEach.call(grid.querySelectorAll('.invalid'), function(cell) {
		cell.classList.remove('invalid');
	});

	var table = grid.parentNode;
	table.classList.remove('invalid');
};

// All possible active-inactive results for any line
g119.options = function(length, hints) {
	var groups = hints.length;
	var taken = hints.reduce(function(num, hint) {
		return num + hint;
	}, 0);
	var spacers = groups-1;
	var room = length - taken - spacers;

	var options;
	if (room == 0) {
		var option = '';
		for (var i=0; i<hints.length; i++) {
			if (i > 0) {
				option += '0';
			}
			for (var j=0; j<hints[i]; j++) {
				option += '1';
			}
		}
		options = [option];
	}
	else {
		var flexibles = groups+1;
		var code = 'var spread = [];';
		for (var f=0; f<flexibles; f++) {
			code += 'for (var g' + f + '=0; g' + f + '<=' + room + '; g' + f + '++) ';
		}
		var vars = [];
		for (var f=0; f<flexibles; f++) {
			vars.push('g' + f);
		}
		code += 'if (' + vars.join(' + ') + ' == ' + room + ') ';
		code += 'spread.push([' + vars.join(', 1+') + '-1]); ';
		code += 'return spread;';

		var fn = new Function(code);
		var spread = fn();

		options = [];
		for (var i=0; i<spread.length; i++) {
			var option = '';
			for (var j=0; j<hints.length; j++) {
				// Add inactives before
				for (var x=0; x<spread[i][j]; x++) {
					option += '0';
				}

				// Add actives
				for (var x=0; x<hints[j]; x++) {
					option += '1';
				}
			}

			// Add last inactives after
			for (var x=0; x<spread[i][j]; x++) {
				option += '0';
			}
			options.push(option);
		}
	}

	return options;
};

// Evaluate grid difficulty
g119.difficulty = function(grid) {
	var table = document.createElement('table');
	table.innerHTML = grid.innerHTML;

	// @todo Optionally use the board for hints, instead of the THs

	grid = table.tBodies[0];
	g119.empty(grid);

	var difficulty = 0;
	var changes = true;
	var oldwhite = 0;
	var oldBlack = 0;
	while (changes) {
		difficulty++;

		var rows = grid.rows.length - 1;
		for (var i=0; i<rows; i++) {
			g119.fillRowWithLine(grid, i, g119.commonCells(g119.validLines(g119.getLineForRow(grid, i), g119.getHintsForRow(grid, i))));
		}

		var cols = grid.rows[0].cells.length - 1;
		for (var i=0; i<cols; i++) {
			g119.fillColumnWithLine(grid, i, g119.commonCells(g119.validLines(g119.getLineForColumn(grid, i), g119.getHintsForColumn(grid, i))));
		}

		var newWhite = grid.querySelectorAll('td[data-state="inactive"]').length;
		var newBlack = grid.querySelectorAll('td[data-state="active"]').length;

		changes = oldwhite != newWhite || oldBlack != newBlack;
		oldwhite = newWhite;
		oldBlack = newBlack;
	}

	var leftover = (grid.rows.length-1) * (grid.rows[0].cells.length-1) - newWhite - newBlack;
	difficulty += Math.ceil(leftover / 4);

	return difficulty;
};

// See if the entire grid is fully solved according to all hints
g119.solvedGrid = function(grid) {
	// Rows
	for (var i=0; i<grid.rows.length-1; i++) {
		if (!g119.validRow(grid, i, true)) {
			return false;
		}
	}

	// Rows
	for (var i=0; i<grid.rows[0].cells.length-1; i++) {
		if (!g119.validColumn(grid, i, true)) {
			return false;
		}
	}

	return true;
};

// Create solved-regex from hints
g119.hintsToRegex = function(hints) {
	var groups = hints.map(function(length) {
		return '1{' + length + '}';
	});
	var regex = '^0*' + groups.join('0+') + '0*$';
	return new RegExp(regex);
};

// See if a user generated line is (still) valid for the given hints
g119.validLine = function(line, hints) {
	var options = g119.options(line.length, hints);
	var regex = new RegExp('^' + line.replace(/_/g, '.') + '$');
	for (var i=0; i<options.length; i++) {
		if (regex.test(options[i])) {
			return true;
		}
	}
	return false;
};

// See if a line is fully solved according to its hints
g119.solvedLine = function(line, hints) {
	var regex = g119.hintsToRegex(hints);
	return regex.test(line);
};

// See if a user filled row is (still) valid.
g119.validRow = function(grid, index, solved) {
	var line = g119.getLineForRow(grid, index, !solved);
	var hints = g119.getHintsForRow(grid, index);
	return solved ? g119.solvedLine(line, hints) : g119.validLine(line, hints);
};

// See if a user filled column is (still) valid.
g119.validColumn = function(grid, index, solved) {
	var line = g119.getLineForColumn(grid, index, !solved);
	var hints = g119.getHintsForColumn(grid, index);
	return solved ? g119.solvedLine(line, hints) : g119.validLine(line, hints);
};

// Find still valid solutions from hints + user input
g119.validLines = function(line, hints) {
	var options = g119.options(line.length, hints);
	var regex = new RegExp('^' + line.replace(/_/g, '.') + '$');
	return options.filter(function(option) {
		return regex.test(option);
	});
};

// Find common cells in all the given options
g119.commonCells = function(options) {
	var counts = [];
	for (var i=0; i<options.length; i++) {
		var option = options[i];
		for (var j=0; j<option.length; j++) {
			var cell = option[j];
			if (counts[j] == null) {
				counts[j] = 0;
			}
			if (cell == '1') {
				counts[j]++;
			}
		}
	}

	var commons = '';
	for (var i=0; i<counts.length; i++) {
		var count = counts[i];
		if (count == options.length) {
			commons += '1';
		}
		else if (count == 0) {
			commons += '0';
		}
		else {
			commons += '_';
		}
	}

	return commons;
};

// Save a line to the board
g119.fillCellsWithLine = function(cells, line) {
	if (cells.length == line.length) {
		for (var i=0; i<cells.length; i++) {
			cells[i].dataset.state = g119.charToState(line[i]);
		}
	}
};

// Save a row line to the board
g119.fillRowWithLine = function(grid, index, line) {
	return g119.fillCellsWithLine(grid.rows[index].querySelectorAll('td'), line);
};

// Save a column line to the board
g119.fillColumnWithLine = function(grid, index, line) {
	return g119.fillCellsWithLine(grid.querySelectorAll('td:nth-child(' + (index + 1) + ')'), line);
};

// Get user generated line for cells
g119.getLineForCells = function(cells, withUnknowns) {
	return [].map.call(cells, function(cell) {
		return g119.stateToChar(cell.dataset.state, withUnknowns !== false);
	}).join('');
};

// Get user generated line for a row
g119.getLineForRow = function(grid, index, withUnknowns) {
	return g119.getLineForCells(grid.rows[index].querySelectorAll('td'), withUnknowns !== false);
};

// Get user generated line for a column
g119.getLineForColumn = function(grid, index, withUnknowns) {
	return g119.getLineForCells(grid.querySelectorAll('td:nth-child(' + (index + 1) + ')'), withUnknowns !== false);
};

// Get hints for a meta cell
g119.getHintsForCell = function(cell) {
	return cell.dataset.hints.split(',').map(function(hint) {
		return parseInt(hint);
	});
};

// Get hints for a row
g119.getHintsForRow = function(grid, index) {
	return g119.getHintsForCell(g119.getMetaCellForRow(grid, index));
};

// Get hints for a column
g119.getHintsForColumn = function(grid, index) {
	return g119.getHintsForCell(g119.getMetaCellForColumn(grid, index));
};

g119.getMetaCellForRow = function(grid, index) {
	return grid.querySelector('tr:nth-child(' + (index + 1) + ') > th');
};

g119.getMetaCellForColumn = function(grid, index) {
	return grid.querySelector('tr:last-child > th:nth-child(' + (index + 1) + ')');
};

// Get state short (0, 1, _) for a cell
g119.stateToChar = function(state, withUnknowns) {
	return state == 'active' ? 1 : state == 'inactive' || !withUnknowns ? 0 : '_';
};

// Get state name (active, inactive) for a cell
g119.charToState = function(char) {
	return char == '1' ? 'active' : char == '0' ? 'inactive' : '';
};

// Create string map from cells
g119.map = function(grid, withUnknowns) {
	var width = grid.rows[0].querySelectorAll('td').length;
	var cells = [].map.call(grid.querySelectorAll('td'), function(cell) {
		return g119.stateToChar(cell.dataset.state, withUnknowns);
	});

	var str = String(width) + '.' + cells.join('');
	if (!withUnknowns) {
		str = str.replace(/0*$/, '');
	}
	return str;
};

// Validate row & column from a cell
g119.validateFromCell = function(cell) {
	var tbody = cell.parentNode.parentNode;
	return setTimeout(function() {
		g119.validateRow(tbody, cell.parentNode.sectionRowIndex);

		g119.validateColumn(tbody, cell.cellIndex);

		g119.markTableValidity(tbody);
	});
};

// Mark a row for validity
g119.validateRow = function(tbody, index) {
	var valid = g119.validRow(tbody, index, false);
	g119.getMetaCellForRow(tbody, index).classList[valid ? 'remove' : 'add']('invalid');
};

// Mark a column for validity
g119.validateColumn = function(tbody, index) {
	var valid = g119.validColumn(tbody, index, false);
	g119.getMetaCellForColumn(tbody, index).classList[valid ? 'remove' : 'add']('invalid');
};

// Mark all rows & columns for validity
g119.validateTable = function(tbody) {
	for (var i = 0; i < tbody.rows.length; i++) {
		g119.validateRow(tbody, i);
	}

	var C = tbody.rows[0].querySelectorAll('td').length;
	for (var i = 0; i < C; i++) {
		g119.validateColumn(tbody, i);
	}

	g119.markTableValidity(tbody);
};

// Mark entire table for validity
g119.markTableValidityTimer = -1;
g119.markTableValidity = function(tbody) {
	clearTimeout(g119.markTableValidityTimer);
	var table = tbody.parentNode;

	var valid = !table.querySelector('th.invalid');
	if (valid) {
		table.classList.remove('invalid');
	}
	else {
		g119.markTableValidityTimer = setTimeout(function() {
			var valid = !table.querySelector('th.invalid');
			table.classList[valid ? 'remove' : 'add']('invalid');
		}, 500);
	}
};

// Click handler for grid
g119.click = function(cell, states, undo) {
	var delta = undo ? -1 : +1;

	var state = cell.dataset.state || states[0];
	var stateIndex = states.indexOf(state);
	stateIndex = (stateIndex + delta) % states.length;
	cell.dataset.state = states[(stateIndex + states.length) % states.length];

	var map = g119.map(tbody, true);
	sessionStorage.setItem('g119_' + g119.solution, map);

	if (!undo) {
		g119.history.push(cell);
	}
};

// Disable tap zoom
g119.noZoom = function(grid) {
	grid.addEventListener('touchend', function(e) {
		e.preventDefault();
	});
};

// Hash map for solution
g119.shash = function(str) {
	var hash = 0, chr, len;
	for (var i = 0, len = str.length; i < len; i++) {
		chr = str.charCodeAt(i);
		hash = ((hash << 5) - hash) + chr;
		hash |= 0;
	}
	return String(hash>>>0);
};
