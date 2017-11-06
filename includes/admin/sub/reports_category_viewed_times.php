<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
	if (!strcmp($sub, "category_viewed_times"))
	{
		$category_report=GetCategortyViewedTimesReport();

		$smarty->assign("categories", $category_report );

		//set sub-department template
		$smarty->assign("admin_sub_dpt", "reports_category_viewed_times.tpl.html");
	}
?>