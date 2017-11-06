<?php

?><?php
global  $aux_page;
	if (!strcmp($sub, "aux_pages"))
	{
		if ( isset($_GET["delete"]) )
		{
			if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
			{
				Redirect( "admin.php?dpt=conf&sub=aux_pages&safemode=yes" );
			}
			auxpgDeleteAuxPage( $_GET["delete"] );
			Redirect( "admin.php?dpt=conf&sub=aux_pages" );
		}
		if ( isset($_GET["add_new"]) )
		{
			if ( isset($_POST["save"]) )
			{
				$aux_page_text_type = 0;
				if ( isset($_POST["aux_page_text_type"]) )
					$aux_page_text_type = 1;
				auxpgAddAuxPage( $_POST["aux_page_name"], $_POST["url_name"],
					$_POST["aux_page_text"], $aux_page_text_type,
					$_POST["meta_keywords"], $_POST["meta_description"] );
				header("Location: admin.php?dpt=conf&sub=aux_pages");
			}
			$smarty->assign( "add_new", 1 );
		}
		else if ( isset($_GET["edit"]) )
		{

			if ( isset($_POST["save"]) )
			{
				if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
				{
					Redirect( "admin.php?dpt=conf&sub=aux_pages&safemode=yes&edit=".$_GET["edit"] );
				}

				$aux_page_text_type = 0;
				if ( isset($_POST["aux_page_text_type"]) )
					$aux_page_text_type = 1;
				auxpgUpdateAuxPage( $_GET["edit"], $_POST["url_name"], $_POST["aux_page_name"],
					$_POST["aux_page_text"], $aux_page_text_type,
					$_POST["meta_keywords"], $_POST["meta_description"]  );
				header("Location: admin.php?dpt=conf&sub=aux_pages");
			}

			$aux_page = auxpgGetAuxPage( $_GET["edit"] );
			$smarty->assign( "aux_page", $aux_page );

			$smarty->assign( "edit", 1 );
		}
		else
		{
			$aux_pages = auxpgGetAllPageAttributes();
			$smarty->assign( "aux_pages", $aux_pages );
		}

		//set sub-department template
		$smarty->assign("admin_sub_dpt", "conf_aux_pages.tpl.html");
	}

?>