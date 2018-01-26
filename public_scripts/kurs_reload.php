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



$id_brends = $_GET['id'];
$action = $_GET['action'];
$kurs = $_GET['kurs'];
$valuta = iconv("UTF-8","windows-1251",$_GET['valuta']);
//echo 'id_brends  = '.$id_brends.' kurs = '.$kurs;

switch ($action) {
    case "1":
        UpdateKursValuta($valuta,$id_brends, $kurs);
    break;
    case "2":
       
        RecountPrice($kurs);    
    default:     
    break;
}

function RecountPrice($kurs)
{
 
    if($kurs >0)
    {
        $sql = "UPDATE ".PRODUCTS_TABLE." SET Price= CEILING(Price_UE*".$kurs.")  WHERE in_stock>0 AND producer='".$_GET['brend']."' ";
      // echo $sql;
        $q1 = mysql_query($sql);
        $mysql_nul_field  = mysql_affected_rows();
        echo ' Updated '.$mysql_nul_field.' products';
        mysql_close($connect);
    }
    else
        echo ' Update kurs and valuta first';            
        
}
function UpdateKursValuta($valuta,$id_brends, $kurs)
{
    if($valuta !== '' AND $kurs>0)
    {    
    
        $sql = "UPDATE ".PRODUCTS_BRENDS." SET kurs=".$kurs.", valuta='".$valuta."'  WHERE id=".$id_brends." ";
    //    echo $sql;
        $q1 = mysql_query($sql);
        $mysql_nul_field  = mysql_affected_rows();
        echo ' Updated '.$mysql_nul_field.' time kurs/valuta';
        mysql_close($connect);
    }
}
?>