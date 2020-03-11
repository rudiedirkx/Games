window.level = 1;

const width = 8;
const height = 6;
var winChecker;

const colors = () => window.level + 1;

function randomBlock() {
	return 1 + parseInt(Math.random() * colors());
}

function fillFrame() {
	const $frame = $('#frame').empty();

	for ( let x = 0; x < width; x++ ) {
		const col = document.el('div', {"class": 'column'}).data('x', x).inject($frame);
		for ( let y = 0; y < height; y++ ) {
			col.append(document.el('div', {"class": 'block'}).data('y', y).data('t', randomBlock()));
		}
	}
}

function filterBlocksByType(blocks, type) {
	return $$(blocks).filter(function(div){ return div.data('t') == type; });
}

function extendNeighbours(source, neighbours) {
	if (neighbours.includes(source)) return neighbours;
	neighbours.push(source);

	const type = source.data('t');
	const x = 1 + source.parentNode.elementIndex();
	const y = 1 + source.elementIndex();

	const neighbourBlocks = $$(([
		`.column:nth-child(${x}) > [data-t="${type}"]:nth-child(${y-1})`,
		`.column:nth-child(${x}) > [data-t="${type}"]:nth-child(${y+1})`,
		`.column:nth-child(${x-1}) > [data-t="${type}"]:nth-child(${y})`,
		`.column:nth-child(${x+1}) > [data-t="${type}"]:nth-child(${y})`,
	]).join(', '));
	neighbourBlocks.css('background-color', 'black');

	neighbourBlocks.forEach(block => extendNeighbours(block, neighbours));
	return neighbours;
}

function blockClicked() {
	var neighbours = [];
	extendNeighbours(this, neighbours);

	if (neighbours.length < 2) return;

	neighbours.invoke('remove');
	$$('.column:empty').invoke('remove');

	clearTimeout(winChecker);
	winChecker = setTimeout(checkWin, 60);
}

function checkWin() {
	if ($('#frame').childElementCount == 0) {
		alert('You win!');
	}
}
