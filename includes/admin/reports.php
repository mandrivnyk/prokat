<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
	//ADMIN :: new orders managment

	//define a new admin department
	$admin_dpt = array(
		"id" => "reports", //department ID
		"sort_order" => 50, //sort order (less `sort_order`s appear first)
		"name" => ADMIN_REPORTS, //department name
		"sub_departments" => array
		 (
			array( "id" => "category_viewed_times", "name" => ADMIN_CATEGORY_VIEWED_TIMES ),
			array( "id" => "customer_log", "name" => ADMIN_CUSTOMER_LOG ),
			array( "id" => "product_report", "name" => ADMIN_PRODUCT_REPORT )
		 )
	);
	add_department($admin_dpt);

	//show department if it is being selected
	if ($dpt == "reports")
	{
		//set default sub department if required
		if (!isset($sub)) $sub = "category_viewed_times";

		if (file_exists("./includes/admin/sub/".$admin_dpt["id"]."_$sub.php")) //sub-department file exists
		{
			//assign admin main department template
			$smarty->assign("admin_main_content_template", $admin_dpt["id"].".tpl.html");
			//assign subdepts
			$smarty->assign("admin_sub_departments", $admin_dpt["sub_departments"]);
			//include selected sub-department
			include("./includes/admin/sub/".$admin_dpt["id"]."_$sub.php");
		}
		else //no sub department found
			$smarty->assign("admin_main_content_template", "notfound.tpl.html");
	}

?>