<?php
	//products and categories tree view

	if (!strcmp($sub, "products_categories"))
	{

		function _getUrlToSubmit()
		{
			$res = "admin.php?dpt=catalog&sub=products_categories";
			if ( isset($_GET["categoryID"]) )
				$res .= "&categoryID=".$_GET["categoryID"];
			if ( isset($_GET["offset"]) )
				$res .= "&offset=".$_GET["offset"];
			if ( isset($_GET["sort"]) )
				$res .= "&sort=".$_GET["sort"];
			if ( isset($_GET["sort_dir"]) )
				$res .= "&sort_dir=".$_GET["sort_dir"];

			if ( isset($_GET["search_criteria"]) )
				$res .= "&search_criteria=".$_GET["search_criteria"];
			if ( isset($_GET["search_value"]) )
				$res .= "&search_value=".$_GET["search_value"];
			if ( isset($_POST["search_criteria"]) )
				$res .= "&search_criteria=".$_POST["search_criteria"];
			if ( isset($_POST["search_value"]) )
				$res .= "&search_value=".$_POST["search_value"];

			if ( isset($_GET["search"]) )
				$res .= "&search=".$_GET["search"];
			if ( isset($_GET["show_all"]) )
				$res .= "&show_all=".$_GET["show_all"];
			return $res;
		}

		function _getUrlToDelete()
		{
			return _getUrlToSubmit();
		}

		function _getUrlToCategoryTreeExpand()
		{
			return _getUrlToSubmit();
		}

		function _getUrlToNavigate()
		{
			return _getUrlToSubmit();

			/*$res = "admin.php?dpt=catalog&sub=products_categories";
			if ( isset($_GET["categoryID"]) )
				$res .= "&categoryID=".$_GET["categoryID"];
			if ( isset($_GET["sort"]) )
				$res .= "&sort=".$_GET["sort"];
			if ( isset($_GET["sort_dir"]) )
				$res .= "&sort_dir=".$_GET["sort_dir"];
			return $res;*/
		}

		function _getUrlToSort()
		{
			return _getUrlToSubmit();

			/*$res = "admin.php?dpt=catalog&sub=products_categories";
			if ( isset($_GET["categoryID"]) )
				$res .= "&categoryID=".$_GET["categoryID"];
			return $res;*/
		}

		$callBackParam = array();

		if ( isset($_GET["search"]) )
		{
			if (isset($_POST["search_value"])) //"Find" button pressed
			{
				$search_value = $_POST["search_value"];
			}
			else if (isset($_GET["search_value"])) //after search is made customer pushed 'delete' button, changed sort order, etc.
			{
				$search_value = $_GET["search_value"];
			}

			$array = explode( " ", $search_value );
			$search_value_array = array();
			foreach( $array as $val )
			{
				if ( $val != "" )
					$search_value_array[] = $val;
			}

			if (isset($_POST["search_criteria"]))
			{
				$search_criteria = $_POST["search_criteria"];
			}
			else if (isset($_GET["search_criteria"]))
			{
				$search_criteria = $_GET["search_criteria"];
			}

			if ( $search_criteria == "name" )
				$callBackParam["name"] = $search_value_array;
			if ( $search_criteria == "product_code" )
				$callBackParam["product_code"] = $search_value_array;

			$smarty->assign( "search_criteria", $search_criteria );
			$smarty->xassign( "search_value", $search_value );
			$smarty->assign( "searched_done", 1 );
		}

		if ( isset($_GET["expandCat"]) )
			catExpandCategory( $_GET["expandCat"], "expandedCategoryID_Array" );

		if ( isset($_GET["shrinkCat"]) )
			catShrinkCategory( $_GET["shrinkCat"], "expandedCategoryID_Array" );

		if ( isset($_GET["delete_all_products"])  )
		{
			if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
			{
				Redirect( _getUrlToSubmit()."&safemode=yes" );
			}

			if ( DeleteAllProductsOfThisCategory( (int) $_GET["categoryID"]) )
				Redirect("admin.php?dpt=catalog&sub=products_categories&categoryID=".
						$_GET["categoryID"]);
			else
				Redirect("admin.php?dpt=catalog&sub=products_categories&categoryID=".
						$_GET["categoryID"].
						"&couldntToDeleteThisProducts=1" );
		}
 		else if (isset($_POST["products_update"]))
		{
			if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
			{
				Redirect( _getUrlToSubmit()."&safemode=yes" );
			}

			//save changes in current category
			$data = ScanPostVariableWithId( array( "priceUE","price",  "enable", "left", "sort_order" ) );

			/* echo '<pre>';
			 print_r($data);
			echo '</pre>';
			exit(); */
			foreach( $data as $key => $val )
			{
				if ( isset($val["priceUE"]) )
				{
				   // echo 'tyt';exit();
					$temp = $val["priceUE"];
					//$temp = round($temp*100)/100;
					db_query( "UPDATE ".PRODUCTS_TABLE." SET Price_UE='$temp' ".
						" WHERE productID=".$key );
				}
				if ( isset($val["price"]) )
				{
					$temp = $val["price"];
					$temp = round($temp*100)/100;
					db_query( "UPDATE ".PRODUCTS_TABLE." SET Price='$temp' ".
						" WHERE productID=".$key );
				}

				if ( isset($val["enable"]) )
				{
					db_query( "update ".PRODUCTS_TABLE.
						" set enabled=".$val["enable"]." ".
					    " WHERE productID=".$key );
				}

				if ( isset($val["left"]) )
				{
					db_query( "UPDATE ".PRODUCTS_TABLE.
						" SET in_stock = '".$val["left"]."' WHERE productID=".$key);
				}

				if ( isset($val["sort_order"]) )
				{
					db_query( "UPDATE ".PRODUCTS_TABLE.
						" SET sort_order = '".$val["sort_order"]."' WHERE productID=".$key);
				}
			}

			if ( CONF_UPDATE_GCV == '1' )
				update_products_Count_Value_For_Categories(1);

			Redirect( _getUrlToSubmit() );
		}
		else if (isset($_GET["terminate"])) //delete product
		{
			if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
			{
				Redirect( _getUrlToSubmit()."&safemode=yes" );
			}

	 		if (DeleteProduct($_GET["terminate"]))
				Redirect( _getUrlToSubmit() );
			else
				Redirect( _getUrlToSubmit()."&couldntToDelete=1" );
		}

		if (isset($_POST["update_gc_value"])) //update button pressed
		{
			@set_time_limit(60*4);
			update_products_Count_Value_For_Categories(1);
			Redirect( "admin.php?dpt=catalog&sub=products_categories&categoryID=".$_POST["categoryID"]);
		}

		//calculate how many products are there in root category
		$q = db_query("SELECT count(*) FROM ".PRODUCTS_TABLE." WHERE categoryID=1") or die (db_error());
		$cnt = db_fetch_row($q);
		$smarty->assign("products_in_root_category",$cnt[0]);

		if ( !isset($_SESSION["expandedCategoryID_Array"]) )
			$_SESSION["expandedCategoryID_Array"] = array( 1 );

		$c = catGetCategoryCList( $_SESSION["expandedCategoryID_Array"] );
		/*echo '<pre>';
			print_r($c);
		echo '</pre>';*/
		$smarty->assign("categories", $c);

		//show category name as a title
		$row = array();
		if (!isset($_GET["categoryID"]) && !isset($_POST["categoryID"]))
		{
			$categoryID = 1;
			$row[0] = ADMIN_CATEGORY_ROOT;
		}
		else //go to the root if category doesn't exist
		{
			$categoryID = isset($_GET["categoryID"]) ? $_GET["categoryID"] : $_POST["categoryID"];
			$q = db_query("SELECT name FROM ".CATEGORIES_TABLE." WHERE categoryID<>0 and categoryID='$categoryID'") or die (db_error());
			$row = db_fetch_row($q);
			if (!$row)
			{
				$categoryID = 0;
				$row[0] = ADMIN_CATEGORY_ROOT;
			}
		}

		$smarty->assign("categoryID", $categoryID);
		$smarty->assign("category_name", $row[0]);

		$count_row	= 0;
		$offset		= 0;
		$products	= null;

		if ( isset($_GET["sort"]) )
		{
			$callBackParam["sort"] = $_GET["sort"];
			if ( isset($_GET["sort_dir"]) )
				$callBackParam["direction"] = $_GET["sort_dir"];
		}
		$callBackParam["categoryID"] = $categoryID;
		$callBackParam["searchInSubcategories"] = false;

		$count = 0;
		$navigatorHtml = GetNavigatorHtml(
			_getUrlToNavigate(), 20,
			'prdSearchProductByTemplate', $callBackParam, $products, $offset, $count );

		for( $i=0; $i < count($products); $i++ )
		{
			$products[$i]["picture_count"]		= GetPictureCount( $products[$i]["productID"] );
			$products[$i]["thumbnail_count"]	= GetThumbnailCount( $products[$i]["productID"] );
			$products[$i]["enlarged_count"]		= GetEnlargedPictureCount( $products[$i]["productID"] );
		}

		$smarty->assign("navigatorHtml", $navigatorHtml );


		$smarty->xassign( "urlToSort", _getUrlToSort() );
		$smarty->xassign( "urlToSubmit", _getUrlToSubmit() );
		$smarty->xassign( "urlToDelete", _getUrlToDelete() );
		$smarty->xassign( "urlToCategoryTreeExpand", _getUrlToCategoryTreeExpand());

		$smarty->assign( "searched_count",
						str_replace( "{N}",
							count($products),  ADMIN_N_RECORD_IS_SEARCHED )  );

		//products list
		$smarty->assign("products", $products );
		/* echo '<pre>';
		  print_r($products);
		echo '</pre>'; */
		//set main template
		$smarty->assign("admin_sub_dpt", "catalog_products_categories.tpl.html");

	}

?>