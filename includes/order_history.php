<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
    if ( isset($order_history) && isset($_SESSION["log"]) )
	{

		function _setCallBackParamsToSearchOrders( &$callBackParam )
		{
			$callBackParam = array( "customerID" => regGetIdByLogin($_SESSION["log"]) );
			if ( isset($_GET["sort"]) )
			{
				$callBackParam["sort"] = $_GET["sort"];
				if ( isset($_GET["direction"]) )
					$callBackParam["direction"] = $_GET["direction"];
			}
			else
			{
				$callBackParam["sort"] = "order_time";
				$callBackParam["direction"] = "DESC";
			}

			$callBackParam["orderStatuses"] = array();
		}

		function _getReturnUrl()
		{
			$url = "index.php?order_history=yes";
			if ( isset($_GET["order_search_type"]) )
				$url .= "&order_search_type=".$_GET["order_search_type"];
			if ( isset($_GET["orderID_textbox"]) )
				$url .= "&orderID_textbox=".$_GET["orderID_textbox"];
			$data = ScanGetVariableWithId( array("checkbox_order_status") );
			foreach( $data as $key => $val )
				$url .= "&checkbox_order_status_".$key."=".$val["checkbox_order_status"];
			if ( isset($_GET["offset"]) )
				$url .= "&offset=".$_GET["offset"];
			if ( isset($_GET["show_all"]) )
				$url .= "&show_all=".$_GET["show_all"];
			$data = ScanGetVariableWithId( array("set_order_status") );
			$changeStatusIsPressed = (count($data)!=0);
			if ( isset($_GET["search"]) || $changeStatusIsPressed )
				$url .= "&search=1";
			if ( isset($_GET["sort"]) )
				$url .= "&sort=".$_GET["sort"];
			if ( isset($_GET["direction"]) )
				$url .= "&direction=".$_GET["direction"];
			return base64_encode( $url );
		}

		function _copyDataFromGetToPage( &$smarty, &$order_statuses )
		{
			if ( isset($_GET["order_search_type"])  )
				$smarty->assign( "order_search_type", $_GET["order_search_type"] );
			if ( isset($_GET["orderID_textbox"]) )
				$smarty->assign( "orderID", (int)$_GET["orderID_textbox"] );
			$data = ScanGetVariableWithId( array("checkbox_order_status") );
			for( $i=0; $i<count($order_statuses); $i++ )
				$order_statuses[$i]["selected"] = 0;
			foreach( $data as $key => $val )
			{
				if ( $val["checkbox_order_status"] == "1" )
				{
					for( $i=0; $i<count($order_statuses); $i++ )
						if ( (int)$order_statuses[$i]["statusID"] == (int)$key )
							$order_statuses[$i]["selected"] = 1;
				}
			}
		}

		function _getUrlToSort()
		{
			$url = "index.php?order_history=yes";
			if ( isset($_GET["order_search_type"]) )
				$url .= "&order_search_type=".$_GET["order_search_type"];
			if ( isset($_GET["orderID_textbox"]) )
				$url .= "&orderID_textbox=".$_GET["orderID_textbox"];
			$data = ScanGetVariableWithId( array("checkbox_order_status") );
			foreach( $data as $key => $val )
				$url .= "&checkbox_order_status_".$key."=".$val["checkbox_order_status"];
			if ( isset($_GET["offset"]) )
				$url .= "&offset=".$_GET["offset"];
			if ( isset($_GET["show_all"]) )
				$url .= "&show_all=".$_GET["show_all"];

			if ( isset($_GET["search"]) )
				$url .= "&search=1";
			return $url;
		}

		function _getUrlToNavigate()
		{
			$url = "index.php?order_history=yes";
			if ( isset($_GET["order_search_type"]) )
				$url .= "&order_search_type=".$_GET["order_search_type"];
			if ( isset($_GET["orderID_textbox"]) )
				$url .= "&orderID_textbox=".$_GET["orderID_textbox"];
			$data = ScanGetVariableWithId( array("checkbox_order_status") );
			foreach( $data as $key => $val )
				$url .= "&checkbox_order_status_".$key."=".$val["checkbox_order_status"];

			if ( isset($_GET["search"]) )
				$url .= "&search=1";

			if ( isset($_GET["sort"]) )
				$url .= "&sort=".$_GET["sort"];
			if ( isset($_GET["direction"]) )
				$url .= "&direction=".$_GET["direction"];
			return $url;
		}



		$order_statuses = ostGetOrderStatues();
		$smarty->assign( "completed_order_status", ostGetCompletedOrderStatus() );

		$callBackParam = array();
		_setCallBackParamsToSearchOrders( $callBackParam );
		_copyDataFromGetToPage( $smarty, $order_statuses );

		$orders = array();
		$offset = 0;
		$count = 0;
		$navigatorHtml = GetNavigatorHtml( _getUrlToNavigate(), 20, 
			'ordGetOrders', $callBackParam, $orders, $offset, $count );
		$orders = xHtmlSpecialChars($orders, null, 'name');

		$smarty->assign( "orders_navigator", $navigatorHtml );
		$smarty->assign( "user_orders", $orders );	
		$smarty->assign( "urlToSort", _getUrlToSort() );

		$smarty->assign( "urlToReturn", _getReturnUrl() );
		$smarty->assign( "order_statuses", $order_statuses);
		$smarty->assign( "main_content_template", "order_history.tpl.html" );
	}



	if ( isset($order_detailed) )
	{
		$orderID = (int) $order_detailed;

		$smarty->assign( "urlToReturn", base64_decode($_GET["urlToReturn"]) );

		$order = ordGetOrder( $orderID );

		if (!$order || ($order["customerID"] != regGetIdByLogin($_SESSION["log"]))) //attempt to view orders of other customers
		{
			unset($order);
			$smarty->assign( "main_content_template", "page_not_found.tpl.html");
		}
		else
		{
			$orderContent = xHtmlSpecialChars(ordGetOrderContent( $orderID ), null, 'name');
			
			$order_status_report = xNl2Br(html_spchars(stGetOrderStatusReport( $orderID )), 'status_comment');
			$order_statuses = ostGetOrderStatues();
			$smarty->assign( "completed_order_status", ostGetCompletedOrderStatus() );
			$smarty->assign( "orderContent", $orderContent );
			$smarty->hassign( "order", $order );
			$smarty->assign( "https_connection_flag", 1 );
			$smarty->assign( "order_status_report", $order_status_report );
			$smarty->assign( "order_statuses", $order_statuses );
			$smarty->assign( "order_detailed", 1 );
			$smarty->assign( "main_content_template", "order_history.tpl.html");
		}
	}

?>