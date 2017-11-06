<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
	if (!strcmp($sub, "discounts"))
	{
		if ( isset($_GET["delete"]) )
		{
			if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
			{
				Redirect( "admin.php?dpt=custord&sub=discounts&safemode=yes" );
			}

			dscDeleteOrderPriceDiscount( $_GET["delete"] );
			Redirect( "admin.php?dpt=custord&sub=discounts" );
		}

		if ( isset($_GET["error"]) )
			$smarty->assign( "error", 1 );

		if ( isset($_POST["discount_type_save"]) ) //update discount type?
		{
			if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
			{
				Redirect( "admin.php?dpt=custord&sub=discounts&safemode=yes" );
			}

			$_POST["save"] = 1;
		}

		$control = settingCallHtmlFunction( "CONF_DISCOUNT_TYPE" );

		if ( isset($_POST["discount_type_save"]) )
			Redirect( "admin.php?dpt=custord&sub=discounts" );

		$smarty->assign( "control", $control );

		if ( isset($_POST["save_order_price_discounts"]) )
		{
			if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
			{
				Redirect( "admin.php?dpt=custord&sub=discounts&safemode=yes" );
			}

			$error = false;

			$data = ScanPostVariableWithId( array( "percent_discount", "price_range" ) );
			foreach( $data as $discount_id => $val )
			{
				if ( !dscUpdateOrderPriceDiscount( $discount_id, $val["price_range"], 
						(float)$val["percent_discount"] ) )
					$error = true;
			}

			if ( trim($_POST["new_price_range"]) != "" )
			{
				if ( !dscAddOrderPriceDiscount( (float)$_POST["new_price_range"], 
					(float)$_POST["new_percent_discount"]) )
					$error = true;
			}

			if ( $error )
				Redirect("admin.php?dpt=custord&sub=discounts&error=yes");
			else
				Redirect("admin.php?dpt=custord&sub=discounts");
		}


		$discounts = dscGetAllOrderPriceDiscounts();
		$smarty->assign("discounts", $discounts );
		$smarty->assign("admin_sub_dpt", "custord_discounts.tpl.html");
	}
?>