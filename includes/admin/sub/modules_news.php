<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
/**
 * News module
 */

if (!strcmp($sub, "news")){

	//set sub-department template
	require_once('./modules/news/class.newsmodule.php');
	$NewsObj = new News();
	$NewsObj->generatePage('admin news list');
}
?>