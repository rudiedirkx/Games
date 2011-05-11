<?php
// Entangled: http://entanglement.gopherwoodstudios.com/

?>
<!DOCTYPE html>
<html>

<head>
<title></title>
<style>
* { margin:0; padding:0; }
#board { height:567px; padding:0 12px; float:left; position:relative; background-color:#eee; }
#board > div { float:left; }
#board > div > div { z-index:2; opacity:0.75; width:94px; height:81px; margin:0 -11px; background:url(images/163_tile_1.png) no-repeat center center; -webkit-transform:rotate(0deg); -webkit-transition:all 200ms ease-out; -moz-transform:rotate(0deg); -moz-transition:all 200ms ease-out; }
#board > div > div.center { z-index:1; opacity:1; background-image:url(images/163_tile_center.png); }
#board > div > div:hover { opacity:1; }
#board > div.c2 > div, #board > div.c6 > div { position:relative; top:80px; }
#board > div.c3 > div, #board > div.c5 > div { position:relative; top:40px; }
#board > div.c1 > div, #board > div.c7 > div { position:relative; top:120px; }
</style>
<script src="/js/mootools_1_11.js"></script>
</head>

<body>

<div id=board></div>

<script>
var tiles = [
	[
		[1, 5],
		[2, 3],
		[4, 8],
		[6, 10],
		[7, 11],
		[9, 12]
	]
];
Function.extend({'repeat': function(n){ var fn=this; for(var i=0; i<n; i++){ fn(i); } }});
Element.extend({
	'rotate': function(way) {
		if ( this.getTag() != 'div' || !this.hasClass('tile') ) return;
		var rotation = parseInt(this.attr('rotation')) || 0;
//console.log(rotation);
//		this.removeClass('rotation-'+rotation);
		rotation += way;
//		if ( rotation > 5 ) rotation -= 6;
//		else if ( rotation < 0 ) rotation += 6;
//console.log(rotation);
//		this.addClass('rotation-'+rotation);
		this.css('-webkit-transform', 'rotate(' + (60*rotation) + 'deg)')
			.css('-moz-transform', 'rotate(' + (60*rotation) + 'deg)');
		this.attr('rotation', rotation);
	}
});
function emptyBoard(fill) {
	fill = $pick(fill, true);
	var b = $('board');
	b.empty();
	(function(i) {
		var c = new Element('div', {'class': 'col c'+(i+1)}).inject(b);
		var r = i == 3 ? 7 : ( i < 3 ? 4+i : 10-i );
		(function(j) {
			new Element('div', {'class': 'tile r'+(j+1)}).inject(c);
		}).repeat(r);
	}).repeat(7);
	$$$('#board > div.c4 > div.r4').removeClass('tile').addClass('center');
	return b;
}
$(function() {
	emptyBoard();
	$('board').addEvent('mousewheel', function(e) {
console.log(e);
		e = new Event(e);
		if ( e.target.hasClass('tile') ) {
			e.stop();
			var tile = e.target;
			tile.rotate(e.wheel);
		}
	});
});
</script>

</body>

</html>