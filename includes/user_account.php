<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
	// registration form

	if (isset($_GET["user_details"]) && isset($_SESSION["log"])) //show user's account
	{
		$cust_password				= null;
		$Email						= null;
		$first_name					= null;
		$last_name					= null;
		$subscribed4news			= null;
		$additional_field_values	= null;
		regGetContactInfo( $_SESSION["log"], $cust_password, $Email, $first_name, 
				$last_name, $subscribed4news, $additional_field_values );
		$smarty->hassign("additional_field_values", $additional_field_values );
		$smarty->hassign("first_name", $first_name );
		$smarty->hassign("last_name", $last_name );
		$smarty->hassign("Email", $Email );
		$smarty->hassign("login", $_SESSION["log"] );


		$customerID = regGetIdByLogin( $_SESSION["log"] );
		$custgroup = GetCustomerGroupByCustomerId( $customerID );
		$smarty->assign( "custgroup_name", $custgroup["custgroup_name"] );
		$smarty->assign('affiliate_customers', affp_getCustomersNum($customerID));
		if ( CONF_DISCOUNT_TYPE == '2' )
			if ( $custgroup["custgroup_discount"] > 0 )
				$smarty->assign( "discount", $custgroup["custgroup_discount"] );

		if ( CONF_DISCOUNT_TYPE == '4' || CONF_DISCOUNT_TYPE == '5' )
			if ( $custgroup["custgroup_discount"] > 0 )
				$smarty->assign( "min_discount", $custgroup["custgroup_discount"] );

		$defaultAddressID = regGetDefaultAddressIDByLogin( $_SESSION["log"] );
		$addressStr = regGetAddressStr( $defaultAddressID );
		$smarty->assign("addressStr", $addressStr );

		$smarty->assign("visits_count", stGetVisitsCount( $_SESSION["log"] ) );

		$smarty->assign('CONF_AFFILIATE_EMAIL_NEW_PAYMENT', CONF_AFFILIATE_EMAIL_NEW_PAYMENT);
		$smarty->assign('CONF_AFFILIATE_EMAIL_NEW_COMMISSION', CONF_AFFILIATE_EMAIL_NEW_COMMISSION);
		$smarty->assign('CONF_AFFILIATE_PROGRAM_ENABLED', CONF_AFFILIATE_PROGRAM_ENABLED);
		
		$smarty->assign("status_distribution", ordGetDistributionByStatuses( $_SESSION["log"] ) );
		$smarty->assign("main_content_template", "user_account.tpl.html");
	}

?>