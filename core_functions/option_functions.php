<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
// *****************************************************************************
// Purpose	gets all options
// Inputs   nothing
// Remarks		
// Returns	array of item
//					"optionID"
//					"name"
//					"sort_order"
//					"count_variants"
function optGetOptions(){
	
	$SQL = '
		SELECT ps.optionID, ps.name, ps.sort_order, COUNT(povv.variantID) as count_variants FROM '.PRODUCT_OPTIONS_TABLE.' as ps 
		LEFT JOIN '.PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE.' as povv ON ps.optionID = povv.optionID GROUP BY ps.optionID ORDER BY sort_order, name
	';
	$q = db_query($SQL);
	$result=array();
	
	while( $row=db_fetch_row($q) ){
		
		$row['name'] = TransformDataBaseStringToText( $row['name'] );
		$result[] = $row;
	}
	return $result;
}


// *****************************************************************************
// Purpose	gets all options
// Inputs   $optionID - option ID
// Remarks		
// Returns	array of item
//					"optionID"
//					"name"
//					"sort_order"
//					"count_variants"
function optGetOptionById($optionID)
{
	$q = db_query("select optionID, name, sort_order from ".
				PRODUCT_OPTIONS_TABLE." where optionID='".$optionID."'");
	if ( $row=db_fetch_row($q) )
	{
		$row["name"] = TransformDataBaseStringToText( $row["name"] );
		return $row;
	}
	else
		return null;
}


// *****************************************************************************
// Purpose	gets all options
// Inputs   array of item
//				each item consits of			
//					"extra_option"			- option name
//					"extra_sort"			- enlarged picture
//				key is option ID
// Remarks		
// Returns	nothig
function optUpdateOptions($updateOptions)
{
	foreach($updateOptions as $key => $val)
	{
		if (isset($val["extra_option"]) && $val["extra_option"]!="")
		{
			$val["extra_option"]	= TransformStringToDataBase( $val["extra_option"] );
			$val["extra_sort"]		= (int)$val["extra_sort"];
			$s = "update ".PRODUCT_OPTIONS_TABLE." set name='".trim($val["extra_option"]).
				"', sort_order='".$val["extra_sort"]."' where optionID='$key';";
			db_query($s);
		}
	}
}


// *****************************************************************************
// Purpose	adds new option
// Inputs		
//				$extra_option	- option name
//				$extra_sort		- sort order
// Remarks		
// Returns	nothig
function optAddOption($extra_option, $extra_sort)
{
	$extra_option	= TransformStringToDataBase($extra_option);
	$extra_sort		= (int)$extra_sort;
	if ( trim($extra_option) == "" )
		return;
	db_query("insert into ".PRODUCT_OPTIONS_TABLE.
			" (name, sort_order) values ('".$extra_option."', '".$extra_sort."')");
}

// *****************************************************************************
// Purpose	get option values
// Inputs		
// Remarks		
// Returns	
function optGetOptionValues($optionID){
	
	$q = db_query("select variantID, optionID, option_value, sort_order from ".
				PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE.
				" where optionID=".intval($optionID).
				" order by sort_order, option_value")
				or die (db_error());
	$result=array();
	while($row=db_fetch_row($q))
		$result[] = $row;
	return $result;
}

// *****************************************************************************
// Purpose	get option values
// Inputs		
// Remarks		
// Returns	
function optOptionValueExists($optionID, $value_name)
{
	$value_name = TransformStringToDataBase($value_name);
	$q = db_query("select variantID from ".
				PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE.
				" where optionID=".$optionID." and option_value='".$value_name."';");
	$row = db_fetch_row($q);
	if ($row)
		return $row[0]; //return variant ID
	else
		return false;
}

// *****************************************************************************
// Purpose	updates option values
// Inputs   array of item
//				each item consits of			
//					"option_value"			- option name
//					"sort_order"			- enlarged picture
//				key is option ID
// Remarks		
// Returns	
function optUpdateOptionValues($updateOptions)
{
	foreach($updateOptions as $key => $value)
	{
		$value["option_value"]	= TransformStringToDataBase( $value["option_value"] );
		$value["sort_order"]	= (int)$value["sort_order"];
		$s = "update ".PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE.
			 " set option_value='".$value["option_value"]."', ".
			 " sort_order='".$value["sort_order"]."' ".
			 " where variantID='".$key."'";
		db_query( $s );
	}
}



// *****************************************************************************
// Purpose	updates option values
// Inputs   
//				$optionID	- option ID
//				$value		- value 
//				$sort_order - sort order
// Remarks		
// Returns	
function optAddOptionValue($optionID, $value, $sort_order)
{
	$value		= TransformStringToDataBase( $value );
	$sort_order = (int)$sort_order;
	db_query("insert into ".PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE.
					"(optionID, option_value, sort_order) ".
					"values('".$optionID."', '".$value."', '".
							$sort_order."' )" );
	return db_insert_id();
}

?>