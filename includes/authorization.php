<?php
	//forgot password page

	if (isset($_GET["logout"])) //user logout
	{
		unset($_SESSION["log"]);
		unset($_SESSION["pass"]);
		session_unregister("log"); //calling session_unregister() is required since unset() may not work on some systems
		session_unregister("pass");
		RedirectJavaScript( "index.php" );
	}
	else
	if (isset($_POST["enter"]) && !isset($_SESSION["log"])) //user login
	{

		if ( regAuthenticate($_POST["user_login"],$_POST["user_pw"]) )
		{
			if (!isset($_POST["order"]))
			{
				if (!strcmp($_SESSION["log"], ADMIN_LOGIN))
					Redirect( "admin.php" );
				else
					Redirect( "index.php?user_details=yes" );
			}
		}
		else
			$wrongLoginOrPw = 1;
	}


	if (isset($_POST["forgotpw"])) //forgot password?
	{
		$smarty->assign("forgotpw", xStripSlashesHTMLspecialChars($_POST["forgotpw"]));
		$res = regSendPasswordToUser( $_POST["forgotpw"], $smarty_mail );
		if ( $res )
			$smarty->assign("login_was_found", 1);
		else
			$smarty->assign("login_wasnt_found", 1);
		$show_password_form = 1;
	}

?>