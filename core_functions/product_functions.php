<?php
?><?php
// *****************************************************************************
// Purpose
// Inputs
// Remarks
// Returns	true if product exists
function prdProductExists( $productID )
{
    $q = db_query( "select count(*) from ".PRODUCTS_TABLE." where productID=".$productID );
    $row = db_fetch_row($q);
    return ($row[0]!=0);
}
// *****************************************************************************
// Purpose	gets product
// Inputs   $productID - product ID
// Remarks
// Returns	array of fieled value
//			"name"				- product name
//			"product_code"		- product code
//			"description"		- description
//			"brief_description"	- short description
//			"customers_rating"	- product rating
//			"in_stock"			- in stock (this parametr is persist if CONF_CHECKSTOCK == 1 )
//			"option_values"		- array of
//					"optionID"		- option ID
//					"name"			- name
//					"value"	- option value
//					"option_type" - option type
//			"ProductIsProgram"		- 1 if product is program, 0 otherwise
//			"eproduct_filename"		- program filename
//			"eproduct_available_days"	- program is available days to download
//			"eproduct_download_times"	- attempt count download file
//			"weight"			- product weigth
//			"meta_description"		- meta tag description
//			"meta_keywords"			- meta tag keywords
//			"free_shipping"			- 1 product has free shipping,
//							0 - otherwise
//			"min_order_amount"		- minimum order amount
//			"classID"			- tax class ID
function GetProduct( $productID){
    $productID = (int)$productID;
    $q = db_query('SELECT * FROM '.PRODUCTS_TABLE.' WHERE productID='.$productID);
    if ( $product=db_fetch_row($q ) ){
        $product["ProductIsProgram"] = 	(trim($product["eproduct_filename"]) != "");
        $sql = '
			SELECT pot.optionID,pot.name,povt.option_value,povt.option_value as value,povt.option_type FROM '.PRODUCT_OPTIONS_VALUES_TABLE.' as povt
			LEFT JOIN '.PRODUCT_OPTIONS_TABLE.' as pot ON pot.optionID=povt.optionID
			WHERE productID='.$productID.'
		';
        $Result = db_query($sql);
        $product['option_values'] = array();
        while ($_Row = db_fetch_row($Result)) {
            $product['option_values'][] = $_Row;
        }
        $product['date_added']=format_datetime( $product['date_added'] );
        $product['date_modified']=format_datetime( $product['date_modified'] );
        /*echo '<pre>';
        print_r($product);
        echo '</pre>';
        exit();*/
        return $product;
    }
    return false;
}
// *****************************************************************************
// Purpose	updates product
// Inputs   $productID - product ID
//				$categoryID			- category ID ( see CATEGORIES_TABLE )
//				$name				- name of product
//				$Price				- price of product
//				$description		- product description
//				$in_stock			- stock counter
//				$customers_rating	- rating
//				$brief_description  - short product description
//				$list_price			- old price
//				$product_code		- product code
//				$sort_order			- sort order
//				$ProductIsProgram		- 1 if product is program, 0 otherwise
//				$eproduct_filename		- program filename
//				$eproduct_available_days	- program is available days to download
//				$eproduct_download_times	- attempt count download file
//				$weight			- product weigth
//				$meta_description	- meta tag description
//				$meta_keywords		- meta tag keywords
//				$free_shipping		- 1 product has free shipping,
//							0 - otherwise
//				$min_order_amount	- minimum order amount
//				$classID		- tax class ID
// Remarks
// Returns
function UpdateProduct( $productID,
                        $categoryID, $title_one,$title_two,$url_name,$name,$producer, $num_topsale, $Price, $description, $description2, $brief_description2,
                        $in_stock,$skidka, $customers_rating,
                        $brief_description, $list_price,
                        $product_code, $sort_order,
                        $ProductIsProgram,
                        $eproduct_filename,
                        $eproduct_available_days,
                        $eproduct_download_times,
                        $weight, $meta_description, $meta_keywords,
                        $free_shipping, $min_order_amount, $shipping_freight, $classID, $updateGCV = 1  )
{
    // special symbol prepare
    $title_one				= TransformStringToDataBase( $title_one );
    $title_two				= TransformStringToDataBase( $title_two );
    $url_name				= TransformStringToDataBase( $url_name );
    $name				= TransformStringToDataBase( $name );
    $description		= TransformStringToDataBase( $description );
    $description2		= TransformStringToDataBase( $description2 );
    $brief_description	= TransformStringToDataBase( $brief_description );
    $brief_description2	= TransformStringToDataBase( $brief_description2 );
    $product_code		= TransformStringToDataBase( $product_code );
    $eproduct_filename	= TransformStringToDataBase( $eproduct_filename );
    $meta_description	= TransformStringToDataBase( $meta_description );
    $meta_keywords		= TransformStringToDataBase( $meta_keywords );
    $weight = (float)$weight;
    $shipping_freight = (float)$shipping_freight;
    $min_order_amount = (int)$min_order_amount;
    if ( $min_order_amount == 0 )
        $min_order_amount = 1;
    $eproduct_available_days = (int)$eproduct_available_days;
    if ( !$ProductIsProgram )
        $eproduct_filename = "";
    if ( !$free_shipping ) $free_shipping = 0;
    else $free_shipping = 1;
    $q = db_query("select eproduct_filename from ".PRODUCTS_TABLE.
        " where productID=$productID");
    $old_file_name = db_fetch_row( $q );
    $old_file_name = $old_file_name[0];
    if ( $classID == null )
        $classID = "NULL";
    if ( $eproduct_filename != "" )
    {
        if ( trim($_FILES[$eproduct_filename]["name"]) != ""  )
        {
            if ( trim($old_file_name) != "" && file_exists("./products_files/$old_file_name") )
                unlink("./products_files/$old_file_name");
            if ( $_FILES[$eproduct_filename]["size"]!=0 )
                $r = move_uploaded_file($_FILES[$eproduct_filename]["tmp_name"],
                    "./products_files/".$_FILES[$eproduct_filename]["name"]);
            $eproduct_filename = trim($_FILES[$eproduct_filename]["name"]);
            SetRightsToUploadedFile( "./products_files/".$eproduct_filename );
        }
        else
            $eproduct_filename = $old_file_name;
    }
    else
        $eproduct_filename = $old_file_name;
    $s = "UPDATE ".PRODUCTS_TABLE." SET ".
        "categoryID='".$categoryID."', ".
        "title_one='".$title_one."', ".
        "title_two='".$title_two."', ".
        "url_name='".$url_name."', ".
        "name='".$name."', ".
        "producer='".$producer."', ".
        "num_topsale='".$num_topsale."', ".
        "Price='".$Price."', ".
        "description='".$description."', ".
        "description2='".$description2."', ".
        "brief_description2='".$brief_description2."', ".
        "in_stock='".$in_stock."', ".
        "skidka='".$skidka."', ".
        "customers_rating='".$customers_rating."', ".
        "brief_description='".$brief_description."', ".
        "list_price='".$list_price."', ".
        "product_code='".$product_code."', ".
        "sort_order='".$sort_order."', ".
        "date_modified='".get_current_time()."', ".
        "eproduct_filename='$eproduct_filename', ".
        "eproduct_available_days=$eproduct_available_days, ".
        "eproduct_download_times=$eproduct_download_times,  ".
        "weight=$weight, meta_description='$meta_description', ".
        "meta_keywords='$meta_keywords', free_shipping=$free_shipping, ".
        "min_order_amount = $min_order_amount, ".
        "shipping_freight = $shipping_freight ";
    if ($classID != null)
        $s .= ", classID = $classID ";
    $s .= "where productID='".$productID."'";
    db_query($s) or die (db_error());
    db_query("delete from ".CATEGORIY_PRODUCT_TABLE." where productID = '$productID' and categoryID = '$categoryID'") or die (db_error());
    if ($updateGCV == 1 && CONF_UPDATE_GCV == '1') //update goods count values for categories in case of regular file editing. do not update during import from excel
        update_products_Count_Value_For_Categories(1);
}
// *****************************************************************************
// Purpose	sets product file
// Inputs
// Remarks
// Returns
function SetProductFile( $productID, $eproduct_filename )
{
    db_query( "update ".PRODUCTS_TABLE." set eproduct_filename='".$eproduct_filename."' ".
        " where productID=".$productID );
}
// *****************************************************************************
// Purpose	adds product
// Inputs
//				$categoryID			- category ID ( see CATEGORIES_TABLE )
//				$name				- name of product
//				$Price				- price of product
//				$description		- product description
//				$in_stock			- stock counter
//				$brief_description  - short product description
//				$list_price			- old price
//				$product_code		- product code
//				$sort_order			- sort order
//				$ProductIsProgram		- 1 if product is program,
//									0 otherwise
//				$eproduct_filename		- program filename ( it is index of $_FILE variable )
//				$eproduct_available_days	- program is available days
//									to download
//				$eproduct_download_times	- attempt count download file
//				$weight			- product weigth
//				$meta_description	- meta tag description
//				$meta_keywords		- meta tag keywords
//				$free_shipping		- 1 product has free shipping,
//							0 - otherwise
//				$min_order_amount	- minimum order amount
//				$classID		- tax class ID
// Remarks
// Returns
function AddProduct(
    $categoryID, $title_one,$title_two, $url_name, $name, $producer, $num_topsale, $Price, $description, $description2, $brief_description2,
    $in_stock,$skidka,
    $brief_description, $list_price,
    $product_code, $sort_order,
    $ProductIsProgram, $eproduct_filename,
    $eproduct_available_days, $eproduct_download_times,
    $weight, $meta_description, $meta_keywords,
    $free_shipping, $min_order_amount, $shipping_freight,
    $classID, $updateGCV = 1 )
{
    echo '11<br>';
    // special symbol prepare
    $title_one				= TransformStringToDataBase( $title_one );
    $title_two				= TransformStringToDataBase( $title_two );
    $url_name				= TransformStringToDataBase( $url_name );
    $name				= TransformStringToDataBase( $name );
    $description		= TransformStringToDataBase( $description );
    $description2		= TransformStringToDataBase( $description2 );
    $brief_description	= TransformStringToDataBase( $brief_description );
    $brief_description2	= TransformStringToDataBase( $brief_description2 );
    $product_code		= TransformStringToDataBase( $product_code );
    $eproduct_filename	= TransformStringToDataBase( $eproduct_filename );
    $meta_description	= TransformStringToDataBase( $meta_description );
    $meta_keywords		= TransformStringToDataBase( $meta_keywords );
    if ( $free_shipping )
        $free_shipping = 1;
    else
        $free_shipping = 0;
    if ( $classID == null )
        $classID = "NULL";
    $weight = (float)$weight;
    $min_order_amount = (int)$min_order_amount;
    if ( $min_order_amount == 0 )
        $min_order_amount = 1;
    $eproduct_available_days = (int)$eproduct_available_days;
    if ( !$ProductIsProgram )
        $eproduct_filename = "";
    if ( $eproduct_filename != "" )
    {
        if ( trim($_FILES[$eproduct_filename]["name"]) != ""  )
        {
            if ( $_FILES[$eproduct_filename]["size"]!=0 )
                $r = move_uploaded_file($_FILES[$eproduct_filename]["tmp_name"],
                    "./products_files/".$_FILES[$eproduct_filename]["name"]);
            $eproduct_filename = trim($_FILES[$eproduct_filename]["name"]);
            SetRightsToUploadedFile( "./products_files/".$eproduct_filename );
        }
    }
    $shipping_freight = (float)$shipping_freight;
    if ( trim($name) == "" ) $name = "?";
    echo "<br>SQL-CODE<br>INSERT INTO ".PRODUCTS_TABLE.
        " ( categoryID,title_one,title_two, url_name,name, producer, num_topsale, description, description2, brief_description2,".
        "	customers_rating, Price, in_stock, skidka,".
        "	customer_votes, items_sold, enabled, ".
        "	brief_description, list_price, ".
        "	product_code, sort_order, date_added, ".
        " 	eproduct_filename, eproduct_available_days, ".
        " 	eproduct_download_times, ".
        "	weight, meta_description, meta_keywords, ".
        "	free_shipping, min_order_amount, shipping_freight, classID ".
        " ) ".
        " VALUES ('".
        $categoryID."','".
        $title_one."','".
        $title_two."','".
        $url_name."','".
        $name."','".
        $producer."','".
        $num_topsale."','".
        $description."', ".
        $description2."', ".
        $brief_description2."', ".
        "0, '".
        $Price."', '".
        $in_stock."', '".
        $skidka."', ".
        " 0, 0, 1, '".
        $brief_description."', '".
        $list_price."', '".
        $product_code."', '".
        $sort_order."', '".
        get_current_time()."',  ".
        "'".$eproduct_filename."', ".
        $eproduct_available_days.", ".
        $eproduct_download_times.",  ".
        $weight.", ".
        "'".$meta_description."', ".
        "'".$meta_keywords."', ".
        $free_shipping.", ".
        $min_order_amount.", ".
        $shipping_freight.", ".
        $classID." ".
        ");";
    db_query("INSERT INTO ".PRODUCTS_TABLE.
        " ( categoryID,title_one,title_two, url_name,name, producer, num_topsale, description, ".
        "	customers_rating, Price, in_stock, skidka,".
        "	customer_votes, items_sold, enabled, ".
        "	brief_description, list_price, ".
        "	product_code, sort_order, date_added, ".
        " 	eproduct_filename, eproduct_available_days, ".
        " 	eproduct_download_times, ".
        "	weight, meta_description, meta_keywords, ".
        "	free_shipping, min_order_amount, shipping_freight, classID ".
        " ) ".
        " VALUES ('".
        $categoryID."','".
        $title_one."','".
        $title_two."','".
        $url_name."','".
        $name."','".
        $producer."','".
        $num_topsale."','".
        $description."', ".
        "0, '".
        $Price."', '".
        $in_stock."', '".
        $skidka."', ".
        " 0, 0, 1, '".
        $brief_description."', '".
        $list_price."', '".
        $product_code."', '".
        $sort_order."', '".
        get_current_time()."',  ".
        "'".$eproduct_filename."', ".
        $eproduct_available_days.", ".
        $eproduct_download_times.",  ".
        $weight.", ".
        "'".$meta_description."', ".
        "'".$meta_keywords."', ".
        $free_shipping.", ".
        $min_order_amount.", ".
        $shipping_freight.", ".
        $classID." ".
        ");" );
    $insert_id = db_insert_id();
    if ( $updateGCV == 1 && CONF_UPDATE_GCV == '1')
        update_products_Count_Value_For_Categories(1);
    return $insert_id;
}
// *****************************************************************************
// Purpose	deletes product
// Inputs   $productID - product ID
// Remarks
// Returns	true if success, else false otherwise
function DeleteProduct($productID, $updateGCV = 1)
{
    $whereClause = " where productID='".$productID."'";
    $q = db_query( "select itemID from ".SHOPPING_CART_ITEMS_TABLE." ".$whereClause );
    while( $row=db_fetch_row($q) )
        db_query( "delete from ".SHOPPING_CARTS_TABLE." where itemID=".$row["itemID"] );
    // delete all items for this product
    db_query("update ".SHOPPING_CART_ITEMS_TABLE.
        " set productID=NULL ".$whereClause);
    // delete all product option values
    db_query("delete from ".PRODUCTS_OPTIONS_SET_TABLE.$whereClause);
    db_query("delete from ".PRODUCT_OPTIONS_VALUES_TABLE.$whereClause);
    // delete pictures
    db_query("delete from ".PRODUCT_PICTURES.$whereClause);
    // delete additional categories records
    db_query("delete from ".CATEGORIY_PRODUCT_TABLE.$whereClause);
    // delete discussions
    db_query("delete from ".DISCUSSIONS_TABLE.$whereClause);
    // delete special offer
    db_query("delete from ".SPECIAL_OFFERS_TABLE.$whereClause);
    // delete related items
    db_query("delete from ".RELATED_PRODUCTS_TABLE.$whereClause );
    db_query("delete from ".RELATED_PRODUCTS_TABLE." where Owner=$productID");
    // delete product
    db_query("delete from ".PRODUCTS_TABLE.$whereClause);
    if ( $updateGCV == 1 && CONF_UPDATE_GCV == '1')
        update_products_Count_Value_For_Categories(1);
    return true;
}
// *****************************************************************************
// Purpose	deletes all products of category
// Inputs   $categoryID - category ID
// Remarks
// Returns	true if success, else false otherwise
function DeleteAllProductsOfThisCategory($categoryID)
{
    $q=db_query("select productID from ".PRODUCTS_TABLE.
        " where categoryID='".$categoryID."'");
    $res=true;
    while( $r=db_fetch_row( $q ) )
    {
        if ( !DeleteProduct( $r["productID"], 0 ) )
            $res = false;
    }
    if ( CONF_UPDATE_GCV == '1')
        update_products_Count_Value_For_Categories(1);
    return $res;
}
// *****************************************************************************
// Purpose	gets extra parametrs
// Inputs   $productID - product ID
// Remarks
// Returns	array of value extraparametrs
//				each item of this array has next struture
//					first type "option_type" = 0
//						"name"					- parametr name
//						"option_value"			- value
//						"option_type"			- 0
//					second type "option_type" = 1
//						"name"					- parametr name
//						"option_show_times"		- how times does show in client side this
//												parametr to select
//						"variantID_default"		- variant ID by default
//						"values_to_select"		- array of variant value to select
//							each item of "values_to_select" array has next structure
//								"variantID"			- variant ID
//								"price_surplus"		- to added to price
//								"option_value"		- value
function GetExtraParametrs( $productID ){
    if(!is_array($productID)){
        $ProductIDs = array($productID);
        $IsProducts = false;
    }elseif(count($productID)) {
        $ProductIDs = &$productID;
        $IsProducts = true;
    }else {
        return array();
    }
    $ProductsExtras = array();
    $sql = '
		SELECT povt.productID,pot.optionID,pot.name,povt.option_value,povt.option_type,povt.option_show_times, povt.variantID, povt.optionID
		FROM ?#PRODUCT_OPTIONS_VALUES_TABLE as povt LEFT JOIN  ?#PRODUCT_OPTIONS_TABLE as pot ON pot.optionID=povt.optionID
		WHERE povt.productID IN (?@) ORDER BY pot.sort_order, pot.name
	';
    $Result = db_phquery($sql, $ProductIDs);
    while ($_Row = db_fetch_assoc($Result)) {
        $_Row;
        $b=null;
        if (($_Row['option_type']==0 || $_Row['option_type']==NULL) && strlen( trim($_Row['option_value']))>0){
            $ProductsExtras[$_Row['productID']][] = array(
                'option_type' => $_Row['option_type'],
                'name' => $_Row['name'],
                'option_value' => $_Row['option_value']
            );
        }
        /**
         * @features "Extra options values"
         * @state begin
         */
        else if ( $_Row['option_type']==1 ){
            //fetch all option values variants
            $sql = '
				SELECT povvt.option_value, povvt.variantID, post.price_surplus
				FROM '.PRODUCTS_OPTIONS_SET_TABLE.' as post
				LEFT JOIN '.PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE.' as povvt
				ON povvt.variantID=post.variantID
				WHERE povvt.optionID='.$_Row['optionID'].' AND post.productID='.$_Row['productID'].' AND povvt.optionID='.$_Row['optionID'].'
				ORDER BY povvt.sort_order, povvt.option_value
			';
            $q2=db_query($sql);
            $_Row['values_to_select']=array();
            $i=0;
            while( $_Rowue = db_fetch_assoc($q2)  ){
                $_Row['values_to_select'][$i]=array();
                $_Row['values_to_select'][$i]['option_value'] = $_Rowue['option_value'];
                if ( $_Rowue['price_surplus'] > 0 )$_Row['values_to_select'][$i]['option_value'] .= ' (+ '.show_price($_Rowue['price_surplus']).')';
                elseif($_Rowue['price_surplus'] < 0 )$_Row['values_to_select'][$i]['option_value'] .= ' (- '.show_price(-$_Rowue['price_surplus']).')';
                $_Row['values_to_select'][$i]['option_valueWithOutPrice'] = $_Rowue['option_value'];
                $_Row['values_to_select'][$i]['price_surplus'] = show_priceWithOutUnit($_Rowue['price_surplus']);
                $_Row['values_to_select'][$i]['variantID']=$_Rowue['variantID'];
                $i++;
            }
            $ProductsExtras[$_Row['productID']][] = $_Row;
        }
        /**
         * @features "Extra options values"
         * @state end
         */
    }
    if(!$IsProducts){
        if(!count($ProductsExtras))return array();
        else {
            return $ProductsExtras[$productID];
        }
    }
    return $ProductsExtras;
}
function _setPictures( & $product){
    if ( isset($product['default_picture'])&&!is_null($product['default_picture'])&&isset($product['productID']) ){
        $Result = db_phquery('SELECT filename, thumbnail, enlarged FROM ?#PRODUCT_PICTURES WHERE photoID=?',$product['default_picture']);
        $Picture = db_fetch_assoc($Result);
        $product['picture'] = file_exists('./products_pictures/'.$Picture['filename'])?$Picture['filename']:0;
        $product['thumbnail']=file_exists('./products_pictures/'.$Picture['thumbnail'])?$Picture['thumbnail']:0;
        $product['big_picture']=file_exists('./products_pictures/'.$Picture['enlarged'])?$Picture['enlarged']:0;
    }elseif (is_array($product)&&!isset($product['productID'])){
        $Products = &$product;
        $DefaultPictures = array();
        $TC = count($Products);
        for ($j=0;$j<$TC;$j++){
            print '<pre>';
            print_r($Products);
            print '</pre>';
            if(!is_null($Products[$j]['default_picture']))$DefaultPictures[intval($Products[$j]['default_picture'])][] = $j;
        }
        if(!count($DefaultPictures))return;
        $sql = '
			SELECT photoID,filename, thumbnail, enlarged FROM '.PRODUCT_PICTURES.' WHERE photoID IN('.implode(',',array_keys($DefaultPictures)).')
		';
        $Result = db_query($sql);
        while ($Picture = db_fetch_assoc($Result)){
            foreach ($DefaultPictures[$Picture['photoID']] as $j){
                $Products[$j]['picture'] = file_exists('./products_pictures/'.$Picture['filename'])?$Picture['filename']:0;
                $Products[$j]['thumbnail']=file_exists('./products_pictures/'.$Picture['thumbnail'])?$Picture['thumbnail']:0;
                $Products[$j]['big_picture']=file_exists('./products_pictures/'.$Picture['enlarged'])?$Picture['enlarged']:0;
            }
        }
    }
}
function GetProductInSubCategories( $callBackParam, &$count_row, $navigatorParams = null )
{
    if ( $navigatorParams != null )
    {
        $offset			= $navigatorParams["offset"];
        $CountRowOnPage	= $navigatorParams["CountRowOnPage"];
    }
    else
    {
        $offset = 0;
        $CountRowOnPage = 0;
    }
    $categoryID	= $callBackParam["categoryID"];
    $subCategoryIDArray = catGetSubCategories( $categoryID );
    $cond = "";
    foreach( $subCategoryIDArray as $subCategoryID )
    {
        if ( $cond != "" )
            $cond .= " OR categoryID=$subCategoryID";
        else
            $cond .= " categoryID=$subCategoryID ";
    }
    $whereClause = "";
    if ( $cond != "" )
        $whereClause = " where ".$cond;
    $result = array();
    if ( $whereClause == "" )
    {
        $count_row = 0;
        return $result;
    }
    $q=db_query("select categoryID, name, brief_description, ".
        " customers_rating, Price, in_stock, skidka,".
        " customer_votes, list_price, ".
        " productID, default_picture, sort_order from ".PRODUCTS_TABLE.
        " ".$whereClause." order by sort_order, name " );
    $i=0;
    while( $row=db_fetch_row($q) )
    {
        if ( ($i >= $offset && $i < $offset + $CountRowOnPage) ||
            $navigatorParams == null  )
        {
            $row["PriceWithUnit"]		= show_price($row["Price"]);
            $row["list_priceWithUnit"] 	= show_price($row["list_price"]);
            // you save (value)
            $row["SavePrice"]		= show_price($row["list_price"]-$row["Price"]);
            // you save (%)
            if ($row["list_price"])
                $row["SavePricePercent"] = ceil(((($row["list_price"]-$row["Price"])/$row["list_price"])*100));
            _setPictures( $row );
            $row["product_extra"]=GetExtraParametrs($row["productID"]);
            $row["PriceWithOutUnit"]= show_priceWithOutUnit( $row["Price"] );
            $result[] = $row;
        }
        $i++;
    }
    $count_row = $i;
    return $result;
}
// *****************************************************************************
// Purpose	gets all products by categoryID
// Inputs     	$callBackParam item
//			"categoryID"
//			"fullFlag"
// Remarks
// Returns
function prdGetProductByCategory( $callBackParam, &$count_row, $navigatorParams = null )
{
    if ( $navigatorParams != null )
    {
        $offset			= $navigatorParams["offset"];
        $CountRowOnPage	= $navigatorParams["CountRowOnPage"];
    }
    else
    {
        $offset = 0;
        $CountRowOnPage = 0;
    }
    $result = array();
    $categoryID	= $callBackParam["categoryID"];
    $fullFlag	= $callBackParam["fullFlag"];
    if ( $fullFlag )
    {
        $conditions = array( " categoryID=$categoryID " );
        $q = db_query("select productID from ".
            CATEGORIY_PRODUCT_TABLE." where  categoryID=$categoryID");
        while( $products = db_fetch_row( $q ) )
            $conditions[] = " productID=".$products[0];
        $data = array();
        foreach( $conditions as $cond )
        {
            $q=db_query("select categoryID, name, brief_description, ".
                " customers_rating, Price, in_stock, skidka, ".
                " customer_votes, list_price, ".
                " productID, default_picture, sort_order, items_sold, enabled, product_code from ".PRODUCTS_TABLE.
                " where ".$cond );
            while( $row = db_fetch_row($q) )
            {
                $row["PriceWithUnit"]		= show_price($row["Price"]);
                $row["list_priceWithUnit"] 	= show_price($row["list_price"]);
                // you save (value)
                $row["SavePrice"]		= show_price($row["list_price"]-$row["Price"]);
                // you save (%)
                if ($row["list_price"])
                    $row["SavePricePercent"] = ceil(((($row["list_price"]-$row["Price"])/$row["list_price"])*100));
                _setPictures( $row );
                $row["product_extra"]=GetExtraParametrs($row["productID"]);
                $row["PriceWithOutUnit"]= show_priceWithOutUnit( $row["Price"] );
                $data[] = $row;
            }
        }
        function _compare( $row1, $row2 )
        {
            if ( (int)$row1["sort_order"] == (int)$row2["sort_order"] )
                return 0;
            return ((int)$row1["sort_order"] < (int)$row2["sort_order"]) ? -1 : 1;
        }
        usort($data, "_compare");
        $result = array();
        $i = 0;
        foreach( $data as $res )
        {
            if ( ($i >= $offset && $i < $offset + $CountRowOnPage) ||
                $navigatorParams == null )
                $result[] = $res;
            $i++;
        }
        $count_row = $i;
        return $result;
    }
    else
    {
        $q=db_query("select categoryID, name, brief_description, ".
            " customers_rating, Price, in_stock, skidka, ".
            " customer_votes, list_price, ".
            " productID, default_picture, sort_order, items_sold, enabled, product_code from ".PRODUCTS_TABLE.
            " where categoryID=$categoryID order by sort_order, name" );
        $i=0;
        while( $row=db_fetch_row($q) )
        {
            if ( ($i >= $offset && $i < $offset + $CountRowOnPage) ||
                $navigatorParams == null  )
                $result[] = $row;
            $i++;
        }
        $count_row = $i;
        return $result;
    }
}
function _getConditionWithCategoryConjWithSubCategories( $condition, $categoryID ) //fetch products from current category and all its subcategories
{
    $new_condition = "";
    $categoryID_Array = catGetSubCategories( $categoryID );
    $categoryID_Array[] = (int)$categoryID;
    foreach( $categoryID_Array as $catID )
    {
        if ( $new_condition != "" )
            $new_condition .= " OR ";
        $new_condition .= _getConditionWithCategoryConj($condition, $catID);
    }
    return $new_condition;
}
function _getConditionWithCategoryConj( $condition, $categoryID ) //fetch products from current category
{
    $category_condition = "";
    $q = db_query("select productID from ".
        CATEGORIY_PRODUCT_TABLE." where categoryID=$categoryID");
    while( $product = db_fetch_row( $q ) )
    {
        if ( $category_condition != "" )
            $category_condition .= " OR ";
        $category_condition .= " productID=".$product[0];
    }
    if (strlen($category_condition)>0) $category_condition = "(".$category_condition.")";
    if ( $condition == "" )
    {
        if ( $category_condition == "" )
            return "categoryID=".$categoryID;
        else
            return $category_condition." OR categoryID=".$categoryID;
    }
    else
    {
        if ( $category_condition == "" )
            return $condition." AND categoryID=".$categoryID;
        else
            return "( $condition AND $category_condition ) OR ".
                " ( $condition AND categoryID=$categoryID )";
    }
}
// *****************************************************************************
// Purpose
// Inputs
//				$productID - product ID
//				$template  - array of item
//					"optionID"	- option ID
//					"value"		- value or variant ID
// Remarks
// Returns	returns true if product matches to extra parametr template
//			false otherwise
function _testExtraParametrsTemplate( $productID, &$template ){
    // get category ID
    $categoryID = $template["categoryID"];
    foreach( $template as $key => $item ){
        if( !isset($item["optionID"]) ) continue;
        if((string)$key == "categoryID" ) continue;
        // get value to search
        if ( $item['set_arbitrarily'] == 1 ){
            $valueFromForm = $item["value"];
        }else{
            if ( (int)$item["value"] == 0 ) continue;
            if(!isset($template[$key]['__option_value_from_db'])){
                $SQL = '
					SELECT option_value FROM ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE WHERE variantID=?
				';
                $option_value = db_fetch_assoc(db_phquery($SQL, $item['value']));
                $template[$key]['__option_value_from_db'] = $option_value['option_value'];
            }
            $valueFromForm = $template[$key]['__option_value_from_db'];
        }
        // get option value
        $SQL = '
			SELECT option_value, option_type FROM ?#PRODUCT_OPTIONS_VALUES_TABLE WHERE optionID=? AND productID=?
		';
        $q = db_phquery($SQL,$item['optionID'],$productID);
        if(!($row=db_fetch_row($q))){
            if ( trim($valueFromForm) == '' ) continue;
            else return false;
        }
        $option_value = $row['option_value'];
        $option_type	= $row['option_type'];
        $valueFromDataBase = array();
        if ( $option_type == 0 ){
            $valueFromDataBase[] = $option_value;
        }else{
            $SQL = '
				SELECT povv.option_value FROM ?#PRODUCTS_OPTIONS_SET_TABLE as pos
				LEFT JOIN ?#PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE as povv ON pos.variantID=povv.variantID WHERE pos.optionID=? AND pos.productID=?
			';
            $Result = db_phquery($SQL,$item["optionID"], $productID);
            while ($Row = db_fetch_assoc($Result)){
                $valueFromDataBase[] = $Row['option_value'];
            }
        }
        if ( trim($valueFromForm) != '' ){
            $existFlag = false;
            foreach( $valueFromDataBase as $value ){
                if(strstr(strtolower((string)trim($value)),strtolower((string)trim($valueFromForm)))){
                    $existFlag = true;
                    break;
                }
            }
            if ( !$existFlag ) return false;
        }
    }
    return true;
}
// *****************************************************************************
// Purpose
// Inputs
// Remarks
// Returns
function _deletePercentSymbol( &$str )
{
    $str = str_replace( "%", "", $str );
    return $str;
}
// *****************************************************************************
// Purpose	gets all products by categoryID
// Inputs     	$callBackParam item
//					"search_simple"				- string search simple
//					"sort"					- column name to sort
//					"direction"				- sort direction DESC - by descending,
//												by ascending otherwise
//					"searchInSubcategories" - if true function searches
//						product in subcategories, otherwise it does not
//					"searchInEnabledSubcategories"	- this parametr is actual when
//											"searchInSubcategories" parametr is specified
//											if true this function take in mind enabled categories only
//					"categoryID"	- is not set or category ID to be searched
//					"name"			- array of name template
//					"product_code"		- array of product code template
//					"price"			-
//								array of item
//									"from"	- down price range
//									"to"	- up price range
//					"enabled"		- value of column "enabled"
//									in database
//					"extraParametrsTemplate"
// Remarks
// Returns
function prdSearchProductByTemplate($callBackParam, &$count_row, $navigatorParams = null ){
    /*echo '<pre>';
    print_r($navigatorParams);
    echo '</pre>';*/
    // navigator params
    if ( $navigatorParams != null ){
        $offset			= $navigatorParams["offset"];
        $CountRowOnPage	= $navigatorParams["CountRowOnPage"];
    }else{
        $offset = 0;
        $CountRowOnPage = 0;
    }
    if ( isset($callBackParam["extraParametrsTemplate"]) ){
        $replicantExtraParametersTpl = $callBackParam["extraParametrsTemplate"];
    }
    // special symbol prepare
    if ( isset($callBackParam["search_simple"]) ){
        for( $i=0; $i<count($callBackParam["search_simple"]); $i++ )
        {
            $callBackParam["search_simple"][$i] = TransformStringToDataBase( $callBackParam["search_simple"][$i] );
        }
        _deletePercentSymbol( $callBackParam["search_simple"] );
    }
    if ( isset($callBackParam["name"]) ) {
        for( $i=0; $i<count($callBackParam["name"]); $i++ )
            $callBackParam["name"][$i] =
                TransformStringToDataBase( $callBackParam["name"][$i] );
        _deletePercentSymbol( $callBackParam["name"][$i] );
    }
    if ( isset($callBackParam["product_code"]) ){
        for( $i=0; $i<count($callBackParam["product_code"]); $i++ )
        {
            $callBackParam["product_code"][$i] =
                TransformStringToDataBase( $callBackParam["product_code"][$i] );
        }
        _deletePercentSymbol( $callBackParam["product_code"] );
    }
    if ( isset($callBackParam["extraParametrsTemplate"]) ){
        foreach( $callBackParam["extraParametrsTemplate"] as $key => $value )	{
            if ( is_int($key) ){
                $callBackParam["extraParametrsTemplate"][$key] =
                    TransformStringToDataBase( $callBackParam["extraParametrsTemplate"][$key] );
                _deletePercentSymbol( $callBackParam["extraParametrsTemplate"][$key] );
            }
        }
    }
    $where_clause = "";
    if ( isset($callBackParam["search_simple"]) ){
        if (!count($callBackParam["search_simple"])) //empty array
        {
            $where_clause = " where 0";
        }
        else //search array is not empty
        {
            foreach( $callBackParam["search_simple"] as $value )
            {
                if ( $where_clause != "" )
                    $where_clause .= " AND ";
                $where_clause .= " ( LOWER(name) LIKE '%".strtolower($value)."%' OR ".
                    "   LOWER(description) LIKE '%".strtolower($value)."%' OR ".
                    "   LOWER(brief_description) LIKE '%".strtolower($value)."%' ) ";
            }
            if ( $where_clause != "" )
            {
                $where_clause = " where categoryID>1 and enabled=1 and ".$where_clause;
            }
            else
            {
                $where_clause = "where categoryID>1 and enabled=1";
            }
        }
    }else{
        // "enabled" parameter
        if ( isset($callBackParam["enabled"]) )
        {
            if ( $where_clause != "" )
                $where_clause .= " AND ";
            $where_clause.=" enabled=".$callBackParam["enabled"];
        }
        // take into "name" parameter
        if ( isset($callBackParam["name"]) )
        {
            foreach( $callBackParam["name"] as $name )
                if (strlen($name)>0)
                {
                    if ( $where_clause != "" )
                        $where_clause .= " AND ";
                    $where_clause .= " LOWER(name) LIKE '%".strtolower($name)."%' ";
                }
        }
        // take into "product_code" parameter
        if ( isset($callBackParam["product_code"]) )
        {
            foreach( $callBackParam["product_code"] as $product_code )
            {
                if ( $where_clause != "" )
                    $where_clause .= " AND ";
                $where_clause .= " product_code LIKE '%".$product_code."%' ";
            }
        }
        // take into "price" parameter
        if ( isset($callBackParam["price"]) )
        {
            $price = $callBackParam["price"];
            if ( trim($price["from"]) != "" && $price["from"] != null )
            {
                if ( $where_clause != "" )
                    $where_clause .= " AND ";
                $from	= ConvertPriceToUniversalUnit( $price["from"] );
                $where_clause .= "$from<=Price ";
            }
            if ( trim($price["to"]) != "" && $price["to"] != null )
            {
                if ( $where_clause != "" )
                    $where_clause .= " AND ";
                $to		= ConvertPriceToUniversalUnit( $price["to"] );
                $where_clause .= " Price<=$to ";
            }
        }
        // categoryID
        if ( isset($callBackParam["categoryID"]) )
        {
            $searchInSubcategories = false;
            if ( isset($callBackParam["searchInSubcategories"]) )
            {
                if ( $callBackParam["searchInSubcategories"] )
                    $searchInSubcategories = true;
                else
                    $searchInSubcategories = false;
            }
            if ( $searchInSubcategories )
            {
                $where_clause = _getConditionWithCategoryConjWithSubCategories( $where_clause,
                    $callBackParam["categoryID"] );
            }
            else
            {
                $where_clause = _getConditionWithCategoryConj( $where_clause,
                    $callBackParam["categoryID"] );
            }
        }
        if ( $where_clause != "" )
            $where_clause = "where ".$where_clause;
    }
    $order_by_clause = "order by sort_order, name";
    if ( isset($callBackParam["sort"]) ){
        if (	$callBackParam["sort"] == "categoryID"			||
            $callBackParam["sort"] == "name"				||
            $callBackParam["sort"] == "brief_description"	||
            $callBackParam["sort"] == "producer"			||
            $callBackParam["sort"] == "in_stock"			||
            $callBackParam["sort"] == "Price"				||
            $callBackParam["sort"] == "customer_votes"		||
            $callBackParam["sort"] == "customers_rating"	||
            $callBackParam["sort"] == "list_price"			||
            $callBackParam["sort"] == "sort_order"			||
            $callBackParam["sort"] == "items_sold"			||
            $callBackParam["sort"] == "product_code"		||
            $callBackParam["sort"] == "shipping_freight"		)
        {
            if($callBackParam["sort"] == "producer")
            {
                //echo '7';
                //$order_by_clause = "order by producer, name";
                if(isset($callBackParam["brend"]) && ($callBackParam["brend"] <> 'all'))
                {
                    $where_clause .= ' AND producer = \''.$callBackParam["brend"].'\' ';
                    /*echo '1';
                    echo $where_clause;*/
                }
            }
            else
            {
                if($callBackParam["sort"] == "Price")
                    $order_by_clause = " order by  IF (".$callBackParam["sort"]."=0, 1,0), ".$callBackParam["sort"]." ASC";
                else
                    $order_by_clause = " order by ".$callBackParam["sort"]." ASC";
                //		IF(Price = 0, 1, 0), Price ASC		$order_by_clause = " order by if(case when in_stock>0 then 0 else 1 end, ".$callBackParam["sort"].") ASC ";

                if (  isset($callBackParam["direction"]) )
                    if (  $callBackParam["direction"] == "DESC" )
                    {
                        if($callBackParam["sort"] == "Price")
                            $order_by_clause = " order by  IF (".$callBackParam["sort"]."=0, 1,0), ".$callBackParam["sort"]." DESC";
                        else
                            $order_by_clause = " order by ".$callBackParam["sort"]." DESC ";
                    }
            }
        }
    }
    $sqlQueryCount = "select count(*) from ".PRODUCTS_TABLE.
        " $where_clause $order_by_clause";
    $q = db_query( $sqlQueryCount );
    $products_count = db_fetch_row($q);
    $products_count = $products_count[0];
    $sqlQuery = "
		SELECT categoryID, producer, num_topsale,  title_one,title_two,url_name, name, brief_description,brief_description2, customers_rating, Price,Price_UE, in_stock, skidka, customer_votes, list_price, productID,
		default_picture, sort_order, items_sold, enabled, product_code, description, shipping_freight FROM ".PRODUCTS_TABLE." $where_clause $order_by_clause
	";
//print $sqlQuery.'<br>';die;
//==========================================================
    if(isset($callBackParam["brend"]))
        $callBackParam["brend"] = trim($callBackParam["brend"]);
//=========================================ѕодсчет количества товаров отфильтрованных
    if(isset($callBackParam["sort"]))
    {
        if($callBackParam["sort"] == "producer")
        {
            //$order_by_clause = "order by producer, name";
            if(isset($callBackParam["brend"]) && ($callBackParam["brend"] <> 'all'))
            {
                $q1 = db_query( $sqlQuery );
                $qp = 0;
                while( $row1 = db_fetch_row($q1) )
                {
                    //echo $row1['producer'].'<br>';
                    $row1['producer'] = trim($row1['producer']);
                    if($row1['producer'] == $callBackParam["brend"])
                    {
                        $qp++;
                    }
                }
                if($qp >0)
                    $products_count = $qp;
            }
        }
    }
    //echo $qp;
//=========================================
    $q = db_query( $sqlQuery );
    $result = array();
    $i = 0;
//==========================================================
    if ($offset >= 0 && $offset <= $products_count ){
        while( $row = db_fetch_row($q) )
        {
            if(isset($qp))
            {
                if($qp >0) //=================== »спользуетс€ дл€ фильтра по производителю===============================
                {
                    $row['producer'] = trim($row['producer']);
                    if($row['producer'] !==$callBackParam["brend"]) continue;
                }
            }
            if ( isset($callBackParam["extraParametrsTemplate"]) ){
                // take into "extra" parametrs
                $testResult = _testExtraParametrsTemplate( $row["productID"], $replicantExtraParametersTpl );
                if ( !$testResult )
                    continue;
            }
            if ( ($i >= $offset && $i < $offset + $CountRowOnPage) || $navigatorParams == null  ){
                $row["PriceWithUnit"] = show_price($row["Price"]);
                $row["list_priceWithUnit"] = show_price($row["list_price"]);
                // you save (value)
                $row["SavePrice"] = show_price($row["list_price"]-$row["Price"]);
                // you save (%)
                if ($row["list_price"])
                    $row["SavePricePercent"] = ceil(((($row["list_price"]-$row["Price"])/$row["list_price"])*100));
                _setPictures( $row );
                $row["product_extra"]		= GetExtraParametrs( $row["productID"] );
                $row["PriceWithOutUnit"]	= show_priceWithOutUnit( $row["Price"] );
                if ( ((float)$row["shipping_freight"]) > 0 )
                    $row["shipping_freightUC"] = show_price( $row["shipping_freight"] );
                $row["name"]				= TransformDataBaseStringToText( $row["name"] );
                $row["description"]			= TransformDataBaseStringToHTML_Text( $row["description"] );
                $row["brief_description"]	= TransformDataBaseStringToHTML_Text( $row["brief_description"] );
                $row["product_code"]		= TransformDataBaseStringToText( $row["product_code"] );
                $result[] = $row;
            }
            $i++;
        }
    }
    $count_row = $i;
    return $result;
}
function prdGetMetaKeywordTag( $productID )
{
    $productID = (int)$productID;
    $q = db_query("select meta_description from ".
        PRODUCTS_TABLE.
        " where productID=$productID" );
    if ( $row=db_fetch_row($q) )
        return TransformDataBaseStringToText( trim($row["meta_description"]) );
    else
        return "";
}
function prdGetMetaTags( $productID ) //gets META keywords and description - an HTML code to insert into <head> section
{
    $productID = (int) $productID;
    $q = db_query( "select meta_description, meta_keywords from ".
        PRODUCTS_TABLE." where productID=".$productID );
    $row = db_fetch_row($q);
    $meta_description	= TransformDataBaseStringToText( trim($row["meta_description"]) );
    $meta_keywords		= TransformDataBaseStringToText( trim($row["meta_keywords"]) );
    $res = "";
    if(isset($_GET['discuss']) && isset($_GET['discuss']) == 'yes' )
    {
        $discuss =  ' обсуждение ';
    }
    else
        $discuss =  ' ';
    if  ( $meta_description != "" )
        $res .= "<meta name=\"Description\" content=\"".$discuss.str_replace("\"","&quot;",$meta_description)."\">\n";
    if  ( $meta_keywords != "" )
        $res .= "<meta name=\"KeyWords\" content=\"".$discuss.str_replace("\"","&quot;",$meta_keywords)."\" >\n";
    return $res;
}
function GetProductBrends()
{
    $q = db_query("SELECT * FROM ".
        PRODUCTS_BRENDS.
        "   ORDER BY  name ASC") or die (db_error());
    $result = array(); //parents
    $i = 0;
    while ($row = db_fetch_row($q))
    {
        $result[$i]['id'] = $row["id"];
        $result[$i]['kurs'] = $row["kurs"];
        $result[$i]['valuta'] = $row["valuta"];
        $result[$i]['warranty'] = $row["warranty"];
        $result[$i]['filename'] = $row["filename"];
        $result[$i++]['name'] = $row["name"];
    }
    /* echo '<pre>';
     print_r($result);
     echo '</pre>'; */
    return $result;
}
function GetProductClass()
{
    $q = db_query("SELECT * FROM ".
        PRODUCTS_CLASS.
        "   ORDER BY  name ASC") or die (db_error());
    $result = array(); //parents
    $i = 0;
    while ($row = db_fetch_row($q))
    {
        $result[$i]['id'] = $row["id"];
        $result[$i++]['name'] = $row["name"];
    }
    /*	echo '<pre>';
    print_r($result);
    echo '</pre>';*/
    return $result;
}
function GetProductClassRukzak()
{
    $q = db_query("SELECT * FROM ".
        PRODUCTS_CLASS_RUKZAK.
        "   ORDER BY  name ASC") or die (db_error());
    $result = array(); //parents
    $i = 0;
    while ($row = db_fetch_row($q))
    {
        $result[$i]['id'] = $row["id"];
        $result[$i++]['name'] = $row["name"];
    }
    /*	echo '<pre>';
    print_r($result);
    echo '</pre>';*/
    return $result;
}
function _getProductIDsForSort($categoryID)
{
    $i=0;
    $productsID ='';
    $sql_string = "SELECT productID FROM ".CATEGORIY_PRODUCT_TABLE." WHERE categoryID IN (".$categoryID.")";
    //echo $sql_string;
    $q = db_query($sql_string) or die (db_error());
    //echo '<br>'.$categoryID.'<br>';
    //echo "SELECT productID FROM ".CATEGORIY_PRODUCT_TABLE." WHERE categoryID IN (".$categoryID.")";
    while ($row = db_fetch_row($q))
    {
        $productsID= $productsID.$row[0].',';
        $i++;
    }
    $arr_prod['productsID'] = $productsID;
    $arr_prod['count_productsID'] = $i;
    //echo $arr_prod['productsID'];
    //echo $arr_prod['count_productsID'];
    return $arr_prod;
}
function  _getProductIDsForSort1($productsID, $i, $categoryID)
{
    $q = db_query("SELECT productID FROM ".PRODUCTS_TABLE." WHERE categoryID IN ($categoryID)") or die (db_error());
    //echo "SELECT productID FROM ".PRODUCTS_TABLE." WHERE categoryID=$categoryID";
    while ($row = db_fetch_row($q))
    {
        $productsID= $productsID.$row[0].',';
        $i++;
    }
    $arr_prod1['productsID'] = $productsID;
    $arr_prod1['count_productsID'] = $i;
    //echo $arr_prod['productsID'];
    //echo $arr_prod['count_productsID'];
    return $arr_prod1;
}
function getProducersByCategory($categoryID)
{
    //===========================================¬ыт€гиваем айди продуктов, которые св€занны с категорией
    $arr_prod = _getProductIDsForSort($categoryID);
    $productsID = $arr_prod['productsID'];
    $i = $arr_prod['count_productsID'];
    //echo $productsID.'<br>';
    //===========================================¬ыт€гиваем айди продуктов, которые принадлежат  категории
    $arr_prod1 = _getProductIDsForSort1($productsID, $i, $categoryID);
    $productsID = $arr_prod1['productsID'];
    $i = $arr_prod1['count_productsID'];
    //echo $productsID;
    //echo $i;
    //==========================================================
    if($i==0) //===============  огда у категории есть подкатегории с продуктами
    { //echo '1';
        $categoryIDs = '';
        $q = db_query("SELECT categoryID FROM ".CATEGORIES_TABLE." WHERE parent=$categoryID") or die (db_error());
        //echo "SELECT categoryID FROM ".CATEGORIY_TABLE." WHERE parent=$categoryID"; die();
        $y=0;
        while ($row = db_fetch_row($q))
        {
            $categoryIDs= $categoryIDs.$row[0].',';
            $y++;
        }
        //echo $y; exit();
        if($y>0)
        {
            $categoryIDs = substr($categoryIDs, 0, -1);
            //===========================================¬ыт€гиваем айди продуктов, которые св€занны с категорией
            $arr_prod = _getProductIDsForSort($categoryIDs);
            $productsID = $arr_prod['productsID'];
            $i = $arr_prod['count_productsID'];
            //echo $productsID.'<br>';
            //===========================================¬ыт€гиваем айди продуктов, которые принадлежат  категории
            $arr_prod1 = _getProductIDsForSort1($productsID, $i, $categoryIDs);
            $productsID = $arr_prod1['productsID'];
            $i = $arr_prod1['count_productsID'];
            //echo $productsID;
            //echo $i;
            //print_r($categoryIDs);
            //echo $productsID;
            //echo $i;
        }
    }
    //===========================================================
    if($i >0)
    {
        $productsID = substr($productsID, 0, -1);
        $q = db_query("SELECT producer FROM ".PRODUCTS_TABLE." WHERE productID IN ($productsID) AND enabled=1 AND producer <> 'null' GROUP BY producer") or die (db_error());
        //echo "SELECT producer FROM ".PRODUCTS_TABLE." WHERE productID IN ($productsID) AND enabled=1 AND producer <> 'null' GROUP BY producer";
        while ($row = db_fetch_row($q))
        {
            $producers[]=$row[0];
        }
        /*echo '<pre>';
            print_r($producers);
        echo '</pre>';*/
    }
    else
        $producers = 0;
    return $producers;
}
?>
