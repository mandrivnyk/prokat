<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
//orders list
if (  !strcmp($sub, "new_orders") )
{

	if(!isset($_GET['urlToReturn']))$_GET['urlToReturn']='';
	$order_detailes = (  isset($_POST["orders_detailed"]) || isset($_GET["orders_detailed"])  );

	if ( !$order_detailes )
	{

		$order_statuses = ostGetOrderStatues();

		function _setCallBackParamsToSearchOrders( &$callBackParam )
		{
			if ( isset($_GET["sort"]) )
				$callBackParam["sort"] = $_GET["sort"];
			if ( isset($_GET["direction"]) )
				$callBackParam["direction"] = $_GET["direction"];

			if ( $_GET["order_search_type"] == "SearchByOrderID" )
				$callBackParam["orderID"] = (int)$_GET["orderID_textbox"];
			else if ( $_GET["order_search_type"] == "SearchByStatusID" )
			{
				$orderStatuses = array();
				$data = ScanGetVariableWithId( array("checkbox_order_status") );
				foreach( $data as $key => $val )
					if ( $val["checkbox_order_status"] == "1" )
						$orderStatuses[] = $key;
				$callBackParam["orderStatuses"] = $orderStatuses;
			}
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

		function _getReturnUrl()
		{
			$url = "admin.php?dpt=custord&sub=new_orders";
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

		function _getUrlToNavigate()
		{
			$url = "admin.php?dpt=custord&sub=new_orders";
			if ( isset($_GET["order_search_type"]) )
				$url .= "&order_search_type=".$_GET["order_search_type"];
			if ( isset($_GET["orderID_textbox"]) )
				$url .= "&orderID_textbox=".$_GET["orderID_textbox"];
			$data = ScanGetVariableWithId( array("checkbox_order_status") );
			foreach( $data as $key => $val )
				$url .= "&checkbox_order_status_".$key."=".$val["checkbox_order_status"];

			$data = ScanGetVariableWithId( array("set_order_status") );
			$changeStatusIsPressed = (count($data)!=0);

			if ( isset($_GET["search"]) || $changeStatusIsPressed )
				$url .= "&search=1";

			if ( isset($_GET["sort"]) )
				$url .= "&sort=".$_GET["sort"];
			if ( isset($_GET["direction"]) )
				$url .= "&direction=".$_GET["direction"];

			return $url;
		}


		function _getUrlToSort()
		{
			$url = "admin.php?dpt=custord&sub=new_orders";
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
			return $url;
		}

		$data = ScanGetVariableWithId( array("set_order_status") );
		$changeStatusIsPressed = (count($data)!=0);

		if ( isset($_GET["search"]) || $changeStatusIsPressed )
		{
			_copyDataFromGetToPage( $smarty, $order_statuses );

			$callBackParam = array();
			_setCallBackParamsToSearchOrders( $callBackParam );
			$orders = array();
			$count = 0;
			
			$navigatorHtml = GetNavigatorHtml( _getUrlToNavigate(), 20, 
				'ordGetOrders', $callBackParam, $orders, $offset, $count );
			$smarty->hassign( "orders", $orders );
			$smarty->assign( "navigator", $navigatorHtml );
		}

		if ( isset($_GET["offset"]) )
			$smarty->assign( "offset", $_GET["offset"] );
		if ( isset($_GET["show_all"]) )
			$smarty->assign( "show_all", $_GET["show_all"] );

		$smarty->assign( "urlToSort", _getUrlToSort() );
		$smarty->assign( "urlToReturn", _getReturnUrl() );
		$smarty->assign( "order_statuses", $order_statuses );
	}#if ( !$order_detailes )
	else
	{
		if ( isset($_GET["delete"]) )
		{
			if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
			{
				Redirect( "admin.php?dpt=custord&sub=new_orders&orders_detailed=yes&orderID=".$_GET["orderID"]."&urlToReturn=".$_GET["urlToReturn"]."&safemode=yes" );
			}

			ordDeleteOrder( $_GET["orderID"] );
			Redirect( base64_decode($_GET["urlToReturn"]) );
		}

		if ( isset($_POST["set_status"]) )
		{
			if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
			{
				Redirect( "admin.php?dpt=custord&sub=new_orders&orders_detailed=yes&orderID=".$_GET["orderID"]."&urlToReturn=".$_GET["urlToReturn"]."&safemode=yes" );
			}

			if ( (int)$_POST["status"] != -1 )
				ostSetOrderStatusToOrder( $_GET["orderID"], 
					$_POST["status"], 
					isset($_POST['status_comment'])?$_POST['status_comment']:'',
					isset($_POST['notify_customer'])?$_POST['notify_customer']:'' );

			Redirect( "admin.php?dpt=custord&sub=new_orders&orders_detailed=yes&orderID=".$_GET["orderID"]."&urlToReturn=".$_GET["urlToReturn"] );
		}

		if ( isset($_GET["urlToReturn"]) )
			$smarty->assign( "encodedUrlToReturn", $_GET["urlToReturn"] );
		if ( isset($_GET["urlToReturn"]) )
			$smarty->assign( "urlToReturn", base64_decode($_GET["urlToReturn"]) );

		$order = ordGetOrder( $_GET["orderID"] );
		$orderContent = xHtmlSpecialChars(ordGetOrderContent( $_GET["orderID"] ), null, 'name');

		$order_status_report = xNl2Br(html_spchars(stGetOrderStatusReport( $_GET["orderID"] )), 'status_comment');
		$order_statuses = ostGetOrderStatues();

		$smarty->assign( "cancledOrderStatus", ostGetCanceledStatusId() );
		$smarty->assign( "orderContent", $orderContent );
		$smarty->assign( "order", $order );
		$smarty->assign( "https_connection_flag", 1 );
		$smarty->assign( "order_status_report", $order_status_report );
		$smarty->assign( "order_statuses", $order_statuses );
		$smarty->assign( "order_detailed", 1 );
	}#$order_detailes
	$smarty->assign( "admin_sub_dpt", "custord_new_orders.tpl.html" );
}
?>