<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
//	database functions :: MySQL
include_once("./core_functions/placeholders_functions.php" );

function db_connect($host,$user,$pass) //create connection
{
	$r = @mysql_connect($host,$user,$pass);
	if($r == '')
	{
		echo '<Br><p style="font-size:18px;color:black;">На сайте ведутся технические работы.<Br> Приносим наши извинения.</span>';
		exit();
	}
	
	if(preg_match('/^5\./',mysql_get_server_info($r)))db_query('SET SESSION sql_mode=0');
	
	return $r;
}

function db_select_db($name) //select database
{
	return mysql_select_db($name);
}

function db_query($s){

//static $TC;
//if(!isset($TC))$TC=0;
//$TC++;
//$timer = new Timer();
//$timer->timerStart();
	$res = array();
	$res["resource"] = mysql_query($s);
	if(!$res['resource']){
		die( db_error()." SQL query : ".$s);
	}
	$res["columns"]=array();
	$column_index = 0;
	
	while($xwer = @mysql_fetch_field($res["resource"])){
		
		$res["columns"][$xwer->name] = $column_index;
		$column_index++;
	}
//print '<strong>'.$TC.' - '.$timer->timerStop().'</strong>) '.$s.dr(debug_backtrace(),true,true).'<br />';
	return $res;
}

function db_fetch_row($q) //row fetching
{
	$res = mysql_fetch_row($q["resource"]);
	if ( $res )
	{
		foreach( $q["columns"] as $column_name => $column_index )
			$res[$column_name] = $res[$column_index];
	}
	return $res;
}

function db_insert_id($gen_name = "") //id of last inserted record
				//$gen_name is used for InterBase
{
	return mysql_insert_id();
}

function db_error() //database error message
{
	return mysql_error();
}

function db_get_all_tables()
{
	$q = db_query( "show tables" );
	$res = array();
	while( $row=db_fetch_row($q) )
		$res[] = strtolower($row[0]);
	return $res;
}

function db_get_all_ss_tables( $xmlFileName )
{
	$res = array();
	$tables = db_get_all_tables();
	$xmlNodeTableArray = GetXmlTableNodeArray( $xmlFileName );
	foreach( $xmlNodeTableArray as $xmlNodeTable )
	{
		$attr = $xmlNodeTable->GetXmlNodeAttributes();
		$existFlag = false;
		foreach( $tables as $tableName )
		{
			if ( strtolower($attr["NAME"]) == $tableName )
				$existFlag = true;
		}
		if ( $existFlag )
			$res[] = $attr["NAME"];
	}
	return $res;
}

function db_delete_table( $tableName )
{
	db_query( "drop table ".$tableName );
}

function db_delete_all_tables()
{
	$tableArray = db_get_all_tables();
	foreach( $tableArray as $tableName )
		db_query( "drop table ".$tableName );
}

function db_add_column( $tableName, $columnName, $type, $default, $nullable )
{
	if ( $nullable )
		$nullableStr = " NULL ";
	else
		$nullableStr = " NOT NULL ";
	if ( $default != null )
		db_query( "alter table ".$tableName." add column ".$columnName." $type ".$nullableStr.
						" default ".$default );
	else
		db_query( "alter table ".$tableName." add column ".$columnName." $type ".$nullableStr );
}

function db_rename_column( $tableName, $oldColumnName, $newColumnName, $type, $default, $nullable )
{
	if ( $nullable )
		$nullableStr = " NULL ";
	else
		$nullableStr = " NOT NULL ";
	if ( $default != null )
		db_query( "alter table ".$tableName." change ".$oldColumnName." ".
				$newColumnName." ".$type." ".$nullableStr." default ".$default );
	else
		db_query( "alter table ".$tableName." change ".$oldColumnName." ".
				$newColumnName." ".$type." ".$nullableStr );
}

function db_delete_column( $tableName, $columnName )
{
	db_query( "alter table ".$tableName." drop column ".$columnName );
}

/**
 * return number of rows in result after sql-query
 *
 * @param resource $_result
 * @return integer
 */
function db_num_rows($_result){

	return mysql_num_rows($_result);
}

/**
 * retrieve columns information for table
 *
 * @param string $_TableName
 * @return array
 */
function db_getColumns($_TableName){
	
	$Columns = array();
	$sql = '
		SHOW COLUMNS FROM `'.$_TableName.'`
	';
	$Result = db_query($sql);
	if(!db_num_rows($Result["resource"]))return $Columns;
	while ($_Row = db_fetch_row($Result)){
		
		$Columns[strtolower($_Row['Field'])] = $_Row;
	}
	return $Columns;
}

function db_phquery(){
	
	$args = func_get_args();
	$tmpl = array_shift($args);
	$sql = sql_placeholder_ex($tmpl, $args, $error);
	if ($sql === false) $sql = PLACEHOLDER_ERROR_PREFIX.$error;
	return db_query($sql);
}

function db_fetch_assoc($Result){
	
	return mysql_fetch_assoc($Result['resource']);
}
?>