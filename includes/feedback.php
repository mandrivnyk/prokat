<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
	// auxiliary information page presentation

	if (isset($_GET["feedback"]) || isset($_POST["feedback"]))
	{
		if (isset($_POST["feedback"]))
		{
			$customer_name = xStripSlashesGPC( $_POST["customer_name"] );
			$customer_email = xStripSlashesGPC( $_POST["customer_email"] ) ;
			$message_subject = xStripSlashesGPC( $_POST["message_subject"] ) ;
			$message_text = xStripSlashesGPC( $_POST["message_text"] );
		}
		else
		{
			$customer_name = "";
			$customer_email = "";
			$message_subject = "";
			$message_text = "";
		}

		//validate input data
		if (isset($customer_name) && isset($customer_email) && isset($message_subject) && isset($message_text) && isset($_POST["send"]) && trim($customer_email)!="" && trim($customer_name)!="" && trim($message_subject)!="" && trim($message_text)!="" && eregi("^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$", $customer_email))
		{
			
			$customer_name = str_replace(array('@','<',"\n"), array('[at]', '', ''), $customer_name);
			$customer_email = str_replace(array("\n",'<'), '', $customer_email);
			//send a message to store administrator
			ss_mail(CONF_GENERAL_EMAIL, $message_subject, $message_text, "From: \"".$customer_name."\"<".$customer_email.">\n".
				EMAIL_MESSAGE_PARAMETERS."\nReturn-path: <".$customer_email.">");
			header("Location: index.php?feedback=1&sent=1");
		}
		else if (isset($_POST["feedback"])) $smarty->assign("error",1);

		//extract input to Smarty
		$smarty->hassign("customer_name",$customer_name);
		$smarty->hassign("customer_email",$customer_email);
		$smarty->hassign("message_subject",$message_subject);
		$smarty->hassign("message_text",$message_text);

		if (isset($_GET["sent"])) $smarty->assign("sent",1);

		$smarty->assign("main_content_template", "feedback.tpl.html");
		$regular_head = 1;
	}

?>