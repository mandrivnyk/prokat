<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
	// product discussion page

	if (isset($_POST["add_topic"]) && isset($productID)) // add post to the product discussion
	{
		if ( !prdProductExists($productID) )
			Redirect( "index.php?page_not_found=yes" );
		discAddDiscussion( $productID, $_POST["nick"], $_POST["topic"], $_POST["body"] );
		Redirect("index.php?productID=$productID&discuss=yes");
	}

	if (isset($_GET["remove_topic"]) && isset($productID) && isset($_SESSION["log"]) && !strcmp($_SESSION["log"], ADMIN_LOGIN)) // delete topic in the discussion
	{
		if ( !prdProductExists($productID) )
			Redirect( "index.php?page_not_found=yes" );
		discDeleteDiscusion( $_GET["remove_topic"] );
		Redirect("index.php?productID=$productID&discuss=yes");
	}


	if (isset($productID) && $productID>0 && (isset($_GET["discuss"]) || isset($_POST["discuss"]))) //show discussion form
	{
		if ( !prdProductExists($productID) )
			Redirect( "index.php?page_not_found=yes" );

		$smarty->assign("discuss","yes");
		$smarty->assign("main_content_template", "product_discussion.tpl.html");

		$q = db_query("select name, url_name from ".PRODUCTS_TABLE." where productID='$productID' and enabled=1") or die (db_error());
		$a = db_fetch_row($q);
		if ($a)
		{
			$smarty->hassign("product_name", $a[0]);
			$smarty->hassign("url_name", $a[1]);
			$q = db_query("select count(*) from ".DISCUSSIONS_TABLE." WHERE productID='$productID'") or die (db_error());
			$cnt = db_fetch_row($q);
			if ($cnt[0])
			{
				$q = db_query(
					"select Author, Body, add_time, DID, Topic FROM ".DISCUSSIONS_TABLE.
					" WHERE productID='$productID' ORDER BY add_time DESC") or die (db_error());
				$result = array();
				while ($row = db_fetch_row($q))
				{
					$row["Author"]	= TransformDataBaseStringToText( $row["Author"] );
					$row["Body"]	= TransformDataBaseStringToText( $row["Body"] );
					$row["Topic"]	= TransformDataBaseStringToText( $row["Topic"] );
					$row["add_time"]= format_datetime( $row["add_time"] );
					$result[] = $row;
				}

				$smarty->assign("product_reviews", $result);
			}
			else
			{
				$smarty->assign("product_reviews", NULL);
			}
		}
	}
?>
