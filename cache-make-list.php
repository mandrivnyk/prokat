<?php
define('DBMS', 'mysql');					// database system			
define('DB_HOST', 'localhost');		// database host			
define('DB_USER', 'root');	// username					
define('DB_PASS', 'kbyerc');	// password					
define('DB_NAME', 'tatonka');		// database name			
define('ADMIN_LOGIN', 'admin');			// administrator's login		
include('./cfg/tables.inc.php');


//connect to the database
$res = 'ќшибка: Ќевозможно соединитьс€ с базой данных. ѕопробуйте позже.'.mysql_error();
$connect = mysql_connect(DB_HOST, DB_USER, DB_PASS) or die($res);

$res1 = 'ќшибка: Ќевозможно выбрать базу данных. ѕопробуйте позже.';

if($connect)
	mysql_select_db(DB_NAME) or die($res1);

//----создание списка станиц дл€ формировани€ кеша=================================				
$dir = "./cache";
$i=0;
$fp = fopen('./cache-list.txt', 'w');
//ListFromDir($dir,$i, $fp );
ListFromURL($fp );


function catGetCategoryProductCountLocal( $categoryID, $_countEnabledProducts = false )

{

	$categoryID = (int)$categoryID;

	if (!$categoryID) return 0;



	$res = 0;

	$sql = "

		SELECT count(*) FROM ".PRODUCTS_TABLE."

		WHERE categoryID=$categoryID".($_countEnabledProducts?" AND enabled<>0":"")."

	";

	$q = mysql_query($sql);

	$t = mysql_fetch_array($q);

	$res += $t[0];

	if($_countEnabledProducts)

		$sql = "

			SELECT COUNT(*) FROM ".PRODUCTS_TABLE." AS prot

			LEFT JOIN ".CATEGORIY_PRODUCT_TABLE." AS catprot

			ON prot.productID=catprot.productID

			WHERE catprot.categoryID='{$categoryID}' AND prot.enabled<>0

		";

	else

		$sql = "

			select count(*) from ".CATEGORIY_PRODUCT_TABLE.

			" where categoryID=$categoryID

		";

	$q1 = mysql_query($sql);

	$row = mysql_fetch_array($q1);

	$res += $row[0];

	return $res;

}




function ListFromURL($fp)
{
	
	//-------------------- собираем все url_name категорий -----------------------
	//$i= 0;	
	$q = mysql_query("SELECT categoryID, url_name FROM ".CATEGORIES_TABLE." where categoryID<>0  AND enable =1 AND url_name <> '' ORDER BY url_name") or die (db_error());
	while ($row = mysql_fetch_array($q))
	{
		/*echo '<pre>';
		print_r($row);
		echo '</pre>';*/
		$url= 'http://tatonka.in.ua/'.$row['url_name'].chr(10);
		$url.= 'http://tatonka.in.ua/index.php?categoryID='.$row['categoryID'].chr(10);
		//echo $url.'<br>';
		//$cats[$i++] = 'http://tatonka.in.ua/'.$row['url_name'];
		if(strlen($url)<=180)
        			$res = fwrite($fp, $url); 
		
	}
	
	
	
	//-------------------- собираем все url_name товаров и ссылки вида http://tatonka.in.ua/index.php?productID=607-----------------------
	//$y= 0;
	$q = mysql_query("SELECT productID, url_name FROM ".PRODUCTS_TABLE." where productID<>0  AND enabled =1  AND url_name <> '' ORDER BY url_name") or die (db_error());
	while ($row = mysql_fetch_array($q))
	{
		/*echo '<pre>';
		print_r($row);
		echo '</pre>';*/
		$url= 'http://tatonka.in.ua/'.$row['url_name'].chr(10);
		$url .= 'http://tatonka.in.ua/index.php?productID='.$row['productID'].chr(10);
		
		//http://tatonka.in.ua/index.php?productID=607
		//echo $url.'<br>';
		//$cats[$y++] = 'http://tatonka.in.ua/'.$row['url_name'];
		if(strlen($url)<=180)
        			$res = fwrite($fp, $url); 
		
	}
	
	//-------------------- собираем все show_all-yes дл€ категорий -----------------------
	//$i= 0;	
	$q = mysql_query("SELECT categoryID FROM ".CATEGORIES_TABLE." where categoryID<>0  AND enable =1 AND url_name <> '' ORDER BY categoryID") or die (db_error());
	while ($row = mysql_fetch_array($q))
	{
		/*echo '<pre>';
		print_r($row);
		echo '</pre>';*/
		
		//-------------------- собираем все show_all-yes дл€ категорий  вида http://tatonka.in.ua/categoryID/253/show_all/yes-----------------------
		$url= 'http://tatonka.in.ua/categoryID/'.$row['categoryID'].'/show_all/yes'.chr(10);
		if(strlen($url)<=180)
        			$res = fwrite($fp, $url); 
        			
        //-------------------- собираем все show_all-yes дл€ категорий  c сортировкой name -----------------------
		$url= 'http://tatonka.in.ua/categoryID/'.$row['categoryID'].'/sort/name/direction/DESC/show_all/yes'.chr(10);
		$url .= 'http://tatonka.in.ua/categoryID/'.$row['categoryID'].'/sort/name/direction/ASC/show_all/yes'.chr(10);
		if(strlen($url)<=180)
        			$res = fwrite($fp, $url); 	
        			
        					
        //-------------------- собираем все show_all-yes дл€ категорий  c сортировкой Price -----------------------
		$url= 'http://tatonka.in.ua/categoryID/'.$row['categoryID'].'/sort/Price/direction/DESC/show_all/yes'.chr(10);
		$url .= 'http://tatonka.in.ua/categoryID/'.$row['categoryID'].'/sort/Price/direction/ASC/show_all/yes'.chr(10);
		if(strlen($url)<=180)
        			$res = fwrite($fp, $url);         			
        		
        					
        //-------------------- собираем все show_all-yes дл€ категорий  c сортировкой customers_rating -----------------------
		$url= 'http://tatonka.in.ua/categoryID/'.$row['categoryID'].'/sort/customers_rating/direction/DESC/show_all/yes'.chr(10);
		$url .= 'http://tatonka.in.ua/categoryID/'.$row['categoryID'].'/sort/customers_rating/direction/ASC/show_all/yes'.chr(10);
		if(strlen($url)<=180)
        			$res = fwrite($fp, $url); 
        			
        	
        			
        					
        	
         //-------------------- собираем все ссылки вида: -----------------------									
        //http://tatonka.in.ua/categoryID/252/sort/name/direction/DESC/offset/0 - http://tatonka.in.ua/categoryID/252/sort/name/direction/DESC/offset/210
        	
        $numProds = catGetCategoryProductCountLocal($row['categoryID']); //------ получаем кол-во продуктов в категории дл€ подсчета кол-ва офсетов 
		//echo '<br>'.$numProds;
		$celoe = ceil($numProds/15);
		//echo '<br>'.$celoe;
       //exit();
       if($numProds !== 0)
       {
       		$i = 0;
	        for($y=0;$y<$celoe;$y++) 
	        {
	        	$url = 'http://tatonka.in.ua/categoryID/'.$row['categoryID'].'/sort/name/direction/DESC/offset/'.$i.chr(10);
				$url .= 'http://tatonka.in.ua/categoryID/'.$row['categoryID'].'/sort/name/direction/ASC/offset/'.$i.chr(10);
	        	if(strlen($url)<=180)
	        			$res = fwrite($fp, $url); 
	        	
	        	
	        	$url = 'http://tatonka.in.ua/categoryID/'.$row['categoryID'].'/sort/Price/direction/DESC/offset/'.$i.chr(10);
				$url .= 'http://tatonka.in.ua/categoryID/'.$row['categoryID'].'/sort/Price/direction/ASC/offset/'.$i.chr(10);
				if(strlen($url)<=180)
	        			$res = fwrite($fp, $url); 
	        	//		echo $url.'<br>';
	        	
	        	
	        	
	        	$url = 'http://tatonka.in.ua/categoryID/'.$row['categoryID'].'/sort/customers_rating/direction/DESC/offset/'.$i.chr(10);
				$url .= 'http://tatonka.in.ua/categoryID/'.$row['categoryID'].'/sort/customers_rating/direction/ASC/offset/'.$i.chr(10);
				if(strlen($url)<=180)
	        			$res = fwrite($fp, $url); 
	        	
	        			
				//-------------------- собираем все ссылки вида: -----------------------										        			
	        	//http://tatonka.in.ua/index.php?categoryID=252&offset=45&sort=name&direction=DESC	
	        	
	        	$url = 'http://tatonka.in.ua/index.php?categoryID='.$row['categoryID'].'&offset='.$i.'&sort=name&direction=DESC'.chr(10);
	        	$url .= 'http://tatonka.in.ua/index.php?categoryID='.$row['categoryID'].'&offset='.$i.'&sort=name&direction=ASC'.chr(10);				
				if(strlen($url)<=180)
	        			$res = fwrite($fp, $url); 	
	        			
	        	$url = 'http://tatonka.in.ua/index.php?categoryID='.$row['categoryID'].'&offset='.$i.'&sort=Price&direction=DESC'.chr(10);
	        	$url .= 'http://tatonka.in.ua/index.php?categoryID='.$row['categoryID'].'&offset='.$i.'&sort=Price&direction=ASC'.chr(10);				
				if(strlen($url)<=180)
	        			$res = fwrite($fp, $url); 	
	        			
	        	$url = 'http://tatonka.in.ua/index.php?categoryID='.$row['categoryID'].'&offset='.$i.'&sort=customers_rating&direction=DESC'.chr(10);
	        	if(strlen($url)<=180)
	        			$res = fwrite($fp, $url); 
	        	$url = 'http://tatonka.in.ua/index.php?categoryID='.$row['categoryID'].'&offset='.$i.'&sort=customers_rating&direction=ASC'.chr(10);				
				if(strlen($url)<=180)
	        			$res = fwrite($fp, $url); 	
	        			
	        	//-------------------- собираем все ссылки вида: -----------------------									
	        	//http://tatonka.in.ua/categoryID/279/offset/0		http://tatonka.in.ua/categoryID/279/offset/210		
	        	$url = 'http://tatonka.in.ua/categoryID/'.$row['categoryID'].'/offset/'.$i.chr(10);
			
				if(strlen($url)<=180)
	        			$res = fwrite($fp, $url); 
	        	//		echo $url.'<br>';
	        	
					/*echo 'http://tatonka.in.ua/categoryID/'.$row['categoryID'].'/sort/name/direction/DESC/offset/'.$i.chr(10).'<br>';
	        		echo 'http://tatonka.in.ua/categoryID/'.$row['categoryID'].'/sort/name/direction/ASC/offset/'.$i.chr(10).'<br>';
	        		echo 'http://tatonka.in.ua/categoryID/'.$row['categoryID'].'/sort/Price/direction/DESC/offset/'.$i.chr(10).'<br>';
	        		echo 'http://tatonka.in.ua/categoryID/'.$row['categoryID'].'/sort/Price/direction/ASC/offset/'.$i.chr(10).'<br>';*/
				
	        	
	        	$i+=15;	
	        }	
       }
           //-------------------- собираем все ссылки вида: -----------------------									
        //http://tatonka.in.ua/categoryID/252/sort/name/direction/ASC/offset/0 - http://tatonka.in.ua/categoryID/252/sort/name/direction/ASC/offset/210
        
/*
        for($i=0;$i<=210;$i+=15)
        {
        	$url = 'http://tatonka.in.ua/categoryID/'.$row['categoryID'].'/sort/name/direction/ASC/offset/'.$i.chr(10);
			
        		echo 'http://tatonka.in.ua/categoryID/'.$row['categoryID'].'/sort/name/direction/ASC/offset/'.$i.chr(10).'<br>';
			
        		
        	if(strlen($url)<=180)
        			$res = fwrite($fp, $url);  
        	
        }	*/
       //echo '<br>'.$celoe;
		//exit();
	}
	
	echo 'GOTOVO';
	 fclose($fp);
	
	
	
// Open a known directory, and proceed to read its contents
			
//echo 'GOTOVO';
}


function ListFromDir($dir,$i, $fp )
{
// Open a known directory, and proceed to read its contents
if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) 
        {
        	//if(($file !== '.') OR ($file !== '..'))
       	//if(($file !== '.') OR ($file !== '..'))
        	$file	=  preg_replace('/.cache/', '', $file);
			//$file = strtok($file, '.cache');
         //echo 'http://www.tatonka.in.ua/'.$file.'<br>';
        // $homepage = file_get_contents('http://www.tatonka.in.ua/'.$file);
        	//echo "filename: $file <br>"; 
        	$url = 'http://tatonka.in.ua/'.$file.chr(10);
        	//$request_uri = str_replace('/', '--', $request_uri); //--- замена во всей строке
        	$url = str_replace('--', '/', $url);
        	$url = str_replace('php_prod', 'php?prod', $url);
        	$url = str_replace('php_cat', 'php?cat', $url);
        	$url = str_replace('.cache', '', $url);
        //	echo $url.'<br>';
        	if(strlen($url)<=180)
        			$res = fwrite($fp, $url); 
        	//$res = fwrite($fp, $url);    
        }
        fclose($fp);
        closedir($dh);
    }
}				
echo 'GOTOVO';
}


?>