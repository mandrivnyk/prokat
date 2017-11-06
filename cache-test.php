<?

//$from = iconv("UTF-8","windows-1251",$_GET['from']);
//$shag = iconv("UTF-8","windows-1251",$_GET['shag']);
$url = iconv("UTF-8","windows-1251",urldecode($_GET['vurl']));
//$to = $from + $shag;
//echo 'tut';
//exit();
if (!file_exists('./cache'.$url.'.cache'))
{	$homepage = @file_get_contents('http://zooland.in.ua'.$url);

	
	 $host = "s1.ho.com.ua";
	  $connect = ftp_connect($host);
	  if(!$connect)
	  {
	    echo("Ошибка соединения");
	    exit;
	  }
	  else
	  {
	    echo("Соединение установлено");  
	  }
	 $user = "prokat";
	  $password = "`ghjrfn[jrjv.f`";
	  $result = ftp_login($connect, $user, $password);
	  ftp_pasv ($connect, true) ;
	  
	   
	  
	  if (ftp_put($connect, '/htdocs/cache'.$url.'.cache', '/sata1/home/users/mandrivnu1/www/www.zooland.in.ua/cache/'.$url.'.cache', FTP_ASCII)) {
	 echo "$file успешно загружен на сервер\n";
	} else {
	 echo "Не удалось загрузить $file на сервер\n";
	}
	ftp_close($connect);
	
}

?>