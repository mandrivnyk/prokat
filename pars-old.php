<?
include_once('js/simplehtmldom_1_5/simple_html_dom.php');


echo "PARCING<br>";
get_BIG_GOOGLE_imgs_txt_file_list();
/*
$homepage = @file_get_contents($url_http);
echo strpos($homepage, 'id="ic1"').'<br>';
*/



// Создаем объект DOM на основе кода, полученного по ссылке


//$html = file_get_html($url_http);


// Find all images

//caft_akc.txt
/*$url_http = 'http://www.google.com.ua/search?num=10&hl=ru&site=imghp&tbm=isch&source=hp&biw=1280&bih=848&q=1900263+BASIC+2-PACK+JR-110+116+1430+-+BRIGHT+RED&tbs=isz:m&tbm=isch&source=lnt&sa=X#q=1900263+BASIC+2-PACK+JR-110+116+1430+-+BRIGHT+RED&num=10&hl=ru&sa=X&site=imghp&tbs=isz:m&tbm=isch&bav=on.2,or.r_gc.r_pw.r_qf.&fp=483e4923551677a2&biw=1280&bih=439';
echo $url_http.'<br>';
$html = file_get_html($url_http);*/
//$ret = $html->find('a[class=rg_li]'); 
//echo $html->find('a[class=rg_hl uh_hl]');
/*echo '<pre>';
	       print_r($html->find('a[class=rg_hl uh_hl]'));
	    echo '</pre>';*/


function get_BIG_GOOGLE_imgs_txt_file_list()
{
	
	$iteration = 1;
	//get_unique_in_txt_file('./images/pars/caft_akc.txt');
	//exit();
	$fp = fopen('./images/pars/caft_akc.txt', "r"); // Открываем файл в режиме чтения

		if ($fp)
		{   
			while (!feof($fp))
			{  
				echo '$iteration= '.$iteration,'<br>';	
				//==================== PAUSE==============================
				if($iteration%5==0)
				        	{        		
				        		//echo 'kratno 50<br>';
				        		$info = '<br>'.$info.date('h:i:s') . " PAUSE START<br>";
				        		usleep(5000000);
				        		$info = $info.date('h:i:s') . "PAUSE END";
					       		
				        	}
				//========================================================
				$num_row= 1;
				$url = trim(fgets($fp, 4096));
				echo $url.'<br>';
				//$url = str_replace(' ', '+',  $url);
				//echo $url.'<br>';
				$url = preg_replace('/[^A-Za-zа-яА-ЯъьЬЪЮёЁїЇіІ0-9-+]+/', '+', $url);
				$dir_name = preg_replace('/[^A-Za-zа-яА-ЯъьЬЪЮёЁїЇіІ0-9-]+/', '_', $url);
				echo $dir_name.'<br>';
				$iteration++;
				if(file_exists('./images/pars/'.$dir_name))
					continue;
				
				echo $url.'<br>';
				
				
				$url_http = 'http://www.google.com.ua/search?num=10&hl=ru&site=imghp&tbm=isch&source=hp&biw=1280&bih=848&q='.$url.'&tbs=isz:m&tbm=isch&source=lnt&sa=X';
				echo $url_http.'<br>';
				$html = file_get_html($url_http);
				
				foreach($html->find('a') as $element)
				{
					
				    if($is_a = strstr($element->href, '/imgres?imgurl='))
				    {
					   /* echo '<pre>';
					       print_r($element->href);
					    echo '</pre>';*/
					    $url = str_replace('/imgres?imgurl=', '',  $element->href);
					     $url = strtok($url, '&');
					    echo '<pre>';
					       print_r($url);
					    echo '</pre>';
					    _getIMAGEandSAVE($url, $dir_name, $num_row++);
				    }
				}
				
			}
		}
		else echo "Ошибка при открытии файла";
		fclose($fp);
}


function get_imgs_GOOGLE_txt_file_list()
{
	$fp = fopen('./images/pars/caft_akc.txt', "r"); // Открываем файл в режиме чтения
		if ($fp)
		{   $num_row= 1;
			while (!feof($fp))
			{  
				
				$url = trim(fgets($fp, 4096));
				echo $url.'<br>';
				//$url = str_replace(' ', '+',  $url);
				//echo $url.'<br>';
				$url = preg_replace('/[^A-Za-z0-9-+]+/', '+', $url);
				$dir_name = preg_replace('/[^A-Za-z0-9-]+/', '', $url);
				
				echo $url.'<br>';
				echo $dir_name.'<br>';
				
				$url_http = 'http://www.google.com.ua/search?num=10&hl=ru&site=imghp&tbm=isch&source=hp&biw=1280&bih=848&q='.$url.'&tbs=isz:m&tbm=isch&source=lnt&sa=X';
				echo $url_http.'<br>';
				_getIMAGESandSAVE($url_http, $dir_name);
				//$num_row++;
			}
		}
		else echo "Ошибка при открытии файла";
		fclose($fp);
	
}	
	

/*
 Функция вытягивает картинки по урлу и сохраняет в директорию	
*/
function _getIMAGESandSAVE($url_http, $dir_name)
{	
	$html = file_get_html($url_http);
	$i=1;	
	//----------------Вытягивание картинок----------------------------------------			
	foreach($html->find('img') as $element)
	{
	       echo $element->src . '<br>';
	//if($i==6 || $i==7)
	if($i!==1)
	{ 
		$ch = curl_init($element->src);
		if(!file_exists('./images/pars/'.$dir_name))
			mkdir('./images/pars/'.$dir_name, 0700);
		$path_name= './images/pars/'.$dir_name.'/'.$i.'.jpg';
		$fp = fopen($path_name, 'wb');
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);
	}
		$i++;
	}
}
/*
 Функция вытягивает картинки по урлу и сохраняет в директорию	
*/
function _getIMAGEandSAVE($url_img_big, $dir_name, $num_row)
{	
	
	$i=1;	
	//----------------Вытягивание картинок----------------------------------------			
	
	      
	
		$ch = curl_init($url_img_big);
		if(!file_exists('./images/pars/'.$dir_name))
			mkdir('./images/pars/'.$dir_name, 0700);
		$path_name= './images/pars/'.$dir_name.'/'.$dir_name.'_'.$num_row.'.jpg';
		$fp = fopen($path_name, 'wb');
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);
	
		return;
}


?>

