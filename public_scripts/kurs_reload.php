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
if(isset($_GET['warranty']))
    $warranty = $_GET['warranty'];

$valuta = iconv("UTF-8","windows-1251",$_GET['valuta']);
//echo urldecode($_GET['brend']);
//echo ' mb_detect_encoding = '.mb_detect_encoding($_GET['brend']); 
//echo '<pre>';
//    print_r($_GET);
//echo '</pre>';
if(isset($_GET['brend']))
    $brend = @iconv("UTF-8","windows-1251", $_GET['brend']);
//$brend = iconv("UTF-8", "windows-1251", $_GET['brend']);
//echo ' mb_detect_encoding = '.mb_detect_encoding($brend);
//$brend = GetBrendName( $id_brends); 

//echo ' mb_detect_encoding = '.mb_detect_encoding($_GET['brend']);
//$brend = $_GET['brend'];
//echo ' $brend  = '.$brend.' kurs = '.$kurs;

switch ($action) {
    case "1":
        UpdateKursValuta($valuta,$id_brends, $kurs,$warranty, $connect);
    break;
    case "2":
       
        RecountPrice($kurs,$brend, $id_brends, $connect);    
    default:     
    break;
}

function GetBrendName( $id_brends)
{

    $q ="SELECT name FROM ".PRODUCTS_BRENDS."   where id=".$id_brends."";
    $q1 = mysql_query($q);
    $row = mysql_fetch_row($q1);
		//$result = $row["name"];
   // $row = iconv("UTF-8","windows-1251",$row[0]);
	   echo '<pre>';
		print_r($row);
		echo '</pre>';
	return $row[0];
     
}


function RecountPrice($kurs, $brend, $id_brends,$connect) 
{ 
 
    if($kurs >0)
    {
      //  if($id_brends  == 77)
       //     $sql = "UPDATE ".PRODUCTS_TABLE." SET Price= CEILING(Price_UE*".$kurs.")  WHERE in_stock>0 AND producer='Ижевск' ";
       // else 
      $sql = "UPDATE ".PRODUCTS_TABLE." SET Price= CEILING(Price_UE*".$kurs.")  WHERE in_stock>0 AND producer='".$brend."' ";
     //echo $sql;
        $q1 = mysql_query($sql);
        echo mysql_error();
        $mysql_nul_field  = mysql_affected_rows();
        echo ' Updated '.$mysql_nul_field.' products';
        mysql_close($connect);
    }
    else
        echo ' Update kurs and valuta first';            
         
}
function UpdateKursValuta($valuta,$id_brends, $kurs,$warranty, $connect)
{
    if($valuta !== '' AND $kurs>0)
    {    
    echo $warranty;
        $sql = "UPDATE ".PRODUCTS_BRENDS." SET kurs=".$kurs.", valuta='".$valuta."', warranty='".$warranty."'   WHERE id=".$id_brends." ";
     //echo $sql; 
        $q1 = mysql_query($sql);
        $mysql_nul_field  = mysql_affected_rows();
        echo ' Updated '.$mysql_nul_field.' time kurs/valuta';
        mysql_close($connect);
    }
}
?>