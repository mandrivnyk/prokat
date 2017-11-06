<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
	if ( !strcmp($sub, "order_statuses") )
	{	

		if ( isset($_GET["delete"]) )
		{
			if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
			{
				Redirect( "admin.php?dpt=custord&sub=order_statuses&safemode=yes" );
			}

			if ( !ostDeleteOrderStatus( $_GET["delete"] ) )
				$smarty->assign("prompt", ADMIN_COULDNT_DELETE_ORDER_STATUS);		

			Redirect( "admin.php?dpt=custord&sub=order_statuses" );
		}

		if ( isset($_POST["save"]) )
		{
			if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
			{
				Redirect( "admin.php?dpt=custord&sub=order_statuses&safemode=yes" );
			}
		
			if ( trim($_POST["new_status_name"]) != "" )
			{
				$sort_order = (int)$_POST["new_sort_order"];
				ostAddOrderStatus(trim($_POST["new_status_name"]), $sort_order);
			}
			$updateData = ScanPostVariableWithId( array( "status_name", "sort_order" ) );
			foreach( $updateData as $key => $value )
				ostUpdateOrderStatus( $key, $value["status_name"], $value["sort_order"] );

			Redirect( "admin.php?dpt=custord&sub=order_statuses" );

		}

		$order_statues = ostGetOrderStatues( false, 'html' );
		
		
		$smarty->assign("order_statues", $order_statues );

		//set sub-department template
		$smarty->assign("admin_sub_dpt", "custord_order_statuses.tpl.html");		
	}

?>