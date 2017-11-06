<?php
	// show whole price list

	function pricessCategories($parent,$level)
	{

		//same as processCategories(), except it creates a pricelist of the shop

		$out = array();
		$cnt = 0;

		$q1 = db_query("select categoryID, name, url_name from ".CATEGORIES_TABLE.
			" where parent=$parent order by sort_order, name") or die (db_error());
		while ($row = db_fetch_row($q1))
		{

			//define back color of the cell
			$r = hexdec(substr(CONF_MIDDLE_COLOR, 0, 2)); 
			$g = hexdec(substr(CONF_MIDDLE_COLOR, 2, 2)); 
			$b = hexdec(substr(CONF_MIDDLE_COLOR, 4, 2)); 
			$m = (float)max($r, max($g,$b));
			$r = round((190+20*min($level,3))*$r/$m);
			$g = round((190+20*min($level,3))*$g/$m);
			$b = round((190+20*min($level,3))*$b/$m);
			$c = dechex($r).dechex($g).dechex($b); //final color

			//add category to the output
			$out[$cnt][0] = $row[0];
			$out[$cnt][1] = $row[1];
			$out[$cnt][2] = $level;
			$out[$cnt][3] = $c;
			$out[$cnt][4] = 0; //0 is for category, 1 - product
			$out[$cnt][5] = $row[2];
			$out[$cnt][6] = $row[3];
			$cnt++;

			if ( !isset($_GET["sort"]) )
				$order_clause = "order by sort_order";
			else
			{
				//verify $_GET["sort"]
				switch ($_GET["sort"]){
					default:
						$_GET["sort"] = "name";
					case 'name':
					case 'Price':
					case 'customers_rating':
						break;
				}

				$order_clause = " order by ".$_GET["sort"];
				if ( isset($_GET["direction"]) )
				{
					if ( !strcmp( $_GET["direction"] , "DESC" ) )
						$order_clause .= " DESC ";
					else
						$order_clause .= " ASC ";
				}
			}

			$sql = "
				select productID, name, Price, in_stock, url_name,title_one, producer from ".PRODUCTS_TABLE.
				" where categoryID=".$row[0]." and Price>0 and enabled=1 ".
				$order_clause."
			";
			//add products
			$q = db_query( $sql ) or die (db_error());
			while ($row1 = db_fetch_row($q))
			{
				if ($row1[2] <= 0)
					$row1[2]= "n/a";
				else
					$row1[2] = show_price($row1[2]);

				//add product to the output
				$out[$cnt][0] = $row1[0];
				$out[$cnt][1] = $row1[1];
				$out[$cnt][2] = $level;
				$out[$cnt][3] = "FFFFFF";
				$out[$cnt][4] = 1; //0 is for category, 1 - product
				$out[$cnt][5] = $row1[2];
				$out[$cnt][6] = $row1[3];
				$out[$cnt][7] = $row1[4];
				$out[$cnt][8] = $row1[5];
				$out[$cnt][9] = $row1[6];
				$cnt++;
			}

			//process all subcategories
			$sub_out = pricessCategories($row[0], $level+1);

			//add $sub_out to the end of $out
			for ($j=0; $j<count($sub_out); $j++)
			{
				$out[] = $sub_out[$j];
				$cnt++;
			}
 		}

		return $out;

	} //pricessCategories

	function _sortPriceListSetting( &$smarty, $urlToSort )
	{
		$sort_string = STRING_PRICELIST_ITEM_SORT;
		$sort_string = str_replace( "{ASC_NAME}",   
			"<a href='".$urlToSort."&sort=name&direction=ASC'>".STRING_ASC."</a>",	$sort_string );
		$sort_string = str_replace( "{DESC_NAME}",  
			"<a href='".$urlToSort."&sort=name&direction=DESC'>".STRING_DESC."</a>",	$sort_string );
		$sort_string = str_replace( "{ASC_PRICE}",   
			"<a href='".$urlToSort."&sort=Price&direction=ASC'>".STRING_ASC."</a>",	$sort_string );
		$sort_string = str_replace( "{DESC_PRICE}",  
			"<a href='".$urlToSort."&sort=Price&direction=DESC'>".STRING_DESC."</a>",	$sort_string );
		$smarty->assign( "string_product_sort", $sort_string );
	}

	if (isset($_GET["show_price"])) //show pricelist
	{
		_sortPriceListSetting( $smarty, "index.php?show_price=yes" );

		$pricelist_elements = pricessCategories(1, 0);
		/*echo '<pre>';
			print_r($pricelist_elements);
		echo '</pre>';*/
		$smarty->assign("pricelist_elements", $pricelist_elements);
		$smarty->assign("main_content_template", "pricelist.tpl.html");
	}

?>