"use strict";

const Coord = Coords3D;

Coord.dir6Coords = [
	new Coord(-1, -1,  0),
	new Coord(+1, +1,  0),
	new Coord( 0, -1, -1),
	new Coord( 0, +1, +1),
	new Coord(-1,  0, +1),
	new Coord(+1,  0, -1),
];

Coord.prototype.reverse = function() {
	return new Coord(this.x * -1, this.y * -1, this.z * -1);
};
Coord.prototype.underscore = function() {
	return this.x + '_' + this.y + '_' + this.z;
};
Coord.fromUnderscore = function(coord) {
	const C = coord.split('_');
	return new Coord(parseInt(C[0]), parseInt(C[1]), parseInt(C[2]))
};

class AbaloneSelection {
	constructor(coords) {
		this.coords = coords;
	}

	get length() {
		return this.coords.length;
	}

	get head() {
		return this.coords[0];
	}

	get tail() {
		return this.coords[this.coords.length - 1];
	}

	get axis() {
		if (this.coords.length < 2) return null;

		const el1 = this.coords[0];
		const el2 = this.coords[1];
		if (el1.x == el2.x) {
			return 'x';
		}
		else if (el1.y == el2.y) {
			return 'y';
		}
		else if (el1.z == el2.z) {
			return 'z';
		}

		return null;
	}

	add(C) {
		this.coords.push(C);
	}

	remove(C) {
		this.coords = this.coords.filter(el => !el.equal(C));
	}

	isInline() {
		if (this.coords.length < 3) return null;

		const axis = this.axis;
		const el1 = this.coords[0];
		for ( let i = 1; i < this.coords.length; i++ ) {
			const el = this.coords[i];
			if (!['x', 'y', 'z'].every(ax =>  {
				return axis == ax ? (el1[ax] == el[ax]) : (el1[ax] != el[ax]);
			})) {
				return false;
			}
		}

		return true;
	}

	isAdjacent() {
		if (this.coords.length < 2) return null;

		return this.coords.every(C => {
			return this.coords.some(el => {
				return C != el && Coord.dir6Coords.some(D => el.add(D).equal(C));
			});
		});
	}

	prepDir(dir) {
		if (this.length < 2) return {inline: false};
		if (dir[this.axis] != 0) return {inline: false};

		const rev = dir.reverse();
		let cur = this.getHead(dir);
		const els = [cur];
		for ( let i = 1; i < this.coords.length; i++ ) {
			cur = cur.add(rev);
			els.push(cur);
		}

		this.coords = els;
		return {inline: true};
	}

	getHead(dir) {
		let head = this.coords[0];
		for ( let i = 0; i < this.coords.length; i++ ) {
			const next = head.add(dir);
			if (!this.coords.some(el => el.equal(next))) {
				return head;
			}
			head = next;
		}
	}
}

class Abalone extends GridGame {

	static STATUS_REQUEST_MS = 1500;
	static OFFLINE_AFTER = 120;
	static INACTIVE_AFTER = 20;

	static SELECT_MAX_BALLS = 4;

	static oppositeColor( color ) {
		return 'white' == color ? 'black' : 'white';
	}

	reset() {
		this.unselectBallsAfter = 0;
		this.removeBallAfter = 0;

		this.playerColor = null;
		this.opponentColor = null;

		this.previousState = null;
		this.lastMove = null;
		this.turnColor = null;
	}

	startGame(playerColor, state) {
		const time = 500;

		this.playerColor = playerColor;
		this.opponentColor = Abalone.oppositeColor(this.playerColor);

		document.body.data('player', this.playerColor).addClass('ready');
		document.body.style.setProperty('--ball-move-time', time);

		this.unselectBallsAfter = 600;
		this.removeBallAfter = 1000;

		this.populateBoard(state);

		this.tickStatus();
	}

	replayFrom(state, moves) {
		const time = 200;
		document.body.style.setProperty('--ball-move-time', time);

		this.removeBallAfter = 700;

		this.populateBoard(state);

		const nextMove = () => {
			const move = moves.shift();
			this.playMove(move.balls, move.direction);
			if (moves.length) {
				setTimeout(nextMove, 2 * time);
			}
		};
		setTimeout(nextMove, time);
	}

	createStats() {}

	listenControls() {
		this.m_objGrid.on('click', `a.ball[data-color="${this.playerColor}"]`, e => {
			e.preventDefault();

			this.clickBall(Coord.fromUnderscore(e.target.dataset.coord));
		});
		this.m_objGrid.on('click', 'a.direction', e => {
			e.preventDefault();

			const C = Coord.fromUnderscore(e.target.dataset.dir);
			this.clickDirection(C);
		});

		$('#replay-last-move').on('click', e => {
			this.playLastMove();
		});
	}

	tickStatus() {
		this.updateStatus();

		setInterval(() => {
			if (!document.hidden) {
				this.updateStatus();
			}
		}, Abalone.STATUS_REQUEST_MS);
	}

	updateStatus() {
		$.get(location.search + '&status=1').on('done', (e, rv) => {
			if (this.turnColor && rv.status.turn != this.turnColor && rv.status.turn == this.playerColor) {
				this.lastMove = rv.lastMove;
				this.saveCurrentState();
				this.playMove(rv.lastMove.balls, rv.lastMove.direction);
			}

			this.setTurn(rv.status.turn);

			const tr = $('tr.other');
			tr.cells[1].title = `Online: ${rv.status.opponentOnline} sec ago`;
			tr.data('status', this.getOnlineStatus(rv.status.opponentOnline));

			$('#player-balls-left').setText(rv.status.playerBalls);
			$('#opponent-balls-left').setText(rv.status.opponentBalls);
		});
	}

	getOnlineStatus( ago ) {
		if (ago > Abalone.OFFLINE_AFTER) {
			return 'offline';
		}
		if (ago > Abalone.INACTIVE_AFTER) {
			return 'inactive';
		}
		if (ago == -1) {
			return 'pending';
		}
		return 'active';
	}

	createIndex() {
		const index = {};
		this.m_objGrid.getElements('.ball').forEach(el => {
			index[el.dataset.coord] = el.dataset.color;
		});

		return index;
	}

	populateBoard(balls) {
		this.m_objGrid.getElements('.ball').forEach(el => el.remove());

		$.each(balls, (color, coord) => {
			const tag = color == this.playerColor ? 'a' : 'span';
			const el = document.el(tag, {href: '#'}).addClass('ball').data('color', color).data('coord', coord).attr('title', coord);
			this.m_objGrid.append(el);
		});
	}

	setTurn(color) {
		this.turnColor = color;
		const mine = this.playerColor == this.turnColor;
		document.body.data('turn', this.turnColor).toggleClass('my-turn', mine).toggleClass('their-turn', !mine);
	}

	getCurrentState() {
		return this.createIndex();
	}

	saveCurrentState() {
		this.previousState = this.getCurrentState();
	}

	restoreState(state) {
		this.populateBoard(state);
	}

	hole(C) {
		return this.m_objGrid.getElement(`.hole[data-coord="${C.underscore()}"]`);
	}

	ball(C) {
		return this.m_objGrid.getElement(`.ball[data-coord="${C.underscore()}"]`);
	}

	inside(C) {
		return this.hole(C) != null;
	}

	coord(ball) {
		return Coord.fromUnderscore(ball.dataset.coord);
	}

	color(C) {
		const ball = this.ball(C);
		return ball ? ball.dataset.color : '';
	}

	clickBall(C) {
		if ( this.playerColor != this.turnColor ) return console.warn('not your turn');

		if ( this.playerColor !== this.color(C) ) return console.warn('not your ball');

		this.toggleSelectBall(C);
	}

	toggleSelectBall(C) {
		const ball = this.ball(C);
		ball.hasClass('selected') ? this.unselectBall(C) : this.selectBall(C);
	}

	unselectBall(C) {
		const selection = this.getSelection();
		selection.remove(C);

		if (selection.isAdjacent() === false) return console.warn('not adjacent');

		const ball = this.ball(C);
		ball.removeClass('selected');
	}

	selectBall(C) {
		const selection = this.getSelection();
		if ( selection.length >= Abalone.SELECT_MAX_BALLS ) return;

		selection.add(C);

		if (selection.isInline() === false) return console.warn('not inline');
		if (selection.isAdjacent() === false) return console.warn('not adjacent');

		this.ball(C).addClass('selected');
	}

	getSelection() {
		const balls = this.m_objGrid.getElements('.ball.selected');
		const coords = balls.map(ball => this.coord(ball));
		const selection = new AbaloneSelection(coords);
		return selection;
	}

	clickDirection(dir) {
		const selection = this.getSelection();
		const {inline} = selection.prepDir(dir);
		if (inline) {
			this.moveInline(selection, dir);
		}
		else {
			this.moveSideways(selection, dir);
		}
	}

	moveInline(selection, dir) {
		const aheads = this.getAheads(selection.head, dir);
		const takens = this.untilFree(aheads);
		if (!this.inside(selection.head.add(dir))) return console.warn('head outside the board');
		if (takens.length >= selection.length) return console.warn('too many takens');
		if (takens.some(C => this.color(C) == this.playerColor)) return console.warn('own balls in takens');

		const changers = [...selection.coords, ...takens];
		const head = takens[takens.length - 1] || selection.head;
		const infront = head.add(dir);
		if (this.inside(infront)) {
			changers.push(infront);
		}

		this.unselectAllBallsAsync();
		this.saveCurrentState();
		this.playMoveInline(selection, dir);
		this.sendMove(selection, dir, this.getChanges(changers));
	}

	playMoveInline(selection, dir) {
		const aheads = this.getAheads(selection.head, dir);
		const takens = this.untilFree(aheads);
		const movers = [...selection.coords, ...takens];

		const head = takens[takens.length - 1] || selection.head;
		const infront = head.add(dir);
		if (!this.inside(infront)) {
			const posHead = this.getPosition(head);
			const posDiff = posHead.subtract(this.getPosition(head.add(dir.reverse())));
			const ball = this.ball(head);
			ball.css(posHead.add(posDiff).toCSS());
			setTimeout(() => ball.remove(), this.removeBallAfter);
		}

		this.moveBalls(movers, dir);
	}

	moveSideways(selection, dir) {
		const targets = selection.coords.map(C => C.add(dir));
		if (targets.some(C => !this.inside(C))) return console.warn('target outside the board');
		if (targets.some(C => this.color(C))) return console.warn('balls in the way');

		this.unselectAllBallsAsync();
		this.saveCurrentState();
		this.moveBalls(selection.coords, dir);
		this.sendMove(selection, dir, this.getChanges([...selection.coords, ...targets]));
	}

	playMoveSideways(selection, dir) {
		this.moveBalls(selection.coords, dir);
	}

	moveBalls(coords, dir) {
		const balls = coords.map(C => this.ball(C));
		coords.forEach((C, i) => {
			const coord = C.add(dir).underscore();
			balls[i].data('coord', coord).attr('title', coord);
		});
	}

	getChanges(coords) {
		const state = {};
		coords.forEach(C => {
			if (this.inside(C)) {
				state[C.underscore()] = this.color(C);
			}
		});
		return state;
	}

	getAheads(from, dir) {
		const aheads = [];
		var cur = from;
		while (this.inside(cur = cur.add(dir))) {
			aheads.push(cur);
		}

		return aheads;
	}

	untilFree(aheads) {
		for ( let i = 0; i < aheads.length; i++ ) {
			const color = this.color(aheads[i]);
			if (!color) {
				return aheads.slice(0, i);
			}
		}

		return aheads;
	}

	getPosition(C) {
		const style = getComputedStyle(this.hole(C));
		return new Coords2D(parseFloat(style.left), parseFloat(style.top));
	}

	unselectAllBallsAsync() {
		setTimeout(() => this.unselectAllBalls(), this.unselectBallsAfter);
	}

	unselectAllBalls() {
		this.m_objGrid.getElements('.ball.selected').removeClass('selected');
	}

	sendMove(selection, dir, changes) {
		const move = {
			balls: selection.coords.map(C => C.underscore()),
			direction: dir.underscore(),
		};
		const data = {changes, move};
console.log('sendMove', data);
		this.lastMove = move;
		$.post(location.search, r.serialize(data)).on('done', (e, rv) => {
			// console.log('rv', rv);
		});
	}

	playLastMove() {
		if (!this.previousState || !this.lastMove) return;

		this.restoreState(this.previousState);
		setTimeout(() => this.playMove(this.lastMove.balls, this.lastMove.direction), 500);
	}

	playMove(coords, dir) {
		coords = coords.map(coord => Coords3D.fromUnderscore(coord));
		dir = Coords3D.fromUnderscore(dir);

		const selection = new AbaloneSelection(coords);

		const {inline} = selection.prepDir(dir);
		if (inline) {
			this.playMoveInline(selection, dir);
		}
		else {
			this.playMoveSideways(selection, dir);
		}
	}

}
