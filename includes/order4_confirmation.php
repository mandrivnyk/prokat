<?php

// *****************************************************************************
// Purpose			order confirmation page
// Call condition   
//					index.php?order2=yes&shippingAddressID=<address ID>&shippingMethodID=<shipping ID>&
//						billingAddressID=<address ID>&paymentMethodID=<payment method ID>
// Include PHP		index.php -> [order2.php]
// Uses TPL			order2.tpl
// Remarks


	if ( isset($order4_confirmation) )
	{
		$_POST = xStripSlashesGPC($_POST);
		if(!cartCheckMinTotalOrderAmount() && !isset($_GET["order_success"])){
			
			Redirect('index.php?shopping_cart=yes&min_order=error');
		}
		$shServiceID = isset($_GET['shServiceID'])?$_GET['shServiceID']:0;

		if ( !isset($_POST["submit"]) && !isset($_GET["order_success"]) )
		{
			if ( 
					!isset($_GET["order4_confirmation"])	||
					!isset($_GET["shippingAddressID"])		||
					!isset($_GET["billingAddressID"])		||
					!isset($_GET["shippingMethodID"])		||
					!isset($_GET["paymentMethodID"])
				){
				Redirect( "index.php?page_not_found=yes" );
			}

			$_GET["shippingAddressID"]	= (int)$_GET["shippingAddressID"];
			$_GET["billingAddressID"]	= (int)$_GET["billingAddressID"];
			$_GET["shippingMethodID"]	= (int)$_GET["shippingMethodID"];
			$_GET["paymentMethodID"]	= (int)$_GET["paymentMethodID"];


			if ($_GET["shippingAddressID"]  && !regAddressBelongToCustomer(regGetIdByLogin($_SESSION["log"]), $_GET["shippingAddressID"])  ){
				Redirect( "index.php?page_not_found=yes" );
			}

			if (CONF_ORDERING_REQUEST_BILLING_ADDRESS == 0 && $_GET["billingAddressID"] == 0) //no default address specified and billing address is not requested
			{
				$_GET["billingAddressID"] = $_GET["shippingAddressID"];
			}

			if (  $_GET["billingAddressID"] && !regAddressBelongToCustomer(regGetIdByLogin($_SESSION["log"]), $_GET["billingAddressID"])  ){
				Redirect( "index.php?page_not_found=yes" );
			}

			if ( $_GET["shippingMethodID"] != 0 ){
				if ( !shShippingMethodIsExist($_GET["shippingMethodID"]) ){
					Redirect( "index.php?page_not_found=yes" );
				}
			}

			if ( $_GET["paymentMethodID"] != 0 )
				if ( !payPaymentMethodIsExist($_GET["paymentMethodID"])  )
					Redirect( "index.php?page_not_found=yes" );

		}

		if ( !cartCheckMinOrderAmount() )
				Redirect( "index.php?shopping_cart=yes" );

		$shippingModuleFiles = GetFilesInDirectory( "./modules/shipping", "php" );
		foreach( $shippingModuleFiles as $fileName )
			include( $fileName );

		$paymentModuleFiles = GetFilesInDirectory( "./modules/payment", "php" );
		foreach( $paymentModuleFiles as $fileName )
			include( $fileName );

		if ( isset($_POST["submit"]) 
			)
		{

			$cc_number		= "";
			$cc_holdername	= "";
			$cc_expires		= "";
			$cc_cvv			= "";


			if (CONF_ORDERING_REQUEST_BILLING_ADDRESS == 0 && $_GET["billingAddressID"] == 0) //no default address specified and billing address is not requested
			{
				$_GET["billingAddressID"] = $_GET["shippingAddressID"];
			}

			$orderID = ordOrderProcessing(
					$_GET["shippingMethodID"], $_GET["paymentMethodID"],
					$_GET["shippingAddressID"], $_GET["billingAddressID"], 
					$shippingModuleFiles, $paymentModuleFiles, $_POST["order_comment"], 
					$cc_number,
					$cc_holdername, 
					$cc_expires,
					$cc_cvv,
					$_SESSION["log"], $smarty_mail, $shServiceID );

			$_SESSION["newoid"] = $orderID;

			if ( is_bool($orderID) )
				RedirectProtected( "index.php?order4_confirmation=yes".
							"&shippingAddressID=".$_GET["shippingAddressID"].
							"&shippingMethodID=".$_GET["shippingMethodID"].
							"&billingAddressID=".$_GET["billingAddressID"].
							"&paymentMethodID=".$_GET["paymentMethodID"]."&payment_error=1" );
			else
				RedirectProtected( "index.php?order4_confirmation=yes".
										"&order_success=yes&paymentMethodID=".
										$_GET["paymentMethodID"]."&orderID=".$orderID );
		}

		if ( isset($_GET["order_success"]))
		{
			if (isset($_GET["orderID"]) && isset($_SESSION["newoid"]) && (int)$_SESSION["newoid"] == (int)$_GET["orderID"])
			{
				$paymentMethod = payGetPaymentMethodById( $_GET["paymentMethodID"] );
				$currentPaymentModule = modGetModuleObj($paymentMethod["module_id"], PAYMENT_MODULE );

				if ( $currentPaymentModule != null )
					$after_processing_html = 
						$currentPaymentModule->after_processing_html($_GET["orderID"]);
				else
					$after_processing_html = "";
				$smarty->assign( "after_processing_html", $after_processing_html );
			}

			$smarty->assign( "order_success", 1 );
		}
		else
		{
			if ( isset($_GET["payment_error"]) )
			{
				if ($_GET["payment_error"] == 1)
					$smarty->assign( "payment_error", 1 );
				else
					$smarty->assign( "payment_error", base64_decode($_GET["payment_error"]) );
			}elseif( xDataExists('PaymentError')){
				
				$smarty->assign( "payment_error", xPopData('PaymentError') );
			}

			$orderSum = getOrderSummarize(
						$_GET["shippingMethodID"], $_GET["paymentMethodID"],
						$_GET["shippingAddressID"], $_GET["billingAddressID"], 
						$shippingModuleFiles, $paymentModuleFiles, $shServiceID );
			$orderSum['sumOrderContent'] = xHtmlSpecialChars($orderSum['sumOrderContent'], null, 'name');
			$smarty->assign( "orderSum",	$orderSum );
			$smarty->assign( "totalUC",		$orderSum["totalUC"] );
		}
		$smarty->assign( "main_content_template", "order4_confirmation.tpl.html" );
	}
?>