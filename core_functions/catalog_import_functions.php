<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
// *****************************************************************************
// Purpose	gets conventional picture filename
// Inputs     	string "<picture>,<thumbnail>,<big_picture>"
// Remarks
// Returns	
function _getPictureFilename( $stringToParse )
{
	$files=explode(",",$stringToParse);
	if ( count($files) >= 1 )
	 	return trim($files[0]);
	else
		return "";
}


// *****************************************************************************
// Purpose	gets thumbnail picture filename
// Inputs     	string "<picture>,<thumbnail>,<big_picture>"
// Remarks
// Returns	
function _getPictureThumbnail( $stringToParse )
{
	$files=explode(",",$stringToParse);
	if ( count($files) >= 2 )
	 	return trim($files[1]);
	else
		return "";
}

// *****************************************************************************
// Purpose	gets big picture filename
// Inputs     	string "<picture>,<thumbnail>,<big_picture>"
// Remarks
// Returns	
function _getPictureBigPicture( $stringToParse )
{
	$files=explode(",",$stringToParse);
	if ( count($files) >= 3 )
	 	return trim($files[2]);
	else
		return "";
}


// *****************************************************************************
// Purpose	insert pictures 
// Inputs     	
//		$stringToParse string has formats "<picture>,<thumbnail>,<big_picture>"
//		$productID - product ID
// Remarks
// Returns	
function _insertPictures( $stringToParse, $productID )
{
	// get filename
	$filename = _getPictureFilename( $stringToParse );

	// get thumbnail
	$thumbnail = _getPictureThumbnail( $stringToParse );

    // get big_picture
	$big_picture = _getPictureBigPicture( $stringToParse );

	$filename		= TransformStringToDataBase($filename);
	$thumbnail		= TransformStringToDataBase($thumbnail);
	$big_picture	= TransformStringToDataBase($big_picture);

	if ( trim($filename)!="" || trim($thumbnail)!="" || trim($big_picture)!="" )
	{
		db_query("insert into ".PRODUCT_PICTURES.
				"(productID, filename, thumbnail, enlarged) ".
				"values( '".$productID."', ".
						" '".TransformStringToDataBase($filename)."', ".
						" '".TransformStringToDataBase($thumbnail)."', ".
						" '".TransformStringToDataBase($big_picture)."' )" );
	}
}

// *****************************************************************************
// Purpose	
// Inputs     	
//		$row - row from file to import
//		$dbc - array of column index, $dbc[<column_name>] -index of <column_name> column
// Remarks
// Returns	true if column value for current row is set
function _columnIsSet($row, $dbc, $column_name)
{
	if ( !strcmp($dbc[$column_name], "not defined") )
		return false;
	return ( trim($row[$dbc[$column_name]]) != "" );
}


// *****************************************************************************
// Purpose	
// Inputs     	
//		$row from file to import
// Remarks
// Returns	true if column value is set
function _isCategory($row, $dbc)
{
	if (   !strcmp($dbc["name"], "not defined")  )
		return false;
	if ( _columnIsSet($row, $dbc, "product_code") )
		return false;
	if ( _columnIsSet($row, $dbc, "Price") )
		return false;
	if ( _columnIsSet($row, $dbc, "in_stock") )
		return false;
	if ( _columnIsSet($row, $dbc, "list_price") )
		return false;
	if ( _columnIsSet($row, $dbc, "items_sold") )
		return false;
	if ( _columnIsSet($row, $dbc, "brief_description") )
		return false;
	return true;
}


// *****************************************************************************
// Purpose 	this function simulates fgetcsv, it works with russian 
//			characters correctly
// Inputs     	
// Remarks	see standart fgetcsv function description
// Returns	two dimension array, this array containes table by rows
//		for example, $data[5][2] five index row, two index column
function myfgetcsv($fname, $del)
{
	$filesize	= filesize( $fname );
	$f			= fopen( $fname, "r" );
	$res		= array();
	$firstFlag	= true;
	$columnCount = 0;
	while( $row = fgetcsv($f, $filesize, $del) )
	{
		if ( $firstFlag )
			$columnCount = count($row);
		$firstFlag = false;
		while( count($row) < $columnCount )
			$row[] = "";
		$res[] = $row;
	}
	fclose($f);
	return $res;

/*
	$content_file = str_replace( "'","`", implode ("", file($fname) ) );

	$data 	= array();
	$row	= array();
	$cell	= "";

	$stack  = array();
	for( $i=0; $i < strlen($content_file); $i++ )
	{
		if ( $content_file[$i] == "\"" )
		{
			$cell .= "\"";
			if ( count($stack) == 0 )
				$stack[0] = $content_file[$i];
			else
			{
				if ( strstr($stack[count($stack)-1], "\"") )
					unset( $stack[count($stack)-1] );
				else if ( strstr($stack[count($stack)-1], "\`")  )
					$stack[ count($stack) ] = "\`";
			}
		}
		else if ( $content_file[$i] == "\`" )
		{
			$cell .= "\`";
			if ( count($stack) == 0 )
				$stack[0] = $content_file[$i];
			else
			{
				if ( strstr($stack[count($stack)-1], "\`") )
					unset( $stack[count($stack)-1] );
				else if ( strstr($stack[count($stack)-1], "\"")  )
					$stack[ count($stack) ] = "\"";
			}			
		}
		else if ( (count($stack)==0) && ($content_file[$i]==";")  )
		{
			$row[] 	= $cell;
			$cell	= "";
		}
	 	else if ( (count($stack)==0) && ($content_file[$i]=="\n")  )
		{
			$row[] 	= $cell;
			$data[] = $row;
			$row	= array();
			$cell	= "";			
		}
		else
			$cell .= $content_file[$i];
	}
	return $data;
*/
}



// *****************************************************************************
// Purpose 	clears database content
// Inputs     	
// Remarks	
// Returns	nothing
function imDeleteAllProducts()
{
	db_query("UPDATE ".PRODUCT_OPTIONS_VALUES_TABLE." SET variantID=NULL");
	db_query("DELETE FROM ".PRODUCTS_OPTIONS_SET_TABLE);
	db_query("DELETE FROM ".PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE );
	db_query("DELETE FROM ".SHOPPING_CART_ITEMS_CONTENT_TABLE);
	db_query("DELETE FROM ".PRODUCT_OPTIONS_VALUES_TABLE);
	db_query("DELETE FROM ".PRODUCT_OPTIONS_TABLE);
	db_query("DELETE FROM ".RELATED_PRODUCTS_TABLE);
	db_query("DELETE FROM ".PRODUCT_PICTURES);
	db_query("DELETE FROM ".DISCUSSIONS_TABLE);
	db_query("DELETE FROM ".SPECIAL_OFFERS_TABLE);
	db_query("UPDATE ".SHOPPING_CART_ITEMS_TABLE." SET productID = NULL");
	db_query("DELETE FROM ".PRODUCTS_TABLE);
	db_query("DELETE FROM ".CATEGORIES_TABLE);
	db_query("INSERT INTO ".CATEGORIES_TABLE." (categoryID, name, parent) VALUES (1, 'ROOT', NULL);");
	db_query("DELETE FROM ".CATEGORIY_PRODUCT_TABLE);
}

// *****************************************************************************
// Purpose 	clears database content
// Inputs     	$data is returned by myfgetcsv ( see comment for this function )
// Remarks	
// Returns	import configurator html code
function imGetImportConfiguratorHtmlCode($data)
{
	//skip empty lines
	$i = 0;
	while ($i<count($data) && count($data[$i])>0 && 
		($n = get_NOTempty_elements_count($data[$i])) 
			< count($data[$i]))
	{
		$i++;
	}
	$notl = $i;


	// display all headers into a form that allows to 
	// assign each column a value into database
	$excel_configurator = "<table>";
	for ($j=0; $j<$n; $j++)
		if (isset($data[$i][$j]))
	  	{
         		$excel_configurator .= "
				<tr>
					<td><b><input type=text name=column_name_$j value=\"".str_replace("\"","&quot;",$data[$i][$j])."\"></b></td>
					<td>=></td>
					<td>
						<select name=db_association_$j>
							<option value=\"ignore\">".ADMIN_IGNORE."</option>
							<option value=\"add\">".ADMIN_ADD_AS_NEW_PARAMETER."</option>
							<option value=\"product_code\"".mark_as_selected($data[$i][$j],ADMIN_PRODUCT_CODE).">".ADMIN_PRODUCT_CODE."</option>
							<option value=\"name\"".mark_as_selected($data[$i][$j],ADMIN_PRODUCT_NAME).">".ADMIN_PRODUCT_NAME."</option>
							<option value=\"Price\"".mark_as_selected($data[$i][$j],ADMIN_PRODUCT_PRICE).">".ADMIN_PRODUCT_PRICE."</option>
							<option value=\"list_price\"".mark_as_selected($data[$i][$j],ADMIN_PRODUCT_LISTPRICE).">".ADMIN_PRODUCT_LISTPRICE."</option>
							<option value=\"in_stock\"".mark_as_selected($data[$i][$j],ADMIN_PRODUCT_INSTOCK).">".ADMIN_PRODUCT_INSTOCK."</option>
							<option value=\"items_sold\"".mark_as_selected($data[$i][$j],ADMIN_PRODUCT_SOLD).">".ADMIN_PRODUCT_SOLD."</option>
							<option value=\"description\"".mark_as_selected($data[$i][$j],ADMIN_PRODUCT_DESC).">".ADMIN_PRODUCT_DESC."</option>
							<option value=\"brief_description\"".mark_as_selected($data[$i][$j],ADMIN_PRODUCT_BRIEF_DESC).">".ADMIN_PRODUCT_BRIEF_DESC."</option>
							<option value=\"pictures\"".mark_as_selected($data[$i][$j],ADMIN_PRODUCT_PICTURES).">".ADMIN_PRODUCT_PICTURES."</option>
							<option value=\"sort_order\"".mark_as_selected($data[$i][$j],ADMIN_SORT_ORDER).">".ADMIN_SORT_ORDER."</option>
							<option value=\"meta_keywords\"".mark_as_selected($data[$i][$j],ADMIN_META_KEYWORDS).">".ADMIN_META_KEYWORDS."</option>
							<option value=\"meta_description\"".mark_as_selected($data[$i][$j],ADMIN_META_DESCRIPTION).">".ADMIN_META_DESCRIPTION."</option>
							<option value=\"shipping_freight\"".mark_as_selected($data[$i][$j],ADMIN_SHIPPING_FREIGHT).">".ADMIN_SHIPPING_FREIGHT."</option>
							<option value=\"weight\"".mark_as_selected($data[$i][$j],ADMIN_PRODUCT_WEIGHT).">".ADMIN_PRODUCT_WEIGHT."</option>
							<option value=\"free_shipping\"".mark_as_selected($data[$i][$j],ADMIN_FREE_SHIPPING2).">".ADMIN_FREE_SHIPPING2."</option>
							<option value=\"min_order_amount\"".mark_as_selected($data[$i][$j],ADMIN_MIN_ORDER_AMOUNT).">".ADMIN_MIN_ORDER_AMOUNT."</option>".
							"<option value=\"eproduct_filename\"".mark_as_selected($data[$i][$j],ADMIN_EPRODUCT_FILENAME).">".ADMIN_EPRODUCT_FILENAME."</option>
							<option value=\"eproduct_available_days\"".mark_as_selected($data[$i][$j],ADMIN_EPRODUCT_AVAILABLE_DAYS2).">".ADMIN_EPRODUCT_AVAILABLE_DAYS2."</option>
							<option value=\"eproduct_download_times\"".mark_as_selected($data[$i][$j],ADMIN_EPRODUCT_DOWNLOAD_TIMES).">".ADMIN_EPRODUCT_DOWNLOAD_TIMES."</option>
							<option value=\"tax\"".mark_as_selected($data[$i][$j],STRING_TAX).">".STRING_TAX."</option>".
						"</select>
					</td>
				</tr>";
		}
	$excel_configurator .= "</table>";
	return $excel_configurator;
}


// *****************************************************************************
// Purpose 	read db_association select control 
//			( see GetImportConfiguratorHtmlCode )
// Inputs     	
// Remarks	
// Returns	
function _readDb_associationSelectControl()
{
	$db_association = array(); // array select control values
	foreach( $_POST as $key => $val )
	{
		if (strstr($key, "db_association_"))
		{
			$i = str_replace("db_association_", "", $key);
			if ( $val != "pictures" )
				$db_association[$i] = $val;
		}
	}	
	return $db_association;
}

// *****************************************************************************
// Purpose 	get index select control set to "pictures" value
//			( see GetImportConfiguratorHtmlCode )
// Inputs     	
// Remarks	
// Returns	
function _getIndexArraySelectPictures()
{
	$dbcPhotos	= array(); // index array of "pictures"
	foreach( $_POST as $key => $val )
	{
		if (strstr($key, "db_association_"))
		{
			$i = str_replace("db_association_", "", $key);
			if ( $val == "pictures" )
			$dbcPhotos[] = $i;
		}
	}
	return $dbcPhotos;
}


// *****************************************************************************
// Purpose 	read column_name control 
//			( see GetImportConfiguratorHtmlCode )
// Inputs     	
// Remarks	
// Returns	 
function _readColumn_nameControl($dbcPhotos)
{

	$cname = array();
	foreach( $_POST as $key => $val )
	{
		if (strstr($key, "column_name_"))
		{
			$i = str_replace("column_name_", "", $key);
			$searchFlag = false;
			for(  $j=0; $j < count($dbcPhotos); $j ++ )
				if ($i == $dbcPhotos[$j])
					$searchFlag = true;

			if ( ! $searchFlag )
				$cname[$i] = $val;
		}
	}
	return $cname;
}


// *****************************************************************************
// Purpose 	now reverse -- create backwards 
// 			association table: db_column -> file_column
// Inputs     	
// Remarks	
// Returns	 
function _createBackwards( $db_association )
{
	$dbc = array(
		"name"						=> "not defined",
		"product_code"				=> "not defined",
		"Price"						=> "not defined",
		"in_stock"					=> "not defined",
		"list_price"				=> "not defined",
		"items_sold"				=> "not defined",
		"description"				=> "not defined",
		"brief_description"			=> "not defined",
		"sort_order"				=> "not defined",
		"meta_keywords"				=> "not defined",
		"meta_description"			=> "not defined",
		"shipping_freight"			=> "not defined",
		"weight"					=> "not defined",
		"free_shipping"				=> "not defined",
		"min_order_amount"			=> "not defined",
		"eproduct_filename"			=> "not defined",
		"eproduct_available_days"	=> "not defined",	
		"eproduct_download_times"	=> "not defined",
		"tax"						=> "not defined"
	);
	foreach( $db_association as $i => $value )
	{
		if ($value == "name") $dbc["name"] = $i;
		else if ($value == "product_code")				$dbc["product_code"] = $i;
		else if ($value == "Price")						$dbc["Price"] = $i;
		else if ($value == "in_stock")					$dbc["in_stock"] = $i;
		else if ($value == "list_price")				$dbc["list_price"] = $i;
		else if ($value == "items_sold")				$dbc["items_sold"] = $i;
		else if ($value == "description")				$dbc["description"] = $i;
		else if ($value == "brief_description")			$dbc["brief_description"] = $i;
		else if ($value == "pictures")					$dbc["pictures"] = $i;
		else if ($value == "sort_order")				$dbc["sort_order"] = $i;
		else if ($value == "meta_keywords" )			$dbc["meta_keywords"] = $i;
		else if ($value == "meta_description" )			$dbc["meta_description"] = $i; 
		else if ($value == "shipping_freight" )			$dbc["shipping_freight"] = $i;
		else if ($value == "weight" )					$dbc["weight"] = $i;
		else if ($value == "free_shipping" )			$dbc["free_shipping"] = $i;
		else if ($value == "min_order_amount" )			$dbc["min_order_amount"] = $i;
		else if ($value == "eproduct_filename" )		$dbc["eproduct_filename"] = $i;
		else if ($value == "eproduct_available_days" )	$dbc["eproduct_available_days"] = $i;
		else if ($value == "eproduct_download_times" )	$dbc["eproduct_download_times"] = $i;
		else if ($value == "tax" )						$dbc["tax"] = $i;
	}
	return $dbc;	
}


// *****************************************************************************
// Purpose 	add new product extra option
// Inputs     	
// Remarks	
// Returns	 
function _addExtraOptionToDb( $db_association, $cname )
{
	$updated_extra_option = array();
	for ($i=0; $i<count($cname); $i++) 
		$updated_extra_option[$i] = 0;

	foreach( $db_association as $i => $value )
	{
		if ($value == "add")
		{
			$q = db_query("select count(*) from ".PRODUCT_OPTIONS_TABLE.
				" where name LIKE '".TransformStringToDataBase($cname[$i])."'") or die (db_error());
			$row = db_fetch_row($q);
			if (!$row[0]) 	// no option exists => insert new
			{
				db_query("insert into ".PRODUCT_OPTIONS_TABLE.
					" (name) values ('".TransformStringToDataBase($cname[$i])."')") or die (db_error());
				$op_id = db_insert_id("PRODUCT_OPTIONS_GEN");
			}
			else 		// get current $id
			{
				$q = db_query("select optionID from ".PRODUCT_OPTIONS_TABLE.
					" where name LIKE '".TransformStringToDataBase($cname[$i])."'") or die (db_error());
				$op_id = db_fetch_row($q);
				$op_id = $op_id[0];
			}
			//update extra options list
			$updated_extra_option[$i] = $op_id;
		}
	}	
	return $updated_extra_option;
}


// *****************************************************************************
// Purpose 	
// Inputs     	
// Remarks	
// Returns	
function imReadImportConfiguratorSettings()
{//echo "<pre>";
	// read db_association select control ( see GetImportConfiguratorHtmlCode )
	$db_association = _readDb_associationSelectControl();
//var_dump($db_association);

	// get index select control set to "pictures" value ( see GetImportConfiguratorHtmlCode )
	$dbcPhotos	= _getIndexArraySelectPictures();
//var_dump($dbcPhotos);
	// read column_name input field ( see GetImportConfiguratorHtmlCode )
	$cname 		= _readColumn_nameControl( $dbcPhotos );
//echo "cname";	var_dump($cname);

	// now reverse -- create backwards association table: db_column -> file_column
	$dbc		= _createBackwards( $db_association );
//var_dump($dbc);
//var_dump($db_association);
//var_dump($cname);

	// add new extra option to database
	$updated_extra_option = _addExtraOptionToDb( $db_association, $cname );		

	$res = array();
	$res["db_association"] 		= $db_association;
	$res["dbcPhotos"]		= $dbcPhotos;
	$res["dbc"]			= $dbc;
	$res["updated_extra_option"]	= $updated_extra_option;
	return $res;
}


// *****************************************************************************
// Purpose 	import row to database
// Inputs     	
// Remarks	
// Returns	
function _importCategory( $row, $dbc, &$parents, $dbcPhotos, & $currentCategoryID )
{
	$sort_order = 0;
	if ( strcmp( $dbc["sort_order"], "not defined") )
		$sort_order = (int)$row[ $dbc["sort_order"] ];

	// set picture file name
	$picture_file_name="";
	if ( count($dbcPhotos) > 0 )
		$picture_file_name=trim($row[ $dbcPhotos[0] ]);

	// 
	$row[ "not defined" ] = "";
	$cname = $row[$dbc["name"]];
	if ($cname == "") return;
	for ($sublevel=0; 
		$sublevel<strlen($cname) && $cname[$sublevel] == '!'; $sublevel++);
	$cname = substr($cname,$sublevel);

	$sl = $sublevel;
	if (!isset($parents[$sublevel])) //not many '!' -- searching for root category
	{
		for (; $sl>0 && !isset($parents[$sl]); $sl--);
	}

	$q = db_query("select count(*) from ".CATEGORIES_TABLE.
			" where categoryID<>0 and categoryID<>1 and name LIKE '".TransformStringToDataBase($cname)."' ".
			" and parent='$parents[$sl]'");	
	$rowdb = db_fetch_row($q);
	if ( $rowdb[0] == 0  ) // insert category
	{
		db_query("insert into ".CATEGORIES_TABLE.
			 " (name, parent, products_count, description, ".
			 " picture, products_count_admin, meta_keywords, meta_description, sort_order) ".
			 "values ('".TransformStringToDataBase($cname)."',$parents[$sl],0, ".
				" '".TransformStringToDataBase($row[ $dbc["description"] ])."', ".
				" '".TransformStringToDataBase($picture_file_name)."',0, ".
				" '".TransformStringToDataBase($row[ $dbc["meta_keywords"] ])."', ".
				" '".TransformStringToDataBase($row[ $dbc["meta_description"] ])."', '".$sort_order."');");
		$currentCategoryID = db_insert_id("CATEGORIES_GEN");
	}
	else
	{
		$q = db_query("select categoryID from ".CATEGORIES_TABLE.
			" where categoryID<>0 and categoryID<>1 and name LIKE '$cname' and parent='$parents[$sl]'");
		$rowdb = db_fetch_row($q);
		$currentCategoryID = $rowdb[0];

		$query = "";
		if (strcmp($dbc["description"], "not defined"))
			$query .= " description = '".TransformStringToDataBase($row[$dbc["description"]])."'";
		if (strcmp($dbc["sort_order"], "not defined"))
		{
			if (strlen($query)>0) $query .= ",";
			$query .= " sort_order = ".$sort_order."";
		}
		if (count($dbcPhotos) > 0)
		{
			if (strlen($query)>0) $query .= ",";
			$query .= " picture = '".TransformStringToDataBase($picture_file_name)."'";
		}

		if (strlen($query) > 0)
			db_query("update ".CATEGORIES_TABLE.
				" set ".$query." where categoryID='$currentCategoryID'") or die (db_error());
	}	
	$parents[$sublevel+1] = $currentCategoryID;
}


function _importProductPictures( $row, $dbcPhotos, $productID )
{
	// delete pictures for this product
	$productID = (int) $productID;

	db_query( "delete from ".PRODUCT_PICTURES." where productID=".$productID );

	for( $j=0; $j < count($dbcPhotos); $j++ )
		_insertPictures( $row[ $dbcPhotos[$j] ], 
			$productID );

	$q = db_query( "select default_picture from ".PRODUCTS_TABLE." where productID=".
		$productID );
	$row = db_fetch_row($q);
	//if (!$row || !$row[0])
	{
		$q = db_query( "select photoID from ".PRODUCT_PICTURES." where productID=".
		$productID );
		$row = db_fetch_row($q);
		if ($row)
		{
			// update DEFAULT PICTURE information

			db_query( "update ".PRODUCTS_TABLE." set default_picture = $row[0] where productID=".$productID );
		}
	}
}

function _importExtraOptionValues($row, $productID, $updated_extra_option)
{
	//now setup all product's extra options
	for ($j=0; $j<count($updated_extra_option); $j++)
	{
		if (isset($updated_extra_option[$j]) && $updated_extra_option[$j]) //a column which is an extra option
		{
			$optionID = $updated_extra_option[$j];

			$curr_value = $row[$j];
			$default_variantID = 0;
			if (strpos($curr_value,"{")===0 && strpos($curr_value,"}")==strlen($curr_value)-1) //is it a selectable value?
			{
				$curr_value = substr( $curr_value, 1, strlen($curr_value)-2);
				$values_options = explode(",",$curr_value);
				//delete all current product option configuration
				db_query("delete from ".PRODUCT_OPTIONS_VALUES_TABLE.
					" where optionID=$optionID and productID=$productID");
				db_query("delete from ".PRODUCTS_OPTIONS_SET_TABLE.
					" where optionID=$optionID and productID=$productID");

				foreach ($values_options as $key => $val)
				{
					if (strstr($val,"=")) // current value is "OPTION_NAME=SURCHARGE", e.g. red=3, xl=1, s=-1, m=0
					{
						$a = explode("=",$val);
						$val_name = $a[0];
						$val_surcharge = (float)$a[1];
					}
					else // current value is a option value name, e.g. red, xl, s, m
					{
						$val_name = $val;
						$val_surcharge = 0;
					}

					//search for a specified option value in the database
					$variantID = optOptionValueExists($optionID, $val_name);
					if ( !$variantID ) //does not exist => add new variant value
					{
						$variantID = optAddOptionValue($optionID, $val_name, 0);
					}
					if (!$default_variantID) $default_variantID = $variantID;

					//now append this variant value to the product
					db_query("insert into ".PRODUCTS_OPTIONS_SET_TABLE.
						" (productID, optionID, variantID, price_surplus) ".
						" values ($productID, $optionID, $variantID, $val_surcharge);");
				}

				//assign default variant ID - first option in the variants list is default
				if ($default_variantID)
				{
					db_query("insert into ".PRODUCT_OPTIONS_VALUES_TABLE.
						" (optionID, productID, option_type, option_show_times, variantID) ".
						" values ($optionID, $productID, 1, 1, $default_variantID)");
				}

			}
			else // a custom fixed value
			{
				db_query("delete from ".PRODUCT_OPTIONS_VALUES_TABLE.
					" where optionID=$optionID and productID=$productID");
				db_query("insert into ".PRODUCT_OPTIONS_VALUES_TABLE.
					" (optionID, productID, option_value) ".
					" values ($optionID, $productID, '".TransformStringToDataBase($curr_value)."')");
			}
		}
	}
}


// *****************************************************************************
// Purpose 	import row to database
// Inputs     	
// Remarks	
// Returns	
function _importProduct( $row, $dbc, $identity_column, $dbcPhotos,
			$updated_extra_option, $currentCategoryID  )
{
	$row["not defined"] = "";
	//search for product within current category
	$q = db_query("select productID, categoryID, customers_rating  from ".
		PRODUCTS_TABLE." where categoryID=".$currentCategoryID." and ".$_POST["update_column"].
		" LIKE '".TransformStringToDataBase( $row[$identity_column] )."'");
	$rowdb = db_fetch_row($q);
	if (!$rowdb) //not found
	{
		//search for product in all categories
		$q = db_query("select productID, categoryID, customers_rating  from ".
			PRODUCTS_TABLE." where ".$_POST["update_column"].
			" LIKE '".TransformStringToDataBase( $row[$identity_column] )."'");
		$rowdb = db_fetch_row($q);
	}

	if ( $rowdb ) //update product info
	{
		$productID = $rowdb["productID"];

		$rowdb =  GetProduct( $productID );

		if ( strcmp($dbc["Price"], "not defined") )
		{
			$Price	= $row[ $dbc["Price"] ];
			$Price	= str_replace( " ",  "", $Price );
			$Price	= str_replace( ",", ".", $Price );
			$Price	= (float)$Price;
		}
		else $Price = $rowdb["Price"];
		if ( strcmp($dbc["list_price"], "not defined") )
		{
			$list_price	= $row[ $dbc["list_price"] ];
			$list_price	= str_replace( " ",  "", $list_price );
			$list_price	= str_replace( ",", ".", $list_price );
			$list_price = (float)$list_price;
		}
		else $list_price = $rowdb["list_price"];
		if ( strcmp($dbc["sort_order"], "not defined") )
			$sort_order = (int)$row[ $dbc["sort_order"] ];
		else $sort_order = $rowdb["sort_order"];
		if ( strcmp($dbc["in_stock"], "not defined") )
			$in_stock = (int)$row[ $dbc["in_stock"] ];
		else $in_stock = $rowdb["in_stock"];
		if ( strcmp($dbc["eproduct_filename"], "not defined") )
			$eproduct_filename = $row[ $dbc["eproduct_filename"] ];
		else $eproduct_filename = $rowdb["eproduct_filename"];
		if ( strcmp($dbc["eproduct_available_days"], "not defined") )
			$eproduct_available_days = (int)$row[ $dbc["eproduct_available_days"] ];
		else $eproduct_available_days = $rowdb["eproduct_available_days"];
		if ( strcmp($dbc["eproduct_download_times"], "not defined") )
			$eproduct_download_times = (int)$row[ $dbc["eproduct_download_times"] ];
		else $eproduct_download_times = $rowdb["eproduct_download_times"];
		if ( strcmp($dbc["weight"], "not defined") )
			$weight = (float)$row[ $dbc["weight"] ];
		else $weight = $rowdb["weight"];
		if ( strcmp($dbc["free_shipping"], "not defined") )
			$free_shipping = ( trim($row[$dbc["free_shipping"]])=="+"?1:0 );
		else $free_shipping = $rowdb["free_shipping"];
		if ( strcmp($dbc["min_order_amount"], "not defined") )
			$min_order_amount = (int)$row[ $dbc["min_order_amount"] ];
		else $min_order_amount = $rowdb["min_order_amount"];
		if ( strcmp($dbc["shipping_freight"], "not defined") )
			$shipping_freight = (float)$row[ $dbc["shipping_freight"] ];
		else $shipping_freight = $rowdb["shipping_freight"];
		if ( strcmp($dbc["description"], "not defined") )
			$description = $row[ $dbc["description"] ];
		else $description = $rowdb["description"];
		if ( strcmp($dbc["brief_description"], "not defined") )
			$brief_description = $row[ $dbc["brief_description"] ];
		else $brief_description = $rowdb["brief_description"];
		if ( strcmp($dbc["product_code"], "not defined") )
			$product_code = $row[ $dbc["product_code"] ];
		else $product_code = $rowdb["product_code"];
		if ( strcmp($dbc["meta_description"], "not defined") )
			$meta_description = $row[ $dbc["meta_description"] ];
		else $meta_description = $rowdb["meta_description"];
		if ( strcmp($dbc["meta_keywords"], "not defined") )
			$meta_keywords = $row[ $dbc["meta_keywords"] ];
		else $meta_keywords = $rowdb["meta_keywords"];
		if ( strcmp($dbc["name"], "not defined") )
			$name = $row[ $dbc["name"] ];
		else $name = $rowdb["name"];
		if ( strcmp($dbc["tax"], "not defined") )
			$_taxname = $row[ $dbc["tax"] ];
		else $_taxname = $rowdb["tax"];

		//get tax class ID
		$tax = null;
		$_taxes = taxGetTaxClasses();
		foreach ($_taxes as $key => $val)
		{
			if ( !strcmp($val["name"],$_taxname))
			{
				$tax = $val["classID"];
			}
		}


		$categoryID		= $rowdb["categoryID"];
		$customers_rating	= $rowdb["customers_rating"];
		$ProductIsProgram   = trim($eproduct_filename) != "";
		UpdateProduct( $productID,
				$categoryID, $name, $Price, $description, 
				$in_stock, $customers_rating,
				$brief_description, $list_price,
				$product_code, $sort_order,
				$ProductIsProgram, 
				"", 
				$eproduct_available_days,
				$eproduct_download_times,
				$weight, $meta_description, $meta_keywords, 
				$free_shipping, $min_order_amount, $shipping_freight, $tax, 0 );
	}
	else // add new product
	{
		$Price						= 0.0;
		$list_price 				= 0.0;
		$sort_order 				= 0;
		$in_stock					= 0;
		$eproduct_filename			= "";
		$eproduct_available_days	= 0;
		$eproduct_download_times	= 0;
		$weight						= 0.0;
		$free_shipping				= 0;
		$min_order_amount			= 1;
		$shipping_freight			= 0.0;
		$tax						= CONF_DEFAULT_TAX_CLASS;

		if ( strcmp($dbc["Price"], "not defined") )
			$Price	= (float)$row[ $dbc["Price"] ];
		if ( strcmp($dbc["list_price"], "not defined") )
			$list_price = (float)$row[ $dbc["list_price"] ];
		if ( strcmp($dbc["sort_order"], "not defined") )
			$sort_order = (int)$row[ $dbc["sort_order"] ];
		if ( strcmp($dbc["in_stock"], "not defined") )
			$in_stock = (int)$row[ $dbc["in_stock"] ];
		if ( strcmp($dbc["eproduct_filename"], "not defined") )
			$eproduct_filename = $row[ $dbc["eproduct_filename"] ];
		if ( strcmp($dbc["eproduct_available_days"], "not defined") )
			$eproduct_available_days = (int)$row[ $dbc["eproduct_available_days"] ];
		if ( strcmp($dbc["eproduct_download_times"], "not defined") )
			$eproduct_download_times = (int)$row[ $dbc["eproduct_download_times"] ];
		if ( strcmp($dbc["weight"], "not defined") )
			$weight = (float)$row[ $dbc["weight"] ];
		if ( strcmp($dbc["free_shipping"], "not defined") )
			$free_shipping = ( trim($row[$dbc["free_shipping"]])=="+"?1:0 );
		if ( strcmp($dbc["min_order_amount"], "not defined") )
			$min_order_amount = (int)$row[ $dbc["min_order_amount"] ];
		if ( strcmp($dbc["shipping_freight"], "not defined") )
			$shipping_freight = (float)$row[ $dbc["shipping_freight"] ];
		if ( strcmp($dbc["tax"], "not defined") )
			$_taxname = $row[ $dbc["tax"] ];

		//get tax class ID
		$_taxes = taxGetTaxClasses();
		foreach ($_taxes as $key => $val)
		{
			if ( !strcmp($val["name"],$_taxname))
			{
				$tax = $val["classID"];
			}
		}

		$ProductIsProgram   = trim($row[$dbc["eproduct_filename"]]) != "";
		$productID = AddProduct(
				$currentCategoryID, $row[ $dbc["name"] ], $Price, $row[ $dbc["description"] ], 
			    $in_stock, 
				$row[ $dbc["brief_description"] ], $list_price,
			    $row[ $dbc["product_code"] ], $sort_order,
				$ProductIsProgram, "",
				$eproduct_available_days, $eproduct_download_times, 
				$weight, $row[$dbc["meta_description"]], $row[$dbc["meta_keywords"]], 
				$free_shipping, $min_order_amount, $shipping_freight, 
				$tax, 0 );
	}
	if (strlen($eproduct_filename))
		SetProductFile( $productID, $eproduct_filename );

	_importExtraOptionValues( $row, $productID, $updated_extra_option );

	if ( count($dbcPhotos) > 0 )
		_importProductPictures( $row, $dbcPhotos, $productID );				

}

// *****************************************************************************
// Purpose 	import row to database
// Inputs     	
// Remarks	
// Returns	
function imImportRowToDataBase( $row, $dbc, $identity_column, 
	$dbcPhotos, $updated_extra_option, &$parents, &$currentCategoryID )
{
	if ( _isCategory($row, $dbc) )
	{
		_importCategory( $row, $dbc, $parents, $dbcPhotos, $currentCategoryID );
	}
	else
		_importProduct( $row, $dbc, $identity_column, 
			$dbcPhotos, $updated_extra_option, $currentCategoryID );
}


?>