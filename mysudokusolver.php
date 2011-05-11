<?php

require_once 'inc.cls.statesolver.php';

$arr = array(
	array(1,8),
	array(7,4),
);
exit(implode(',', statesolver::array_join($arr)));

class CSS {

	public $iX = 3;
	public $iY = 3;
	public $map = array();

	public function __construct($map) {
		$szDefault = implode('', range(1, $this->iX*$this->iY));
		$this->map = array_fill(0, $this->iX*$this->iY, array_fill(0, $this->iX*$this->iY, $szDefault));
		foreach ( $map AS $x => $c ) {
			foreach ( $c AS $y => $v ) {
				$this->map[$x][$y] = (int)$v;
				$this->updateByField($x, $y);
			}
		}
		$this->evalSubs();
		$this->evalHors();
		$this->evalVerts();
	}

	public function possiblesInField( $x, $y ) {
		return strlen($this->map[$x][$y]);
	}

	public function checkForNewSures() {
		foreach ( $this->map AS $x => $col ) {
			foreach ( $col AS $y => $cell ) {
				if ( 1 == strlen($cell) ) {
					$this->map[$x][$y] = (int)$cell;
				}
			}
		}
	}

	public function updateByField( $x, $y ) {
		if ( !is_int($this->map[$x][$y]) ) {
			return;
		}
		$v = $this->map[$x][$y]-1;
		// Horizontally
		for ( $i=0; $i<$this->iX*$this->iY; $i++ ) {
			if ( is_string($this->map[$i][$y]) ) {
				$this->map[$i][$y] = substr_replace($this->map[$i][$y], '', $v-1, 1);
			}
		}
		// Vertically
		for ( $i=0; $i<$this->iX*$this->iY; $i++ ) {
			if ( is_string($this->map[$x][$i]) ) {
				$this->map[$x][$i] = substr_replace($this->map[$x][$i], '', $v-1, 1);
			}
		}
		// Subly
		$st = $this->getSubStartByField($x, $y);
		for ( $i=$st[0]; $i<$st[0]+$this->iX; $i++ ) {
			for ( $j=$st[1]; $j<$st[1]+$this->iY; $j++ ) {
				if ( is_string($this->map[$i][$j]) ) {
					$this->map[$i][$j] = substr_replace($this->map[$i][$j], '', $v-1, 1);
				}
			}
		}
	}

	public function getSubStartByField( $x, $y ) {
		$x = floor( $x / $this->iX ) * $this->iX;
		$y = floor( $y / $this->iY ) * $this->iY;
		return array($x, $y);
	}

	public function evalNumberInSub( $x, $y, $n ) {
		list($x, $y) = $this->getSubStartByField( $x, $y );
echo '['.$x.':'.$y.' ('.$n.')]';
		
	}

	public function evalSubs() {
		for ( $i=0; $i<$this->iX*$this->iY; $i+=$this->iX ) {
			for ( $j=0; $j<$this->iX*$this->iY; $j+=$this->iY ) {
				for ( $a=0; $a<$this->iX*$this->iY; $a++ ) {
					$this->evalNumberInSub($i, $j, $a);
				}
			}
		}
	}

	public function evalHors() {
		
	}

	public function evalVerts() {
		
	}

	public function __tostring() {
		$szHtml = '<table border="0" cellpadding="0" cellspacing="0">'."\n";
		for ( $y=0; $y<$this->iX*$this->iY; $y++ ) {
			$szHtml .= "\t".'<tr'.( 0 == ($y+1)%$this->iY || 0 == $y ? ' class="b'.( 0 == $y ? 't' : 'b' ).'"' : '' ).'>'."\n";
			for ( $x=0; $x<$this->iX*$this->iY; $x++ ) {
				$szHtml .= "\t\t".'<th'.( 0 == ($x+1)%$this->iX || 0 == $x ? ' class="b'.( 0 == $x ? 'l' : 'r' ).'"' : '' ).'>'.( is_int($this->map[$x][$y]) ? $this->map[$x][$y] : '<br />' ).'</th>'."\n";
			}
			$szHtml .= "\t".'</tr>'."\n";
		}
		$szHtml .= '</table>'."\n";
		return $szHtml;
	}

}

class SCSS extends CSS {
	public $iY = 2;
}

// normal
$s = unserialize('a:9:{i:0;a:2:{i:1;i:7;i:5;i:2;}i:2;a:2:{i:1;i:9;i:6;i:5;}i:1;a:4:{i:2;i:4;i:3;i:5;i:5;i:1;i:8;i:8;}i:4;a:2:{i:0;i:5;i:8;i:6;}i:5;a:2:{i:1;i:1;i:3;i:3;}i:7;a:4:{i:0;i:6;i:3;i:9;i:5;i:3;i:6;i:7;}i:6;a:2:{i:2;i:8;i:7;i:4;}i:3;a:2:{i:5;i:7;i:7;i:2;}i:8;a:2:{i:3;i:1;i:7;i:3;}}');
$ss = new CSS($s);

// small
#$s = unserialize('a:6:{i:0;a:3:{i:0;i:2;i:5;i:1;i:2;i:4;}i:1;a:3:{i:1;i:4;i:4;i:2;i:3;i:1;}i:2;a:2:{i:2;i:2;i:3;i:3;}i:3;a:2:{i:3;i:2;i:2;i:3;}i:4;a:3:{i:4;i:4;i:1;i:3;i:2;i:1;}i:5;a:3:{i:5;i:3;i:0;i:5;i:3;i:4;}}');
#$ss = new SCSS($s);

?>
<style type="text/css">
body {
	opacity : 0.15;
}
table {
	border-collapse : collapse;
}
th {
	border : solid 1px black;
	width : 30px;
	height : 30px;
}
tr.bb th {
	border-bottom-width : 3px;
}
tr.bt th {
	border-top-width : 3px;
}
th.br {
	border-right-width : 3px;
}
th.bl {
	border-left-width : 3px;
}
</style>
<pre>
<?php

echo (string)$ss;

print_r($ss);

?>