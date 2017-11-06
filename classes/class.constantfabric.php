<?php
/**
 * Generate constant with unic value
 *
 */
class ConstantFabric{
	
	/**
	 * Generator
	 *
	 * @param string $_ConstantName - constant name
	 * @return boolean - true if successful generation else false
	 */
	function generateConstant($_ConstantName){
		
		static $GeneratorCounter = 1001;
		if(!$_ConstantName) return false;
		if(defined($_ConstantName)) return false;
		define($_ConstantName, $GeneratorCounter);
		return $GeneratorCounter++;
	}
}
?>