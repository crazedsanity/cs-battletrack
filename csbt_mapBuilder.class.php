<?php

/*
 *  SVN INFORMATION::::
 * --------------------------
 * $HeadURL$
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */

class csbt_mapBuilder extends cs_webapplibsAbstract {
	
	private $height = 0;
	private $width = 0;
	private $_size = null;
	
	//-------------------------------------------------------------------------
	public function __construct($sizeSpec) {
		if(is_array($this->validate_size_spec($sizeSpec))) {
			$bits = $this->validate_size_spec($sizeSpec);
			$this->height = $bits[0];
			$this->width = $bits[1];
		}
		else {
			throw new exception(__METHOD__ .": invalid spec (". $sizeSpec .")");
		}
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function validate_size_spec($spec) {
		$retval = false;
		
		$bits = explode('x', $spec);
		if(count($bits) == 2 && is_numeric($bits[0]) && is_numeric($bits[1])) {
			if($bits[0] >= 2 && $bits[1] >= 2) {
				$retval = $bits;
			}
		}

		return($retval);
	}//end validate_size_spec()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function build_grid($sqSize=32) {
		if(!is_numeric($sqSize) || $sqSize < 5) {
			$sqSize = 32;
		}
		//<div id="x0y0" class="gridSquareHidden" style="position: absolute; left: 0px; top: 0px; width: 50px; height: 50px;">
		$output="";
		$top = 0;
		for($x = 0; $x < $this->width; $x++) {
			$offsetY = ($sqSize * $x);
			
			$left = 0;
			
			for($y = 0; $y < $this->height; $y++) {
				$offsetX = ($sqSize * $y);
				$innerSize = ($sqSize -1);
				$output .= "\t". '<div id="coord_'. $y .'-'. $x .'" class="tile col_'. $y .'" '.
					'style="position: absolute; left: '. $offsetX .'px; top: '. $offsetY .'px; '.
					'width: '. $sqSize .'px; height: '. $sqSize .'px;">'.
					'<div class="inner" style="height: '. $innerSize .'px; width: '. $innerSize .'px;"></div></div>'. "\n";
			}
			#$output .= "</div>\n";
			$top += $sqSize;
		}
		$this->_size = array(
			'x'	=> ($sqSize * $this->width),
			'y'	=> ($sqSize * $this->height)
		);
		
		return($output);
	}//end build_grid()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_size($xOrY) {
		return($this->_size[$xOrY]);
	}//end get_size()
	//-------------------------------------------------------------------------
}
