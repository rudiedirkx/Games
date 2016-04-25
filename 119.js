g119 = {};

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

// See if an user generated line is (still) valid for the given hints
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
g119.getLineForCells = function(cells) {
	return [].map.call(cells, function(cell) {
		return g119.stateToChar(cell.dataset.state, true);
	}).join('');
};

// Get user generated line for a row
g119.getLineForRow = function(grid, index) {
	return g119.getLineForCells(grid.rows[index].querySelectorAll('td'));
};

// Get user generated line for a column
g119.getLineForColumn = function(grid, index) {
	return g119.getLineForCells(grid.querySelectorAll('td:nth-child(' + (index + 1) + ')'));
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

	return String(width) + '.' + cells.join('').replace(/0*$/, '');
};

// Click handler for grid
g119.click = function(cell, states) {
	var state = cell.dataset.state || states[0];
	var stateIndex = states.indexOf(state);
	stateIndex = (stateIndex + 1) % states.length;
	cell.dataset.state = states[stateIndex];
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
