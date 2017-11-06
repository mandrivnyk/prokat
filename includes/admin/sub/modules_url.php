<?php
/**
 * Url module
 */

if (!strcmp($sub, "url")){

	//set sub-department template
	require_once('./modules/url/class.urlmodule.php');
	$UrlObj = new Url();
	$UrlObj->generatePage('admin url list');
}
?>