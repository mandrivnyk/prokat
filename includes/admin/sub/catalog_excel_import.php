<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
	//products and categories catalog import from MS Excel .CSV files
	if (!strcmp($sub, "excel_import"))
	{

		//validate $_POST["delimeter"] data
		if (isset($_POST["delimeter"]) && strlen($_POST["delimeter"])==0)
			$_POST["delimeter"] = ";";


		if (isset($_POST["proceed"]) && isset($_POST["mode"])) //upload file and show import configurator
		{
			if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
			{
				Redirect("admin.php?dpt=catalog&sub=excel_import&safemode=yes");
			}

			$res = 0;

			if ($_POST["mode"] == 2) // reset database content
			{
				imDeleteAllProducts();
				$res = 1;
				$smarty->assign("excel_import_result", "ok");
			}
			else //update
			{
				//upload CSV-file
				if (isset($_FILES["csv"]) && $_FILES["csv"]["name"])
				{
					$file_excel_name = "./temp/file.csv";
					$res = @move_uploaded_file($_FILES["csv"]["tmp_name"], $file_excel_name);
					$smarty->assign("file_excel_name", $file_excel_name);
				}
				if (isset($res) && $res) //uploaded successfully
				{
					SetRightsToUploadedFile( $file_excel_name );

					//show import configurator
					$data = myfgetcsv($file_excel_name, $_POST["delimeter"]);
					if (!count($data)) die (ERROR_CANT_READ_FILE);
					$excel_configurator = imGetImportConfiguratorHtmlCode($data);
					$smarty->assign("excel_import_configurator", $excel_configurator);
					$smarty->assign("delimeter", $_POST["delimeter"]);
				}
				else $smarty->assign("excel_import_result", "upload_file_error");
			}
		}

		//last step of import = fill database with new content
		//configuration finished - update database
		if (isset($_POST["do_excel_import"]) && isset($_POST["filename"]) && isset($_POST["update_column"]))
		{
			if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
			{
				Redirect("admin.php?dpt=catalog&sub=excel_import&safemode=yes");
			}

			@set_time_limit(0);

			//import file content
			$data=myfgetcsv($_POST["filename"], $_POST["delimeter"]);
			if (!count($data)) die (ERROR_CANT_READ_FILE);

			$res = imReadImportConfiguratorSettings();
			$db_association			= $res["db_association"];
			$dbcPhotos				= $res["dbcPhotos"];
			$dbc					= $res["dbc"];
			$updated_extra_option	= $res["updated_extra_option"];

			//get update column
			$uc = $dbc[ $_POST["update_column"] ];
			if (!strcmp($uc,"not defined")) //not set update column
			{
				$smarty->assign("excel_import_result", "update_column_error");
				//go to the previous step
				$proceed = 1;
				$file_excel = "";
				$file_excel_name = $_POST["filename"];
				$res = 1;
			}
			else
			{$begin = time();
				$parents = array(); //2 create a category tree
				$parents[0] = 1;
				$currentCategoryID = 1;
				for ($i=$_POST["number_of_titles_line"]+1; $i<count($data); $i++)
				{
					$a = time();
					imImportRowToDataBase( $data[$i], $dbc, $uc, $dbcPhotos, 
						$updated_extra_option, $parents, $currentCategoryID );
					$b = time();
					//echo $data[$i][$dbc["name"]]." - ".($b-$a)."<br>";
				}
				$end = time();

				//update products count value if defined
				//if (CONF_UPDATE_GCV == 1)
					update_products_Count_Value_For_Categories(1);
				$smarty->assign("excel_import_result", "ok");
			}
		}
		$smarty->assign("admin_sub_dpt", "catalog_excel_import.tpl.html");
	}
?>