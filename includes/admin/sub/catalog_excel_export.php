<?php
	function _exportCategoryLine($categoryID, $level, &$f, $delimiter = ";") //writes a category line into CSV file.
	{
		global $picture_columns_count;
		global $extra_columns_count;

		$q = db_query("select categoryID, name, description, sort_order, picture, meta_keywords, meta_description from ".
			CATEGORIES_TABLE.
			" where categoryID=$categoryID");
		$cat_data = db_fetch_row($q);
		if (!$cat_data) return;

		$lev = "";
		for ($i=0;$i<$level;$i++) $lev .= "!";
		$cat_data["name"] = $lev.$cat_data["name"];

		foreach ($cat_data as $key => $val)
			if (strstr($val,"\"") || strstr($val,"\n") || strstr($val,$delimiter))
			{
				$cat_data[$key] = '"'.str_replace('"','""', str_replace("\r\n"," ",$val) ).'"';
			}
		
		fputs($f,
			$cat_data["sort_order"].$delimiter.
			$delimiter.
			$cat_data["name"].$delimiter.
			$cat_data["description"].$delimiter.
			$delimiter.
			$delimiter.
			$delimiter.
			$delimiter.
			$delimiter.
			$cat_data["meta_keywords"].$delimiter.
			$cat_data["meta_description"].$delimiter.
			$delimiter.
			$delimiter.
			$delimiter.
			$delimiter.
			$delimiter.
			$delimiter.
			$delimiter.
			$delimiter.
			$cat_data["picture"]
		);

		for ($i=1;$i<$picture_columns_count+$extra_columns_count;$i++)
		{
			fputs($f,$delimiter);
		}
		fputs($f,"\n");
	}

	function _exportProducts($categoryID, &$f, $delimiter = ";") //writes all products inside a single category to a CSV file
	{
		global $picture_columns_count;
		global $extra_columns_count;

		//products
		$q1 = db_query("select sort_order, product_code, name, description, brief_description, Price, list_price, in_stock, items_sold, meta_keywords, meta_description, shipping_freight, weight, free_shipping, min_order_amount".
		", eproduct_filename, eproduct_available_days, eproduct_download_times".
		", default_picture, productID, classID from ".
			PRODUCTS_TABLE." where categoryID=".$categoryID." ORDER BY sort_order, name ") or die (db_error());
		while ($row1 = db_fetch_row($q1))
		{
			foreach ($row1 as $key => $val)
			{
				if (strstr($val,"\"") || strstr($val,"\n") || strstr($val,$delimiter) ||
					!strcmp($key,"description") || !strcmp($key,"brief_description"))
				{//var_dump($val);
					$row1[$key] = '"'.str_replace('"','""',str_replace("\r\n"," ",$val)).'"';
				}
				if (!strcmp($key,"Price") || !strcmp($key,"list_price"))
				{
					$val = round(100*$val)/100;
					if (round($val*10) == $val*10 && round($val)!=$val) 
						$val = (string)$val."0"; //to avoid prices like 17.5 - write 17.50 instead
					$row1[$key] = $val;
				}
			}

			if ($row1["classID"])
			{
				$tax = taxGetTaxClassById( $row1["classID"] );
				$tax = $tax["name"];
			}
			else
			{
				$tax = "";
			}

			//write primary product information
			fputs($f,
				$row1["sort_order"].$delimiter.
				$row1["product_code"].$delimiter.
				$row1["name"].$delimiter.
				$row1["description"].$delimiter.
				$row1["brief_description"].$delimiter.
				$row1["Price"].$delimiter.
				$row1["list_price"].$delimiter.
				$row1["in_stock"].$delimiter.
				$row1["items_sold"].$delimiter.
				$row1["meta_keywords"].$delimiter.
				$row1["meta_description"].$delimiter.
				$row1["shipping_freight"].$delimiter.
				$row1["weight"].$delimiter.
				$row1["free_shipping"].$delimiter.
				$row1["min_order_amount"].
				$delimiter.$row1["eproduct_filename"].
				$delimiter.$row1["eproduct_available_days"].
				$delimiter.$row1["eproduct_download_times"].
				$delimiter.$tax.
				''
			);

			//pictures
			//at first, fetch default picture
			$cnt = 0;
			if (!$row1["default_picture"]) $row1["default_picture"] = 0; //no default picture defined;
			$qp = db_query("select filename, thumbnail, enlarged from ".PRODUCT_PICTURES." where productID=".$row1["productID"]." and photoID=".$row1["default_picture"]);
			$rowp = db_fetch_row($qp);
			$s = "";
			if ($rowp)
			{
				if ($rowp[0]) $s .= $rowp[0];
				if ($rowp[1]) $s .= ",".$rowp[1];
				if ($rowp[2]) $s .= ",".$rowp[2];
			}
			if (strstr($s,"\"") || strstr($s,"\n") || strstr($s,$delimiter))
			{
				$s = '"'.str_replace('"','""',str_replace("\r\n"," ",$s)).'"';
			}
			fputs($f,$delimiter.$s);
			$cnt++;
			//the rest of the photos
			$qp = db_query("select filename, thumbnail, enlarged from ".PRODUCT_PICTURES." where productID=".$row1["productID"]." and photoID <> ".$row1["default_picture"]);
			while ($rowp = db_fetch_row($qp))
			{
				$s = "";
				if ($rowp)
				{
					if ($rowp[0]) $s .= $rowp[0];
					if ($rowp[1]) $s .= ",".$rowp[1];
					if ($rowp[2]) $s .= ",".$rowp[2];
				}
				if (strstr($s,"\"") || strstr($s,"\n") || strstr($s,$delimiter))
				{
					$s = '"'.str_replace('"','""',str_replace("\r\n"," ",$s)).'"';
				}
				fputs($f,$delimiter.$s);
				$cnt++;
			}
			if ($cnt < $picture_columns_count)
				for ($i=$cnt; $i<$picture_columns_count; $i++) fputs($f,$delimiter);


			//extra options
			$q2 = db_query("select optionID from ".PRODUCT_OPTIONS_TABLE." ORDER BY sort_order, name ");
			//browse options list
			while ($row2 = db_fetch_row($q2))
			{
				//browser all option values of current product
				$q3 = db_query("select option_value, option_type, variantID from ".PRODUCT_OPTIONS_VALUES_TABLE." where productID=".$row1['productID']." and optionID=$row2[0]");
				$row3 = db_fetch_row($q3);
				if (!$row3) $row3 = array("",0,0);
				
				if ((int)$row3[1] == 1) //selectable option - prepare a string to insert into a CSV file, e.g {red=3,blue=1,white}
				{
					if (!$row3[2]) $row3[2] = 0;
					//prepare an array of available option variantIDs. the first element (0'th) is the default varinatID
					$available_variants = array( array($row3[2],0) );

					$q4 = db_query( "select variantID, price_surplus from ".PRODUCTS_OPTIONS_SET_TABLE." where productID=".$row1['productID']." and optionID=".$row2[0] );
					while ($row4 = db_fetch_row($q4))
					{
						if ($row4[0] == $row3[2]) //is it a default variantID
						{
							$available_variants[0] = $row4;
						}
						else
							$available_variants[] = $row4; //add this value to array
					}
					//now write all variants
					$s = "{";
					$tmp = "";

					foreach ($available_variants as $key => $val)
						if ($val[0])
						{
							$qvar = db_query("select option_value from ".PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE." where optionID=".$row2[0]." and variantID=".$val[0]);
							$rowvar = db_fetch_row($qvar);
							$s .= $tmp;
							$s .= $rowvar[0]."";
							if ($val[1]) $s .= "=".$val[1];
							$tmp = ",";
						}
					$s .= "}";

					$row3[0] = $s;
				}

				//write a string into CSV file
				if (strstr($row3[0],"\"") || strstr($row3[0],"\n") || strstr($row3[0],$delimiter))
				{
					$row3[0] = '"'.str_replace('"','""',str_replace("\r\n"," ",$row3[0])).'"';
				}
				fputs($f, $delimiter."$row3[0]");
			}
			fputs($f,"\n");
		}
	}

	function _exportSubCategoriesAndProducts($parent, $level, &$f, $delimiter=";") //exports products and subcategories of $parent to a CSV file $f
		//a recurrent function
	{
		$cnt = 0;
		$q = db_query("select categoryID from ".
			CATEGORIES_TABLE.
			" where parent=$parent order by sort_order, name");

		//fetch all subcategories
		while ($row = db_fetch_row($q))
		{
				_exportCategoryLine($row[0], $level, $f, $delimiter);
				_exportProducts($row[0], $f, $delimiter);
				
				//process all subcategories
				_exportSubCategoriesAndProducts($row[0], $level+1, $f, $delimiter);
		}

	} //_exportSubCategoriesAndProducts




	//products and categories catalog import from MS Excel .CSV files
	if (!strcmp($sub, "excel_export"))
	{

		if (isset($_POST["excel_export"])) //export products
		{
			@set_time_limit(0);

			if ($_POST["delimiter"]==";" || $_POST["delimiter"]=="," || $_POST["delimiter"]=="\t")
			{
				$delimiter = $_POST["delimiter"];
			}
			else
			{
				$delimiter = ";";
			}
			$f = fopen("./temp/catalog.csv","w");

			//write a header line
			fputs($f,
				ADMIN_SORT_ORDER.$delimiter.
				ADMIN_PRODUCT_CODE.$delimiter.
				ADMIN_PRODUCT_NAME.$delimiter.
				ADMIN_PRODUCT_DESC.$delimiter.
				ADMIN_PRODUCT_BRIEF_DESC.$delimiter.
				ADMIN_PRODUCT_PRICE.$delimiter.
				ADMIN_PRODUCT_LISTPRICE.$delimiter.
				ADMIN_PRODUCT_INSTOCK.$delimiter.
				ADMIN_PRODUCT_SOLD.$delimiter.
				ADMIN_META_KEYWORDS.$delimiter.
				ADMIN_META_DESCRIPTION.$delimiter.
				ADMIN_SHIPPING_FREIGHT.$delimiter.
				ADMIN_PRODUCT_WEIGHT.$delimiter.
				ADMIN_FREE_SHIPPING2.$delimiter.
				ADMIN_MIN_ORDER_AMOUNT.$delimiter.
				ADMIN_EPRODUCT_FILENAME.$delimiter.
				ADMIN_EPRODUCT_AVAILABLE_DAYS2.$delimiter.
				ADMIN_EPRODUCT_DOWNLOAD_TIMES.$delimiter.
				STRING_TAX.$delimiter.
				ADMIN_PRODUCT_PICTURE
			);

			//calculate the number of 'Picture' columns
			$max = 1;
			$q = db_query("select productID from ".PRODUCT_PICTURES." order by productID");
			$max = 0;
			$currID = 0;
			$result = array();
			while ($row = db_fetch_row($q))
			{
				if ($currID != $row[0]) $cnt = 0;
				$cnt++;
				$currID = $row[0];

				if ($max < $cnt) $max = $cnt;
			}
			//record as many PICTURE columns in the file as located in the database
			for ($i=1;$i<$max;$i++)
			{
				fputs($f, $delimiter.ADMIN_PRODUCT_PICTURE);
			}
			$picture_columns_count = $max;

			//extra parameters columns
			$q = db_query("select name from ".PRODUCT_OPTIONS_TABLE." ORDER BY sort_order, name ");
			$cnt = 0;
			while ($row = db_fetch_row($q))
			{
				if (strstr($row[0],"\"") || strstr($row[0],"\n"))
				{
					$row[0] = '"'.str_replace('"','""',$row[0]).'"';
				}
				fputs($f, $delimiter."$row[0]");
				$cnt++;
			}
			$extra_columns_count = $cnt;

			fputs($f,"\n");

			//export selected products and categories
			//root
			if (isset($_POST["categ_1"]))
			{
				_exportProducts(1, $f, $delimiter);
			}
			//other categories
			$q = db_query("select categoryID, name from ".CATEGORIES_TABLE." where parent=1 order by sort_order, name");
			$result = array();
			while ($row = db_fetch_row($q))
				if (isset($_POST["categ_$row[0]"]))
				{
					_exportCategoryLine($row[0], 0, $f, $delimiter);
					_exportProducts($row[0], $f, $delimiter);
					_exportSubCategoriesAndProducts($row[0], 1, $f, $delimiter);
				}

			header("Location:admin.php?dpt=catalog&sub=excel_export&export_completed=yes");
			fclose($f);
		}

		if (isset($_GET["export_completed"])) //show successful save confirmation message
		{
			if (file_exists("./temp/catalog.csv"))
			{
				$getFileParam = cryptFileParamCrypt( "GetCSVCatalog", null );
				$smarty->assign( "getFileParam", $getFileParam );

				$smarty->assign("excel_export_successful", 1);
				$smarty->assign("excel_filesize", (string) round( filesize("./temp/catalog.csv") / 1024 ) );
			}
		}
		else //prepare categories list
		{
			$q = db_query("select categoryID, name from ".CATEGORIES_TABLE." where parent=1 order by sort_order, name");
			$result = array();
			while ($row = db_fetch_row($q)) $result[] = $row;
			$smarty->assign("categories",$result);
		}

		$smarty->assign("admin_sub_dpt", "catalog_excel_export.tpl.html");
	}
?>