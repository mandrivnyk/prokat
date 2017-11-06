<?php
	//ADMIN :: configuration

	//define admin department
	$admin_dpt = array(
		"id" => "conf", //department ID
		"sort_order" => 30, //sort order (less `sort_order`s appear first)
		"name" => ADMIN_SETTINGS, //department name
		"sub_departments" => array
		 (
			array("id"=>"setting", "name"=>ADMIN_SETTINGS ),
			array("id"=>"currencies", "name"=>ADMIN_CURRENCY_TYPES),
			array("id"=>"shipping", "name"=>STRING_SHIPPING_TYPE),
			array("id"=>"payment", "name"=>STRING_PAYMENT_TYPE),
			array("id"=>"countries", "name" => ADMIN_COUNTRIES),
			array("id"=>"zones", "name" => ADMIN_ZONES),
			array("id"=>"aux_pages", "name"=>ADMIN_AUX_PAGES),
			array("id"=>"taxes", "name"=>ADMIN_TAXES)
		 )
	);
	add_department($admin_dpt);


	//show department if it is being selected
	if ($dpt == "conf")
	{
		//set default sub department if required
		if (!isset($sub)) $sub = "setting";

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