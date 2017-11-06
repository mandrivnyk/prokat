<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
	if ( !strcmp($sub, "product_report") )
	{

		function _getUrlToNavigate()
		{
			$res = "admin.php?dpt=reports&sub=product_report";
			if ( isset($_GET["categoryID"]) )
				$res .= "&categoryID=".$_GET["categoryID"];
			if ( isset($_GET["sort"]) )
				$res .= "&sort=".$_GET["sort"];
			if ( isset($_GET["sort_dir"]) )
				$res .= "&sort_dir=".$_GET["sort_dir"];
			return $res;
		}

		function _getUrlToCategoryTreeExpand()
		{
			$res = "admin.php?dpt=reports&sub=product_report";
			if ( isset($_GET["categoryID"]) )
				$res .= "&categoryID=".$_GET["categoryID"];
			if ( isset($_GET["sort"]) )
				$res .= "&sort=".$_GET["sort"];
			if ( isset($_GET["sort_dir"]) )
				$res .= "&sort_dir=".$_GET["sort_dir"];
			if ( isset($_GET["offset"]) )
				$res .= "&offset=".$_GET["offset"];
			if ( isset($_GET["show_all"]) )
				$res .= "&show_all=".$_GET["show_all"];
			return $res;
		}

		function _getUrlToSort()
		{
			$res = "admin.php?dpt=reports&sub=product_report";
			if ( isset($_GET["categoryID"]) )
				$res .= "&categoryID=".$_GET["categoryID"];
			if ( isset($_GET["offset"]) )
				$res .= "&offset=".$_GET["offset"];
			if ( isset($_GET["show_all"]) )
				$res .= "&show_all=".$_GET["show_all"];
			return $res;
		}


		
		if ( isset($_GET["categoryID"]) )
		{
			$category = catGetCategoryById( $_GET["categoryID"] );
			$smarty->assign( "category", $category );


			$callBackParam = array();
			$callBackParam["categoryID"] = $_GET["categoryID"];
			if ( isset($_GET["sort"]) )
				$callBackParam["sort"] = $_GET["sort"];
			if ( isset($_GET["sort_dir"]) )
				$callBackParam[ "direction" ] = $_GET["sort_dir"];
			$product_report = array();
			$navigatorHtml = GetNavigatorHtml( _getUrlToNavigate(), 20, 
							'repGetProductReportByCategoryID', $callBackParam, 
								$product_report, $offset, $count );

			$smarty->assign( "urlToSort", _getUrlToSort() );
			$smarty->assign( "product_report", $product_report );
			$smarty->assign( "navigator", $navigatorHtml );
		}


		if ( isset($_GET["expandCat"]) )
			catExpandCategory( $_GET["expandCat"], "expandedCategoryID_Array__ProductReport" );

		if ( isset($_GET["shrinkCat"]) )
			catShrinkCategory( $_GET["shrinkCat"], "expandedCategoryID_Array__ProductReport" );

		if ( !isset($_SESSION["expandedCategoryID_Array__ProductReport"]) )
			$_SESSION["expandedCategoryID_Array__ProductReport"] = array( 1 );


		$smarty->assign( "urlToCategoryTreeExpand", _getUrlToCategoryTreeExpand() );


		$c = catGetCategoryCList( $_SESSION["expandedCategoryID_Array__ProductReport"] );
		$smarty->assign("categories", $c);
	    $smarty->assign( "admin_sub_dpt", "reports_product_report.tpl.html" );
	}
?>