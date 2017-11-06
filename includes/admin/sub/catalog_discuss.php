<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
	if ( !strcmp($sub, "discuss") )
	{


		function GetUrlToFind()
		{
			$res = "admin.php?dpt=catalog&sub=discuss";
			if ( isset( $_GET["offset"] ) )
				$res .= "&offset=".$_GET["offset"];
			if ( isset( $_GET["sort"] ) )
				$res .= "&sort=".$_GET["sort"];
			return $res;
		}

		function GetUrlToNavigate()
		{
			$res = "admin.php?dpt=catalog&sub=discuss";
			if ( isset( $_GET["productID"] ) )
				$res .= "&productID=".$_GET["productID"];
			if ( isset( $_GET["sort"] ) )
				$res .= "&sort=".$_GET["sort"];
			return $res;
		}

		function GetUrlToSort()
		{
			$res = "admin.php?dpt=catalog&sub=discuss";
			if ( isset( $_GET["productID"] ) )
				$res .= "&productID=".$_GET["productID"];
			if ( isset( $_GET["offset"] ) )
				$res .= "&offset=".$_GET["offset"];
			return $res;
		}

		function GetFullUrl()
		{
			$res = "admin.php?dpt=catalog&sub=discuss";
			if ( isset( $_GET["productID"] ) )
				$res .= "&productID=".$_GET["productID"];
			if ( isset( $_GET["offset"] ) )
				$res .= "&offset=".$_GET["offset"];
			if ( isset( $_GET["sort"] ) )
				$res .= "&sort=".$_GET["sort"];
			if ( isset( $_GET["direction"] ) )
				$res .= "&direction=".$_GET["direction"];
			return $res;
		}






		if ( isset($_GET["answer"]) )
		{
			$discussion = discGetDiscussion( $_GET["answer"] );
			$return_url = GetFullUrl();

			if ( isset($_POST["add"]) )
			{
				discAddDiscussion( $discussion["productID"], 
					$_POST["newAuthor"], $_POST["newTopic"], $_POST["newBody"] );

				Redirect( $return_url );
			}


			$smarty->assign( "return_url", $return_url );
			$smarty->assign( "discussion", $discussion );
			$smarty->assign( "answer", 1);
		}
		else
		{
			if ( isset($_GET["delete"]) )
			{
				if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
				{
					Redirect("admin.php?dpt=catalog&sub=discuss&productID=".$_GET["productID"]."&safemode=yes");
				}
				discDeleteDiscusion( $_GET["delete"] );
			}

			$callBackParam = array();
			if ( isset($_GET["sort"])  )
				$callBackParam["sort"] = $_GET["sort"];

			if ( isset($_GET["direction"]) )
				$callBackParam["direction"] = $_GET["direction"];


			$discussions	= array();
			$navigatorHtml	= "";

			$discussed_products = discGetAllDiscussedProducts();

			$smarty->hassign( "products", $discussed_products );

			if ( isset($_GET["productID"]) )
			{
				$callBackParam["productID"] = $_GET["productID"];
				$smarty->assign( "productID", $_GET["productID"] );

				$count = 0;
				$navigatorHtml = GetNavigatorHtml( GetUrlToNavigate(), 20, 
							'discGetAllDiscussion', $callBackParam, 
							$discussions, $offset, $count );

				if ( count($discussions) == 0 )
				{
					if (count($discussed_products)>0)
						Redirect( GetUrlToFind()."&productID=".$discussed_products[0]["productID"] );
					else
						Redirect( GetUrlToFind() );
				}
			}
			else
				$smarty->assign( "productID", 0 );
		

			$smarty->assign( "discussions", xHtmlSpecialChars($discussions,null, 'product_name') );
			$smarty->assign( "navigator", $navigatorHtml );
			$smarty->assign( "fullUrl", GetFullUrl() );
			$smarty->assign( "urlToSort", GetUrlToSort() );
			$smarty->assign( "urlToFind", GetUrlToFind() );
		}


		//set sub-department template
		$smarty->assign("admin_sub_dpt", "catalog_discuss.tpl.html");

	}
?>