<?php
Header("HTTP/1.0 200 OK"); //строка нужна только для п. 1
Header("Last-Modified: ".gmdate("D, M d Y H:i:s",filemtime("index.php"))." GMT");

	// <head> variables definition: title, meta



	// TITLE & META Keywords & META Description



	if ( !isset($_GET["show_aux_page"]) ) //not an aux page, e.g. homepage, product/category page, registration form, checkout, etc.

	{



		if (isset($categoryID) && !isset($productID) && $categoryID>0) //category page

		{

			$q = db_query("SELECT name FROM ".CATEGORIES_TABLE." WHERE categoryID<>0 and categoryID<>1 and categoryID='$categoryID'") or die (db_error());

			$r = db_fetch_row($q);

			if ($r)

			{

				$page_title = $r[0]." - ".CONF_DEFAULT_TITLE;

			}

			else

			{

				$page_title = CONF_DEFAULT_TITLE;

			}

			$page_title = str_replace( "<", "&lt;", $page_title );

			$page_title = str_replace( ">", "&gt;", $page_title );



			$meta_tags = catGetMetaTags($categoryID);



		}

		else if (isset($productID) && $productID>0) //product information page

			{

				$q = db_query("SELECT name FROM ".PRODUCTS_TABLE." WHERE productID='$productID'") or die (db_error());

				$r = db_fetch_row($q);

				if ($r)

				{

					$page_title = $r[0]." - ".CONF_DEFAULT_TITLE;

				}

				else

				{

					$page_title = CONF_DEFAULT_TITLE;

				}

				$page_title = str_replace( "<", "&lt;", $page_title );

				$page_title = str_replace( ">", "&gt;", $page_title );



				$meta_tags = prdGetMetaTags($productID);

			}

			else // other page

			{

				$page_title = CONF_DEFAULT_TITLE;

				$meta_tags = "";

				if  ( CONF_HOMEPAGE_META_DESCRIPTION != "" )

					$meta_tags .= "<meta name=\"Description\" content=\"".CONF_HOMEPAGE_META_DESCRIPTION."\">\n";

				if  ( CONF_HOMEPAGE_META_KEYWORDS != "" )

					$meta_tags .= "<meta name=\"KeyWords\" content=\"".CONF_HOMEPAGE_META_KEYWORDS."\" >\n";

			}



	}

	else // aux page => get title and META information from database

	{

		$page = auxpgGetAuxPage( $show_aux_page );

		$page_title				= $page["aux_page_name"]." - ".CONF_DEFAULT_TITLE;

		$meta_tags = "";

		if  ( $page["meta_description"] != "" )

			$meta_tags .= "<meta name=\"Description\" content=\"".str_replace("\"","&quot;",$page["meta_description"])."\">\n";

		if  ( $page["meta_keywords"] != "" )

			$meta_tags .= "<meta name=\"KeyWords\" content=\"".str_replace("\"","&quot;",$page["meta_keywords"])."\" >\n";

	}



	$smarty->assign("page_title",	$page_title );

	$smarty->assign("page_meta_tags", $meta_tags );





?>