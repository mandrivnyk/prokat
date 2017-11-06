<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
/**
 * Works only with virtualModule-based objects
 */

if(strcmp($sub, 'smsmail'))return 0;

$_POST = xStripSlashesGPC($_POST);

include('./classes/class.virtual.smsmail.php');

$xREQUEST_URI = set_query('&install=&setting_up=&uninstall=&enableSMSNotify=&msg=&disableSMSNotify=');

$moduleFiles = GetFilesInDirectory( "./modules/smsmail", "php" );

foreach( $moduleFiles as $fileName )
	include( $fileName );

$ModuleObjects = modGetModuleObjects($moduleFiles);
$MSGInfo = array('status'=>0,'message'=>'');

if(isset($_GET['msg'])){
	
	switch($_GET['msg']){
		case 1:
			$MSGInfo['status'] = 1;
			$MSGInfo['message'] = MSG_INFORMATION_SAVED;
			break;
		case 2:
			if(!isset($_SESSION['ERROR_MSG']))break;
			$MSGInfo['status'] = 2;
			$MSGInfo['message'] = $_SESSION['ERROR_MSG']['error'];
			$PeriodErrorHTML = $_SESSION['ERROR_MSG']['html'];
			unset($_SESSION['ERROR_MSG']);
			break;
	}
}

if(isset($_POST['SAVE_NOTIFY_SETTINGS'])){
	
	if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
	{
		Redirect( set_query('&safemode=yes', $xREQUEST_URI) );
	}

	settingCallHtmlFunction(  'CONF_SMSNOTIFY_SMSSENDER_CONFIG_ID' );
	$Ret = settingCallHtmlFunction(  'CONF_SMSNOTIFY_SEND_PERIOD' );
	settingCallHtmlFunction(  'CONF_SMSNOTIFY_PHONES' );	
	if(is_array($Ret)){
		
		session_register('ERROR_MSG');
		$_SESSION['ERROR_MSG'] = $Ret;
	}
	Redirect( set_query('&msg='.(is_array($Ret)?'2':'1'), $xREQUEST_URI) );
}
if(isset($_GET['disableSMSNotify'])){
	
	if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
	{
		Redirect( set_query('&safemode=yes', $xREQUEST_URI) );
	}

	$NotifyObject = new SMSNotify();
	if($NotifyObject->is_installed()){
	
		_setSettingOptionValue('CONF_SMSNOTIFY_ENABLED',0);
	}
	Redirect( $xREQUEST_URI );
}
if(isset($_GET['enableSMSNotify'])){
	
	if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
	{
		Redirect( set_query('&safemode=yes', $xREQUEST_URI) );
	}

	$NotifyObject = new SMSNotify();
	if(!$NotifyObject->is_installed()){
		
		$NotifyObject->install();
	}
	_setSettingOptionValue('CONF_SMSNOTIFY_ENABLED',1);
	Redirect( $xREQUEST_URI );
}
if ( isset($_GET["install"]) )
{
	if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
	{
		Redirect( set_query('&safemode=yes', $xREQUEST_URI) );
	}

	$ModuleObjects[ (int)$_GET["install"] ]->install();
	Redirect( $xREQUEST_URI );
}
if ( isset($_GET["uninstall"]) )
{
	if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
	{
		Redirect( set_query('&safemode=yes', $xREQUEST_URI) );
	}

	
	$ModuleConfig = modGetModuleConfig($_GET["uninstall"]);
	if($ModuleConfig['ModuleClassName']){
		
		modUninstallModuleConfig($_GET["uninstall"]);
	}
	Redirect( $xREQUEST_URI );
}

if ( isset($_GET["setting_up"]) ){
	
	$xREQUEST_URI = set_query('&setting_up='.$_GET["setting_up"], $xREQUEST_URI);
	
	if (isset($_POST) && count($_POST)>0)
	{
		if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
		{
			Redirect( "admin.php?dpt=modules&sub=payment&setting_up=".$_GET["setting_up"]."&safemode=yes" );
		}
	}

	$ModuleObject = null;
	
	$ModuleConfig = modGetModuleConfig($_GET["setting_up"]);
	
	if($ModuleConfig['ModuleClassName']){
		
		eval('$ModuleObject = new '.$ModuleConfig['ModuleClassName'].'('.$_GET["setting_up"].');');
	}

	$constants = $ModuleObject->settings_list();
	$settings = array();
	$controls = array();

	foreach( $constants as $constant )
	{
		$settings[]	= settingGetSetting( $constant );
		$controls[]	= settingCallHtmlFunction(  $constant );
		$smarty->assign("settings", $settings );
		$smarty->assign("controls", $controls );
	}

//	$ModuleObject->sendSMS('Hello', '79055132663');
	$smarty->assign("ModuleObject", $ModuleObject );
	$smarty->assign("constant_managment", 1);
}else{

	$ModuleConfigs = array();
	$NotifyObject = new SMSNotify();
	
	$_TC = count($ModuleObjects)-1;
	for (; $_TC>=0; $_TC--){
	
		$_tMConfigs = modGetModuleConfigs(get_class($ModuleObjects[$_TC]));
		if(!count($_tMConfigs))continue;
		$ModuleConfigs = array_merge($ModuleConfigs, $_tMConfigs);
	}
	
	$smarty->assign('ModuleObjects', $ModuleObjects);
	$smarty->assign('ModuleConfigs', $ModuleConfigs);
	$smarty->assign('SMSNotifyEnabled', $NotifyObject->is_installed()&&CONF_SMSNOTIFY_ENABLED);
	if($NotifyObject->is_installed()){
		
		$smarty->assign('ConfigIDHTML', settingCallHtmlFunction(  'CONF_SMSNOTIFY_SMSSENDER_CONFIG_ID' ));
		$smarty->assign('SendPeriodHTML', isset($PeriodErrorHTML)?$PeriodErrorHTML:settingCallHtmlFunction(  'CONF_SMSNOTIFY_SEND_PERIOD' ));
		$smarty->assign('PhoneNumbersHTML', settingCallHtmlFunction(  'CONF_SMSNOTIFY_PHONES' ));
	}
}

$smarty->assign('xREQUEST_URI', $xREQUEST_URI);
$smarty->assign( "admin_sub_dpt", "custord_smsmail.tpl.html");
$smarty->assign('MSGInfo', $MSGInfo);
?>