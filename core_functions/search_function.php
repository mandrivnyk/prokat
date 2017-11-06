<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/

	function schIsAllowProductsSearch( $categoryID )
	{
		$q = db_query("select allow_products_search from ".CATEGORIES_TABLE.
			" where categoryID=$categoryID");
		if ( $row = db_fetch_row($q) )
			return $row["allow_products_search"];
		return false;		
	}


	function schUnSetOptionsToSearch( $categoryID )
	{
		$q = db_query( "select optionID from ".CATEGORY_PRODUCT_OPTIONS_TABLE.
			" where categoryID=$categoryID " );
		$data = array();
		while( $row = db_fetch_row($q) )
			$data[] = $row["optionID"];

		foreach( $data as $val )
		{
			db_query( " delete from ".CATEGORY_PRODUCT_OPTION_VARIANTS.
				" where categoryID=$categoryID AND optionID=$val" );

			db_query( " delete from ".CATEGORY_PRODUCT_OPTIONS_TABLE.
				" where categoryID=$categoryID AND optionID=$val" );
		}
	}

	function schSetOptionToSearch( $categoryID, $optionID, $set_arbitrarily )
	{
		db_query( "insert into ".CATEGORY_PRODUCT_OPTIONS_TABLE.
				" ( categoryID, optionID, set_arbitrarily ) ".
				" values( $categoryID, $optionID, $set_arbitrarily ) " );
	}

	function schOptionIsSetToSearch( $categoryID, $optionID )	{
		
		$res = array();
		
		$SQL = '
			SELECT set_arbitrarily FROM ?#CATEGORY_PRODUCT_OPTIONS_TABLE WHERE categoryID=? AND optionID=?
		';
		$q = db_phquery($SQL,$categoryID, (int)$optionID);
		if ( $row = db_fetch_row($q) ){
			
			$res['isSet'] = 1;
			$res['set_arbitrarily'] = $row['set_arbitrarily'];
		}else{
			$res['isSet'] = 0;
		}
		return $res;
	}

	function &schOptionsAreSetToSearch( $categoryID, &$options ){
		
		$TC = count($options);
		$r_OptionID2Option = array();
		$r_OptionID = array();
		$r_OptionRes = array();
		
		for ($j=0;$j<$TC;$j++){
			
			$r_OptionID2Option[$options[$j]['optionID']] = &$options[$j];
			$r_OptionID[] = $options[$j]['optionID'];
			if(count($r_OptionID)>299||($j+1)==$TC){
				
				$SQL = '
					SELECT optionID,set_arbitrarily FROM ?#CATEGORY_PRODUCT_OPTIONS_TABLE
					WHERE categoryID=? AND optionID IN(?@)
				';
				$Result = db_phquery($SQL, $categoryID, $r_OptionID);
				while ($Row = db_fetch_assoc($Result)){
					
					$r_OptionRes[$Row['optionID']] = $Row['set_arbitrarily'];
				}
				
				$r_OptionID = array();
			}
		}
		
		return $r_OptionRes;
	}

	function schUnSetVariantsToSearch( $categoryID, $optionID ){
		
		$SQL = '
			DELETE FROM ?#CATEGORY_PRODUCT_OPTION_VARIANTS WHERE categoryID=? AND optionID=? 
		';
		db_phquery($SQL,$categoryID,$optionID);
	}


	function schSetVariantToSearch( $categoryID, $optionID, $variantID )
	{
		db_query( "insert into ".CATEGORY_PRODUCT_OPTION_VARIANTS.
				" ( optionID, categoryID, variantID )  ".
				" values( $optionID, $categoryID, $variantID ) " );
	}	

	function schVariantIsSetToSearch( $categoryID, $optionID, $variantID ){
		
		$SQL = '
			SELECT COUNT(*) FROM ?#CATEGORY_PRODUCT_OPTION_VARIANTS WHERE categoryID=? AND optionID=? AND variantID=?
		';
		$q = db_phquery($SQL,$categoryID,$optionID,$variantID);
		$row = db_fetch_row($q);
		return ( $row[0] != 0 );
	}
	
	function &schGetVariantsForSearch($categoryID, $optionID, $variants = null){
		
		if(is_null($variants)){
			
			$variants = optGetOptionValues( $optionID);
		}
		$r_VariantID2Variant = array();
		$r_VariantID = array();
		$TC = count($variants);
		$tTC = 0;
		for ($j=0;$j<$TC;$j++){
			
			$r_VariantID[$variants[$j]['variantID']] = &$variants[$j];
			$tTC++;
			if(count($r_VariantID)>299||($j+1)==$TC){
				
				$SQL = '
					SELECT variantID FROM ?#CATEGORY_PRODUCT_OPTION_VARIANTS WHERE categoryID=? AND optionID=? AND variantID IN(?@)
				';
				$Result = db_phquery($SQL, $categoryID, $optionID, array_keys($r_VariantID));
				while ($Row = db_fetch_assoc($Result)){
					
					$r_VariantID2Variant[$Row['variantID']] = &$r_VariantID[$Row['variantID']];
				}
				$tTC = 0;
				$r_VariantID = array();
			}
		}
		
		return $r_VariantID2Variant;
	}

?>