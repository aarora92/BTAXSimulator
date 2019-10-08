<?php

class aXDebug
{
	var $area;
	var $posititon;
	
	// constructor
	function aXDebug ( $area, $position )
	{
		$this->area = $area;
		$this->position = $position;
	}
	function writeMessage($message)
	{
		ax_debug($this->area,$this->position." :\t".$message);
	}
}

?>