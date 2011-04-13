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
		$output = "<table class=\"ttorp\">\n";
		
		#//build the border row.
		#$output .= "<tr class=\"border\">\n";
		#for($i=0;$i<=$this->width;$i++) {
		#	$output .= "\t<td class=\"borderRow borderCell\" id=\"border_". $i ."-0\"></td>\n";
		#}
		#$output .= "</tr>\n";
		
		for($h=0;$h<$this->height;$h++) {
			$rowNum = ($h+1);
			$rowClasses = "row_". $rowNum;
			$addColClasses = "";
			if($h==0) {
				$rowClasses .= " borderRow";
				$addColClasses = " borderCol";
			}
			$thisRow = "<tr class=\"". $rowClasses ."\">\n";
			$output .= $thisRow;
			
			#//add a border cell.
			#$output .= "\t<td class=\"borderCol borderCell\" id=\"border_0-". ($h+1) ."\"></td>\n";
			
			for($w=0;$w<$this->width;$w++) {
				$coord = ($w+1) ."-". ($h+1);
				$colClasses = "tile col_". ($w+1) ." row_". ($h+1);
				if($w==0) {
					$colClasses .= " borderRow";
				}
				$colClasses .= $addColClasses;
				$thisCol = "\t<td class=\"". $colClasses ."\" id=\"coord_". $coord ."\">&nbsp;</td>\n";
				$output .= $thisCol;
			}
			$output .= "</tr>\n";
		}
		$output .= "</table>";
		return($output);
	}//end build_grid()
	//-------------------------------------------------------------------------
}
