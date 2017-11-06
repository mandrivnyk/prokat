<?php
	// payment types list

	if ( !strcmp($sub,"setting") )
	{
		$setting_groups = settingGetAllSettingGroup();
		$smarty->assign("setting_groups", $setting_groups );

		if ( isset($_POST) && count($_POST)>0 )
		{
			if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
			{
				if ( isset($_GET["settings_groupID"]) )
					Redirect( "admin.php?dpt=conf&sub=setting&settings_groupID=".(int)$_GET["settings_groupID"]."&safemode=yes" );
				else
					Redirect( "admin.php?dpt=conf&sub=setting&safemode=yes" );
			}
		}

		if ( isset($_GET["settings_groupID"]) )
		{
			$settings = settingGetSettings( $_GET["settings_groupID"] );
			$smarty->assign("settings", $settings );

			$smarty->assign("controls", settingCallHtmlFunctions($_GET["settings_groupID"]) );
			$smarty->assign("settings_groupID", $_GET["settings_groupID"] );
		}

		if ( !isset($_GET["settings_groupID"]) && count($setting_groups) > 0 )
			header("Location: admin.php?dpt=conf&sub=setting&settings_groupID=".
					$setting_groups[0]["settings_groupID"] );

	}

	// set sub-department template
	$smarty->assign("admin_sub_dpt", "conf_setting.tpl.html");
?>