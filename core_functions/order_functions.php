<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
function ordGetOrders( $callBackParam, &$count_row, $navigatorParams = null )
{
	global $selected_currency_details;
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
	$where_clause = "";
	if ( isset($callBackParam["orderStatuses"]) )
	{
		foreach( $callBackParam["orderStatuses"] as $statusID )
		{
			if ( $where_clause == "" )
				$where_clause .= " statusID=".$statusID;
			else 
				$where_clause .= " OR statusID=".$statusID;
		}
		if ( isset($callBackParam["customerID"]) )
		{
			if ( $where_clause != "" )
				$where_clause = " customerID=".$callBackParam["customerID"].
						" AND ( ".$where_clause." ) ";
			else
				$where_clause = " customerID=".$callBackParam["customerID"];
		}
		if ( $where_clause != "" )
			$where_clause = " where ".$where_clause;
		else
			$where_clause = " where statusID = -1 ";
	}
	else
	{
		if ( isset($callBackParam["customerID"]) )
			$where_clause .= " customerID = ".$callBackParam["customerID"];
		if ( isset($callBackParam["orderID"]) )
		{
			if ( $where_clause != "" )
				$where_clause .= " and orderID=".$callBackParam["orderID"];
			else
				$where_clause .= " orderID=".$callBackParam["orderID"];
		}
		if ( $where_clause != "" )
			$where_clause = " where ".$where_clause;
		else
			$where_clause = " where statusID = -1 ";
	}
	$order_by_clause = "";
	if ( isset($callBackParam["sort"]) )
	{
		$order_by_clause .= " order by ".$callBackParam["sort"]." ";
		if ( isset($callBackParam["direction"]) )
		{
			if ( $callBackParam["direction"] == "ASC" )
				$order_by_clause .= " ASC ";
			else
				$order_by_clause .= " DESC ";
		}
		else
			$order_by_clause .= " ASC ";
	}
	$q = db_query( "select orderID, customerID, order_time, customer_ip, shipping_type, ".
		" payment_type, customers_comment, statusID, shipping_cost, order_amount, ".
		" order_discount, currency_code, currency_value, customer_email, ".
		" shipping_firstname, shipping_lastname, ".
		" shipping_country,	shipping_state, shipping_zip, shipping_city, ".
		" shipping_address, billing_firstname, billing_lastname, ".
		" billing_country, billing_state, billing_zip, billing_city, ".
		" billing_address, cc_number, cc_holdername, cc_expires, cc_cvv, shippingServiceInfo ".
		" from ".ORDERS_TABLE." ".$where_clause." ".$order_by_clause 
		);
	$res = array();
	$i = 0;
	$total_sum = 0;
	while( $row = db_fetch_row($q) )
	{
		if ( ($i >= $offset && $i < $offset + $CountRowOnPage) || 
				$navigatorParams == null  )
		{       	
			$row["OrderStatus"] = ostGetOrderStatusName( $row["statusID"] );
			$total_sum += $row["order_amount"];
			$row["order_amount"] = $row["currency_code"]." ".RoundFloatValueStr($row["currency_value"]*$row["order_amount"]);
			$q_orderContent = db_query( "select name, Price, Quantity, tax, load_counter, itemID from ".
				       ORDERED_CARTS_TABLE.
				       " where orderID=".$row["orderID"] );
			$content = array();
			while( $orderContentItem = db_fetch_row($q_orderContent) )
			{
				$productID = GetProductIdByItemId( $orderContentItem["itemID"] );
				$product   = GetProduct( $productID );
				if ( $product["eproduct_filename"] != null &&
				     strlen($product["eproduct_filename"]) > 0 )
				{
					if (  file_exists("./products_files/".$product["eproduct_filename"])   )
					{
							$orderContentItem["eproduct_filename"] = $product["eproduct_filename"];
							$orderContentItem["file_size"] = filesize( "./products_files/".$product["eproduct_filename"] );
							if ( isset($callBackParam["customerID"]) )
							{
								$custID = $callBackParam["customerID"];
							}
							else
							{
								$custID = -1;
							}
							$orderContentItem["getFileParam"] = 
								"orderID=".$row["orderID"]."&".
								"productID=".$productID."&".
								"customerID=".$custID;
							//additional security for non authorized customers
							if ($custID == -1)
							{
								$orderContentItem["getFileParam"] .= "&order_time=".base64_encode($row["order_time"]);
							}
							$orderContentItem["getFileParam"] = cryptFileParamCrypt( 
											$orderContentItem["getFileParam"], null );
							$orderContentItem["load_counter_remainder"]		= 
									$product["eproduct_download_times"] - $orderContentItem["load_counter"];
							$currentDate	= dtGetParsedDateTime( get_current_time() );
							$betweenDay		= _getDayBetweenDate( 
									dtGetParsedDateTime( $row["order_time"] ), 
									$currentDate );
							$orderContentItem["day_count_remainder"]		= 
									$product["eproduct_available_days"] - $betweenDay;
							//if ( $orderContentItem["day_count_remainder"] < 0 )
							//		$orderContentItem["day_count_remainder"] = 0;
					}
				}
				$content[] = $orderContentItem;
			}
			$row["content"] = $content;
			$row["order_time"] = format_datetime( $row["order_time"] );
			$res[] = $row;
		}
		$i++;
	}
	$count_row = $i;
	if ( isset($callBackParam["customerID"]) )
	{
		if ( count($res) > 0 )
		{
		        $q = db_query( "select CID from ".CUSTOMERS_TABLE.
				" where customerID=".$callBackParam["customerID"] );
			$row = db_fetch_row($q);
			if ( $row["CID"]!=null && $row["CID"]!="" )
			{
					$q = db_query( "select currency_value, currency_iso_3 from ".
						CURRENCY_TYPES_TABLE.
						" where CID=".$row["CID"] );
					$row = db_fetch_row($q);
					$res[0]["total_sum"] = $row["currency_iso_3"]." ".$row["currency_value"]*$total_sum;
			}
			else
			{
					$res[0]["total_sum"] = $selected_currency_details["currency_iso_3"].
								" ".$selected_currency_details["currency_value"]*$total_sum;
			}
		}
	}
	return $res;
}
function ordGetDistributionByStatuses( $log )
{
	$q = db_query( "select statusID, status_name, sort_order from ".
		ORDER_STATUES_TABLE.
		" order by sort_order, status_name" );
	$data = array();
	while( $row = db_fetch_row( $q ) )
	{
	 	$q1 = db_query( "select count(*) from ".ORDERS_TABLE.
			" where statusID=".$row["statusID"]." AND ".
			" customerID='".regGetIdByLogin($log)."' " );
	 	$row1 	= db_fetch_row($q1);
		if ( $row["statusID"] == ostGetCanceledStatusId() )
			$row["status_name"] = STRING_CANCELED_ORDER_STATUS;
		$item	= array( "status_name" => $row["status_name"], 
					"count" => $row1[0] );
		$data[] = $item;
	}
	return $data;
}
function _moveSessionCartContentToOrderedCart( $orderID )
{
	$i=0;
	$sql = "
		DELETE FROM ".ORDERED_CARTS_TABLE." WHERE orderID=".intval($orderID)."
	";
	db_query($sql);
	foreach( $_SESSION["gids"] as $productID )
	{
		if ( $productID == 0 )
			continue;
		$q = db_query( "select count(productID) from ".PRODUCTS_TABLE.
			" where productID=".$productID );
		$row = db_fetch_row($q);
		if ( $row[0] == 0 )
			continue;
		// create new item
		db_query( "insert into ".SHOPPING_CART_ITEMS_TABLE.
			"(productID) values('".$productID."')" );
		$itemID=db_insert_id();
		foreach( $_SESSION["configurations"][$i] as $var )
		{
			db_query("insert into ".
					SHOPPING_CART_ITEMS_CONTENT_TABLE."(itemID, variantID) ".
					"values( '".$itemID."', '".$var."')" );
		}
		$q_product = db_query( "select name, Price, product_code from ".PRODUCTS_TABLE." where productID=$productID " );
		$product = db_fetch_row( $q_product );
		$quantity = $_SESSION["counts"][$i];
		$variants = array();
		foreach( $_SESSION["configurations"][$i] as $var )
			$variants[] = $var;
		$options = '';
		foreach( $variants as $var )
		{
			$options .= str_replace("+", " ",$var);
		}
		if ( $options != "" )
			$productComplexName = $product["name"]." ( ".$options." )";
		else
			$productComplexName = $product["name"];
			
/*echo '$_SESSION["configurations"]<pre>';
	print_r($_SESSION["configurations"]);
echo '</pre>';
echo '$productComplexName = '.$productComplexName;*/
		if ( strlen($product["product_code"]) > 0 )
			$productComplexName = "[".$product["product_code"]."] ".$productComplexName;
		$price = GetPriceProductWithOption( $variants, $productID );
		$shippingAddress = array(
					"countryID" => $_SESSION["receiver_countryID"],
					"zoneID"	=> $_SESSION["receiver_zoneID"],
					"zip"		=> $_SESSION["receiver_zip"] );
				$billingAddress = array(
					"countryID" => $_SESSION["billing_countryID"],
					"zoneID"	=> $_SESSION["billing_zoneID"],
					"zip"		=> $_SESSION["billing_zip"] );
		$tax = taxCalculateTax2( $productID, $shippingAddress, $billingAddress );
		$sql = "insert into ".ORDERED_CARTS_TABLE." ( itemID, orderID, name, Price, Quantity, tax ) ".
			 "values( ".$itemID.", ".$orderID.", '".xEscapeSQLstring($productComplexName)."', '".$price."', ".
				$quantity.", ".$tax." ) ";
		db_query( $sql );
		$i++;
	}
	unset($_SESSION["gids"]);
	unset($_SESSION["counts"]);
	unset($_SESSION["configurations"]);
	///session_unregister("gids"); //calling session_unregister() is required since unset() may not work on some systems
	//session_unregister("counts");
	//session_unregister("configurations");
}
function _quickOrderUnsetSession()
{
	unset( $_SESSION["first_name"] );
	unset( $_SESSION["last_name"] );
	unset( $_SESSION["email"] );
	unset( $_SESSION["billing_first_name"] );
	unset( $_SESSION["billing_last_name"] );
	unset( $_SESSION["billing_state"] );
	unset( $_SESSION["billing_zip"] );
	unset( $_SESSION["billing_city"] );
	unset( $_SESSION["billing_address"] );
	unset( $_SESSION["billing_countryID"] );
	unset( $_SESSION["billing_zoneID"] );
	unset( $_SESSION["receiver_first_name"] );
	unset( $_SESSION["receiver_middle_name"] );
	unset( $_SESSION["receiver_last_name"] );
	unset( $_SESSION["receiver_state"] );
	unset( $_SESSION["receiver_zip"] );
	unset( $_SESSION["receiver_city"] );
	unset( $_SESSION["receiver_address"] );
	unset( $_SESSION["receiver_countryID"] );
	unset( $_SESSION["receiver_zoneID"] );
}
function _getOrderById( $orderID )
{
	$sql = "select ".
		"	orderID, ".
		"	customerID, ".
		"	order_time, ".
		"	customer_ip, ".
		"	shipping_type, ".
		"	payment_type, ".
		"	customers_comment, ".
		"	statusID, ".
		"	shipping_cost, ".
		"	order_discount, ".
		"	order_amount, ".
		"	currency_code, ".
		"	currency_value, ".
		"	customer_firstname, ".
		"	customer_lastname, ".
		"	customer_email, ".
		"	shipping_firstname, ".
		"	shipping_lastname, ".
		"	shipping_country, ".
		"	shipping_state, ".
		"	shipping_zip, ".
		"	shipping_city, ".
		"	shipping_address, ".
		"	billing_firstname, ".
		"	billing_lastname, ".
		"	billing_country, ".
		"	billing_state, ".
		"	billing_zip, ".
		"	billing_city, ".
		"	billing_address, ".				
		"	cc_number, ".
		"	cc_holdername, ".
		"	cc_expires, ".
		"	cc_cvv ".
		" from ".ORDERS_TABLE." where orderID=".$orderID;
	$q = db_query( $sql );
	return db_fetch_row($q);
}
function _sendOrderNotifycationToCustomer( $orderID, &$smarty_mail, $email, $login,
			 	$payment_email_comments_text, $shipping_email_comments_text, $tax )
{
	$order = _getOrderById( $orderID );
	$smarty_mail->assign( "customer_firstname", $order["customer_firstname"] );
	$smarty_mail->assign( "orderID", $order["orderID"] );
	$smarty_mail->assign( "discount", RoundFloatValueStr($order["order_discount"]) );
	$smarty_mail->assign( "shipping_type", $order["shipping_type"] );
	$smarty_mail->assign( "shipping_firstname", $order["shipping_firstname"] );
	$smarty_mail->assign( "shipping_lastname", $order["shipping_lastname"] );
	$smarty_mail->assign( "shipping_country", $order["shipping_country"] );
	$smarty_mail->assign( "shipping_state", $order["shipping_state"] );
	$smarty_mail->assign( "shipping_zip", $order["shipping_zip"] );
	$smarty_mail->assign( "shipping_city", $order["shipping_city"] );
	$smarty_mail->assign( "shipping_address", $order["shipping_address"] );
	$smarty_mail->assign( "shipping_cost", RoundFloatValueStr($order["currency_value"]*$order["shipping_cost"])." ".$order["currency_code"] );
	$smarty_mail->assign( "payment_type", $order["payment_type"] );
	$smarty_mail->assign( "billing_firstname", $order["billing_firstname"] );
	$smarty_mail->assign( "billing_lastname", $order["billing_lastname"] );
	$smarty_mail->assign( "billing_country", $order["billing_country"] );
	$smarty_mail->assign( "billing_state", $order["billing_state"] );
	$smarty_mail->assign( "billing_zip", $order["billing_zip"] );
	$smarty_mail->assign( "billing_city", $order["billing_city"] );
	$smarty_mail->assign( "billing_address", $order["billing_address"] );
	$smarty_mail->assign( "order_amount", RoundFloatValueStr($order["currency_value"]*$order["order_amount"])." ".$order["currency_code"] );
	$smarty_mail->assign( "payment_comments", $payment_email_comments_text );
	$smarty_mail->assign( "shipping_comments", $shipping_email_comments_text );
	$smarty_mail->assign( "order_total_tax", RoundFloatValueStr($order["currency_value"]*$tax)." ".$order["currency_code"] );
	//additional reg fields
	$addregfields = GetRegFieldsValuesByOrderID( $orderID );
	$smarty_mail->assign("customer_add_fields", $addregfields);
	$content = ordGetOrderContent( $orderID );
	for( $i=0; $i<count($content); $i++ )
	{
		$productID = GetProductIdByItemId( $content[$i]["itemID"] );
		if ( $productID == null || trim($productID) == "" )
			continue;
		$sql = "select  name, product_code";
		$sql .= ", eproduct_filename, ".
			" eproduct_available_days, eproduct_download_times";
		$sql .= " from ".PRODUCTS_TABLE.
			" where productID=".$productID;
		$q = db_query( $sql );
		$product = db_fetch_row($q);
		
	
		
		
		
		
		
		$content[$i]["product_code"] = $product["product_code"];
		$variants	= GetConfigurationByItemId( $content[$i]["itemID"] );
		//$options	= GetStrOptions( $variants );

		
	/*	
		if ( $options != "" )
			$content[$i]["name"] = $product["name"]."(".$options.")";
		else
			$content[$i]["name"] = $product["name"];*/
		$content[$i]["Price"] = ( RoundFloatValueStr($order["currency_value"]*$content[$i]["Price"]) )." ".$order["currency_code"];
		if ( strlen($product["eproduct_filename"])>0 && file_exists("product_files/".$product["eproduct_filename"]) )
		{
			if ($login != null)
				$customerID = regGetIdByLogin( $login );
			else
				$customerID = -1;
			$content[$i]["eproduct_filename"]		= $product["eproduct_filename"];
			$content[$i]["eproduct_available_days"] = $product["eproduct_available_days"];
			$content[$i]["eproduct_download_times"] = $product["eproduct_download_times"];
			$content[$i]["file_size"]				= filesize( "./products_files/".$product["eproduct_filename"] );
			$content[$i]["getFileParam"]			= 
										"orderID=".$order["orderID"]."&".
										"productID=".$productID."&".
										"customerID=".$customerID;
			//additional security for non authorized customers
			if ($customerID == -1)
			{
				$content[$i]["getFileParam"] .= "&order_time=".base64_encode($order["order_time"]);
			}
			$content[$i]["getFileParam"] = 
				cryptFileParamCrypt( $content[$i]["getFileParam"], null );
		}
		$content[$i]["name"] = $content[$i][0];
	}
	
	$smarty_mail->assign( "content", $content );
/*echo '<pre>';
	print_r($content);
echo '</pre>';*/
	
//exit();
	$html = $smarty_mail->fetch( "order_notification.txt" );
	$res = ss_mail( $email, "Order #".$orderID, 
				$html, 
				"From: \"".CONF_SHOP_NAME."\"<".CONF_GENERAL_EMAIL.">\n".
					stripslashes(EMAIL_MESSAGE_PARAMETERS)."\nReturn-path: <".
					CONF_GENERAL_EMAIL.">" );
}
function _sendOrderNotifycationToAdmin( $orderID, &$smarty_mail, $tax )
{
	$order = _getOrderById( $orderID );
	$smarty_mail->assign( "customer_firstname", $order["customer_firstname"] );
	$smarty_mail->assign( "customer_lastname", $order["customer_lastname"] );
	$smarty_mail->assign( "customer_email", $order["customer_email"] );
	$smarty_mail->assign( "customer_ip", $order["customer_ip"] );
	$smarty_mail->assign( "order_time", format_datetime($order["order_time"]) );
	$smarty_mail->assign( "customer_comments", $order["customers_comment"] );
	$smarty_mail->assign( "discount", $order["order_discount"] );
	$smarty_mail->assign( "shipping_type", $order["shipping_type"] );
	$smarty_mail->assign( "shipping_cost", 
			$order["currency_code"]." ".
				RoundFloatValueStr($order["currency_value"]*$order["shipping_cost"]) );
	$smarty_mail->assign( "payment_type", $order["payment_type"] );
	$smarty_mail->assign( "shipping_firstname", $order["shipping_firstname"] );
	$smarty_mail->assign( "shipping_lastname", $order["shipping_lastname"] );
	$smarty_mail->assign( "shipping_country", $order["shipping_country"] );
	$smarty_mail->assign( "shipping_state", $order["shipping_state"] );
	$smarty_mail->assign( "shipping_zip", $order["shipping_zip"] );
	$smarty_mail->assign( "shipping_city", $order["shipping_city"] );
	$smarty_mail->assign( "shipping_address", $order["shipping_address"] );
	$smarty_mail->assign( "billing_firstname", $order["billing_firstname"] );
	$smarty_mail->assign( "billing_lastname", $order["billing_lastname"] );
	$smarty_mail->assign( "billing_country", $order["billing_country"] );
	$smarty_mail->assign( "billing_state", $order["billing_state"] );
	$smarty_mail->assign( "billing_zip", $order["billing_zip"] );
	$smarty_mail->assign( "billing_city", $order["billing_city"] );
	$smarty_mail->assign( "billing_address", $order["billing_address"] );
	$smarty_mail->assign( "billing_address", $order["billing_address"] );
	$smarty_mail->assign( "order_amount", 
		$order["currency_code"]." ".
			RoundFloatValueStr($order["currency_value"]*$order["order_amount"]) );
	$smarty_mail->assign( "orderID", $order["orderID"] );
	$smarty_mail->assign( "total_tax", $order["currency_code"]." ".
								RoundFloatValueStr($order["currency_value"]*$tax) );
	//additional reg fields
	$addregfields = GetRegFieldsValuesByOrderID( $orderID );
	$smarty_mail->assign("customer_add_fields", $addregfields);
	//fetch order content from the database
	$content = ordGetOrderContent( $orderID );
	for( $i=0; $i<count($content); $i++ )
	{
		$productID = GetProductIdByItemId( $content[$i]["itemID"] );
		if ( $productID == null || trim($productID) == "" )
			continue;
		$q = db_query( "select  name, product_code from ".PRODUCTS_TABLE.
			" where productID=".$productID );
		$product = db_fetch_row($q);
		$content[$i]["product_code"] = $product["product_code"];
		$variants	= GetConfigurationByItemId( $content[$i]["itemID"] );
		$options	= GetStrOptions( $variants );
		if ( $options != "" )
			$content[$i]["name"] = $product["name"]."(".$options.")";
		else
			$content[$i]["name"] = $product["name"];
		$content[$i]["Price"] = $order["currency_code"]." ".( 
			RoundFloatValueStr($order["currency_value"]*$content[$i]["Price"])  );
		$content[$i]["name"] = $content[$i][0];
	}
	
	$smarty_mail->assign( "content", $content );
	$html = $smarty_mail->fetch( "admin_order_notification.txt" );
	$res = ss_mail( CONF_ORDERS_EMAIL, "Order #".$orderID,
				$html, 
				"From: \"".CONF_SHOP_NAME."\"<".CONF_GENERAL_EMAIL.">\n".
					stripslashes(EMAIL_MESSAGE_PARAMETERS)."\nReturn-path: <".
					CONF_GENERAL_EMAIL.">" );
}
// *****************************************************************************
// Purpose	get order amount
// Inputs   
//				$cartContent is result of cartGetCartContent function
// Remarks	
// Returns	
function ordOrderProcessing(
		$shippingMethodID, $paymentMethodID,
		$shippingAddressID, $billingAddressID, 
		$shippingModuleFiles, $paymentModulesFiles, $customers_comment, 
		$cc_number,
		$cc_holdername, 
		$cc_expires,
		$cc_cvv,
		$log, 
		$smarty_mail, $shServiceID = 0)
{
	if ( $log != null )$customerID = regGetIdByLogin( $log );
	else $customerID = NULL;
	if ( $log != null )$customerInfo = regGetCustomerInfo2( $log, true );
	else{
		$customerInfo["first_name"] 	= $_SESSION["first_name"];
		$customerInfo["last_name"]	= $_SESSION["last_name"];
		$customerInfo["Email"]		= $_SESSION["email"];
		$customerInfo["affiliationLogin"] = $_SESSION["affiliationLogin"];
	}
	$order_time = get_current_time();
	$customer_ip = stGetCustomerIP_Address();
	$statusID = ostGetNewOrderStatus();
	$customer_affiliationLogin = isset($customerInfo["affiliationLogin"])?$customerInfo["affiliationLogin"]:'';
	$customer_email	= $customerInfo["Email"];
	$currencyID = currGetCurrentCurrencyUnitID();
	if ( $currencyID != 0 )
	{
		$currentCurrency = currGetCurrencyByID( $currencyID );
		$currency_code	 = $currentCurrency["currency_iso_3"];
		$currency_value	 = $currentCurrency["currency_value"];
	}
	else
	{
		$currency_code	= "";
		$currency_value = 1;
	}
	// get shipping address
	if ( $shippingAddressID != 0 )
	{
		$shippingAddress					= regGetAddress( $shippingAddressID );
		$shippingAddressCountry				= cnGetCountryById( $shippingAddress["countryID"] );
		$shippingAddress["country_name"]	= $shippingAddressCountry["country_name"];
	}
	else
	{
		$shippingCountryName	= cnGetCountryById( $_SESSION["receiver_countryID"] );
		$shippingCountryName	= $shippingCountryName["country_name"];
		$shippingAddress["first_name"]		= $_SESSION["receiver_first_name"];
		$shippingAddress["middle_name"]		= $_SESSION["receiver_middle_name"];
		$shippingAddress["last_name"]		= $_SESSION["receiver_last_name"];
		$shippingAddress["country_name"]	= $shippingCountryName;
		$shippingAddress["state"]			= $_SESSION["receiver_state"];
		$shippingAddress["zip"]				= $_SESSION["receiver_zip"];
		$shippingAddress["city"]			= $_SESSION["receiver_city"];
		$shippingAddress["address"]			= $_SESSION["receiver_address"];
		$shippingAddress["zoneID"]			= $_SESSION["receiver_zoneID"];
	}
	if ( is_null($shippingAddress["state"]) || trim($shippingAddress["state"])=="" )
	{
		$zone = znGetSingleZoneById( $shippingAddress["zoneID"] );
		$shippingAddress["state"] = $zone["zone_name"];
	}
	// get billing address
	if ( $billingAddressID != 0 )
	{
		$billingAddress						= regGetAddress( $billingAddressID );
		$billingAddressCountry				= cnGetCountryById( $billingAddress["countryID"] );
		$billingAddress["country_name"]		= $billingAddressCountry["country_name"];
	}
	else
	{
		$billingCountryName = cnGetCountryById( $_SESSION["billing_countryID"] );
		$billingCountryName	= $billingCountryName["country_name"];
		$billingAddress["first_name"]	= $_SESSION["billing_first_name"];
		$billingAddress["last_name"]	= $_SESSION["billing_last_name"];
		$billingAddress["country_name"] = $billingCountryName;
		$billingAddress["state"]		= $_SESSION["billing_state"];
		$billingAddress["zip"]			= $_SESSION["billing_zip"];
		$billingAddress["city"]			= $_SESSION["billing_city"];
		$billingAddress["address"]		= $_SESSION["billing_address"];
		$billingAddress["zoneID"]		= $_SESSION["billing_zoneID"];
	}
	if ( is_null($billingAddress["state"]) || trim($billingAddress["state"])=="" )
	{
		$zone = znGetSingleZoneById( $billingAddress["zoneID"] );
		$billingAddress["state"] = $zone["zone_name"];
	}
	$cartContent = cartGetCartContent();
	if ( $log != null )
		$addresses = array( $shippingAddressID, $billingAddressID );
	else
	{
		$addresses = array(	
						array( 
								"countryID" => $_SESSION["receiver_countryID"], 
								"zoneID"	=> $_SESSION["receiver_zoneID"], 
								"zip"		=> $_SESSION["receiver_zip"] ), 
						array( 
								"countryID" => $_SESSION["billing_countryID"], 
								"zoneID"	=> $_SESSION["billing_zoneID"], 
								"zip"		=> $_SESSION["billing_zip"] ) 
					);
	}
	$orderDetails = array (
			"first_name" => $shippingAddress["first_name"],
			"last_name" => $shippingAddress["last_name"],
			"email" => $customerInfo["Email"],
			"order_amount" => oaGetOrderAmountExShippingRate( $cartContent, $addresses, $log, FALSE )
	);
	$shippingMethod					= shGetShippingMethodById( $shippingMethodID );
	$shipping_email_comments_text	= $shippingMethod["email_comments_text"];
	$shippingName					= $shippingMethod["Name"];
	$paymentMethod					= payGetPaymentMethodById( $paymentMethodID );
	$paymentName					= $paymentMethod["Name"];
	$payment_email_comments_text	= $paymentMethod["email_comments_text"];
	if (isset($paymentMethod["calculate_tax"]) && (int)$paymentMethod["calculate_tax"] == 0)
	{
		$order_amount = oaGetOrderAmount( $cartContent, $addresses,
					$shippingMethodID, $log, $orderDetails,TRUE, $shServiceID );
		$d = oaGetDiscountPercent( $cartContent, $log );
		$tax = 0;
		$shipping_costUC	= oaGetShippingCostTakingIntoTax( $cartContent, $shippingMethodID, $addresses, $orderDetails, FALSE, $shServiceID, TRUE );
		$discount_percent	= oaGetDiscountPercent( $cartContent, $log );
	}
	else
	{
		$order_amount = oaGetOrderAmount( $cartContent, $addresses,
					$shippingMethodID, $log, $orderDetails, TRUE, $shServiceID );
		$d = oaGetDiscountPercent( $cartContent, $log );
		$tax = oaGetProductTax( $cartContent, $d, $addresses );
		$shipping_costUC	= oaGetShippingCostTakingIntoTax( $cartContent, $shippingMethodID, $addresses, $orderDetails, TRUE, $shServiceID, TRUE );
		$discount_percent	= oaGetDiscountPercent( $cartContent, $log );
	}
	

	
	$shServiceInfo = '';
	if(is_array($shipping_costUC)){
		list($shipping_costUC) = $shipping_costUC;
		$shServiceInfo = $shipping_costUC['name'];
		$shipping_costUC = $shipping_costUC['rate'];
	}
	$paymentMethod = payGetPaymentMethodById( $paymentMethodID );
	if ( $paymentMethod ){
		$currentPaymentModule = modGetModuleObj( $paymentMethod["module_id"], PAYMENT_MODULE );
	}else{
		$currentPaymentModule = null;
	}
	if ( $currentPaymentModule != null )
	{
		//define order details for payment module
		$order_payment_details = array(
			"customer_email" => $customer_email,
			"customer_ip" => $customer_ip,
			"order_amount" => $order_amount,
			"currency_code" => $currency_code,
			"currency_value" => $currency_value,
			"shipping_cost" => $shipping_costUC,
			"order_tax" => $tax,
			"shipping_info" => $shippingAddress,
			"billing_info" => $billingAddress
		);
		$process_payment_result = $currentPaymentModule->payment_process( $order_payment_details ); //gets payment processing result
		if ( !($process_payment_result == 1) ) //error on payment processing
		{ //die ($process_payment_result);
			if (isset($_POST))
			{
				$_SESSION["order4confirmation_post"] = $_POST;
			}
			xSaveData('PaymentError', $process_payment_result);
			if (!$customerID)
			{
				RedirectProtected( "index.php?order4_confirmation_quick=yes".
							"&shippingMethodID=".$_GET["shippingMethodID"].
							"&paymentMethodID=".$_GET["paymentMethodID"].
							"&shServiceID=".$shServiceID );
			}
			else
			{
				RedirectProtected( "index.php?order4_confirmation=yes".
							"&shippingAddressID=".$_GET["shippingAddressID"]."&shippingMethodID=".$_GET["shippingMethodID"].	"&billingAddressID=".$_GET["billingAddressID"]."&paymentMethodID=".$_GET["paymentMethodID"].
							"&shServiceID=".$shServiceID );
			}
			return false;
		}
	}
	
//$shippingMethodID	

//=============================== Простой расчет суммы заказа с учетом доставки  ====================
/*$ship_arr = shGetShippingMethodById( $shippingMethodID );

$shipping_costUC = $ship_arr['shipping_price'];

if($order_amount <500)
{
	$order_amount = $order_amount+$shipping_costUC;
}
else 
	$shipping_costUC = 0;*/
//echo '$order_amount = '.$order_amount.'<br>';
//echo '$shipping_costUC = '.$shipping_costUC.'<br>';
//echo '$order_amount = '.$order_amount.'<br>';	
//exit();	

//===============================================================
	$customerID = (int) $customerID;
	$sql = "insert into ".ORDERS_TABLE.
			" ( customerID, ".
			"	order_time, ".
			"	customer_ip, ".
			"	shipping_type, ".
			"	payment_type, ".
			"	customers_comment, ".
			"	statusID, ".
			"	shipping_cost, ".
			"	order_discount, ".
			"	order_amount, ".
			"	currency_code, ".
			"	currency_value, ".
			"	customer_firstname, ".
			"	customer_lastname, ".
			"	customer_email, ".
			"	shipping_firstname, ".
			"	shipping_lastname, ".
			"	shipping_country, ".
			"	shipping_state, ".
			"	shipping_zip, ".
			"	shipping_city, ".
			"	shipping_address, ".
			"	billing_firstname, ".
			"	billing_lastname, ".
			"	billing_country, ".
			"	billing_state, ".
			"	billing_zip, ".
			"	billing_city, ".
			"	billing_address, ".
			"	cc_number, ".
			"	cc_holdername, ".
			"	cc_expires, ".
			"	cc_cvv, ".
			"	affiliateID, ".
			"	shippingServiceInfo".
			"		  ) ".
			" values ( ".
				xEscapeSQLstring($customerID).", ".
				"'".xEscapeSQLstring($order_time)."', ".
				"'".xEscapeSQLstring($customer_ip)."', ".
				"'".xEscapeSQLstring($shippingName)."', ".
				"'".xEscapeSQLstring($paymentName)."', ".
				"'".xEscapeSQLstring($customers_comment)."', ".
				xEscapeSQLstring($statusID).", ".
				( (float) $shipping_costUC ).", ".
				( (float) $discount_percent ).", ".
				( (float) $order_amount ).", ".
				"'".xEscapeSQLstring($currency_code)."', ".
				( (float) $currency_value ).", ". //RoundFloatValue
				"'".xEscapeSQLstring($customerInfo["first_name"])."', ".
				"'".xEscapeSQLstring($customerInfo["last_name"])."', ".
				"'".xEscapeSQLstring($customer_email)."', ".
				"'".xEscapeSQLstring($shippingAddress["first_name"])."', ".
				"'".xEscapeSQLstring($shippingAddress["last_name"])."', ".
				"'".xEscapeSQLstring($shippingAddress["country_name"])."', ".
				"'".xEscapeSQLstring($shippingAddress["state"])."', ".
				"'".xEscapeSQLstring($shippingAddress["zip"])."', ".
				"'".xEscapeSQLstring($shippingAddress["city"])."', ".
				"'".xEscapeSQLstring($shippingAddress["address"])."', ".
				"'".xEscapeSQLstring($billingAddress["first_name"])."', ".
				"'".xEscapeSQLstring($billingAddress["last_name"])."', ".
				"'".xEscapeSQLstring($billingAddress["country_name"])."', ".
				"'".xEscapeSQLstring($billingAddress["state"])."', ".
				"'".xEscapeSQLstring($billingAddress["zip"])."', ".
				"'".xEscapeSQLstring($billingAddress["city"])."', ".
				"'".xEscapeSQLstring($billingAddress["address"])."', ".
				"'".xEscapeSQLstring($cc_number)."', ".
				"'".xEscapeSQLstring($cc_holdername)."', ".
				"'".xEscapeSQLstring($cc_expires)."', ".
				"'".xEscapeSQLstring($cc_cvv)."', ".
				"'".(isset($_SESSION['refid'])?$_SESSION['refid']:regGetIdByLogin($customer_affiliationLogin))."',".
				"'{$shServiceInfo}'".
			" ) ";
	db_query($sql);
	$orderID = db_insert_id( ORDERS_TABLE );
	stChangeOrderStatus($orderID, $statusID);
	$paymentMethod = payGetPaymentMethodById( $paymentMethodID );
	if ( $paymentMethod ){
		$currentPaymentModule = modGetModuleObj( $paymentMethod["module_id"], PAYMENT_MODULE );
//		$currentPaymentModule = payGetPaymentModuleById( $paymentMethod["module_id"], $paymentModulesFiles );
	}else{
		$currentPaymentModule = null;
	}
	//save shopping cart content to database and update in-stock information
	if ( $log != null )
	{
		cartMoveContentFromShoppingCartsToOrderedCarts( $orderID,
			$shippingMethodID, $paymentMethodID,
			$shippingAddressID, $billingAddressID, 
			$shippingModuleFiles, $paymentModulesFiles );
	}
	else //quick checkout
	{
		_moveSessionCartContentToOrderedCart( $orderID );
		//update in-stock information
		if ( $statusID != ostGetCanceledStatusId() && CONF_CHECKSTOCK )
		{
			$q1 = db_query("SELECT itemID, Quantity FROM ".ORDERED_CARTS_TABLE." WHERE orderID=$orderID");
			while ($item = db_fetch_row($q1))
			{
				$q2 = db_query("SELECT productID FROM ".SHOPPING_CART_ITEMS_TABLE." WHERE itemID=".$item["itemID"]);
				$pr = db_fetch_row($q2);
				if ($pr)
				{
					db_query( "update ".PRODUCTS_TABLE." set in_stock = in_stock - ".$item["Quantity"].
									" where productID='".$pr[0]."'" );
				}
			}
		}
		//now save registration form aux fields into CUSTOMER_REG_FIELDS_VALUES_TABLE_QUICKREG
		//for quick checkout orders these fields are stored separately than for registered customer (SS_customers)
		db_query("delete from ".CUSTOMER_REG_FIELDS_VALUES_TABLE_QUICKREG." where orderID=$orderID;");
		foreach($_SESSION as $key => $val)
		{
			if (strstr($key,"additional_field_") && strlen(trim($val)) > 0) //save information into sessions
			{
				$id = (int) str_replace("additional_field_","",$key);
				if ($id > 0)
				{
					db_query("insert into ".CUSTOMER_REG_FIELDS_VALUES_TABLE_QUICKREG." (orderID, reg_field_ID, reg_field_value) values ($orderID, $id, '".xEscapeSQLstring($val)."');");
				}
			}
		}
	}
	if ( $currentPaymentModule != null )
		 $currentPaymentModule->after_processing_php( $orderID );
	_sendOrderNotifycationToAdmin( $orderID, $smarty_mail, $tax );
	_sendOrderNotifycationToCustomer( $orderID, $smarty_mail, $customerInfo["Email"], $log,
				$payment_email_comments_text, $shipping_email_comments_text, $tax );
	if (!CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is OFF
	{
		$SMSNotify = new SMSNotify();
		$SMSNotify->onEvent('new_order',array('OrderAmount'=>(float)$order_amount.' '.$currency_code, 'OrderNumber'=>$orderID));
	}
	if ( $log == null )
		_quickOrderUnsetSession();
	unset($_SESSION["order4confirmation_post"]);
	return $orderID;
}
function _setHyphen( & $str )
{
	if ( trim($str) == "" || $str == null )
		$str = "-";
}
// *****************************************************************************
// Purpose	get order by id
// Inputs   
// Remarks		
// Returns	
function ordGetOrder( $orderID )
{
	$orderID = (int) $orderID;
	$q = db_query( "select orderID, customerID, order_time, customer_ip, ".
		 " shipping_type, payment_type, customers_comment, ".
		 " statusID, shipping_cost, order_discount, order_amount, ".
		 " currency_code, currency_value, customer_firstname, customer_lastname, ".
		 " customer_email, shipping_firstname, shipping_lastname, ".
		 " shipping_country, shipping_state, shipping_zip, shipping_city, ".
		 " shipping_address, billing_firstname, billing_lastname, billing_country, ".
		 " billing_state, billing_zip, billing_city, billing_address, ".
		 " cc_number, cc_holdername, cc_expires, cc_cvv".
		 ", affiliateID".
		 ", shippingServiceInfo  from ".ORDERS_TABLE." where orderID=$orderID " );
	$order = db_fetch_row($q);
	if ( $order )
	{
		/*_setHyphen( $order["shipping_firstname"] );
		_setHyphen( $order["customer_lastname"] );
		_setHyphen( $order["customer_email"] );
		_setHyphen( $order["shipping_firstname"] );
		_setHyphen( $order["shipping_lastname"] );
		_setHyphen( $order["shipping_country"] );
		_setHyphen( $order["shipping_state"] );
		_setHyphen( $order["shipping_zip"] );
		_setHyphen( $order["shipping_city"] );
		_setHyphen( $order["shipping_address"] );
		_setHyphen( $order["billing_firstname"] );
		_setHyphen( $order["billing_lastname"] );
		_setHyphen( $order["billing_country"] );
		_setHyphen( $order["billing_state"] );
		_setHyphen( $order["billing_zip"] );
		_setHyphen( $order["billing_city"] );
		_setHyphen( $order["billing_address"] );*/
		//CC data
		if (CONF_BACKEND_SAFEMODE)
		{
			$order["cc_number"] = ADMIN_SAFEMODE_BLOCKED;
			$order["cc_holdername"] = ADMIN_SAFEMODE_BLOCKED;
			$order["cc_expires"] = ADMIN_SAFEMODE_BLOCKED;
			$order["cc_cvv"] = ADMIN_SAFEMODE_BLOCKED;
		}
		else
		{
			if (strlen($order["cc_number"])>0)
				$order["cc_number"] = cryptCCNumberDeCrypt($order["cc_number"],null);
			if (strlen($order["cc_holdername"])>0)
				$order["cc_holdername"] = cryptCCHoldernameDeCrypt($order["cc_holdername"],null);
			if (strlen($order["cc_expires"])>0)
				$order["cc_expires"] = cryptCCExpiresDeCrypt($order["cc_expires"],null);
			if (strlen($order["cc_cvv"])>0)
				$order["cc_cvv"] = cryptCCNumberDeCrypt($order["cc_cvv"],null);
		}
		//additional reg fields
		$addregfields = GetRegFieldsValuesByOrderID( $orderID );
		$order["reg_fields_values"] = $addregfields;
		$q_status_name = db_query( "select status_name from ".
			ORDER_STATUES_TABLE.
			" where statusID=".$order["statusID"] );
		$status_name = db_fetch_row( $q_status_name );
		$status_name = $status_name[0];
		if ( $order["statusID"] == ostGetCanceledStatusId() )
			$status_name = STRING_CANCELED_ORDER_STATUS;
		// clear cost ( without shipping, discount, tax )
		$q1 = db_query( "select Price, Quantity from ".ORDERED_CARTS_TABLE." where orderID=$orderID" );
		$clear_total_price = 0;
		while( $row=db_fetch_row($q1) )
			$clear_total_price += $row["Price"]*$row["Quantity"];
 		$order["clear_total_priceToShow"] = $order["currency_code"]." ".RoundFloatValueStr($order["currency_value"]*$clear_total_price);
		$order["shipping_costToShow"]	= $order["currency_code"]." ".RoundFloatValueStr($order["currency_value"]*$order["shipping_cost"]);
		$order["order_amountToShow"] 	= $order["currency_code"]." ".RoundFloatValueStr($order["currency_value"]*$order["order_amount"]);
		$order["order_time_mysql"] = $order["order_time"];
		$order["order_time"] = format_datetime( $order["order_time"] );
		$order["status_name"] = $status_name;
	}
	return $order;
}
function ordGetOrderContent( $orderID )
{
	$q = db_query( "select name, Price, Quantity, tax, load_counter, itemID from ".
		ORDERED_CARTS_TABLE.
		" where orderID=".$orderID );
	$q_order = db_query( "select currency_code, currency_value, customerID, order_time from ".ORDERS_TABLE.
		" where orderID=$orderID " );
 	$order = db_fetch_row($q_order);
	$currency_code = $order["currency_code"];
	$currency_value = $order["currency_value"];
	$data = array();
	while( $row=db_fetch_row($q) )
	{
		$productID = GetProductIdByItemId( $row["itemID"] );
		$product   = GetProduct( $productID, true );
		if ( $product["eproduct_filename"] != null &&
			 $product["eproduct_filename"] != null )
		{
			if ( file_exists("./products_files/".$product["eproduct_filename"]) )
			{
					$row["eproduct_filename"]	= $product["eproduct_filename"];
					$row["file_size"]			= filesize( "./products_files/".$product["eproduct_filename"] );
					if ( $order["customerID"] != null )
					{
						$custID = $order["customerID"];
					}
					else
					{
						$custID = -1;
					}
					$row["getFileParam"] = 
								"orderID=".$orderID."&".
								"productID=".$productID."&".
								"customerID=".$custID;
					//additional security for non authorized customers
					if ($custID == -1)
					{
						$row["getFileParam"] .= "&order_time=".base64_encode($order["order_time"]);
					}					
					$row["getFileParam"] = cryptFileParamCrypt( 
									$row["getFileParam"], null );
					$row["load_counter_remainder"]		= 
							$product["eproduct_download_times"] - $row["load_counter"];
					$currentDate	= dtGetParsedDateTime( get_current_time() );
					$betweenDay		= _getDayBetweenDate( 
							dtGetParsedDateTime( $order["order_time"] ), 
							$currentDate );
					$row["day_count_remainder"]		= 
							$product["eproduct_available_days"] - $betweenDay;					
			}
		}
		$row["PriceToShow"] =  $currency_code." ".RoundFloatValueStr($currency_value*$row["Price"]*$row["Quantity"]);
		$data[] = $row;
	}
	return $data;
}
// *****************************************************************************
// Purpose	deletes  order
// Inputs   
// Remarks	this function deletes canceled orders only
// Returns	
function ordDeleteOrder( $orderID )
{
	$orderID = (int) $orderID;
	$q = db_query( "select statusID from ".ORDERS_TABLE." where orderID=".$orderID );
	$row = db_fetch_row( $q );
	if ( $row["statusID"] != ostGetCanceledStatusId() )
		return;
	db_query( "delete from ".ORDERED_CARTS_TABLE." where orderID=$orderID" );
	db_query( "delete from ".ORDERS_TABLE." where orderID=$orderID" );
	db_query( "delete from ".ORDER_STATUS_CHANGE_LOG_TABLE." where orderID=$orderID" );
}
// *****************************************************************************
// Purpose	gets summarize order info to 
// Inputs   
// Remarks		
// Returns	
function getOrderSummarize(
			$shippingMethodID, $paymentMethodID,
			$shippingAddressID, $billingAddressID, 
			$shippingModuleFiles, $paymentModulesFiles, $shServiceID = 0 )
{
	// result this function
	$sumOrderContent = array();
	$q = db_query( "select email_comments_text from ".PAYMENT_TYPES_TABLE.
			" where PID=".$paymentMethodID );
	$payment_email_comments_text = db_fetch_row( $q );
	$payment_email_comments_text = TransformDataBaseStringToText($payment_email_comments_text[0]);
	$q = db_query( "select email_comments_text from ".SHIPPING_METHODS_TABLE.
			" where SID=".$shippingMethodID );
	$shipping_email_comments_text = db_fetch_row( $q );
	$shipping_email_comments_text = TransformDataBaseStringToText($shipping_email_comments_text[0]);
	$cartContent 		= cartGetCartContent();
//	exit();
	$pred_total			= oaGetClearPrice( $cartContent );
	if ( isset($_SESSION["log"]) )
		$log = $_SESSION["log"];
	else
		$log = null;
	$d = oaGetDiscountPercent( $cartContent, $log );
	$discount = $pred_total/100*$d;
	// ordering with registration
	if ( $shippingAddressID != 0 || isset($log) )
	{
		$addresses = array($shippingAddressID, $billingAddressID);
		$shipping_address = regGetAddressStr($shippingAddressID);
		$billing_address  = regGetAddressStr($billingAddressID);
		$shaddr = regGetAddress($shippingAddressID);
		$sh_firstname = $shaddr["first_name"];
		$sh_lastname = $shaddr["last_name"];
	}
	else //quick checkout
	{
		
/*echo '<pre>getOrderSummarize $_SESSION';
print_r	($_SESSION);
echo '</pre>';*/
		if (!isset($_SESSION["receiver_countryID"]) || !isset($_SESSION["receiver_zoneID"]) || !isset($_SESSION["receiver_zip"]))
		//	return NULL;
//echo 'test';
		$shippingAddress = array(
				"countryID" => $_SESSION["receiver_countryID"],
				"zoneID"	=> $_SESSION["receiver_zoneID"],
				"zip"		=> TransformDataBaseStringToText($_SESSION["receiver_zip"]) );
		$billingAddress = array(
				"countryID" => $_SESSION["billing_countryID"],
				"zoneID"	=> $_SESSION["billing_zoneID"],
				"zip"		=> TransformDataBaseStringToText($_SESSION["billing_zip"]) );
		$addresses = array( $shippingAddress, $billingAddress );
		$shipping_address = quickOrderGetReceiverAddressStr();
		$billing_address  = quickOrderGetBillingAddressStr();
		$sh_firstname = $_SESSION["receiver_first_name"];
		$sh_middlename = $_SESSION["receiver_middle_name"];
		$sh_lastname = $_SESSION["receiver_last_name"];
	}
//print_r($cartContent);
	foreach( $cartContent["cart_content"] as $cartItem )
	{
		// if conventional ordering
		if ( $shippingAddressID != 0 )
		{
			$productID = GetProductIdByItemId( $cartItem["id"] );
			$cartItem["tax"] = 
				taxCalculateTax( $productID, $addresses[0], $addresses[1] );
		}
		else // if quick ordering
		{
			$productID = $cartItem["id"];
			$cartItem["tax"] = 
					taxCalculateTax2( $productID, $addresses[0], $addresses[1] );			
		}
		$sumOrderContent[] = $cartItem;
	}
//print_r($sumOrderContent);
//exit();
	$shipping_method	= shGetShippingMethodById( $shippingMethodID );
	if ( !$shipping_method )
		$shipping_name = "-";
	else
	{
		$shipping_name = $shipping_method["Name"];
		$shipping_price = $shipping_method['shipping_price'];
	}
	$payment_method		= payGetPaymentMethodById($paymentMethodID);
	if ( !$payment_method )
		$payment_name = "-";
	else
		$payment_name	= $payment_method["Name"];
	//do not calculate tax for this payment type!
	if (isset($payment_method["calculate_tax"]) && (int)$payment_method["calculate_tax"]==0)
	{
		foreach( $sumOrderContent as $key => $val )
		{
			$sumOrderContent[ $key ] ["tax"] = 0;
		}
		$orderDetails = array (
				"first_name" => $sh_firstname,
				"last_name" => $sh_lastname,
				"email" => "",
				"order_amount" => oaGetOrderAmountExShippingRate( $cartContent, $addresses, $log, FALSE, $shServiceID )
		);
		$tax = 0;
		$total			= oaGetOrderAmount( $cartContent, $addresses, $shippingMethodID, $log, $orderDetails, FALSE, $shServiceID );
		$shipping_cost  = oaGetShippingCostTakingIntoTax( $cartContent, $shippingMethodID, $addresses, $orderDetails, FALSE, $shServiceID );
	}
	else
	{
		$orderDetails = array (
				"first_name" => $sh_firstname,
				"last_name" => $sh_lastname,
				"email" => "",
				"order_amount" => oaGetOrderAmountExShippingRate( $cartContent, $addresses, $log, FALSE )
		);
		$tax			= oaGetProductTax( $cartContent, $d, $addresses );
		$total			= oaGetOrderAmount( $cartContent, $addresses, $shippingMethodID, $log, $orderDetails, TRUE,  $shServiceID );
		$shipping_cost  = oaGetShippingCostTakingIntoTax( $cartContent, $shippingMethodID, $addresses, $orderDetails, TRUE,  $shServiceID );
	}
	if(is_array($shipping_cost)){
		$_T = array_shift($shipping_cost);
		$shipping_cost = $_T['rate'];
	}
	$payment_form_html = "";
	$paymentModule = modGetModuleObj($payment_method["module_id"], PAYMENT_MODULE);
	if($paymentModule){
		$order		= array();
		$address	= array();
		if ( $shippingAddressID != 0 ){
			$payment_form_html = $paymentModule->payment_form_html(array('BillingAddressID'=>$billingAddressID));
		}else{
			$payment_form_html = $paymentModule->payment_form_html(array(
				'countryID' => $_SESSION['billing_countryID'],
				'zoneID'	=> $_SESSION['billing_zoneID'],
				'zip'		=> TransformDataBaseStringToText($_SESSION['billing_zip']),
				'first_name' => $_SESSION["billing_first_name"],
				'last_name' => $_SESSION["billing_last_name"],
				'city' => $_SESSION["billing_city"],
				'address' => $_SESSION["billing_address"],
				));
		}
	}
/*$shipping_cost = $shipping_price;
if($total>500)
	$shipping_cost = 0;*/
	return array(	"sumOrderContent"	=> $sumOrderContent, 
			"discount"			=> $discount,
			"discount_percent" 	=> $d,
			"pred_total_disc"	=> show_price(($pred_total*((100-$d)/100))),
			"pred_total"		=> show_price($pred_total),
			"totalTax"			=> show_price($tax),
			"totalTaxUC"		=> $tax,
			"shipping_address"	=> $shipping_address, 
			"billing_address"	=> $billing_address,
			"shipping_name"		=> $shipping_name,
			"payment_name"		=> $payment_name,
			"shipping_cost"		=> show_price($shipping_cost),
			"shipping_costUC"	=> $shipping_cost,
			"payment_form_html"	=> $payment_form_html,
			"total"				=> show_price($total+$shipping_cost),
			"totalUC"			=> $total,
			"payment_email_comments_text"		=> $payment_email_comments_text, 
			"shipping_email_comments_text"		=> $shipping_email_comments_text,
			"orderContentCartProductsCount"	=> count($sumOrderContent));			
}
function mycal_days_in_month( $calendar, $month, $year )
{
	$month = (int)$month;
	$year  = (int)$year;
	if ( 1 > $month || $month > 12 )
		return 0;
	if ( $month==1 || $month==3 || $month==5 || $month==7 || $month==8 || $month==10 || $month==12 )
		return 31;
	else
	{
		if ( $month==2 && $year % 4 == 0 )
			return 29;
		else if ( $month==2 && $year % 4 != 0 )
			return 28;
		else 
			return 30;
	}
}
function _getCountDay( $date )
{
	$countDay = 0;
	for( $year=1900; $year<$date["year"]; $year++ )
	{
		for( $month=1; $month <= 12; $month++ )
			$countDay += mycal_days_in_month(CAL_GREGORIAN, $month, $year);
	}
	for( $month=1; $month < $date["month"]; $month++ )
		$countDay += mycal_days_in_month(CAL_GREGORIAN, $month, $date["year"]);
	$countDay += $date["day"];
	return $countDay;	
}
// *****************************************************************************
// Purpose	gets address string 
// Inputs   	$date array of item
//			"day"
//			"month"
//			"year"
//		$date2 must be more later $date1
// Remarks		
// Returns	
function _getDayBetweenDate( $date1, $date2 )
{
	if ( $date1["year"] > $date2["year"] )
		return -1;
	if ( $date1["year"]==$date2["year"] && $date1["month"]>$date2["month"] )
		return -1;
	if ( $date1["year"]==$date2["year"] && $date1["month"]==$date2["month"] &&
		$date1["day"] > $date2["day"]  )
		return -1;
	return _getCountDay( $date2 ) - _getCountDay( $date1 );	
}
// *****************************************************************************
// Purpose	
// Inputs   	
// Remarks		
// Returns	
//		-1 	access denied
//		0	success, access granted and load_counter has been incremented
//		1	access granted but count downloading is exceeded eproduct_download_times in PRODUCTS_TABLE					
//		2	access granted but available days are exhausted to download product
//		3	it is not downloadable product
//		4	order is not ready
function ordAccessToLoadFile( $orderID, $productID, & $pathToProductFile, & $productFileShortName )
{
	$order 		= ordGetOrder($orderID);
	$product 	= GetProduct( $productID );
	if ( strlen($product["eproduct_filename"]) == 0 || !file_exists("products_files/".$product["eproduct_filename"]) || $product["eproduct_filename"] == null )
	{
		return 4;
	}
	if ( (int)$order["statusID"] != (int)ostGetCompletedOrderStatus() )
		return 3;
	$orderContent 	= ordGetOrderContent( $orderID );
	foreach( $orderContent as $item )
	{
		if ( GetProductIdByItemId($item["itemID"]) == $productID )
		{
			if ( $item["load_counter"] < $product["eproduct_download_times"] || 
					$product["eproduct_download_times"] == 0 )
			{
				$date1 = dtGetParsedDateTime( $order["order_time_mysql"] ); //$order["order_time"]
				$date2 = dtGetParsedDateTime( get_current_time() );
				$countDay = _getDayBetweenDate( $date1, $date2 );
				if ( $countDay>=$product["eproduct_available_days"] )
					return 2;
				if ( $product["eproduct_download_times"] != 0 )
				{
					db_query( "update ".ORDERED_CARTS_TABLE.
						" set load_counter=load_counter+1 ".
						" where itemID=".$item["itemID"]." AND orderID=".$orderID );
				}
				$pathToProductFile		= "./products_files/".$product["eproduct_filename"];
				$productFileShortName	= $product["eproduct_filename"];
				return 0;
			}
			else
				return 1;
		}
	}
	return -1;
}
?>
