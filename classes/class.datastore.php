<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
ConstantFabric::generateConstant('DATASTORE_TYPE_POST');
ConstantFabric::generateConstant('DATASTORE_TYPE_SESSION');

class DataStore{
	
	var $StorageType = DATASTORE_TYPE_SESSION;
	
	function  DataStore( $_StorageType = null){
		
		if(isset($_StorageType)) $this->StorageType = $_StorageType;
		
		if(!session_is_registered('_xDATA_STORAGE')){
		
			session_register('_xDATA_STORAGE');
			$_SESSION['_xDATA_STORAGE'] = array();
		}
	}
	
	function pushData($_Tag, $_Data, $_StorageType = null){
		
		if(!isset($_StorageType)) $_StorageType = $this->StorageType;
		switch ($_StorageType){
			case DATASTORE_TYPE_SESSION:
				if(!isset($_SESSION['_xDATA_STORAGE'][$_Tag])){
					
					$_SESSION['_xDATA_STORAGE'][$_Tag] = array();
				}
				$_SESSION['_xDATA_STORAGE'][$_Tag][] = $_Data;
				break;
		}
	}
	
	function popData($_Tag, $_StorageType = null){
		
		if(!isset($_StorageType)) $_StorageType = $this->StorageType;
		switch ($_StorageType){
			case DATASTORE_TYPE_SESSION:
				if(!isset($_SESSION['_xDATA_STORAGE'][$_Tag])){
					
					return null;
				}
				if(!count($_SESSION['_xDATA_STORAGE'][$_Tag])){
					
					return null;
				}
				$Return = &$_SESSION['_xDATA_STORAGE'][$_Tag][count($_SESSION['_xDATA_STORAGE'][$_Tag])-1];
				unset($_SESSION['_xDATA_STORAGE'][$_Tag][count($_SESSION['_xDATA_STORAGE'][$_Tag])-1]);
				return $Return;
				break;
		}
	}
}