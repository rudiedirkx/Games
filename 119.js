g119 = {};

// Create string map from cells
g119.map = function(grid) {
	var width = grid.rows[0].querySelectorAll('td').length;
	var cells = [].map.call(grid.querySelectorAll('td'), function(cell) {
		return cell.dataset.state == 'active' ? 1 : 0;
	});

	return String(width) + '.' + cells.join('').replace(/0*$/, '');
};

// Click handler for grid
g119.click = function(cell, states) {
	var state = parseInt(cell.dataset.stateIndex) || 0;
	state = (state + 1) % states.length;
	cell.dataset.stateIndex = state;
	cell.dataset.state = states[state];
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
