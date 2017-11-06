<?php
$from = iconv("UTF-8","windows-1251",$_GET['from']);
$shag = iconv("UTF-8","windows-1251",$_GET['shag']);
$to = $from + $shag;
echo 'tut';
//exit();
cacheUpdate($from, $to, $shag);
function cacheUpdate($from, $to, $shag)
{
	$info = '';
	$i=0;
	$info = '<br>'.$info.date('h:i:s') . " START<br>";
	$fp = fopen('./cache-list.txt', "r"); // Открываем файл в режиме чтения
	if ($fp)
	{
		while (!feof($fp))
		{  
			$url = trim(fgets($fp, 4096));
			if(($i>=$from) AND ($i<$to))
			{
				$info = $info.'<br> i = '.$i.' from = '.$from.' to= '.$to.'<br> url ='.$url;   	
				//$url = trim(fgets($fp, 4096));
				$url_http = $url;
				$url = substr($url,21);
				$url = str_replace('/', '--',  $url); 
				$info = $info.'<br>URL:'.$url.'<br>';
				if (!file_exists('./cache/'.$url.'.cache'))   //--проверка чтоб не тянуть страницу зря
				{	
					$info = $info.'$url_http == '.$url_http.'<br>';
					$homepage = file_get_contents($url_http);
				/* if (file_exists('./cache'.substr($url,24).'.cache'))   //--проверка чтоб копировать только реально сущ. страницу 
				{
					if ($homepage !== false)
					{
						//==========Копируем по фтп файл кеша в Prokat.ho.com.ua =======
						  $host = "s1.ho.com.ua";
						  $connect = ftp_connect($host);
						  if(!$connect)
						  {
						    $info = $info.'<br><b>ОШИБКА  СОЕДИНЕНИЯ</b>';
						    exit;
						  }
						  else
						  {
						    $info = $info.'<br>Соединение установлено';  
						  }
						 $user = "prokat";
						  $password = "easier21068410062012";
						  $result = ftp_login($connect, $user, $password);
						  ftp_pasv ($connect, true) ;
						  $info = $info.'<br>URL:'.$url.'<br>';
				          $info =  $info.'/htdocs/cacher/'.$url.'.cache<br>';
				          $info =  $info.'/var/www/tatonka.in.ua/public_html/cache/'.$url.'.cache<br>';
						  if (ftp_put($connect, '/htdocs/cacher/'.$url.'.cache', '/var/www/tatonka.in.ua/public_html/cache/'.$url.'.cache', FTP_ASCII)) {
						 $info = $info.'<br><b>$file успешно загружен на сервер</b><BR>';
						} else {
						 $info = $info.'<br><b>Не удалось загрузить $file на сервер'.'/htdocs/cacher/'.$url.'.cache</b><BR>';
						}
						ftp_close($connect);
					}
					else 
						$info = $info.'<br><b>file_get_contents == FALSE </b><br>';
				} */
			
				}
				else 
					$info = $info.'<br><b>File already exists, so no copy</b>';
				//	$info = $info.'<br>НЕТ созданного файл кеша, поэтому нет копированя';
				//=========================================================
				/*
				$y=1;  
				if ($homepage !== false)
				{
					while (!file_exists('./cache'.substr($url,24).'.cache'))   
					{
						usleep(10000000);
						//$homepage = @file_get_contents($url);  
						$info = $info.'<br>povtor '.$y;
						$y++;
						if($y==2)
							break;
					}
				}
				else 
					$info = $info.'<span style=`color:red;`>file_get_contents == FALSE </span><br>';*/
				/*if($i%50==0)
				        	{        		
				        		//echo 'kratno 50<br>';
				        		$info = '<br>'.$info.date('h:i:s') . " PAUSE START<br>";
				        		usleep(3000);
				        	  //usleep(30000000);
				        		$info = $info.date('h:i:s') . "PAUSE END";
				        	}
				if($i%9==0)
				        	{        		
				        		//echo 'kratno 9<br>';
				        		$info = '<br>'.$info.date('h:i:s') . " PAUSE START<br>";
				        		usleep(300);
				        		$info = $info.date('h:i:s') . "PAUSE END";
				        	}*/
			}
			$i++;
			if($i>=$to)
			{
				$info = $info.'<br>i='.$i.' $tp='.$to.' BREAK<br>';
				break;
			}			
				//echo $mytext."<br />";
		}
	}
	else echo "Ошибка при открытии файла";
	fclose($fp);
	//echo 'ttt';
	$info = $info.'$i='.$i.'<br>';
	$info = $info.date('h:i:s') . " END<br>";
	echo '<script language="javascript"> var cache_info_div = document.getElementById("cache_info");  cache_info_div.innerHTML ="'.$info.'";</script>';
	//echo '<script language="javascript"> var cache_info_div = document.getElementById("cache_info"); var all_cache_info = cache_info_div.innerHTML; cache_info_div.innerHTML ="'.$info.'"+all_cache_info;</script>';
	if($to<37100)  //-----тут указывается количество файлов из cache-list.txt
	 echo '<script language="javascript"> enable('.$to.','.$shag.');</script>';
	//var cache_info_div = document.getElementById(cache_info); var all_cache_info = cache_info_div.innerHTML; cache_info_div.innerHTML =all_cache_info+"'.$info.'";
}
?>
