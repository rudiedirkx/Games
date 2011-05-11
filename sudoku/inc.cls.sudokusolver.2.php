<?php

class sudokusolver
{

	public $width = 3;

	public $height = 3;

	public $sudoku = array();

	public $fields = array();

	public function __construct($sudoku) {
		if ( pow($this->width*$this->height, 2) != count($sudoku) ) {
			throw new Exception('Sudoku array must be '.pow($this->width*$this->height, 2).' integers long');
		}
		$this->sudoku = $sudoku;
		$this->fields = array_fill(0, pow($this->width*$this->height, 2), pow(2, $this->width*$this->height)-1);
	}

	public function coordinates( $index, &$x, &$y ) {
		$x = $index % ($this->height*$this->width);
		$y = floor( $index / ($this->height*$this->width) );
	}

	public function table( &$rid = null ) {
		$rid = 'ss_x'.rand(1000, 9999);
		$szHtml = '<style type="text/css">table#'.$rid.' { border-collapse:collapse; } table#'.$rid.' th { padding:0; width:35px; height:35px; border:solid 1px #888; } table#'.$rid.' tr.bb th { border-bottom:solid 3px #444; } table#'.$rid.' tr.bt th { border-top:solid 3px #444; } table#'.$rid.' th.bl { border-left:solid 3px #444; } table#'.$rid.' th.br { border-right:solid 3px #444; }</style>';
		$szHtml .= '<table style="opacity:0.2;" id="'.$rid.'">';
		foreach ( $this->sudoku AS $i => $v ) {
			$this->coordinates($i, $x, $y);
			if ( 0 == $i%($this->height*$this->width) ) {
				$szHtml .= '<tr y="'.$y.'"'.( 0 == ($y+1)%$this->height || 0 == $y ? ' class="b'.( 0 == $y ? 't' : 'b' ).'"' : '' ).'>';
			}
			$szHtml .= '<th'.( 0 == ($x+1)%$this->width || 0 == $x ? ' class="b'.( 0 == $x ? 'l' : 'r' ).'"' : '' ).'>'.( 0 < $v ? $v : '<br />' ).'</th>';
			if ( 0 == ($i+1)%($this->height*$this->width) ) {
				$szHtml .= '</tr>';
			}
		}
		$szHtml .= '</table>'."\n";
		return $szHtml;
	}

}

class sudokusolver_3_2 extends sudokusolver {
	public $width = 3;
	public $height = 2;
}


