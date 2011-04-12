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
	public function build_grid() {
		/*
		 * TODO: consider adding ability to create a "border" row & column, to cope 
		 *		with parts of the map not being attached to a grid...
		 */
		$output = "<table class=\"ttorp\">";
		#$output = "<div class=\"table ttorp\">";
		for($h=0;$h<$this->height;$h++) {
			$rowNum = ($h+1);
			$thisRow = "<tr class=\"row_". $rowNum ."\">\n";
			#$thisRow = "<div class=\"tr row_". $rowNum ."\">\n";
			$output .= $thisRow;
			
			for($w=0;$w<$this->width;$w++) {
				$coord = ($w+1) ."-". ($h+1);
				$thisCol = "\t<td class=\"row_". ($h+1) ." col_". ($w+1) ."\" id=\"coord_". $coord ."\">&nbsp;</td>\n";
				#$thisCol = "\t<div class=\"td row_". ($h+1) ." col_". ($w+1) ."\" id=\"coord_". $coord ."\">". $coord ."</td>\n";
				$output .= $thisCol;
			}
			$output .= "</tr>\n";
			#$output .= "</div>\n";
		}
		$output .= "</table>";
		#$output .= "</div>";
		return($output);
	}//end build_grid()
	//-------------------------------------------------------------------------
}
