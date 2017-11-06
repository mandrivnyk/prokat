<?php
	//экспорт товаро в Яндекс.Маркет и другие аналогичные системы

	if (!strcmp($sub, "yandex"))
	{

			require_once('./core_functions/export_products_function.php');
			function _exportToYandexMarket( $f, $rate, $export_product_name )
			{
				$spArray = array(
					'exprtUNIC'=>array(
						'mode' 				=>'toarrays',
						'expProducts' 		=>array()
						)
					);
				$exportCategories = array(array(),array());
				export_exportSubcategories(0, $exportCategories, $spArray);
				_exportBegin( $f );
				_exportAllCategories( $f, $spArray['exprtUNIC']['expProducts'] );
				_exportProducts( $f, $rate, $export_product_name, $spArray['exprtUNIC']['expProducts'] );
				_exportEnd( $f );
			}


			function _deleteHTML_Elements( $str )
			{
				$str = str_replace( "<","&lt;",		$str );
                $str = str_replace( ">","&gt;",		$str );
                $str = str_replace( "&","&amp;",	$str );
                $str = str_replace( "\"","&quot;",	$str );
                $str = str_replace( "'","&apos;",	$str );
				return $str;
			}

			function _exportBegin( $f )
			{
				fputs( $f, "<?xml version=\"1.0\" encoding=\"windows-1251\"?>\n" );
				fputs( $f, "	<!DOCTYPE yml_catalog SYSTEM \"shops.dtd\">\n" );
				fputs( $f, "		<yml_catalog date=\"".date("Y-m-d H:i")."\">\n" );
				fputs( $f, "			<shop>\n" );
				fputs( $f, "				<name>"._deleteHTML_Elements(CONF_SHOP_NAME)."</name>\n");
				fputs( $f, "				<company>"._deleteHTML_Elements(CONF_SHOP_NAME)."</company>\n");
                fputs( $f, "				<url>".correct_URL(CONF_FULL_SHOP_URL)."</url>\n");
				fputs( $f, "				<currencies>\n");
                fputs( $f, "					<currency id=\"RUR\" rate=\"1\"/>\n");
                fputs( $f, "				</currencies>\n");
			}


			function _exportAllCategories( $f, &$_ProductIDs )
			{
				if(!count($_ProductIDs))return 0;
				$Cats = array();
				$execCats = array();
				$sql = "
					SELECT catt.categoryID, catt.name, catt.parent FROM ".CATEGORIES_TABLE." as catt 
					LEFT JOIN ".PRODUCTS_TABLE." as prot ON catt.categoryID=prot.categoryID 
					WHERE prot.productID IN (".implode(", ", $_ProductIDs).")
					GROUP BY prot.categoryID
				";
				$q = db_query($sql);
				fputs($f,"				<categories>\n");
                while ($row = db_fetch_row($q))
				{
					if(!in_array($row[0], $execCats)){
						
						$execCats[] = $row[0];
					}
					if(!in_array($row[2], $Cats) && $row[2]>1){
						
						$Cats[] = $row[2];
					}
					$row[1] = _deleteHTML_Elements( $row[1] );
                    if ($row[2] <= 1)
					{
						fputs($f,"					<category id=\"".$row[0]."\">".$row[1].
								"</category>\n");
					}
                    else 
					{
						fputs($f,"					<category id=\"".$row[0]."\" parentId=\"".$row[2]."\">".$row[1]."</category>\n");
					}
				}
				
				while (count($Cats)) {
					
					$sql = "
						SELECT categoryID, name, parent FROM ".CATEGORIES_TABLE." WHERE categoryID IN (".implode(", ", $Cats).")
						";
					$q = db_query($sql);
					$Cats = array();
	                while ($row = db_fetch_row($q))
					{
						$Disp = false;
						if(!in_array($row[0], $execCats)){
							
							$execCats[] = $row[0];
							$Disp = true;
						}
						if( !in_array($row[2], $execCats) && !in_array($row[2], $Cats) && $row[2]>1){
							
							$Cats[] = $row[2];
						}
						$row[1] = _deleteHTML_Elements( $row[1] );
	                    if ($row[2] <= 1 && $Disp)
						{
							fputs($f,"					<category id=\"".$row[0]."\">".$row[1].
									"</category>\n");
						}
	                    elseif($Disp) 
						{
							fputs($f,"					<category id=\"".$row[0]."\" parentId=\"".$row[2]."\">".$row[1]."</category>\n");
						}
					}
				}
				
                fputs($f,"				</categories>\n");		
			}


			function _exportProducts( $f, $rate, $export_product_name, &$_ProductIDs )
			{

				fputs( $f, "				<offers>\n");

				//товары с нулевым остатком на складе
				if (isset($_POST["yandex_dont_export_negative_stock"]))
					$clause = " and in_stock>0";
				else
					$clause = "";

				//какое описание экспортировать
				if ($_POST["yandex_export_description"] == 1)
				{
					$dsc = "description";
					$dsc_q = ", ".$dsc;
				}
				else if ($_POST["yandex_export_description"] == 2)
				{
					$dsc = "brief_description";
					$dsc_q = ", ".$dsc;
				}
				else
				{
					$dsc = "";
					$dsc_q = "";
				}

				//выбрать товары
				$proCount = count($_ProductIDs);
				$iter = 0;
				for (; $iter<$proCount;$iter+=100){
					
				$sql = "select productID, name, Price, categoryID, default_picture".$dsc_q.", in_stock from ".PRODUCTS_TABLE." 
					where ".(count($_ProductIDs)?"productID IN(".implode(", ", array_slice($_ProductIDs, $iter, 100)).") AND ":"")."enabled=1".$clause;
				$q = db_query($sql);

				$store_url = correct_URL(CONF_FULL_SHOP_URL);

				while ($product = db_fetch_row($q))
				{

					fputs( $f, "					<offer available=\"".(($product['in_stock'] || !CONF_CHECKSTOCK)?'true':'false')."\" id=\"".$product["productID"]."\">\n");
					fputs( $f, "						<url>".$store_url."index.php?productID=".$product["productID"]."&amp;from=ya</url>\n" );
					fputs( $f, "						<price>".RoundFloatValueStr($product["Price"]*$rate)."</price>\n" );
					fputs( $f, "						<currencyId>RUR</currencyId>\n" );
					fputs( $f, "						<categoryId>".$product["categoryID"]."</categoryId>\n" );

					if ($product["default_picture"] != NULL)
					{
						$pic_clause = " and photoID=".((int)$product["default_picture"]);
					}
					else
						$pic_clause = "";

					$q1 = db_query("select filename, thumbnail from ".PRODUCT_PICTURES." where productID=".$product["productID"] . $pic_clause);
					$pic_row = db_fetch_row($q1);

					if ($pic_row) //экспортировать фотографию
					{
						if ( strlen($pic_row["filename"]) && file_exists("./products_pictures/".$pic_row["filename"]) )
							fputs( $f, "						<picture>".$store_url.
								"products_pictures/".str_replace(' ', '%20',_deleteHTML_Elements($pic_row["filename"]))."</picture>\n" );
						else
							if ( strlen($pic_row["thumbnail"]) && file_exists("./products_pictures/".$pic_row["thumbnail"]) )
								fputs( $f, "						<picture>".$store_url.
									"products_pictures/".str_replace(' ', '%20',_deleteHTML_Elements($pic_row["thumbnail"]))."</picture>\n" );

					}


					switch ($export_product_name){
						default:
						case 'only_name':
							$_NameAddi = '';
							break;
						case 'path_and_name':
							$_NameAddi = '';
							$_t = catCalculatePathToCategory( $product['categoryID'] );
							foreach ($_t as $__t)
								if($__t['categoryID']!=1)
									$_NameAddi .= $__t['name'].':';
							break;
					}
					$product["name"]		= $_NameAddi._deleteHTML_Elements( $product["name"] );
					
					fputs( $f, "						<name>".$product["name"]."</name>\n" );

					if ( strlen($dsc)>0 )
					{
						$product[$dsc] = strip_tags( $product[$dsc] );
						$product[$dsc] = _deleteHTML_Elements( $product[$dsc] );
						fputs( $f, "						<description>".$product[ $dsc ].
														"</description>\n" );
					}
					else
					{
						fputs( $f, "						<description></description>\n" );
					}

					fputs( $f, "					</offer>\n");

				}
				
				}
				fputs( $f, "				</offers>\n");
			}

			function _exportEnd( $f )
			{
				fputs( $f, "			</shop>\n" );
				fputs( $f, "		</yml_catalog>\n" );
			}


		if (isset($_GET["yandex_export_successful"])) //show successful save confirmation message
		{
			if (file_exists("./temp/yandex.xml"))
			{
				$smarty->assign("yandex_export_successful", 1);
				$smarty->assign("yandex_filesize", (string) round( filesize("./temp/yandex.xml") / 1024 ) );
			}
		}

		if (!isset($_POST["yandex_export"]))$_POST["yandex_export"] = '';
		if ($_POST["yandex_export"]) //save payment gateways_settings
		{
			$rurrate = (float)$_POST["yandex_rur_rate"];
			$yandex_export_product_name = isset($_POST['yandex_export_product_name'])?$_POST['yandex_export_product_name']:'only_name';
			
			if ($rurrate <= 0)
			{
				$smarty->assign( "yandex_errormsg", "Курс рубля указан неверно. Пожалуйста, вводите положительное число" );
			}
			else //экспортировать товары
			{
				$f = @fopen("./temp/yandex.xml","w");
				if ($f)
				{
				
					_exportToYandexMarket( $f, $rurrate, $yandex_export_product_name );
					fclose($f);
					header("Location: admin.php?dpt=modules&sub=yandex&yandex_export_successful=yes");
				}
				else
				{
					$smarty->assign( "yandex_errormsg", "Ошибка при создании файла yandex.xml" );
				}
			}
		}

		require('./includes/admin/sub/modules.export_products.php');

		$smarty->assign("admin_sub_dpt", "modules_yandex.tpl.html");
	}

?>