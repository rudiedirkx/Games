Pythagorea.levels.push(new PythagoreaLevel('Connect all the given nodes with each other.', function(game) {
	const vertices = [this.vertex(2, 2), this.vertex(4, 2), this.vertex(3, 4)];
	vertices.forEach((V) => game.addVertex(V));
	this.winner = this.createVerticesEdges(vertices);
}, function(game) {
	return this.allEdgesExist(game, this.winner);
}, function(game) {
	this.drawEdges(game, this.winner);
}));

Pythagorea.levels.push(new PythagoreaLevel('Construct the point at equal distance from all given nodes.', function(game) {
	const vertices = [this.vertex(2, 0), this.vertex(0, 2), this.vertex(4, 2)];
	vertices.forEach((V) => game.addVertex(V));
	this.winner = [this.vertex(2, 2)];
}, function(game) {
	return this.allVerticesExist(game, this.winner);
}, function(game) {
	this.drawVertices(game, this.winner);
}));

Pythagorea.levels.push(new PythagoreaLevel('Construct the halfway point between the given nodes.', function(game) {
	game.addVertex(this.vertex(1, 2));
	game.addVertex(this.vertex(3, 1));
	this.winner = this.vertex(2, 1.5);
}, function(game) {
	return this.allVerticesExist(game, [this.winner]);
}, function(game) {
	this.drawVertices(game, [this.winner]);
}));

Pythagorea.levels.push(new PythagoreaLevel('Create a square with the given side.', function(game) {
	game.addEdge(this.edge(this.vertex(3, 0), this.vertex(3, 2)));
}, function(game) {
	return 0 ||
		this.allEdgesExist(game, this.winner = this.createVerticesEdges([this.vertex(3, 0), this.vertex(1, 0), this.vertex(1, 2), this.vertex(3, 2)])) ||
		this.allEdgesExist(game, this.winner = this.createVerticesEdges([this.vertex(3, 0), this.vertex(5, 0), this.vertex(5, 2), this.vertex(3, 2)]))
	;
}, function(game) {
	this.drawEdges(game, this.winner);
}));

Pythagorea.levels.push(new PythagoreaLevel('Create all the possible squares from the given nodes.', function(game) {
	game.addVertex(this.vertex(3, 2));
	game.addVertex(this.vertex(2, 4));

	this.winner = [
		this.createVerticesEdges([this.vertex(3, 2), this.vertex(1, 1), this.vertex(0, 3), this.vertex(2, 4)]),
		this.createVerticesEdges([this.vertex(3, 2), this.vertex(5, 3), this.vertex(4, 5), this.vertex(2, 4)]),
		this.createVerticesEdges([this.vertex(3, 2), this.vertex(3.5, 3.5), this.vertex(2, 4), this.vertex(1.5, 2.5)]),
	];
}, function(game) {
	return !this.winner.some((Es) => !this.allEdgesExist(game, Es));
}, function(game) {
	this.winner.forEach((Es) => this.drawEdges(game, Es));
}));
