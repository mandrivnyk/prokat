<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
	//ADMIN :: related items managment


function showproducts($cid, $owner) //show products of the selected category
{
	$q = db_query("SELECT productID, name FROM ".PRODUCTS_TABLE." WHERE categoryID='$cid'") or die (db_error());
	echo "<table bgcolor=#AACC88 cellpadding=2>";
	while ($row = db_fetch_row($q))
	{
		echo "<tr bgcolor=#DDEEBB><td>&nbsp;&nbsp;&nbsp;&nbsp;";
		echo "<a href=\"wishlist.php?owner=$owner&categoryID=$cid&select_product=$row[0]\"><u>$row[1]</u></a>";
		echo "&nbsp;&nbsp;&nbsp;&nbsp;</td></tr>";
	}
	echo "</table><br>";
}


	include("./cfg/connect.inc.php");
	include("./includes/database/".DBMS.".php");
	include("./core_functions/category_functions.php");
	include( "./core_functions/setting_functions.php" ); 
	include("./core_functions/functions.php");

	MagicQuotesRuntimeSetting();

	//connect 2 database
	db_connect(DB_HOST,DB_USER,DB_PASS) or die (db_error());
	db_select_db(DB_NAME) or die (db_error());

	settingDefineConstants();

	session_start();

	include("./checklogin.php");
	if ( CONF_BACKEND_SAFEMODE != 1 && (!isset($_SESSION["log"]) || strcmp($_SESSION["log"],ADMIN_LOGIN))) //unauthorized
	{
		die (ERROR_FORBIDDEN);
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


	if (!isset($_GET["owner"])) //'owner product' not set
	{
		echo "<center><font color=red>".ERROR_CANT_FIND_REQUIRED_PAGE."</font>\n<br><br>\n";
		echo "<a href=\"javascript:window.close();\">".CLOSE_BUTTON."</a></center></body>\n</html>";
		exit;
	}
	$owner = $_GET["owner"];

	$categoryID = isset($_GET["categoryID"]) ? $_GET["categoryID"] : 0;

	if (isset($_GET["select_product"])) //add 2 wish-list (related items list)
	{
		if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
		{
			Redirect("wishlist.php?safemode=yes&owner=".$owner);
		}

		$q = db_query("SELECT count(*) FROM ".RELATED_PRODUCTS_TABLE." WHERE productID='".$_GET["select_product"]."' AND Owner='$owner'") or die (db_error());
		$cnt = db_fetch_row($q);
		if ($cnt[0] == 0) // insert
			db_query("INSERT INTO ".RELATED_PRODUCTS_TABLE." (productID, Owner) VALUES ('".$_GET["select_product"]."', '$owner')") or die (db_error());
	}

	if (isset($_GET["delete"])) //remove from wish-list
	{
		if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
		{
			Redirect("wishlist.php?safemode=yes&owner=".$owner);
		}
		db_query("DELETE FROM ".RELATED_PRODUCTS_TABLE." WHERE productID='".$_GET["delete"]."' AND Owner='$owner'") or die (db_error());
	}

?>

<html>

<head>
<link rel=STYLESHEET href="style1.css" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo DEFAULT_CHARSET;?>">
<title><?php echo STRING_RELATED_ITEMS;?></title>
</head>

<body bgcolor=#DDEEBB>

<?php
		if ( isset($_GET["safemode"]) )
		{
			echo "<p>\n<font color=red class=cat><b>".ADMIN_SAFEMODE_WARNING."<b></font>";
		}
?>
<p>
<TABLE BORDER=0 WIDTH=100% BGCOLOR=#77AA88 CELLSPACING=1>
<TR bgcolor=#DDEEBB>

<TD>



<table>
<tr><td colspan=2><u><?php echo ADMIN_RELATED_PRODUCTS_SELECT;?>:</u><br><br></td></tr>
<tr><td>&nbsp;</td>
<td>
<table>
<?php
	//calculate a path to the selected category
	// $path = catCalculatePathToCategory( $categoryID );
/*
	$path = array($categoryID);
	$curr = $categoryID;
	do
	{
		$q = db_query("SELECT parent FROM ".CATEGORIES_TABLE." WHERE categoryID='$curr'") or die (db_error());
		$row = db_fetch_row($q);
		$curr = $row ? $row[0] : 0; //get parent ID
		$path[] = $curr;

	} while ($curr);
	//now reverse $path
	$path = array_reverse($path);
*/

	//get category rolled out tree
	// $out = processCategories(0, $path, $categoryID);

	$out = catGetCategoryCompactCList( $categoryID );

	//show categories tree
	for ($i=0; $i<count($out); $i++)
	{
		if ( $out[$i]["categoryID"] == 0 )
			continue;

		echo "<tr><td>";
		for ($j=0; $j<$out[$i]["level"]-1; $j++) echo "&nbsp;&nbsp;";

		if ($out[$i]["categoryID"] == $categoryID) //no link on selected category
		{
			echo "- ".$out[$i]["name"]."\n<p>";
			showproducts($categoryID, $owner);
			echo "</td></tr>\n";
		}
		else //make a link
		{
			echo "<a href=\"wishlist.php?owner=$owner&categoryID=".$out[$i]["categoryID"]."\"";
			if ($out[$i]["level"]>1) echo " class=standard";
			echo ">+ ".$out[$i]["name"]."</a></td></tr>\n";
		}
	}

?>
</table>
</td>
</tr>
</table>

</TD>

<TD VALIGN=TOP WIDTH=50%>
<table width=100%>
<tr><td colspan=2><u><?php echo ADMIN_SELECTED_PRODUCTS;?></u><br><br></td></tr>
<tr><td>&nbsp;</td>
<td>
<?php

	$q = db_query("SELECT productID FROM ".RELATED_PRODUCTS_TABLE." WHERE Owner='$owner'") or die (db_error());
		echo "<table width=90% border=0 bgcolor=#77AA99 cellspacing=1 cellpadding=3>";
		while ($row = db_fetch_row($q))
		{
			$p = db_query("SELECT name FROM ".PRODUCTS_TABLE." WHERE productID=$row[0]") or die (db_error());
			if ($r = db_fetch_row($p))
			{
			  echo "<tr bgcolor=#DDEEBB>";
			  echo "<td width=100%>$r[0]</td>";
			  echo "<td width=1%><a href=\"wishlist.php?owner=$owner&categoryID=$categoryID&delete=$row[0]\"><img src=\"images/remove.jpg\" border=0 alt=\"".DELETE_BUTTON."\"></a></td>";
			  echo "</tr>";
			}
		}
		echo "</table>";

?>
</td>
</tr>
</table>


</TD>
</TR>
</TABLE>

<center>
<form>
<input type=button value="<?php echo CLOSE_BUTTON;?>" onClick="javascript:window.close();">
</form>
</center>

</body>

</html>