<?

//----создание списка станиц для формирования кеша=================================				
$dir = "./cache";
$i=0;
$fp = fopen('./cache-list.txt', 'w');
// Open a known directory, and proceed to read its contents
if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) 
        {
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
        	//echo $url.'<br>';
        	if(strlen($url)<=180)
        	{
        	
        		if(substr_count($url,'search_price_from')==0)
        			$res = fwrite($fp, $url); 
        	}
        	//$res = fwrite($fp, $url);          
        	//$i++;
        }
        fclose($fp);
        closedir($dh);
    }
}				
echo 'GOTOVO';



?>