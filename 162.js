
var width = 8, height = 6, types = 8;
var colors = function(){ return level+2 };

Function.extend({
	repeat: function(n, args) {
		args = args || [];
		args.push(0);
		for ( var i=0; i<n; i++ ) {
			args[args.length-1] = i;
			this.apply(null, args);
		}
		return this;
	}
});
Element.extend({
	isBlock: function() {
		return this.hasClass('block');
	}
});

function fillFrame() {
	$('frame').empty();
	(function(n) {
		var col = new Element('div', {'class': 'column', 'column': ''+(n+1)+''});
		(function(col, n) {
			col.append( (new Element('div', {'t': $random(1, colors()), 'class': 'block', 'block': ''+(n+1)+''})).update('&nbsp;') );
		}).repeat(height, [col]);
		col.inject($('frame'));
	}).repeat(width);
	$('frame').append(new Element('div', {'class': 'clear'}));
}
function filterBlocksByType(blocks, type) {
	return $$(blocks).filter(function(div){ return div.attr('t') == type; });
}
function getNeighbours(source, neighbours) {
	neighbours = neighbours || [];
	var iBlock = parseInt(source.attr('block')), iColumn = parseInt(source.parent().attr('column'));
	var nn = filterBlocksByType($$(Sizzle('div[column='+(iColumn)+'] [block='+(iBlock+1)+'], div[column='+(iColumn)+'] div[block='+(iBlock-1)+'], div[column='+(iColumn+1)+'] div[block='+(iBlock)+'], div[column='+(iColumn-1)+'] div[block='+(iBlock)+']')), source.attr('t'));
	var un = [];
	for ( var i=0; i<nn.length; i++ ) {
		if ( !neighbours.contains(nn[i]) ) {
			un.push(nn[i]);
			neighbours.push(nn[i]);
			getNeighbours(nn[i], neighbours);
		}
	}
	return un;
}
function frameClicked(e) {
	e = new Event(e);
	if ( e.target.isBlock() ) {
		return blockClicked(e.target);
	}
}
function blockClicked(block) {
	var neighbours = [];
	getNeighbours(block, neighbours);
	if ( 2 <= neighbours.length ) {
		for ( var i=0; i<neighbours.length; i++ ) {
			neighbours[i].remove();
		}
		var ec = Sizzle('.column:empty'), i = ec.length;
		while ( i-- ) ec[i].remove();
		if ( 1 == $('frame').childNodes.length ) {
			alert('Congratz');
			document.location.reload();
//			document.location = '?level=' + (level+1);
			return true;
		}
		resetNumbers();
	}
	return false;
}
function resetNumbers() {
	$$('#frame .column').each(function(column, n) {
		column.attr('column', ''+(n+1));
		column.select('.block').each(function(block, n) {
			block.attr('block', ''+(n+1));
		});
	});
}

Document.addEvent('mousedown', function(e){
	new Event(e).stop();
	return false;
});
Window.addEvent('domready', function() {
	$('frame').addEvent('click', frameClicked);
	fillFrame();
});
