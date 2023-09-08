<?php

class StateSolver
{
	static public function array_join( $d_array ) {
		$arr = array();
		while ( 0 < count($d_array) ) {
			$arr = array_merge($arr, array_shift($d_array));
		}
		return $arr;
	}

	// Holds the sudoku puzzel as an array
	public $sudoku;

	// Holds the 27 candidatelists as an array
	protected $_candidates;

	// Holds the list of empty cells as an array
	protected $_emptyCells;

	// Determines whether or not the algorithm has found a solution
	protected $_ready;

	// Constructor
	public function __construct(array $sudoku)
	{
		// Single level array (keys 0 through 80)
		$this->sudoku = $sudoku;
	}

	// Initialize the solving algorithm
	public function findSolution()
	{
		$column = 0;
		$row = 0;
		$region = 0;
		$eIndex = 0;

		// Fill the candidatelists with all 9 bits set
		for ($i = 0; $i < 27; $i++)
		{
			$this->_candidates[$i] = 511;
		}

		// Exclude invalid candidates and get empty cells
		for ($i = 0; $i < 81; $i++)
		{
			if ($this->sudoku[$i] == 0)
			{
				// Add this empty cell to the list
				$this->_emptyCells[$eIndex++] = $i;
			}
			else
			{
				// Exclude this number from the candidatelists
				$this->_getCandidateLists($i, $column, $row, $region);

				$this->_exclude($this->_candidates[$column], $this->sudoku[$i]);
				$this->_exclude($this->_candidates[$row], $this->sudoku[$i]);
				$this->_exclude($this->_candidates[$region], $this->sudoku[$i]);
			}
		}

		// Set the ready flag to false
		$this->_ready = false;

		// Run the recursive backtracking algorithm
		$this->_solve(0);
	}

	// Recursive backtracking solver
	protected function _solve($eIndex)
	{
		$column = 0;
		$row = 0;
		$region = 0;

		// See if haven't reached the end of the pattern
		if ($eIndex < count($this->_emptyCells))
		{
			// Get the corresponding candidatelists
			$this->_getCandidateLists($this->_emptyCells[$eIndex], $column, $row, $region);
// echo "[$column, $row, $region]\n";

			// Check if $i occurs in all three candidatelists
			for ($i = 1; $i < 10; $i++)
			{
				if ($this->_isCandidate($this->_candidates[$column], $i) && $this->_isCandidate($this->_candidates[$row], $i) && $this->_isCandidate($this->_candidates[$region], $i))
				{
					// Suitable candidate found, use it!
// echo "find " . $this->_emptyCells[$eIndex] . " = $i\n";
					$this->sudoku[$this->_emptyCells[$eIndex]] = $i;

					// Exclude this number from the candidatelists
					$this->_exclude($this->_candidates[$column], $i);
					$this->_exclude($this->_candidates[$row], $i);
					$this->_exclude($this->_candidates[$region], $i);

					// Don't advance if a solution has been found
					if ($this->_ready)
						return;

					// Advance to the next cell
					$this->_solve($eIndex + 1);

					// Don't revert if a solution has been found
					if ($this->_ready)
						return;

					// Reset the cell
// echo "reset " . $this->_emptyCells[$eIndex] . "\n";
// dd($this);
					$this->sudoku[$this->_emptyCells[$eIndex]] = 0;

					// Put the candidates back in the lists
					$this->_include($this->_candidates[$column], $i);
					$this->_include($this->_candidates[$row], $i);
					$this->_include($this->_candidates[$region], $i);
				}
			}
		}
		else
		{
			// A solution has been found, get out of recursion
			$this->_ready = true;
		}
	}

	// Obtains the corresponding candidatelist indices
	protected function _getCandidateLists($position, &$column, &$row, &$region)
	{
		$column = $position % 9;
		$row = (int) floor(9 + $position / 9);
		$region = (int) floor(18 + floor($column / 3) + 3 * floor(($row - 9) / 3));
	}

	// Excludes a number from the list of candidates
	protected function _exclude(&$bitSet, $bit)
	{
		$bitSet &= ~(1 << $bit -1);
	}

	// Includes a number into the list of candidates
	protected function _include(&$bitSet, $bit)
	{
		$bitSet |= (1 << $bit - 1);
	}

	// Determines if number occurs in the specified list of candidates
	protected function _isCandidate($bitSet, $bit)
	{
		return (($bitSet & (1 << $bit - 1)) == 0) ? false : true;
	}
}


