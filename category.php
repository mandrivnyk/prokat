<?php
	//ADMIN :: categories managment
	include("./cfg/connect.inc.php");
	include("./includes/database/".DBMS.".php");
	include("./core_functions/category_functions.php");
	include("./core_functions/functions.php");
	include("./core_functions/option_functions.php");
	include("./core_functions/search_function.php");
	include("./core_functions/setting_functions.php");
	include_once("./js/fckeditor/fckeditor.php");
	include_once( "./core_functions/url_function.php" );
	MagicQuotesRuntimeSetting();
	//connect to database
	db_connect(DB_HOST,DB_USER,DB_PASS) or die (db_error());
	db_select_db(DB_NAME) or die (db_error());
	$q = db_query("SET NAMES CP1251");
    $q = db_query("SET COLLATION_CONNECTION=CP1251_GENERAL_CI");
	//checking for authorized access
	session_start();
if(isset($_POST["url_name"]))
	{
		$_POST["url_name"] = trim($_POST["url_name"]);
		//чистим урл
			//$_POST["url_name"]	= eregi_replace("([\~\,\:\@\#\є\%\^\&\?\!\*\(\)\$\+\=\'\"\`\; а-€ј-яЄ®])+", '',$_POST["url_name"]);
			$_POST["url_name"]	= preg_replace('/[^A-Za-z0-9-]+/', '', $_POST["url_name"]);
	}
	//current language
	include("./cfg/language_list.php");
	if (!isset($_SESSION["current_language"]) ||
		$_SESSION["current_language"] < 0 || $_SESSION["current_language"] > count($lang_list))
			$_SESSION["current_language"] = 0; //set default language
	//include a language file
	if (isset($lang_list[$_SESSION["current_language"]]) &&
		file_exists("languages/".$lang_list[$_SESSION["current_language"]]->filename))
	{
		//include current language file
		include("languages/".$lang_list[$_SESSION["current_language"]]->filename);
	}
	else
	{
		die("<font color=red><b>ERROR: Couldn't find language file!</b></font>");
	}
	settingDefineConstants();
	include("./checklogin.php");
	if ( CONF_BACKEND_SAFEMODE != 1 && (!isset($_SESSION["log"]) || strcmp($_SESSION["log"],ADMIN_LOGIN))) //unauthorized
	{
		die (ERROR_FORBIDDEN);
	}
	if (isset($_POST) && count($_POST)>0)
	{
		if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
		{
			if (!isset($_POST["must_delete"])) //adding a new category
				Redirect("category.php?safemode=yes");
			else //editing an existing category
				Redirect("category.php?safemode=yes&categoryID=".$_POST["must_delete"]);
		}
	}
	if (isset($_GET["picture_remove"])) //delete category thumbnail from server
	{
		if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
		{
			Redirect("category.php?safemode=yes&categoryID=".$_GET["categoryID"]);
		}
		$q = db_query("SELECT picture FROM ".CATEGORIES_TABLE.
			" WHERE categoryID='".$_GET["categoryID"]."' and categoryID<>1") or die (db_error());
		$r = db_fetch_row($q);
		if ($r[0] && file_exists("./products_pictures/$r[0]")) unlink("./products_pictures/$r[0]");
		db_query("UPDATE ".CATEGORIES_TABLE." SET picture='' WHERE categoryID='".
										$_GET["categoryID"]."'") or die (db_error());
	}
	if (isset($_GET["categoryID"]) && isset($_GET["del"])) //delete category
	{
		if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
		{
			Redirect("category.php?safemode=yes&categoryID=".$_GET["categoryID"]);
		}
		catDeleteCategory( $_GET["categoryID"] );
		//close window
		echo "<script>\n";
		echo "window.opener.location = 'admin.php?dpt=catalog&sub=products_categories&categoryID=1';\n";
		echo "window.close();";
		echo "</script>\n</body>\n</html>";
	}
?>
<html>
<head>
<link rel=STYLESHEET href="style1.css" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo DEFAULT_CHARSET;?>">
<title><?php echo ADMIN_CATEGORY_TITLE;?></title>
<script>
function confirmDelete(text,url)
{
	temp = window.confirm(text);
	if (temp) //delete
	{
		window.location=url;
	}
}
function open_window(link,w,h)
{
	var win = "width="+w+",height="+h+",menubar=no,location=no,resizable=yes,scrollbars=yes";
	wishWin = window.open(link,'wishWin',win);
}
function position_this_window()
{
	var x = (screen.availWidth - 795) / 2;
	window.resizeTo(795, screen.availHeight - 50);
	window.moveTo(Math.floor(x),25);
}
</script>
</head>
<body bgcolor=#D2D2FF onLoad="position_this_window();">
<?php
	function deleteSubCategories($parent) //deletes all subcategories of category with categoryID=$parent
	{
		//subcategories
		$q = db_query("SELECT categoryID FROM ".CATEGORIES_TABLE." WHERE parent=$parent and categoryID<>1") or die (db_error());
		while ($row = db_fetch_row($q))
		{
			deleteSubCategories($row[0]); //recurrent call
		}
		$q = db_query("DELETE FROM ".CATEGORIES_TABLE." WHERE parent=$parent and categoryID<>1") or die (db_error());
		//move all product of this category to the root category
		$q = db_query("UPDATE ".PRODUCTS_TABLE." SET categoryID=1 WHERE categoryID=$parent") or die (db_error());
	}
	function category_Moves_To_Its_SubDirectories($cid, $new_parent)
	{
		$a = false;
		$q = db_query("SELECT categoryID FROM ".CATEGORIES_TABLE." WHERE parent=$cid and categoryID<>1") or die (db_error());
		while ($row = db_fetch_row($q))
			if (!$a)
			{
				if ($row[0] == $new_parent) return true;
				else
				  $a = category_Moves_To_Its_SubDirectories($row[0],$new_parent);
			}
		return $a;
	}
	function _getOptions()
	{
		$options = optGetOptions();
		for( $i=0; $i < count($options); $i++ )
		{
			if ( isset($_GET["categoryID"]) )
				$res = schOptionIsSetToSearch( $_GET["categoryID"], $options[$i]["optionID"] );
			else
				$res = array( "isSet" => true, "set_arbitrarily" => 1 );
			if ( $res["isSet"] )
			{
				$options[$i]["isSet"] = true;
				$options[$i]["set_arbitrarily"] = $res["set_arbitrarily"];
			}
			else
			{
				$options[$i]["isSet"] = false;
				$options[$i]["set_arbitrarily"] = 1;
			}
			$options[$i]["variants"] =
				optGetOptionValues( $options[$i]["optionID"] );
			for( $j=0; $j<count($options[$i]["variants"]); $j++)
			{
				$isSet = false;
				if (  isset($_GET["categoryID"])  )
					$isSet = schVariantIsSetToSearch( $_GET["categoryID"],
								$options[$i]["optionID"],
								$options[$i]["variants"][$j]["variantID"] );
				$options[$i]["variants"][$j]["isSet"] = $isSet;
			}
		}
		return $options;
	}
	if (isset($_POST["save"]) && $_POST["name"])
	{ //save changes
		$_POST["title_one"]			= TransformStringToDataBase($_POST["title_one"]);
		$_POST["title_two"]			= TransformStringToDataBase($_POST["title_two"]);
		$_POST["name"]			= TransformStringToDataBase($_POST["name"]);
		$_POST["head_text"]			= TransformStringToDataBase($_POST["head_text"]);
		$_POST["seo_text"]			= TransformStringToDataBase($_POST["seo_text"]);
		$_POST["desc"]			= TransformStringToDataBase($_POST["desc"]);
		$_POST["meta_d"]		= TransformStringToDataBase($_POST["meta_d"]);
		$_POST["meta_k"]		= TransformStringToDataBase($_POST["meta_k"]);
		$_POST["sort_order"]	= (int)$_POST["sort_order"];
		$_POST["skidka"]	= (int)$_POST["skidka"];
		$allow_products_comparison = isset($_POST["allow_products_comparison"])?1:0;
		$allow_products_search = isset($_POST["allow_products_search"])?1:0;
		$show_subcategories_products = isset($_POST["show_subcategories_products"])?1:0;
// dublicate product *********************************
    if ( isset($_POST["dbl_category"]) )
    {
           $q = db_query("INSERT INTO ".CATEGORIES_TABLE.
				" (url_name, title_one, title_two, name, head_text, seo_text, parent, products_count, description, picture, ".
				"    products_count_admin, sort_order,skidka, ".
				"    allow_products_comparison, ".
				"    allow_products_search, ".
				"show_subcategories_products, ".
				"    meta_description, meta_keywords ) ".
				" VALUES ('".$_POST["url_name"]."','".$_POST["title_one"]."','".$_POST["title_two"]."','".$_POST["name"]."','".$_POST["head_text"]."','".$_POST["seo_text"]."',".$_POST["parent"].",0,'".
					$_POST["desc"]."','',0, ".$_POST["sort_order"].",".$_POST["skidka"].", $allow_products_comparison".
					", $allow_products_search".
					", $show_subcategories_products, '".
					$_POST["meta_d"]."', '".$_POST["meta_k"]."');");
			$pid = db_insert_id("CATEGORIES_GEN");
			//update products count value if defined
			if (CONF_UPDATE_GCV == 1)
			{
				update_products_Count_Value_For_Categories(1);
			}
    }
////////////////////////////////////////////////////////////////////  
		if (!isset($_POST["must_delete"])) //add new category
		{
			$q = db_query("INSERT INTO ".CATEGORIES_TABLE.
				" (url_name, title_one, title_two, name, head_text,seo_text, parent, products_count, description, picture, ".
				"    products_count_admin, sort_order, skidka, ".
				"    allow_products_comparison, ".
				"    allow_products_search, ".
				"show_subcategories_products, ".
				"    meta_description, meta_keywords ) ".
				" VALUES ('".$_POST["url_name"]."','".$_POST["title_one"]."','".$_POST["title_two"]."','".$_POST["name"]."','".$_POST["head_text"]."','".$_POST["seo_text"]."',".$_POST["parent"].",0,'".
					$_POST["desc"]."','',0, ".$_POST["sort_order"].",".$_POST["skidka"].", $allow_products_comparison".
					", $allow_products_search".
					", $show_subcategories_products, '".
					$_POST["meta_d"]."', '".$_POST["meta_k"]."');");
			$pid = db_insert_id("CATEGORIES_GEN");
			//------------------ URL REWRITE-------------------------------------
			include_once("./public_scripts/call_url_rewrite.php");
					
			//------------------------------------------------------------------
		}
		else //update existing category
		{
			if ($_POST["must_delete"] != $_POST["parent"]) //if not moving category to itself
			{
				//if category is being moved to any of it's subcategories - it's
				//neccessary to 'lift up' all it's subcategories
				if (category_Moves_To_Its_SubDirectories($_POST["must_delete"], $_POST["parent"]))
				{
					//lift up is required
					//get parent
					$q = db_query("SELECT parent FROM ".CATEGORIES_TABLE." WHERE categoryID<>1 and categoryID='".$_POST["must_delete"]."'") or die (db_error());
					$r = db_fetch_row($q);
					//lift up
					db_query("UPDATE ".CATEGORIES_TABLE." SET parent='$r[0]' WHERE parent='".$_POST["must_delete"]."'") or die (db_error());
					//move edited category
					db_query("UPDATE ".CATEGORIES_TABLE.
							" SET url_name= '".$_POST["url_name"].
							"',title_one='".str_replace("<","&lt;",$_POST["title_one"]).
							"',title_two='".str_replace("<","&lt;",$_POST["title_two"]).
							"',head_text='".str_replace("<","&lt;",$_POST["head_text"]).
							"',seo_text='".str_replace("<","&lt;",$_POST["seo_text"]).
							"',name='".str_replace("<","&lt;",$_POST["name"]).
							"', description='".$_POST["desc"].
							"', parent='".$_POST["parent"].
							"', sort_order = ".$_POST["sort_order"].
							"', skidka = ".$_POST["skidka"].
							", allow_products_comparison=$allow_products_comparison ".
							", allow_products_search=$allow_products_search ".
							", show_subcategories_products=$show_subcategories_products ".
							", meta_description='".$_POST["meta_d"].
							"', meta_keywords='".$_POST["meta_k"].
							"'  WHERE categoryID='".$_POST["must_delete"]."'") or die (db_error());
				}
				else //just move category
				{
					$q = db_query('SELECT skidka FROM '.CATEGORIES_TABLE.' WHERE categoryID='.$_POST["must_delete"]);
					$skidka_old=db_fetch_row($q );
					db_query("UPDATE ".CATEGORIES_TABLE." SET url_name= '".$_POST["url_name"]."',
					
						title_one='".str_replace("<","&lt;",$_POST["title_one"])."', 
						title_two='".str_replace("<","&lt;",$_POST["title_two"])."', 
						name='".str_replace("<","&lt;",$_POST["name"])."', 
						head_text='".$_POST["head_text"]."', 
						seo_text='".$_POST["seo_text"]."', 
						description='".$_POST["desc"]."', parent='".$_POST["parent"]."', sort_order = ".$_POST["sort_order"].", skidka = ".$_POST["skidka"].", allow_products_comparison=$allow_products_comparison ".
						", allow_products_search=$allow_products_search ".
						", show_subcategories_products=$show_subcategories_products ".
						", meta_description='".$_POST["meta_d"].
						"', meta_keywords='".$_POST["meta_k"].
						"' WHERE categoryID='".$_POST["must_delete"]."'") or die (db_error());
          
						
						
						
						if(intval($skidka_old['skidka']) !== intval($_POST["skidka"]))
						{	
							update_parent_products_skidka($_POST["must_delete"], $skidka_old['skidka'], $_POST["skidka"]);
							//echo 'skidka_old[skidka] = '.$skidka_old['skidka'].'<br>';
							//echo 'skidka = '.$_POST["skidka"].'<br>';
							//echo 'TYT';
							//die();
						}	
						/*	echo 'skidka_old[skidka] = '.$skidka_old['skidka'].'<br>';
							echo 'skidka = '.$_POST["skidka"].'<br>';
							echo 'tyt';
							die();	*/
						
						
				}					
						//------------------ URL REWRITE-------------------------------------
						include_once("./public_scripts/call_url_rewrite.php");
					
				
			}
			$pid = $_POST["must_delete"];
			//update products count value if defined
			if (CONF_UPDATE_GCV == 1)
			{
				update_products_Count_Value_For_Categories(1);
			}
		}
		// update serarch option settings
		$categoryID = $pid;
		schUnSetOptionsToSearch( $categoryID );
		$data = ScanPostVariableWithId( array("checkbox_param") );
		foreach( $data as $optionID => $val )
		{
			schUnSetVariantsToSearch( $categoryID, $optionID );
			if ( isset($_POST["select_arbitrarily_$optionID"]) )
				$set_arbitrarily = $_POST["select_arbitrarily_$optionID"];
			else
				$set_arbitrarily = 1;
			schSetOptionToSearch( $categoryID, $optionID, $set_arbitrarily );
			if ( $set_arbitrarily == 0 )
			{
				$variants = optGetOptionValues( $optionID );
				foreach( $variants as $var )
					if ( isset( $_POST[ "checkbox_variant_".$var["variantID"] ] ) )
						schSetVariantToSearch( $categoryID, $optionID, $var["variantID"] );
			}
		}
		if (isset($_FILES["picture"]) && $_FILES["picture"]["name"] && preg_match('/\.(jpg|jpeg|gif|jpe|pcx|bmp)$/i', $_FILES["picture"]["name"])) //upload category thumbnail
		{
			//old picture
			$q = db_query("SELECT picture FROM ".CATEGORIES_TABLE." WHERE categoryID='$pid' and categoryID<>0") or die (db_error());
			$row = db_fetch_row($q);
			//upload new photo
			$picture_name = str_replace(" ","_", $_FILES["picture"]["name"]);
			if (!@move_uploaded_file($_FILES["picture"]["tmp_name"], "./products_pictures/$picture_name")) //failed to upload
			{
				echo "<center><font color=red>".ERROR_FAILED_TO_UPLOAD_FILE."</font>\n<br><br>\n";
				echo "<a href=\"javascript:window.close();\">".CLOSE_BUTTON."</a></center></body>\n</html>";
				exit;
			}
			else //update db
			{
				SetRightsToUploadedFile( "./products_pictures/$picture_name" );
				db_query("UPDATE ".CATEGORIES_TABLE.
					" SET picture='$picture_name' ".
					" WHERE categoryID='$pid'") or die (db_error());
			}
			//remove old picture...
			if ($row[0] && strcmp($row[0], $picture_name) && file_exists("./products_pictures/$row[0]"))
				unlink("./products_pictures/$row[0]");
		}
		//now close the window (in case of success)
		echo "<script>\n";
		echo "window.opener.location.reload();\n";
		echo "window.close();\n";
		echo "</script>\n</body>\n</html>";
	}
	else //category edition from
	{
		if (isset($_GET["categoryID"])) //edit existing category
		{
			$row = catGetCategoryById($_GET["categoryID"]);
			/*echo '<pre>';
				print_r($row);
			echo '</pre>';
			exit();*/
			if (!$row) //can't find category....
			{
				echo "<center><font color=red>".ERROR_CANT_FIND_REQUIRED_PAGE."</font>\n<br><br>\n";
				echo "<a href=\"javascript:window.close();\">".CLOSE_BUTTON."</a></center></body>\n</html>";
				exit;
			}
			$title_one					= TransformDataBaseStringToText($row["title_one"]);
			$title_two					= TransformDataBaseStringToText($row["title_two"]);
			$url_name					= TransformDataBaseStringToText($row["url_name"]);
			//----- удал€ем кеш файл, чтоб увидеть обновление--------------------------------
			if($url_name !== '')
			{
				if (file_exists('./cache/'.$url_name.'.cache'))
					unlink('./cache/'.$url_name.'.cache');
			}
			$title						= "<b>".TransformDataBaseStringToText($row["name"])."</b>";
			$head_text					=  $row["head_text"];
			$seo_text					=  $row["seo_text"];
 
			$n							= TransformDataBaseStringToText($row["name"]);
			//$d							= TransformDataBaseStringToText($row["description"]);
			$d							= $row["description"];
			$meta_d						= TransformDataBaseStringToText($row["meta_description"]);
			$meta_k						= TransformDataBaseStringToText($row["meta_keywords"]);
			$picture					= $row["picture"];
			$sort_order					= $row["sort_order"];
			$skidka					= $row["skidka"];
			$parent						= $row["parent"];
			$allow_products_comparison	= $row["allow_products_comparison"];
			$allow_products_search		= $row["allow_products_search"];
			$show_subcategories_products= $row["show_subcategories_products"];
		}
		else //create new
		{
			$url_name		= '';
			$title_one		= '';
			$title_two		= '';
			$title		= ADMIN_CATEGORY_NEW;
			$n			= "";
			$d			= "";
			$meta_d		= "";
			$meta_k		= "";
			$picture	= "";
			$sort_order = 0;
			$skidka = 0;
			$allow_products_comparison	= 1;
			$allow_products_search		= 1;
			$parent			= 1;
			$show_subcategories_products = 1;
		}
		$options = _getOptions();
		$showSelectParametrsTable = 0;
		if ( isset($_GET["SelectParametrsHideTable_hidden"]) )
			$showSelectParametrsTable = $_GET["SelectParametrsHideTable_hidden"];
?>
<center><font color=purple><?php echo $title;?></font>
<?php
		if ( isset($_GET["safemode"]) )
		{
			echo "<p>\n<font color=red><b>".ADMIN_SAFEMODE_WARNING."<b></font>";
		}
?>
</center>
<form	enctype="multipart/form-data"
		action="category.php"
		method=post
		name='MainForm' >
<table width=100% border=0>
	<!-- general parent -->
	<tr>
		<td align=right>
		<?php
			if (!isset($_GET["categoryID"])) echo ADMIN_CATEGORY_PARENT;
			else echo ADMIN_CATEGORY_MOVE_TO;
		?>
		</td>
		<td width=5%>&nbsp;</td>
		<td>
			<select name="parent"<?php
	if (CONF_FULLY_EXPAND_CATEGORIES_IN_ADMIN_MODE == 0) // update list
	{
		echo " onChange=\"window.location='category.php?";
		if (isset($_GET["categoryID"])) echo "categoryID=".$_GET["categoryID"]."&";
		echo "change_category='+document.MainForm.parent.value;\"";
	}
?>>
				<!--<option value="1"><?php echo ADMIN_CATEGORY_ROOT;?></option>-->
				<?php
					if (CONF_FULLY_EXPAND_CATEGORIES_IN_ADMIN_MODE == 1)
						echo "<option value=\"1\">".ADMIN_CATEGORY_ROOT."</option>";
					//fill the category combobox
					$core_category = (isset($_GET["change_category"])) ? (int)$_GET["change_category"] : $parent ;
					if (CONF_FULLY_EXPAND_CATEGORIES_IN_ADMIN_MODE == 0)
						$cats = catGetCategoryCompactCList($core_category);
					else
						$cats = catGetCategoryCList();
					for ($i=0; $i<count($cats); $i++)
					{
						echo "<option value=\"".$cats[$i]["categoryID"]."\"";
						if ( $core_category == $cats[$i]["categoryID"] ) //select category
							echo " selected";
						echo ">";
						for ($j=0;$j<$cats[$i]["level"];$j++) echo "&nbsp;&nbsp;";
							echo $cats[$i]["name"];
						echo "</option>";
					}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td align=right>
			<?php echo ADMIN_CATEGORY_URL_NAME;?>
		</td>
		<td>
			&nbsp;
		</td>
		<td>
			<input type="text" name="url_name"
				value="<?php echo str_replace("\"","&quot;",$url_name);?>" size=60>
		</td>
	</tr>
	<tr>
		<td align=right>
			<?php echo ADMIN_CATEGORY_TITLE_ONE;?>
		</td>
		<td>
			&nbsp;
		</td>
		<td>
			<input type="text" name="title_one" value="<?php echo str_replace("\"","&quot;",$title_one);?>" size=60>
		</td>
	</tr>
	<tr>
		<td align=right>
			<?php echo ADMIN_CATEGORY_NAME;?>
		</td>
		<td>
			&nbsp;
		</td>
		<td>
			<input type="text" name="name"
				value="<?php echo str_replace("\"","&quot;",$n);?>" size=60>
		</td>
	</tr>
	
	<tr>
		<td align=right>
			<?php echo ADMIN_CATEGORY_TITLE_TWO;?>
		</td>
		<td>
			&nbsp;
		</td>
		<td>
			<textarea name="title_two" rows=5 cols=46><?php echo str_replace("\"","&quot;",$title_two) ?></textarea>
			
		</td>
	</tr>
	<tr>
		<td align=right>
			<?php echo ADMIN_SORT_ORDER;?>
		</td>
		<td>
			&nbsp;
		</td>
		<td>
			<input type="text" name="sort_order"
				value="<?php echo $sort_order?>" size=60>
		</td>
	</tr>
	<tr>
		<td align=right>
			<?php echo ADMIN_SKIDKA;?>
		</td>
		<td>
			&nbsp;
		</td>
		<td>
			<input type="text" name="skidka" value="<?php echo $skidka?>" size="60" style="background: #eae8e3;">
		</td>
	</tr>
	<tr>
		<td align=right>
			<?php echo ADMIN_ALLOW_PRODUCTS_COMPARISON;?>
		</td>
		<td>
			&nbsp;
		</td>
		<td>
			<input type=checkbox name="allow_products_comparison"
				value='1'
			<?php
				if ( $allow_products_comparison == 1 )
				{
			?>
					checked
			<?php
				}
			?>
			>
		</td>
	</tr>
	<tr>
		<td align=right>
			<?php echo STRING_ADVANCED_SEACH_TITLE;?>
		</td>
		<td>
			&nbsp;
		</td>
		<td>
			<a href="JavaScript:SelectParametrsHideTable();">
				<?php echo ADMIN_SELECT_PARAMETRS;?>...
			</a>
			<br>
			<?php echo ADMIN_SELECT_PARAMETRS_PROMPT;?>
			<input type=hidden name='SelectParametrsHideTable_hidden'
				value='<?php echo $showSelectParametrsTable;?>'>
			<script language='javascript'>
				function SelectParametrsHideTable()
				{
					if ( SelectParametrsTable.style.display == 'none' )
					{
						SelectParametrsTable.style.display = 'block';
						document.MainForm.SelectParametrsHideTable_hidden.value='1';
					}
					else
					{
						SelectParametrsTable.style.display = 'none';
						document.MainForm.SelectParametrsHideTable_hidden.value='0';
					}
				}
			</script>
			<br>
			<table id='SelectParametrsTable'>
					<?php
					foreach( $options as $option )
					{
					?>
						<tr>
							<td>
								<table>
										<tr>
											<td colspan=3>
												<input type=checkbox
													name='checkbox_param_<?php echo $option["optionID"];?>'
													<?php
													if ( $option["isSet"] )
													{
													?>
													<?php
													}
													?>
													onclick='JavaScript:Checkbox_param_Change_<?php echo $option["optionID"];?>()'
												>
														<?php echo $option["name"];?>
											</td>
										</tr>
										<?php
										if ( count($option["variants"]) != 0 )
										{
										?>
										<tr>
											<td>&nbsp;</td>
											<td colspan=2>
												<input type=radio
													name='select_arbitrarily_<?php echo $option["optionID"];?>'
													id='select_arbitrarily1_<?php echo $option["optionID"];?>'
													<?php
													if ( $option["set_arbitrarily"] == 1 )
													{
													?>
														checked
													<?php
													}
													?>
													value='1'
													onclick='Select_arbitrarily_Change_<?php echo $option["optionID"];?>()'
												>
														<?php echo ADMIN_SEARCH_IN_CATEGORY_PARAMETR_VALUE_ARBITRARILY;?>
											</td>
										</tr>
										<tr>
											<td>&nbsp;</td>
											<td colspan=2>
												<input type=radio
													name='select_arbitrarily_<?php echo $option["optionID"];?>'
													id='select_arbitrarily2_<?php echo $option["optionID"];?>'
												<?php
													if ( $option["set_arbitrarily"] == 0 )
													{
												?>
														checked
												<?php
													}
												?>
													value='0'
													onclick='Select_arbitrarily_Change_<?php echo $option["optionID"];?>()'
												>
														<?php echo ADMIN_SEARCH_IN_CATEGORY_PARAMETR_VALUE_SELECT_FROM_VALUES;?>
											</td>
										</tr>
											<?php
											foreach( $option["variants"] as $variant )
											{
											?>
												<tr>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>
														<input type=checkbox
															name='checkbox_variant_<?php echo $variant["variantID"];?>'
															<?php
															if ( $variant["isSet"] )
															{
															?>
																checked
															<?php
															}
															?>
														>
																<?php echo $variant["option_value"];?>
													</td>
												</tr>
											<?php
											}
											?>
										<?php
										}
										?>
							</table>
							<script language='JavaScript'>
								function Checkbox_param_Change_<?php echo $option["optionID"];?>()
								{
									_checked = document.MainForm.
										checkbox_param_<?php echo $option["optionID"];?>.checked;
									<?php
									if ( count($option["variants"]) != 0 )
									{
									?>
										document.MainForm.
											select_arbitrarily1_<?php echo $option["optionID"];?>.disabled =
												!_checked;
										document.MainForm.
											select_arbitrarily2_<?php echo $option["optionID"];?>.disabled =
												!_checked;
									<?php
									}
									?>
									Select_arbitrarily_Change_<?php echo $option["optionID"];?>();
								}
								function Select_arbitrarily_Change_<?php echo $option["optionID"];?>()
								{
									<?php
									if ( count($option["variants"]) != 0 )
									{
									?>
										_enabled =
											document.MainForm.
												select_arbitrarily2_<?php echo $option["optionID"];?>.checked
										    &&
											document.MainForm.
												checkbox_param_<?php echo $option["optionID"];?>.checked;
									<?php
									}
									?>
									<?php
									foreach( $option["variants"] as $variant )
									{
									?>
										document.MainForm.
											checkbox_variant_<?php echo $variant["variantID"];?>.
												disabled = !_enabled;
									<?php
									}
									?>
								}
								Checkbox_param_Change_<?php echo $option["optionID"];?>();
							</script>
						</td>
					</tr>
					<?php
					}
					?>
			</table>
			<script language='JavaScript'>
				<?php
					if ( $showSelectParametrsTable == 0 )
					{
				?>
						SelectParametrsTable.style.display = 'none';
				<?php
					}
				?>
			</script>
		</td>
	</tr>
	<tr>
		<td align=right>
			<?php echo ADMIN_ALLOW_SEARCH_IN_CATEGORY;?>
		</td>
		<td>
			&nbsp;
		</td>
		<td>
			<input type=checkbox name="allow_products_search"
				value='1'
			<?php
				if ( $allow_products_search == 1 )
				{
			?>
					checked
			<?php
				}
			?>
			>
			(<?php echo ADMIN_ALLOW_SEARCH_IN_CATEGORY_PROMPT;?>)
		</td>
	</tr>
	<tr>
		<td align=right>
			<?php echo ADMIN_SHOW_PRODUCT_IN_SUBCATEGORY;?>
		</td>
		<td>
			&nbsp;
		</td>
		<td>
			<input type=checkbox name='show_subcategories_products' value='1'
			<?php
				if ( $show_subcategories_products == 1 )
				{
			?>
					checked
			<?php
				}
			?>
			>
		</td>
	</tr>
	<tr>
		<td align=right>
			<?php echo ADMIN_CATEGORY_LOGO;?>
		</td>
		<td>
			&nbsp;
		</td>
		<td>
			<input type="file" name="picture">
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>
		<?php
			if ($picture != "" && file_exists("./products_pictures/".$picture))
			{
				list( $width, $height, $type, $attr ) =
						getimagesize( "./products_pictures/".$picture );
				$width += 40;
				$height += 40;
				$href = "JavaScript:open_window(\"products_pictures/$picture\",$width,$height)";
				echo "<font class=average></font> <a class=small href='$href'>$picture</a>\n";
				echo "<br><a href=\"javascript:confirmDelete('".QUESTION_DELETE_PICTURE."','category.php?categoryID=".$_GET["categoryID"]."&picture_remove=yes');\">".DELETE_BUTTON."</a>\n";
			}
			else echo "<font class=average>".ADMIN_PICTURE_NOT_UPLOADED."</font>";
		?>
		</td>
	</tr>
	<tr>
		<td align=right>
			<?php echo ADMIN_META_KEYWORDS;?>
		</td>
		<td></td>
		<td>
			<textarea name="meta_k" rows=5 cols=52><?php echo str_replace("<","&lt;",$meta_k); ?></textarea>
		</td>
	</tr>
	<tr>
		<td align=right>
			<?php echo ADMIN_META_DESCRIPTION;?>
		</td>
		<td></td>
		<td>
			<textarea name="meta_d" rows=5 cols=52><?php echo str_replace("<","&lt;",$meta_d); ?></textarea>
		</td>
	</tr>
	<tr>
		<td align=right colspan="3">
			<?php echo ADMIN_HEAD_TEXT;?><br>(HTML)
<?php
	
			    $oFCKeditor = new FCKeditor('head_text') ;
				$oFCKeditor->BasePath = '/js/fckeditor/' ;
				$oFCKeditor->Value = $head_text; 
				$oFCKeditor->Height = 300; 
				
               	$oFCKeditor->Create();
?>
		</td>
	</tr>
	<tr>
		<td align=right colspan="3">
			<?php echo ADMIN_SEO_TEXT;?><br>(HTML)
<?php
	
			    $oFCKeditor = new FCKeditor('seo_text') ;
				$oFCKeditor->BasePath = '/js/fckeditor/' ;
				$oFCKeditor->Value = $seo_text; 
				$oFCKeditor->Height = 300; 
				
               	$oFCKeditor->Create();
?>
		</td>
	</tr>
	<tr>
		<td align=right colspan="3">
			<?php echo ADMIN_CATEGORY_DESC;?><br>(HTML)
<!--<textarea name="desc" rows=9 cols=52><?php echo str_replace("\"","&quot;",$d); ?></textarea>-->
<?php
	
			    $oFCKeditor = new FCKeditor('desc') ;
				$oFCKeditor->BasePath = '/js/fckeditor/' ;
				$oFCKeditor->Value = $d; 
				$oFCKeditor->Height = 300; 
				
               	$oFCKeditor->Create();
?>
		</td>
	</tr>
</table>
<p><center>
<input type="submit" value="<?php echo SAVE_BUTTON;?>" width=5>
<input type="hidden" name="save" value="yes">
<input type="button" value="<?php echo CANCEL_BUTTON;?>" onClick="window.close();">
<?php
	//$must_delete indicated which query should be made: insert/update
	if (isset($_GET["categoryID"]))
	{
		echo "<input type=\"hidden\" name=\"must_delete\" value=\"".str_replace("\"","",$_GET["categoryID"])."\">\n";
		echo "<input type=\"button\" value=\"".DELETE_BUTTON."\" onClick=\"confirmDelete('".QUESTION_DELETE_CONFIRMATION."','category.php?categoryID=".str_replace("\"","",$_GET["categoryID"])."&del=1');\">";
		echo "&nbsp;<input type=submit name=dbl_category value=\"".DBL_BUTTON."\" onClick=\"confirmDelete('".QUESTION_DBL_CONFIRMATION_CAT."','category.php?categoryID=".$_GET["categoryID"]."&dbl=1');\">"; 
	}
?>
</center></p>
</form>
</body>
</html>
<?php }; ?>