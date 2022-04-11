TrackSwitcher.PROBLEMS = [
	new Problem(3, // 1
		[new Engine('1-8', -1), new Wagon(1, '5-1')],
		[new Engine('5-4', 1), new Wagon(1, '5-3')],
	),
	new Problem(3, // 2
		[new Engine('5-4', -1), new Wagon(1, '5-3')],
		[new Engine('5-4', 1), new Wagon(1, '5-3')],
	),
	new Problem(3, // 3
		[new Engine('1-7', -1), new Wagon(1, '1-8'), new Wagon(2, '5-4')],
		[new Engine('lb-2', -1), new Wagon(1, 'lb-1'), new Wagon(2, '7-1')],
	),
	new Problem(5, // 4
		[new Engine('3-1', 1), new Wagon(1, '5-1'), new Wagon(2, '7-1'), new Wagon(3, 'rb-3')],
		[new Engine('1-8', 1), new Wagon(1, '1-6'), new Wagon(2, 'lb-2'), new Wagon(3, '1-7')],
	),
	new Problem(3, // 5
		[new Engine('5-1', -1), new Engine('5-4', 1), new Wagon(1, '3-1')],
		[new Engine('rb-1', -1), new Engine('rb-3', -1), new Wagon(1, 'rb-2')],
	),
	new Problem(3, // 6
		[new Engine('5-1', -1), new Engine('rb-3', -1), new Wagon(1, 'c'), new Wagon(2, '7-1')],
		[new Engine('5-4', 1), new Engine('7-2', -1), new Wagon(1, '6-2'), new Wagon(2, '5-3')],
	),
	new Problem(4, // 7
		[new Engine('lb-2', -1), new Wagon(1, '7-1'), new Wagon(2, 'rb-3'), new Block('3-1')],
		[new Engine('5-1', -1), new Wagon(1, '6-1'), new Wagon(2, '5-2')],
	),
	new Problem(7, // 8
		[new Engine('5-1', 1), new Engine('5-3', 1), new Wagon(1, '3-1'), new Wagon(2, '5-4'), new Block('lb-1')],
		[new Engine('5-1', -1), new Engine('rb-3', 1), new Wagon(1, 'rb-2'), new Wagon(2, '5-2')],
	),
	null,
	null,
	new Problem(5, // 11
		[new Engine('5-3', -1), new Wagon(1, '4-2'), new Wagon(2, '5-4')],
		[new Engine('1-8', 1), new Wagon(1, '1-7'), new Wagon(2, 'lb-2')],
	),
	new Problem(4, // 12
		[new Engine('5-4', 1), new Wagon(1, '5-2'), new Wagon(2, '4-1'), new Wagon(3, '6-1')],
		[new Engine('1-8', 1), new Wagon(1, '6-2'), new Wagon(2, '1-6'), new Wagon(3, '1-7')],
	),
	new Problem(5, // 13
		[new Engine('5-1', -1), new Engine('5-4', -1), new Wagon(1, 'c'), new Wagon(2, 'rb-3')],
		[new Engine('1-8', 1), new Engine('lb-2', -1), new Wagon(1, '1-7'), new Wagon(2, 'lb-1')],
	),
	null,
	new Problem(6, // 15
		[new Engine('lb-2', 1), new Wagon(1, '5-1'), new Wagon(2, '5-4'), new Block('4-1')],
		[new Engine('5-1', -1), new Wagon(1, '6-1'), new Wagon(2, '5-2')],
	),
	new Problem(6, // 16
		[new Engine('5-1', -1), new Wagon(1, '1-8'), new Wagon(2, '5-4'), new Wagon(3, 'rb-3')],
		[new Engine('1-1', -1), new Wagon(1, '1-4'), new Wagon(2, '1-3'), new Wagon(3, '1-2')],
	),
	new Problem(6, // 17
		[new Engine('7-1', -1), new Wagon(1, '1-8'), new Wagon(2, '4-2'), new Wagon(3, 'rb-3')],
		[new Engine('5-1', -1), new Wagon(1, '2'), new Wagon(2, '4-1'), new Wagon(3, '5-2')],
	),
	new Problem(7, // 18
		[new Engine('4-2', -1), new Wagon(1, '1-6'), new Wagon(2, '5-3'), new Wagon(3, '5-4')],
		[new Engine('5-1', -1), new Wagon(1, '4-1'), new Wagon(2, 'lb-2'), new Wagon(3, '5-2')],
	),
];
