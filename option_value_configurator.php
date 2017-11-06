<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
	 //ADMIN :: option value configurator

	 include( "./cfg/connect.inc.php" );
	 include( "./includes/database/".DBMS.".php" );
	 include( "./core_functions/configurator_functions.php" );
	 include( "./core_functions/setting_functions.php" ); 
	 include("./core_functions/functions.php");

	 MagicQuotesRuntimeSetting();

	 //connect 2 database
	 db_connect(DB_HOST,DB_USER,DB_PASS) or die (db_error());
	 db_select_db(DB_NAME) or die (db_error());

	 settingDefineConstants();

	 //authorized access check
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

	 if ( isset($_GET["optionID"]) )
	 {
		 $optionID = $_GET["optionID"];
		 $productID  = $_GET["productID"];
	 }
	 else //_POST
	 {
		 $optionID = $_POST["optionID"];
		 $productID  = $_POST["productID"];
	 }
	 
	 if ( isset($_POST["SAVE"]) )
	 {
		if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
		{
			Redirect("option_value_configurator.php?safemode=yes&productID=".$productID."&optionID=".$optionID);
		}

		$variantID_default="null";
		foreach( $_POST as $key => $value )
		{
			if( strstr($key, "default_radiobutton_") )
			{
				$key = str_replace("default_radiobutton_","",$key);
				$variantID_default = (int)$key;
			}
		}

		$option_show_times = (int)$_POST["option_show_times"];
		if ( (int)$option_show_times <= 0 )
			$option_show_times = 1;

		$data = ScanPostVariableWithId( array( "switchOn", "price_surplus" ) );
		UpdateConfiguriableProductOption($optionID, $productID, 
			$option_show_times, $variantID_default, $data );
	 }


	 if ( isset($_POST["SAVE"]) || isset($_POST["CLOSE"]) )
	 {

		 if ( isset($_POST["SAVE"]) )
		 {
			// save values on opener window
			echo( "<script language='JavaScript'>" );
			echo( "		window.opener.document.MainForm.save_product_without_closing.value='1';" );
			echo( "		window.opener.document.MainForm.option_radio_type_".$optionID."[2].click();" );
			echo( "		window.opener.document.MainForm.save_product.click();" );
			echo( "</script>" );
		 }

		 echo("<script language='JavaScript'>");
		 echo("		window.close();");
		 echo("</script>");
		 exit;
	 }
?><html>
<head>
<link rel=STYLESHEET href="style1.css" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo DEFAULT_CHARSET;?>">
<script type="text/javascript"><!--
function checkboxGroup(_GroupBoxID){
	
	this.GroupBoxID = _GroupBoxID
	this.BoxCollection = Array();
	
	this.addBox = function(_ID, _Settings){
		
		_Obj = document.getElementById(_ID)
		this.BoxCollection.push(_Obj)
		_Obj.spNum = _Settings["spNum"]
		_Obj.GroupObj = this
		eval(_Settings["evalCode"])
	}
	
	this.changeState = function(){
		
		var pObj  = document.getElementById(this.GroupBoxID)
		for(var i=0; i<this.BoxCollection.length; i++){
			
			this.BoxCollection[i].checked = !pObj.checked
			this.BoxCollection[i].click()
		}
	}
	
	this.checkState = function(){
		
		var noChecked = true
		for(var i=0; i<this.BoxCollection.length; i++){
			
			if(this.BoxCollection[i].checked){
				noChecked = false
				break
			}
		}
		if(noChecked){
			
			var pObj  = document.getElementById(this.GroupBoxID)
			pObj.checked = false
		}
	}
}

var chbCol = new checkboxGroup('id_chbGroup')
//--></script>
</head>
<body bgcolor=#FFFFE2>
	<center>

	<?php
		$optionName=db_query("select name from ".PRODUCT_OPTIONS_TABLE.
			" where optionID='".$optionID."'" );
		$optionNameRow=db_fetch_row($optionName);
	?>

	<h5><?php echo ADMIN_CONFIGURATOR_TITLE?> <b><?php echo $optionNameRow["name"]?></b></h5>

<?php
		if ( isset($_GET["safemode"]) )
		{
			echo "<p>\n<font color=red><b>".ADMIN_SAFEMODE_WARNING."<b></font>";
		}
?>

	<form action="option_value_configurator.php" method=post name="option_value_configurator_form">


	<script language='JavaScript'>

		function OnClickRadioButton( numberRadio )
		{
			<?php
				$q=db_query("select variantID from ".PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE.
					" where optionID='".$optionID."'");
				$r=db_fetch_row($q); 
				$variant_count=$r[0];

				$q=db_query("select variantID from ".PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE.
					" where optionID='".$optionID."'");
				while( $r=db_fetch_row($q) )
				{
			?>
					document.option_value_configurator_form["default_radiobutton_"+<?php echo $r["variantID"]?>].checked=
							false;
			<?php
				}
			?>
			document.option_value_configurator_form["default_radiobutton_"+numberRadio].checked = true;
		}

		function OnClickCheckButton( numberCheck )
		{
		 	document.option_value_configurator_form["default_radiobutton_"+numberCheck].disabled=
				!document.option_value_configurator_form["switchOn_"+numberCheck].checked;
			document.option_value_configurator_form["price_surplus_"+numberCheck].disabled=
				!document.option_value_configurator_form["switchOn_"+numberCheck].checked;
			if ( document.option_value_configurator_form["price_surplus_"+numberCheck].disabled )
				document.option_value_configurator_form["price_surplus_"+numberCheck].value="";
			if ( document.option_value_configurator_form["default_radiobutton_"+numberCheck].disabled )
				document.option_value_configurator_form["default_radiobutton_"+numberCheck].checked=false;
		}

	</script>

	<?php
	if ( $variant_count!=0 )
	{
	?>

	<table border="0" cellspacing="0" cellpadding="4">
		<tr align="center"> 
			<td>
				<strong>
					<b><?php echo ADMIN_ON_OFF; ?></b>
				</strong>
			</td>
			<td>
				<strong>
					<b><?php echo ADMIN_BY_DEF; ?></b>
				</strong>
			</td>
			<td>
				<strong>
					<b><?php echo ADMIN_VALUE; ?><b>
				</strong>
			</td>
			<td>
				<strong>
					<b><?php echo ADMIN_PRICE_SURPLUS; ?></b>
				</strong>
			</td>
		</tr>
		<tr>
			<td bgcolor="#C3BD7C" align="center" style="padding:0px"><input type="checkbox" id="id_chbGroup" onclick="chbCol.changeState()" /></td>
			<td colspan="3" bgcolor="#C3BD7C"></td>
		</tr>


	<?php
		$values=db_query("select option_value, variantID from ".PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE.
			" where optionID='".$optionID."' order by sort_order");
		
		$q=db_query("select option_show_times, variantID from ".
				PRODUCT_OPTIONS_VALUES_TABLE." where optionID='".$optionID.
				"' AND productID='".$productID."'" );

		if ( $r=db_fetch_row($q) )
		{
			$option_show_times=$r["option_show_times"];
			$variantID_default=$r["variantID"];
		}
		else
		{
			$option_show_times=1;
			$variantID_default=null;
		}
		
		$first_row_bool = true;
		while( $value_row=db_fetch_row($values) )
		{

			$q=db_query("select price_surplus from ".
				PRODUCTS_OPTIONS_SET_TABLE." where productID='".$productID.
				"' AND optionID='".$optionID."' AND variantID='".
					$value_row["variantID"]."'" );
			if ( $r=db_fetch_row($q) )
				$price_surplus=$r["price_surplus"];
			else
				$price_surplus=null;

			$q1=db_query("select COUNT(*) from ".PRODUCTS_OPTIONS_SET_TABLE.
				" where productID='".$productID.
				"' AND optionID='".$optionID."' AND variantID='".
					$value_row["variantID"]."'" );
			$r1=db_fetch_row($q1);
			$check=($r1[0]!=0);

	?>
	
		<tr> 
			<td align="center">
				<input name="switchOn_<?php echo $value_row["variantID"]?>" 
					id="switchOn_<?php echo $value_row["variantID"]?>"
					type="checkbox"
				>
			</td>
			<td align="center" valign="top"> 
				<p> 
						<input name="default_radiobutton_<?php echo $value_row["variantID"]?>" 
							type="radio" value="<?php echo $value_row["variantID"]?>"
				<?php
					if ( (string)$variantID_default==(string)$value_row["variantID"]
							// || ($first_row_bool && $variantID_default==null) 
							)
					{
				?>
						checked
				<?php
						$first_row_bool = false;
					}
				?>
						onclick='OnClickRadioButton(<?php echo $value_row["variantID"]?>)'

						disabled=true
					>
				</p>
			</td>
			<td>
				<?php echo $value_row["option_value"]?>
			</td>
			<td>
				<input name="price_surplus_<?php echo $value_row["variantID"]?>" 
					type="text" value="<?php echo $price_surplus?>" disabled=true>
				<script language='JavaScript' type="text/javascript"><!--
					chbCol.addBox("switchOn_<?php echo $value_row["variantID"]?>", {spNum:"<?php echo $value_row["variantID"]?>", evalCode: "_Obj.onclick = function(){OnClickCheckButton(this.spNum);	this.GroupObj.checkState()}"});
		<?php
			if ($check)
			{
		?>		
					document.option_value_configurator_form["switchOn_<?php echo $value_row["variantID"]?>"].click();
		<?php
			}
		?>
				//--></script>
			</td>
		</tr>

	<?php
		}
	?>
	</table>




	<p><?php echo ADMIN_OFFER_TO_SELECT;?>
		<input name="option_show_times" type="text" value="<?php echo $option_show_times?>">
	<?php echo ADMIN_OFFER_TIMES;?></p>
	<p> 
		<INPUT name="SAVE" type=submit value="<?php echo SAVE_BUTTON?>">
		<INPUT name="CLOSE" type=submit value="<?php echo CLOSE_BUTTON?>">
	</p>

		<INPUT type=hidden name="optionID" value="<?php echo $optionID?>">
		<INPUT type=hidden name="productID" value="<?php echo $productID?>">

	<?php
	}
	else
	{
	?>
		<?php echo ADMIN_NO_VARIANTS?>...
	<?php
	}
	?>

	</form>

	</center>
</body>

</html>