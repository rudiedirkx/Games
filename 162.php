<!DOCTYPE>
<html manifest="162.manifest">
<head>
<script src="/js/mootools_1_11.js"></script>
<style>
* { padding:0; margin:0; }
#frame { clear:both; /*position:absolute; left:0; bottom:0;*/ }
#frame div.column { float:left; position:relative; /*height:600px;*/ width:100px; }
#frame div.block { outline:solid 1px #555; width:100px; height:100px; /*position:absolute; left:0;*/ cursor:pointer; }
/*#frame div.column div.block:nth-child(1) { bottom:0; }
#frame div.column div.block:nth-child(2) { bottom:100px; }
#frame div.column div.block:nth-child(3) { bottom:200px; }
#frame div.column div.block:nth-child(4) { bottom:300px; }
#frame div.column div.block:nth-child(5) { bottom:400px; }
#frame div.column div.block:nth-child(6) { bottom:500px; }*/
div[t="1"] { background-color:red; background-image:-webkit-gradient(linear, 70% 5%, 20% 100%, from(#f00), to(#500)); }
div[t="2"] { background-color:green; background-image:-webkit-gradient(linear, 70% 5%, 20% 100%, from(#0f0), to(#050)); }
div[t="3"] { background-color:blue; background-image:-webkit-gradient(linear, 70% 5%, 20% 100%, from(#00f), to(#005)); }
div[t="4"] { background-color:money; background-image:-webkit-gradient(linear, 70% 5%, 20% 100%, from(#ff0), to(#550)); }
div[t="5"] { background-image:-webkit-gradient(linear, 70% 5%, 20% 100%, from(#0ff), to(#055)); }
div[t="6"] { background-image:-webkit-gradient(linear, 70% 5%, 20% 100%, from(#f0f), to(#505)); }
div[t="7"] { background-image:-webkit-gradient(linear, 70% 5%, 20% 100%, from(#555), to(#000)); }
div[t="8"] { background-image:-webkit-gradient(linear, 70% 5%, 20% 100%, from(#fff), to(#555)); }
.clear { clear:both; }
</style>
<script>
var width = 8, height = 6, types = 8;
var level = <?php echo isset($_GET['level']) ? (int)$_GET['level'] : 1; ?>, colors = function(){ return level+2 };

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
</script>
</head>

<body>
<div id="frame"></div>
</body>

</html>
