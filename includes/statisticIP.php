<?php

	if (!is_dir('./statistic/'.$_SERVER['REMOTE_ADDR']))  // ---- директори€ - ip  
	{
		//---создать директорию
		if (@mkdir('./statistic/'.$_SERVER['REMOTE_ADDR'], 0755)) 
		{
   			if( !file_exists('./statistic/'.$_SERVER['REMOTE_ADDR'].'/'.date('Y-m-d').'.txt')) 
   			{
				$fp = fopen('./statistic/'.$_SERVER['REMOTE_ADDR'].'/'.date('Y-m-d').'.txt', "w"); // ("r" - считывать "w" - создавать "a" - добовл€ть к тексту), мы создаем файл
				$str = $_SERVER['REMOTE_ADDR'].' = '.date('Y-m-d').' = '.date('H:m:s Y-m-d');
				$str = $str.' = '.$_SERVER['REQUEST_URI'];
				$str = $str.' = '.$_SERVER['HTTP_USER_AGENT']."\r\n";
				$res = fwrite($fp, $str);
				fclose ($fp);
			}
		}
	  //else die('Ќе удалось создать директории...');
	}
	else   // ------------------------------------файл txt - дата
	{
			if( !file_exists('./statistic/'.$_SERVER['REMOTE_ADDR'].'/'.date('Y-m-d').'.txt')) 
   			{
				$fp = fopen('./statistic/'.$_SERVER['REMOTE_ADDR'].'/'.date('Y-m-d').'.txt', "w"); // ("r" - считывать "w" - создавать "a" - добовл€ть к тексту), мы создаем файл
				$str = $_SERVER['REMOTE_ADDR'].' = '.date('Y-m-d').' = '.date('H:m:s Y-m-d');
				$str = $str.' = '.$_SERVER['REQUEST_URI'];
				$str = $str.' = '.$_SERVER['HTTP_USER_AGENT']."\r\n";
				$res = fwrite($fp, $str);
				fclose ($fp);
			}	
			else 
			{
				$fp = fopen('./statistic/'.$_SERVER['REMOTE_ADDR'].'/'.date('Y-m-d').'.txt', "a"); // ("r" - считывать "w" - создавать "a" - добовл€ть к тексту), мы создаем файл
				$str = $_SERVER['REMOTE_ADDR'].' = '.date('Y-m-d').' = '.date('H:m:s Y-m-d');
				$str = $str.' = '.$_SERVER['REQUEST_URI'];
				$str = $str.' = '.$_SERVER['HTTP_USER_AGENT']."\r\n";
				$res = fwrite($fp, $str);
				fclose ($fp);
			}
	}
$fp = fopen('./incomeip.txt', 'a');
$str = $_SERVER['REMOTE_ADDR'].' = '.date('Y-m-d').' = '. date('H:m:s Y-m-d');
$str = $str.' = '.$_SERVER['REQUEST_URI'];
$str = $str.' = '.$_SERVER['HTTP_USER_AGENT']."\r\n";
$res = fwrite($fp, $str);
fclose($fp);
//echo $_SERVER['REMOTE_ADDR'];
if(($_SERVER['REMOTE_ADDR'] == '95.158.10.98')
 || ($_SERVER['REMOTE_ADDR'] ==  '109.254.49.19')
 || ($_SERVER['REMOTE_ADDR'] ==  '77.20.4.67') //http://wortschatz.uni-leipzig.de/findlinks/)
 || ($_SERVER['REMOTE_ADDR'] ==  '95.65.19.30') ///delivery
 || ($_SERVER['REMOTE_ADDR'] ==  '91.201.64.24') ///delivery
 || ($_SERVER['REMOTE_ADDR'] ==  '178.151.13.104') ///delivery
 )
{
	$fp = fopen('./kov.txt', 'a');
	$str = $_SERVER['REMOTE_ADDR'].' = '.date('Y-m-d').' = '. date('H:m:s Y-m-d');
	$str = $str.' = '.$_SERVER['REQUEST_URI'];
	$str = $str.' = '.$_SERVER['HTTP_USER_AGENT']."\r\n";
	$res = fwrite($fp, $str);
	fclose($fp);
	header('Location: http://www.firstchoice.co.uk/hotels/hotel-offers/hotel-only-all-inclusive-offers.html#egypt');
	exit;
}

?>