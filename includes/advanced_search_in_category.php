<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
	$extraParametrsTemplate = null;
	$searchParamName = null;
	$rangePrice = null;

	if ( !isset($_GET["categoryID"]) && isset($_GET["search_with_change_category_ability"]) )
	{
		$categories = catGetCategoryCList();
		$smarty->assign( "categories_to_select", $categories );
	}

	if ( isset($_GET["categoryID"]) )
	{
		
		$_GET["categoryID"] = (int)$_GET["categoryID"];

		if  (  !catGetCategoryById($_GET["categoryID"])  )
			$smarty->assign( "main_content_template", "page_not_found.tpl.html" );
		else
		{
			if ( isset($_GET["search_with_change_category_ability"]) )
			{
				$categories = catGetCategoryCList();
				$smarty->assign( "categoryID1", (int)$_GET["categoryID"] );
				$smarty->assign( "categories_to_select", $categories );
			}

			$getData = null;
			if ( isset($_GET["advanced_search_in_category"]) )
			{
				$extraParametrsTemplate = array();
				$extraParametrsTemplate["categoryID"] = $_GET["categoryID"];

				if ( isset($_GET["search_name"]) )
					if ( trim($_GET["search_name"]) != "" )
						$searchParamName = array( $_GET["search_name"] );

				$rangePrice = 
						array( "from" => $_GET["search_price_from"], 
							  "to"   => $_GET["search_price_to"] );

				$getData = ScanGetVariableWithId( array("param") );
				foreach( $getData as $optionID => $value )
				{
					$res = schOptionIsSetToSearch( $_GET["categoryID"], $optionID );
	
					if ( $res["set_arbitrarily"]==0 && (int)$value["param"] == 0 )
						continue;

					$item = array();
					$item["optionID"]	= $optionID;
					$item["value"]		= xStripSlashesGPC($value["param"]);
					$item["set_arbitrarily"]	= $res["set_arbitrarily"];
					$extraParametrsTemplate[] = $item;
				}
			}


			$params = array();

			$categoryID = $_GET["categoryID"];
			$options = optGetOptions();
			$OptionsForSearch = schOptionsAreSetToSearch($categoryID, $options);
			
			foreach( $options as $option ){
				
				if ( isset($OptionsForSearch[$option["optionID"]])){
					
					$set_arbitrarily = $OptionsForSearch[$option["optionID"]]['set_arbitrarily'];
					$item = array();
					$item["optionID"]	= $option["optionID"];
					$item["value"] = xStripSlashesGPC($getData[ (string)$option["optionID"] ]["param"]);

					$item["controlIsTextField"] = $set_arbitrarily;
					$item["name"]				= $option["name"];
					if ( $set_arbitrarily == 0 )
					{
						$item["variants"]			= array();
						$variants = schGetVariantsForSearch( $categoryID, $option["optionID"]);
						foreach( $variants as $variant ){
							
							$item["variants"][] = array(
								'variantID' => $variant["variantID"],
								'value' => xStripSlashesGPC($variant["option_value"])
								);
						}
					}
					$params[] = $item;
				}
			}

			if ( isset($_GET["search_name"]) )
				$smarty->assign( "search_name", xStripSlashesGPC($_GET["search_name"]) );
			if ( isset($_GET["search_price_from"]) )
				$smarty->assign( "search_price_from", xStripSlashesGPC($_GET["search_price_from"]) );
			if ( isset($_GET["search_price_to"]) )
				$smarty->assign( "search_price_to", xStripSlashesGPC($_GET["search_price_to"]) );

			$smarty->assign( "categoryID", $categoryID );
			if ( isset($_GET["advanced_search_in_category"]) )
				$smarty->assign( "search_in_subcategory", isset($_GET["search_in_subcategory"]) );
			else
				$smarty->assign( "search_in_subcategory", true );
			$smarty->assign( "show_subcategory_checkbox", 1 );
			$smarty->assign( "priceUnit", getPriceUnit() );
			$smarty->assign( "params", $params );
		}
	}
?>