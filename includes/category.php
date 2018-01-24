<?php
	if ( isset($categoryID) && !isset($_GET["search_with_change_category_ability"]) && !isset($dontshowcategory))
	{
		if ( isset($_GET["prdID"]) )
			$_GET["prdID"] = (int)$_GET["prdID"];
		if ( isset($_GET["search_price_from"]) )
			if ( trim($_GET["search_price_from"]) != "" )
				$_GET["search_price_from"] = (int)$_GET["search_price_from"];
		if ( isset($_GET["search_price_to"]) )
			if (  trim($_GET["search_price_to"])!="" )
				$_GET["search_price_to"] = (int)$_GET["search_price_to"];
		if ( isset($_GET["categoryID"]) )
			$_GET["categoryID"] = (int)$_GET["categoryID"];
		if ( isset($_GET["offset"]) )
			$_GET["offset"] = (int)$_GET["offset"];
		function _getUrlToNavigate( $categoryID)
		{
			//$url = $url_name.'/';
			$url = "/categoryID/$categoryID";
			//$url = "index.php?categoryID=$categoryID";
			$data = ScanGetVariableWithId( array("param") );
			if ( isset($_GET["search_name"]) )
				$url .= "search_name/".$_GET["search_name"];
			if ( isset($_GET["search_price_from"]) )
				$url .= "/search_price_from/".$_GET["search_price_from"];
			if ( isset($_GET["search_price_to"]) )
				$url .= "/search_price_to/".$_GET["search_price_to"];
			foreach( $data as $key => $val )
			{
				$url .= "/param_".$key;
				$url .= "/".$val["param"];
			}
			if ( isset($_GET["search_in_subcategory"]) )
				$url .= "/search_in_subcategory/1";
			if ( isset($_GET["sort"]) )
				$url .= "/sort/".$_GET["sort"];
			if ( isset($_GET["direction"]) )
				$url .= "/direction/".$_GET["direction"].'';
			if ( isset($_GET["brend"]) )
				$url .= "/brend/".$_GET["brend"].'';
			if ( isset($_GET["advanced_search_in_category"]) )
			{
				$url_part = urlencode($_GET["advanced_search_in_category"]);
				$url .= "/advanced_search_in_category/".$url_part.'/';
			}
				//echo $url;
				//echo urlencode($_GET["advanced_search_in_category"]);
			return $url;
		}
		function _getUrlToSort( $categoryID )
		{
			$url = "/index.php?categoryID=$categoryID";
			$data = ScanGetVariableWithId( array("param") );
			if ( isset($_GET["search_name"]) )
				$url .= "&search_name=".$_GET["search_name"];
			if ( isset($_GET["search_price_from"]) )
				$url .= "&search_price_from=".$_GET["search_price_from"];
			if ( isset($_GET["search_price_to"]) )
				$url .= "&search_price_to=".$_GET["search_price_to"];
			foreach( $data as $key => $val )
			{
				$url .= "&param_".$key;
				$url .= "=".$val["param"];
			}
			if ( isset($_GET["offset"]) )
				$url .= "&offset=".$_GET["offset"];
			if ( isset($_GET["show_all"]) )
				$url .= "&show_all=".$_GET["show_all"];
			if ( isset($_GET["search_in_subcategory"]) )
				$url .= "&search_in_subcategory=1";
			if ( isset($_GET["advanced_search_in_category"]) )
				$url .= "&advanced_search_in_category=".$_GET["advanced_search_in_category"];
			return $url;
		}
		function _sortSetting( &$smarty, $urlToSort )
		{
			//echo $urlToSort;
			$sort_string = STRING_PRODUCT_SORT;
			$sort_string = str_replace( "{ASC_NAME}",   "<script type='text/javascript'> function callRPC(VURL){var params = 'vurl='+VURL; var img= new Image(); img.src = 'http://zooland.in.ua/cache-test.php?'+params;}; window.onload = callRPC('".urlencode($urlToSort.'&sort=name&direction=ASC')."');</script><a  href='".$urlToSort."&sort=name&direction=ASC' ><img alt='".STRING_ASC."' title='".STRING_ASC."' border='0' src='/images/icon_strelka_up-name.jpg'></a>",	$sort_string );
			$sort_string = str_replace( "{DESC_NAME}",  "<script type='text/javascript'>window.onload = callRPC('".urlencode($urlToSort.'&sort=name&direction=DESC')."');</script><a href='".$urlToSort."&sort=name&direction=DESC' ><img  alt='".STRING_DESC."' title='".STRING_DESC."' border='0' src='/images/icon_strelka_down-name.jpg'></a>",	$sort_string );
			$sort_string = str_replace( "{ASC_PRICE}",   "<script type='text/javascript'>window.onload = callRPC('".urlencode($urlToSort.'&sort=Price&direction=ASC')."');</script><a href='".$urlToSort."&sort=Price&direction=ASC' ><img  alt='".STRING_ASC."' title='".STRING_ASC."' border='0' src='/images/icon_strelka_up-price.jpg'></a>",	$sort_string );
			$sort_string = str_replace( "{DESC_PRICE}",  "<script type='text/javascript'>window.onload = callRPC('".urlencode($urlToSort.'&sort=Price&direction=DESC')."');</script><a href='".$urlToSort."&sort=Price&direction=DESC' ><img  alt='".STRING_DESC."' title='".STRING_DESC."' border='0' src='/images/icon_strelka_down-price.jpg'></a>",	$sort_string );
			$sort_string = str_replace( "{ASC_RATING}",   "<script type='text/javascript'>window.onload = callRPC('".urlencode($urlToSort.'&sort=customers_rating&direction=ASC')."');</script><a href='".$urlToSort."&sort=customers_rating&direction=ASC' >".STRING_ASC."</a>",	$sort_string );
			$sort_string = str_replace( "{DESC_RATING}",  "<script type='text/javascript'>window.onload = callRPC('".urlencode($urlToSort.'&sort=customers_rating&direction=DESC')."');</script><a href='".$urlToSort."&sort=customers_rating&direction=DESC' >".STRING_DESC."</a>",	$sort_string );
			$smarty->assign( "string_product_sort", $sort_string );
		}
		//get selected category info
		$category = catGetCategoryById( $categoryID );
		if ( !$category || $categoryID == 1)
		{
			header("Location: index.php");
		}
		else
		{
			IncrementCategoryViewedTimes($categoryID);
			if ( isset($_GET["prdID"]) )
			{
				if (  isset($_POST["cart_".$_GET["prdID"]."_x"])  )
				{
					$variants=array();
					foreach( $_POST as $key => $val )
					{
						if ( strstr($key, "option_select_hidden") )
						{
							$arr=explode( "_", str_replace("option_select_hidden_","",$key) );
							if ( (string)$arr[1] == (string)$_GET["prdID"] )
								$variants[]=$val;
						}
					}
					unset($_SESSION["variants"]);
					$_SESSION["variants"]=$variants;
					Redirect( "index.php?shopping_cart=yes&add2cart=".$_GET["prdID"] );
				}
			}
			//category thumbnail
			if (!file_exists("./products_pictures/".$category["picture"]))
					$category["picture"] = "";
			$smarty->assign("selected_category", $category );
			if ( $category["show_subcategories_products"] == 1 )
				$smarty->assign( "show_subcategories_products", 1 );
			if ( $category["allow_products_search"] )
				$smarty->assign( "allow_products_search", 1 );
			$callBackParam					= array();
			$products						= array();
			$callBackParam["categoryID"]	= $categoryID;
			$callBackParam["enabled"]		= 1;
			if (  isset($_GET["search_in_subcategory"]) )
				if ( $_GET["search_in_subcategory"] == 1 )
				{
					$callBackParam["searchInSubcategories"] = true;
					$callBackParam["searchInEnabledSubcategories"] = true;
				}
			if ( isset($_GET["sort"]) )
				$callBackParam["sort"] = $_GET["sort"];
			if ( isset($_GET["direction"]) )
				$callBackParam["direction"] = $_GET["direction"];
			if ( isset($_GET["brend"]) )
				$callBackParam["brend"] = $_GET["brend"];
			// search parametrs to advanced search
			if ( $extraParametrsTemplate != null )
					$callBackParam["extraParametrsTemplate"] = $extraParametrsTemplate;
			if ( $searchParamName != null )
					$callBackParam["name"] = $searchParamName;
			if ( $rangePrice != null )
					$callBackParam["price"] = $rangePrice;
			if ( $category["show_subcategories_products"] )
				$callBackParam["searchInSubcategories"] = true;
			$count = 0;
			$url_name = $category['url_name'];
			global $url_name;
			//echo $url_name;
			/*echo '<pre>';
				print_r($callBackParam);			
			echo '</pre>';
			echo '<pre>';
				print_r($offset);			
			echo '</pre>';
			echo '<pre>';
				print_r($count);			
			echo '</pre>';
			echo '<pre>';
				print_r(_getUrlToNavigate($categoryID));			
			echo '</pre>';*/
			$navigatorHtml = GetNavigatorHtml(_getUrlToNavigate($categoryID), CONF_PRODUCTS_PER_PAGE,'prdSearchProductByTemplate', $callBackParam,$products, $offset, $count );
			
			
			
			//,$products, $offset, $count
	/*		
echo '<pre>';
	print_r($products);
echo '</pre>';
exit();*/
/*
$m=0;$k=0;			
for($i=0; $i<count($products); $i++)
{
	
	if(($products[$i]['Price'] == '') || ($products[$i]['Price'] == 0))
	{	
		echo '<pre>';
		print_r($products[$i]);
	echo '</pre>';
		$products_null[$m++] = $products[$i++];
		
	
	}
	else 
	{
		$products_plus[$k++] = $products[$i++];
		
	}
	
}
exit();
if(isset($products_plus))
{
	$products = array_merge($products_plus, $products_null);
}
echo '<pre>';
	print_r($products);
echo '</pre>';*/
/*function compare($x, $y)
{
	if($x[10] == $y[10])
		return 0;
	else if($x[10] > $y[10])
		return -1;
	else 	
		return 1;
	
}
$products = usort($products, 'compare');
echo '<pre>';
	print_r($products);
echo '</pre>';*/

//exit();
			$show_comparison = $category["allow_products_comparison"];
			for($i=0; $i<count($products); $i++)
			{
				$products[$i]["allow_products_comparison"] = $show_comparison;
			}
			$producers = getProducersByCategory($categoryID);
			$brendsAll = GetProductBrends();
			/*echo '<pre>';
				print_r($brendsAll);
			echo '</pre>';*/
			
			$imgs_topsale = GetPicturesTOPSALE();
			$smarty->assign( "imgs_topsale", $imgs_topsale);
			if ( CONF_PRODUCT_SORT == '1' )
				_sortSetting( $smarty, _getUrlToSort($categoryID) );
			$smarty->assign( "subcategories_to_be_shown", catGetSubCategoriesSingleLayer($categoryID) );
			//calculate a path to the category
			$smarty->assign( "product_category_path", catCalculatePathToCategory($categoryID) );
			$smarty->assign( "show_comparison", $show_comparison );
			$smarty->assign( "catalog_navigator", $navigatorHtml );
			$smarty->assign( "cat_parent", $category['parent']);
			$smarty->assign( "title_one", $category['title_one']);
			$smarty->assign( "title_two", $category['title_two']);
			$smarty->assign( "name", $category['name']);
			/*echo '<pre>';
				print_r($products);
			echo '</pre>';*/
			/*echo '<pre>';
				print_r($category);
			echo '</pre>';*/
			if(isset($_GET["brend"]))
			{	$producer_cur = trim($_GET["brend"]);
				$smarty->assign( "producer_cur", $producer_cur);
			}
			$smarty->assign( "brendsAll", $brendsAll);
			$smarty->assign( "producers", $producers);
			$smarty->assign( "producers", $producers);
			$smarty->assign( "products_to_show", $products);
			$smarty->assign( "categoryID", $categoryID);
			$smarty->assign( "main_content_template", "category.tpl.html");
		}
	}
?>
