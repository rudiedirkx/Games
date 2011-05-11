<?php
// Maze

$iUtcStart = microtime(true);

$dim_x = 40;
$dim_y = 20;
$cell_count = $dim_x*$dim_y;
$moves = array();
// MAZE CREATION
for($x=0;$x<$cell_count;$x++){
		$maze[$x] = "01111"; // visted, NSEW
}
$start = $pos = rand(0,$cell_count-1);
//$pos = 0;

$html = '';
//$html = "My start position is randomly set at $pos<br>";

$maze[$pos]{0} = 1;
$visited = 1;

// determine possible directions
while($visited<$cell_count){
	$possible = "";
	if ( floor($pos/$dim_x) == floor(($pos-1)/$dim_x) && $maze[$pos-1]{0} == '0' ) {
		$possible .= "W";
	}
	if ( (floor($pos/$dim_x)==floor(($pos+1)/$dim_x)) && $maze[$pos+1]{0} == '0' ) {
		$possible .= "E";
	}
	if ( (($pos+$dim_x)<$cell_count) && $maze[$pos+$dim_x]{0} == '0' ) {
		$possible .= "S";
	}
	if ( (($pos-$dim_x)>=0) && $maze[$pos-$dim_x]{0} == '0' ) {
		$possible .= "N";
	}
//	$html .= "I am in $pos and I can go to: $possible<br>";
	if($possible){
		$visited ++;
		array_push($moves,$pos);
		$direction = $possible{rand(0,strlen($possible)-1)};
//		$html .= "I randomly choose to go $direction";
		switch($direction){
			case "N":
				$maze[$pos]{1} = 0;
				$maze[$pos-$dim_x]{2} = 0;
				$pos -= $dim_x;
				break;
			case "S":
				$maze[$pos]{2} = 0;
				$maze[$pos+$dim_x]{1} = 0;
				$pos += $dim_x;
				break;
			case "E":
				$maze[$pos]{3} = 0;
				$maze[$pos+1]{4} = 0;
				$pos ++;
				break;
			case "W":
				$maze[$pos]{4} = 0;
				$maze[$pos-1]{3} = 0;
				$pos --;
				break;
		}
		$maze[$pos]{0} = 1;
	}
	else{
//		$html .= "No possible moves, I have to perform a backtracking<br>";
		$pos = array_pop($moves);
	}
/*	$html .= "<table style = \"border:2px solid black\"; cellspacing = \"0\" cellpadding = \"0\">";
	for($x=0;$x<$cell_count;$x++){
		if($x % $dim_x == 0){
			$html .= "<tr>";
		}
		$style = $maze[$x]{2}.$maze[$x]{3};
		if($x!=$pos){
			$html.= "<td title=\"".$maze[$x]."\" class=\"d".$maze[$x]{0}." w$style\">$x</td>";
		}
		else{
			$html .= "<td title=\"".$maze[$x]."\" class=\"d".$maze[$x]{0}." w$style\"><strong>$x</strong></td>";
		}
		if(($x % $dim_x) == ($dim_x-1)){
			$html .= "</tr>";
		}
	}
	$html .= "</table>";*/
}

//$html.= "Hooray, that's the final maze";

?>
<html>
<head>
<style>
td{text-align:center;width:30px;height:30px;border:0px;}
.w01{border-right:2px solid black;}
.w10{border-bottom:2px solid black;}
.w11{border-bottom:2px solid black;border-right:2px solid black;}
strong{color:red;}
.d0 { background-color:#eee; }
.pos { font-weight:bold;color:red; }
.strt { font-weight:bold;color:blue; }
</style>
</head>
<body>
	<?php printMaze(); ?>
</body>
</html>
<?php

echo '<pre>'.number_format(microtime(true)-$iUtcStart, 4).' ('.$start.' -> '.$pos.')</pre>';

?>
<script type="text/javascript">
<!--//
var iStart = <?php echo $start; ?>, iPosition = <?php echo $pos; ?>, x = <?php echo $dim_x; ?>;
document.getElementById('maze').rows[Math.floor(iStart/x)].cells[iStart%x].className += ' strt';
document.getElementById('maze').rows[Math.floor(iPosition/x)].cells[iPosition%x].className += ' pos';
document.getElementById('maze').onclick = function(e) {
	e = e || window.event || this.event;
	if ( 'TD' == e.target.nodeName ) {
		e.target.style.backgroundColor = 'green';
	}
}
//-->
</script>
<?php

function printMaze() {
	global $cell_count, $pos, $dim_x, $dim_y, $maze;
	echo '<table id="maze" width="100%" height="100%" style="border:2px solid #000;border-width:2px 0 0 2px;" cellspacing="0" cellpadding="0">';
	for($x=0;$x<$cell_count;$x++){
		if($x % $dim_x == 0){
			echo "<tr>";
		}
		$style = $maze[$x]{2}.$maze[$x]{3};
		echo "<td class=\"d".$maze[$x]{0}." w$style\">$x</td>";
		if(($x % $dim_x) == ($dim_x-1)){
			echo "</tr>";
		}
	}
	echo "</table>";
}

?>