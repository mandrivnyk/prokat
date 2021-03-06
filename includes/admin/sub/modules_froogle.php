<?php
	require_once('./core_functions/export_products_function.php');
	//export products to Froogle

	if (!strcmp($sub, "froogle"))
	{

			function _exportToFroogle( $f, $rate )
			{
				_exportHeader( $f );
				_exportProducts( $f, $rate );
			}

			function _deleteInvalid_Elements( $str )
			{
				$str = str_replace( "\t"," ", $str );
				$str = str_replace( "\r"," ", $str );
				$str = str_replace( "\n"," ", $str );
				return $str;
			}

			function _exportHeader( $f )
			{
				fputs( $f, "link\ttitle\tdescription\timage_link\tprice\tid\tquantity\tcurrency\n" );
			}

			function _exportProducts( $f, $rate )
			{

				//which description should be exported?
				if ($_POST["froogle_export_description"] == 1)
				{
					$dsc = "description";
				}
				else if ($_POST["froogle_export_description"] == 2)
				{
					$dsc = "brief_description";
				}
				else
				{
					$dsc = "meta_description";
				}

				//export all active products

				function __exportProduct( $ProductID, $params){
					
					$f 		= $params['f'];
					$rate 	= $params['rate'];
					$dsc 	= $params['dsc'];
					
					$store_url = correct_URL(CONF_FULL_SHOP_URL);
					$q = db_query("select productID, name, Price, categoryID, default_picture, in_stock, ".$dsc.", shipping_freight from ".PRODUCTS_TABLE." where productID='{$ProductID}'");
					$product = db_fetch_row($q);
					//format data
					$rate = (float)$rate;
					if ($rate <= 0) $rate = 1;
					$product["name"] = _deleteInvalid_Elements( $product["name"] );
					$product[$dsc] = _deleteInvalid_Elements( $product[$dsc] );
					$product["Price"] = RoundFloatValue( $product["Price"] * $rate );
					//$product["shipping_freight"] = RoundFloatValue( $product["shipping_freight"] * $rate );
					if (CONF_CHECKSTOCK)
						$instock = ($product["in_stock"] > 0 ) ? $product["in_stock"] : 0;
					else
						$instock = "";

					//create categories string
					$category = "";
					$cpath = catCalculatePathToCategory($product["categoryID"]);
					if ($cpath)
					{
						for ($i=1;$i<count($cpath)-1;$i++) $category .= $cpath[$i]["name"]." > ";
						if (count($cpath) > 1)
							$category .= $cpath[ count($cpath)-1 ]["name"];
					}

					//export product picture
					if ($product["default_picture"] != NULL)
					{
						$pic_clause = " and photoID=".((int)$product["default_picture"]);
					}
					else
						$pic_clause = "";

					$q1 = db_query("select filename, thumbnail from ".PRODUCT_PICTURES." where productID=".$product["productID"] . $pic_clause);
					$pic_row = db_fetch_row($q1);
					$pic = "";
					if ($pic_row) //export picture as well
					{
						if ( strlen($pic_row["filename"]) && file_exists("./products_pictures/".$pic_row["filename"]) )
							$pic = $store_url."products_pictures/"._deleteInvalid_Elements($pic_row["filename"]);
						else
							if ( strlen($pic_row["thumbnail"]) && file_exists("./products_pictures/".$pic_row["thumbnail"]) )
								$pic = $store_url."products_pictures/"._deleteInvalid_Elements($pic_row["thumbnail"]);
					}


					fputs( $f, $store_url."index.php?productID=".$product["productID"]."\t"
								.$product["name"]."\t"
								.strip_tags($product[$dsc])."\t"
								.$pic."\t"
								.$product["Price"]."\t"
								.$product["productID"]."\t"
								.$instock."\t"
								."usd\n" );

				}

				$exportCategories = array(array(),array());
				
				
				$_spArray = array('f'=>$f, 'rate'=>$rate, 'dsc'=>$dsc, 'exprtUNIC'=>array('mode'=>'simple'));
				export_exportSubcategories(0, $exportCategories, $_spArray);
			}

			

		if (isset($_GET["froogle_export_successful"])) //show successful save confirmation message
		{
			if (file_exists("./temp/froogle.txt"))
			{
				$getFileParam = cryptFileParamCrypt( "GetFroogleFeed", null );
				$smarty->assign( "getFileParam", $getFileParam );

				$smarty->assign("froogle_export_successful", 1);
				$smarty->assign("froogle_filesize", (string) round( filesize("./temp/froogle.txt") / 1024 ) );
			}
		}

		if (isset($_POST["froogle_export"])) //export products
		if ($_POST["froogle_export"])
		{
			$currency = currGetCurrencyByID ( (int)$_POST["froogle_currency"] );

			if (!$currency)
			{
				$smarty->assign( "froogle_errormsg", ERROR_FROOGLE_CURRENCY_TYPE );
			}
			else //do export
			{
				$f = @fopen("./temp/froogle.txt","w");
				if ($f)
				{
					_exportToFroogle( $f, $currency["currency_value"] );
					fclose($f);
					header("Location: admin.php?dpt=modules&sub=froogle&froogle_export_successful=yes");
				}
				else
				{
					$smarty->assign( "froogle_errormsg", ERROR_FROOGLE_FILE_CREATION );
				}
			}
		}

		require('./includes/admin/sub/modules.export_products.php');
		$currencies = currGetAllCurrencies();
		$smarty->assign("currencies", $currencies);

		//set sub-department template
		$smarty->assign("admin_sub_dpt", "modules_froogle.tpl.html");
	}

?>