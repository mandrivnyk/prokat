<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
// *****************************************************************************
// Purpose			authentication prompt
// Call condition   
//					index.php?register_authorization=yes
// Include PHP		index.php -> [register_authorization.php]
// Uses TPL			register_authorization.tpl
// Remarks

	if ( isset($register_authorization) )
	{

		$_POST = xStripSlashesGPC($_POST);
		if ( !cartCheckMinOrderAmount() )
				Redirect( "index.php?shopping_cart=yes" );


		if ( isset($_GET["remind_password"]) )
			$smarty->assign("remind_password" , 1);

		if ( isset($_POST["user_login"])  )
		{
			$smarty->hassign( "user_login", $_POST["user_login"] );
			$smarty->assign( "login_to_remind_password", $_POST["user_login"] );
		}

		if ( isset($_POST["remind_password"]) ){
			
			$Reminded = regSendPasswordToUser( $_POST["login_to_remind_password"], $smarty_mail )?'yes':'no';
			if($Reminded=='no')
				$smarty->hassign('remind_user_login', $_POST["login_to_remind_password"]);
			$smarty->assign( "password_sent_notifycation",  $Reminded);
		}

		if ( isset($_POST["login"]) )
		{
			if ( trim($_POST["user_login"]) != "" )
			{	
				$cartIsEmpty = cartCartIsEmpty($_POST["user_login"]);
				if ( regAuthenticate( $_POST["user_login"], $_POST["user_pw"], false ) )
				{
					if ( $cartIsEmpty )
						Redirect( "index.php?order2_shipping=yes&shippingAddressID=".
							regGetDefaultAddressIDByLogin($_SESSION["log"]) );
					else 
						Redirect( "index.php?shopping_cart=yes&make_more_exact_cart_content=yes" );
				}
				else{
					
					$smarty->hassign('remind_user_login', $_POST["user_login"]);
					$smarty->assign( "password_sent_notifycation",  'no');
					$smarty->assign("remind_password" , 1);
				}
			}
		}

		$smarty->assign("check_order", "yes");
		$smarty->assign("main_content_template", "register_authorization.tpl.html");
	}
?>