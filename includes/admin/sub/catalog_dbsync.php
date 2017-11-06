<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
	//catalog database synchronization

	//show new orders page if selected
	if (!strcmp($sub, "dbsync"))
	{

		//database synchronization
		//affects only products and categories database! doesn't touch customers and orders tables

		// generate SQL-file //

		if (isset($_POST["export_db"])) //export database to SQL-file
		{
			@set_time_limit(0);

			// write SQL insert statements to file 
			serProductAndCategoriesSerialization( "./temp/database.sql" );

			$getFileParam = cryptFileParamCrypt( "GetDataBaseSqlScript", null );
			$smarty->assign( "getFileParam", $getFileParam );

			$smarty->assign( "sync_action", "export");
			$smarty->assign( "database_filesize", filesize("./temp/database.sql"));

		}
		else
		if (isset($_POST["import_db"])) //execute sql-file
		{
			if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
			{
				Redirect("admin.php?dpt=catalog&sub=dbsync&safemode=yes");
			}

			@set_time_limit(0);

			//upload file
			if (isset($_FILES["db"]) && $_FILES["db"]["name"])
			{
				$db_name = "./temp/file.db";
				$res = @move_uploaded_file($_FILES["db"]["tmp_name"], $db_name);
				if ( $res )
				{

					SetRightsToUploadedFile( $db_name );

					DestroyReferConstraintsXML( DATABASE_STRUCTURE_XML_PATH );

					//clear products&categories database
					serDeleteProductAndCategories();

					//now plainly execute SQL file
							//serImport( $db_name );

					$f = implode("",file($db_name));
					$f = str_replace("insert into ", "INSERT INTO ", $f);
					$f = explode("INSERT INTO ",$f);
					for ($i=0; $i<count($f); $i++)
						if (strlen($f[$i])>0)
						{
							$f[$i] = str_replace(");",")",$f[$i]);
							db_query( "INSERT INTO ".$f[$i] );
						}

					CreateReferConstraintsXML( DATABASE_STRUCTURE_XML_PATH );

					unlink($db_name);
					$smarty->assign("sync_successful", 1);
				}
				else
					$smarty->assign("sync_successful", 0);

			} else $smarty->assign("sync_successful", 0);

			$smarty->assign("sync_action", "import");

			//update products count value if defined
			if (CONF_UPDATE_GCV == 1)
			{
				update_products_Count_Value_For_Categories(1);
			}
		}

		//set sub-department template
		$smarty->assign("admin_sub_dpt", "catalog_dbsync.tpl.html");
	}

?>