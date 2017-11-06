<?php
	//ADMIN :: products and categories view

	//define admin department
	$admin_dpt = array(
		"id" => "modules", //department ID
		"sort_order" => 40, //sort order (less `sort_order`s appear first)
		"name" => ADMIN_MODULES, //department name
		"sub_departments" => array
		(
			array("id"=>"news", "name"=>ADMIN_NEWS),
			array("id"=>"survey", "name"=>ADMIN_VOTING),
			array("id"=>"shipping", "name"=>STRING_SHIPPING_MODULES ),
			array("id"=>"payment", "name"=>STRING_PAYMENT_MODULES ),
			array("id"=>"froogle", "name"=>STRING_MODULES_FROOGLE ),
			array("id"=>"linkexchange", "name"=>STRING_MODULES_LINKEXCHANGE ),
			array("id"=>"yandex", "name"=>"Яндекс.Маркет" ),
			array("id"=>"url", "name"=>STRING_MODULES_URL ),
			array("id"=>"topsale", "name"=>STRING_MODULES_TOP_SALE ),
			array("id"=>"brends", "name"=>STRING_MODULES_BRENDS ),
			array("id"=>"xmlHotline", "name"=>STRING_XML_HOTLINE ),
			array("id"=>"xmlPriceua", "name"=>STRING_XML_PRICEUA ),
			array("id"=>"pars", "name"=>STRING_MODULES_PARS)
		)
	);
	add_department($admin_dpt);


	//show new orders page if selected
	if ($dpt == "modules")
	{
		//set default sub department if required
		if (!isset($sub)) $sub = "news";
//echo "./includes/admin/sub/".$admin_dpt["id"]."_$sub.php";
//        exit();
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