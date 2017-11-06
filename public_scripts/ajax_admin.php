<?php


define('DBMS', 'mysql');					// database system			
define('DB_HOST', 'localhost');		// database host			
define('DB_USER', 'root');	// username					
define('DB_PASS', 'kbyerc');	// password					
define('DB_NAME', 'tatonka');		// database name			
define('ADMIN_LOGIN', 'admin');			// administrator's login


include('../cfg/tables.inc.php');


//connect to the database
$res = 'Ошибка: Невозможно соединиться с базой данных. Попробуйте позже.'.mysql_error();
$connect = mysql_connect(DB_HOST, DB_USER, DB_PASS) or die($res);

$res1 = 'Ошибка: Невозможно выбрать базу данных. Попробуйте позже.';

if($connect)
	mysql_select_db(DB_NAME) or die($res1);




$res = '';

$status = iconv("UTF-8","windows-1251",$_GET['status']);
$id_cat = iconv("UTF-8","windows-1251",$_GET['id_cat']);

$sql = "UPDATE ".CATEGORIES_TABLE." SET enable=".$status."  WHERE categoryID=".$id_cat." || parent=".$id_cat;
	$q1 = mysql_query($sql);
	$mysql_nul_field  = mysql_affected_rows();
mysql_close($connect);
	//$row = mysql_fetch_row($q1);
//echo '$id_cat'.$id_cat;
if($q1 == '')
{
	echo '';
}
elseif ($status == 1)
{
	echo '<img border="0" onclick="enable('.$id_cat.', 0 );" title="видно" src="/images/backend/enable-yes.jpg" id="img_'.$id_cat.'">';
	
}
elseif ($status == 0)
{
	echo '<img border="0" onclick="enable('.$id_cat.', 1 );" title="не видно" src="/images/backend/enable-no.jpg" id="img_'.$id_cat.'">';
}
if($mysql_nul_field >1)
	echo '<script language="javascript">window.location.href=""</script>';

?>