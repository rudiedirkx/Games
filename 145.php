<?php
// LINX

define( 'n', null );
define( 'o', 'orange' );
define( 'y', 'yellow' );
define( 'g', 'green' );
define( 'r', 'red' );
define( 'b', 'black' );
define( 'w', 'white' );
define( 'x', false );

$g_arrBoards = array(
	1 => array(
		array(  ),
		array( n, n, n, n, n, n, b ),
		array( n, n, n, n, n, w, n, r ),
		array( n, n, n, n, n, n, o ),
		array( n, n, o, n, n, n, n, n, n, n, o ),
		array( n, n, n, n, n, n, o ),
		array(  ),
		array( n, n, n, n, n, r, x, w ),
		array(  ),
		array( n, n, n, n, n, n, b ),
	),
);


$iBoard = isset($_GET['board'], $g_arrBoards[$_GET['board']]) ? $_GET['board'] : key($g_arrBoards);
$arrBoard = $g_arrBoards[$iBoard];

?>
<html>

<head> 
<title>Linx</title>
<script type="text/javascript">
<!--//
(new Image()).src = '/images/145_out.bmp';
(new Image()).src = '/images/145_over.bmp';

document.oncontextmenu = function(e) {
	e = e || window.event || document.event;
	if ( e.preventBubble ) {
		e.preventBubble();
	}
	if ( e.stopPropagation ) {
		e.stopPropagation();
	}
	t = e.srcElement || e.target;
	if ( t.className == 'linxpad' ) {
		alert('WHIIIII: <'+t.nodeName+'/>');
	}
	return false;
}
//-->
</script>
<style type="text/css">
#linx td {
	padding				: 0;
}
#linx td a {
	display				: block;
	width				: 35px;
	height				: 35px;
	text-decoration		: none;
	border				: solid 0px blue;
	border-color		: blue black black blue;
	background-color	: midnightblue;
	background-image	: url(images/145_out.bmp);
	cursor				: default;
}
#linx td a:hover {
	border-color		: white;
	background-image	: url(images/145_over.bmp);
}
</style>
</head>

<body>
<table cellspacing="0"><tbody id="linx"><?php
for ( $i=0; $i<12; $i++ )
{
	echo '<tr>';
	for ( $j=0; $j<14; $j++ )
	{
		$szBgColor = isset($arrBoard[$i][$j]) && is_string($arrBoard[$i][$j]) ? $arrBoard[$i][$j] : '';
		echo '<td><a'.( $szBgColor ? ' style="background-image:url();background-color:'.$szBgColor.';"' : '' ).' class="linxpad" href="#'.$j.'_'.$i.'" onclick="return false;"></a></td>';
	}
	echo '</tr>';
}
?></tbody></table>
</body>

</html>