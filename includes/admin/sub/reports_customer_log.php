<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
	if (!strcmp($sub, "customer_log"))
	{
		if ( isset($_POST["clear"]) )
		{
			if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
			{
				Redirect( "admin.php?dpt=reports&sub=customer_log&safemode=yes" );
			}
			stClearCustomerLogReport();
		}

		$customer_log_report = stGetCustomerLogReport();

		$smarty->assign("customer_log_report", $customer_log_report );

		//set sub-department template
		$smarty->assign("admin_sub_dpt", "reports_customer_log.tpl.html");
	}
?>