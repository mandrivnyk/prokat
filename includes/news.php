<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php

	if ( isset($_POST["subscribe"]) )
	{
		$error = subscrVerifyEmailAddress($_POST["email"]);
		if ( $error == "" )
		{
			$smarty->assign( "subscribe", 1 );
			subscrAddUnRegisteredCustomerEmail( $_POST["email"] );
		}
		else
			$smarty->assign( "error_message", $error );
	}

	if ( isset($_POST["email"]) ){
		$smarty->xassign( "email_to_subscribe", $_POST["email"] );
	}
	else{
		$smarty->assign( "email_to_subscribe", "Email" );
	}
	
	require_once('./modules/news/class.newsmodule.php');
	$NewsObj = new News();
	
	$NewsObj->generatePage('frontend news short list');
	
	if ( isset($news) ){

		$NewsObj->generatePage('frontend news list');
	}
	if(isset($new))
	{
		$NewsObj->generatePage('frontend new');
	}
?>