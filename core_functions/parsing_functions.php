<?				 
					 
					 
/*
    Функция принимает параметр url и вытягивает ссылки на продукты , заточено для gorgany.com
    Для каждой ссылки идет вызов фунции _GetProductInfo($url_http), по которой собирается инфа о товаре (название, цена, описание, картинки и др. )
*/

function GetLinksToProducts_TERMO($url_http, $dir_name, $file_name,$dir_name_main, $product_info)		
{					
		//========================================================================================================================							 
									 
									 
									 
									 
						//========================================================================================================================			 
									 
					
				
					
					$info .= $url_http.'<br>';
					$html = file_get_html($url_http);					
					/* echo $url_http.' $html = <pre>';
						       print_r($htmln[0]);
						 echo '</pre>';	
						 exit();*/			
					$res_clear_arr = array();
					for($y=1; $y<1000; $y++)
					{
						$divNum = 'div[id='.$y.']';
						if($html->find($divNum,0))
						{	if($res = $html->find($divNum,0)->find('a'))					
							{
								//$res = $html->find('div[id=215]',0)->find('a');
								$i=0;
								
								foreach($res as $element)
								{
									if($i%2 !== 0)  //-------------- уберет дублируюшие ссылки на продукты
									{
										/*echo '<pre>';
									       print_r($element->href);
									 	echo '</pre>';*/
									 	
   										if(substr_count($element->href,"index.php") == 0)
   										{
   											if($i== 0)
   										
										    	$res_clear_arr[] = $element->href;
											else 
											{
												$key = array_search($element->href, $res_clear_arr); //-----------чистка от дублирующих строк
												if($key == false)
													$res_clear_arr[] = $element->href;
										
											}	
   										}
									}
								//echo '$i='.$i;
									$i++;
								
									/* foreach($res1 as $element)
									 {
										echo '<pre>';
									       print_r($element->outertext);
									 echo '</pre>';	
									 } */
									
									
								}
								//echo '$y='.$y;
								
								
								
							}
						}
					}
				//echo '<hr>';
										/*echo '<pre>';
									       print_r($res_clear_arr);
									 	echo '</pre>';*/
					$k=0;
					foreach($res_clear_arr as $element)
								{
									$product_arr[$k] = _GetProductInfo_TERMO($element, $dir_name, $dir_name_main);
									$product_arr[$k]['categoryID'] = $product_info['categoryID'];
									$product_arr[$k]['title_one'] = $product_info['title_one'];
									$product_arr[$k]['title_two'] = $product_info['title_two'];	
									$product_arr[$k]['producer'] = $product_info['producer'];
									$product_arr[$k]['meta_description'] = $product_arr[$k]['name'].' '.$product_info['meta_description'];
									$product_arr[$k]['meta_keywords'] = $product_arr[$k]['name'].' '.$product_info['meta_keywords'];
									$product_arr[$k++]['list_price'] = $product_info['list_price'];
								}
					
					
					$html->clear(); // подчищаем за собой
 					unset($html);
 					
 					
 					//=======================================================
 					

 					Serialize_arr_in_file($dir_name,  $file_name, $product_arr);  //--------- Упаковываем массив в файл для дальнейшей эксплуатации :)
					$product_arr =  UNserialize_arr_in_file($dir_name,  $file_name); //------------ Распаковка файла в массив для вывода и теста 
					 
					
 					
 					//=======================================================
 					/*echo '<pre>';
						 print_r($product_arr);
					echo '</pre>';*/
 					
 					
					/*$ret = $html->find('div[id=215]');
					$i=0;
					
					
					foreach($ret as $element)
					{		
						
						
						//-------------
						
						echo $element->children(2)->tag.'->';
						echo $element->children(2)->children(0)->tag.'->';
						echo $element->children(2)->children(0)->children(0)->tag.'->';
						echo $element->children(2)->children(0)->children(0)->children(0)->tag.'->';
						echo $element->children(2)->children(0)->children(0)->children(0)->children(0)->tag.'->';
						echo $element->children(2)->children(0)->children(0)->children(0)->children(0)->children(0)->tag.'<br>';
						
						echo $element->children(2)->children(0)->children(0)->children(0)->children(0)->children(0)->children(0)->href.'<br>';
						echo $element->children(2)->children(0)->children(0)->children(0)->children(0)->children(1)->children(0)->href.'<br>';
						echo $element->children(2)->children(0)->children(0)->children(0)->children(0)->children(2)->children(0)->href.'<br>';
						echo $element->children(2)->children(0)->children(0)->children(0)->children(1)->children(0)->children(0)->href.'<br>';
						echo $element->children(2)->children(0)->children(0)->children(0)->children(1)->children(1)->children(0)->href.'<br>';
						 echo '<pre>';
						       print_r($element->href);
						 echo '</pre>';
									
					    echo '$i='.$i++;
					}*/

					
				return  $product_arr;




}
function GetLinksToProducts_REUSCH($url_http, $dir_name, $file_name,$dir_name_main, $product_info)		
{					
		//========================================================================================================================							 
									 
									 
									 
									 
						//========================================================================================================================			 
									 
					
				
					
					$info .= $url_http.'<br>';
					$html = file_get_html($url_http);					
					/* echo $url_http.' $html = <pre>';
						       print_r($htmln[0]);
						 echo '</pre>';	
						 exit();*/			
					$res_clear_arr = array();
					for($y=1; $y<1000; $y++)
					{
						$divNum = 'div[id='.$y.']';
						if($html->find($divNum,0))
						{	if($res = $html->find($divNum,0)->find('a'))					
							{
								//$res = $html->find('div[id=215]',0)->find('a');
								$i=0;
								
								foreach($res as $element)
								{
									if($i%2 !== 0)  //-------------- уберет дублируюшие ссылки на продукты
									{
										/*echo '<pre>';
									       print_r($element->href);
									 	echo '</pre>';*/
									 	
   										if(substr_count($element->href,"index.php") == 0)
   										{
   											if($i== 0)
   										
										    	$res_clear_arr[] = $element->href;
											else 
											{
												$key = array_search($element->href, $res_clear_arr); //-----------чистка от дублирующих строк
												if($key == false)
													$res_clear_arr[] = $element->href;
										
											}	
   										}
									}
								//echo '$i='.$i;
									$i++;
								
									/* foreach($res1 as $element)
									 {
										echo '<pre>';
									       print_r($element->outertext);
									 echo '</pre>';	
									 } */
									
									
								}
								//echo '$y='.$y;
								
								
								
							}
						}
					}
				//echo '<hr>';
										/*echo '<pre>';
									       print_r($res_clear_arr);
									 	echo '</pre>';*/
					$k=0;
					foreach($res_clear_arr as $element)
								{
									$product_arr[$k] = _GetProductInfo_REUSCH($element, $dir_name, $dir_name_main);
									$product_arr[$k]['categoryID'] = $product_info['categoryID'];
									$product_arr[$k]['title_one'] = $product_info['title_one'];
									$product_arr[$k]['title_two'] = $product_info['title_two'];	
									$product_arr[$k]['producer'] = $product_info['producer'];
									$product_arr[$k]['meta_description'] = $product_arr[$k]['name'].' '.$product_info['meta_description'];
									$product_arr[$k]['meta_keywords'] = $product_arr[$k]['name'].' '.$product_info['meta_keywords'];
									$product_arr[$k++]['list_price'] = $product_info['list_price'];
								}
					
					
					$html->clear(); // подчищаем за собой
 					unset($html);
 					
 					
 					//=======================================================
 					

 					Serialize_arr_in_file($dir_name,  $file_name, $product_arr);  //--------- Упаковываем массив в файл для дальнейшей эксплуатации :)
					$product_arr =  UNserialize_arr_in_file($dir_name,  $file_name); //------------ Распаковка файла в массив для вывода и теста 
					 
					
 					
 					//=======================================================
 					/*echo '<pre>';
						 print_r($product_arr);
					echo '</pre>';*/
 					
 					
					/*$ret = $html->find('div[id=215]');
					$i=0;
					
					
					foreach($ret as $element)
					{		
						
						
						//-------------
						
						echo $element->children(2)->tag.'->';
						echo $element->children(2)->children(0)->tag.'->';
						echo $element->children(2)->children(0)->children(0)->tag.'->';
						echo $element->children(2)->children(0)->children(0)->children(0)->tag.'->';
						echo $element->children(2)->children(0)->children(0)->children(0)->children(0)->tag.'->';
						echo $element->children(2)->children(0)->children(0)->children(0)->children(0)->children(0)->tag.'<br>';
						
						echo $element->children(2)->children(0)->children(0)->children(0)->children(0)->children(0)->children(0)->href.'<br>';
						echo $element->children(2)->children(0)->children(0)->children(0)->children(0)->children(1)->children(0)->href.'<br>';
						echo $element->children(2)->children(0)->children(0)->children(0)->children(0)->children(2)->children(0)->href.'<br>';
						echo $element->children(2)->children(0)->children(0)->children(0)->children(1)->children(0)->children(0)->href.'<br>';
						echo $element->children(2)->children(0)->children(0)->children(0)->children(1)->children(1)->children(0)->href.'<br>';
						 echo '<pre>';
						       print_r($element->href);
						 echo '</pre>';
									
					    echo '$i='.$i++;
					}*/

					
				return  $product_arr;




}



function GetLinksToProducts_Craft_Russia($url_http, $dir_name, $file_name,$dir_name_main, $product_info)		
{					
		//========================================================================================================================							 
		//========================================================================================================================			 
										
					$info .= $url_http.'<br>';
					$html = file_get_html($url_http);					
					/* echo $url_http.' $html = <pre>';
						       print_r($htmln[0]);
						 echo '</pre>';	
						 exit();*/			
					$res_clear_arr = array();
					
//========================================================================================================================			 				
				if($html->find('div.scroll-box a'))//'table.hello td'
					{
						$res = $html->find('div.scroll-box a');
						
						foreach($res as $element)
						{
							//echo $element->href.'<br>';
							$res_clear_arr[] = 'http://www.craft-russia.ru'.$element->href; 
						}
						echo '<pre>';
							print_r($res_clear_arr);
						echo '</pre>';
					}
					// $ProductInfo = 
					$res_clear_arr=array_unique($res_clear_arr);
					
					
					/*echo 'array_unique = <pre>';
									       print_r($res_clear_arr);
						echo '</pre>';*/
					//exit();
//========================================================================================================================			 					
					
				//echo '<hr>';
										/*echo '<pre>';
									       print_r($res_clear_arr);
									 	echo '</pre>';*/
					$k=0;
					foreach($res_clear_arr as $element)
								{
									$product_arr[$k] = _GetProductInfo_Craft_Russia($element, $dir_name, $dir_name_main);
									$product_arr[$k]['categoryID'] = $product_info['categoryID'];
									$product_arr[$k]['title_one'] = $product_info['title_one'];
									$product_arr[$k]['title_two'] = $product_info['title_two'];	
									$product_arr[$k]['producer'] = $product_info['producer'];
									$product_arr[$k]['meta_description'] = $product_arr[$k]['name'].' '.$product_info['meta_description'];
									$product_arr[$k]['meta_keywords'] = $product_arr[$k]['name'].' '.$product_info['meta_keywords'];
									$product_arr[$k++]['list_price'] = $product_info['list_price'];
								}
					
					
					$html->clear(); // подчищаем за собой
 					unset($html);
 					
 					
 					//=======================================================
 					

 					Serialize_arr_in_file($dir_name,  $file_name, $product_arr);  //--------- Упаковываем массив в файл для дальнейшей эксплуатации :)
					$product_arr =  UNserialize_arr_in_file($dir_name,  $file_name); //------------ Распаковка файла в массив для вывода и теста 
					 
					
 					


					
				return  $product_arr;

}

function GetLinksToProducts_PRIDBAY($url_http, $dir_name, $file_name,$dir_name_main, $product_info)		
{					
		//========================================================================================================================							 
									 
									 
									 
									 
						//========================================================================================================================			 
									 
					
				
					
					$info .= $url_http.'<br>';
					$html = file_get_html($url_http);					
					/* echo $url_http.' $html = <pre>';
						       print_r($htmln[0]);
						 echo '</pre>';	
						 exit();*/			
					$res_clear_arr = array();
					
//========================================================================================================================			 				
					if($html->find('table.product a'))//'table.hello td'
					{
						$res = $html->find('table.product a');
						/*echo '<pre>';
									       print_r($res);
						echo '</pre>';*/
						
						foreach($res as $element)
								{
									
									if(strstr($element->href,'/product/'))
									{
										
											$res_clear_arr[] = str_replace('../..', 'http://pridbay.com.ua', $element->href); 
													
									}
									
									
								}
						
					}
					// $ProductInfo = 
					$res_clear_arr=array_unique($res_clear_arr);
					/*echo 'array_unique = <pre>';
									       print_r($res_clear_arr);
						echo '</pre>';*/
					//exit();
//========================================================================================================================			 					
					
				//echo '<hr>';
										/*echo '<pre>';
									       print_r($res_clear_arr);
									 	echo '</pre>';*/
					$k=0;
					foreach($res_clear_arr as $element)
								{
									$product_arr[$k] = _GetProductInfo_PRIDBAY($element, $dir_name, $dir_name_main);
									$product_arr[$k]['categoryID'] = $product_info['categoryID'];
									$product_arr[$k]['title_one'] = $product_info['title_one'];
									$product_arr[$k]['title_two'] = $product_info['title_two'];	
									$product_arr[$k]['producer'] = $product_info['producer'];
									$product_arr[$k]['meta_description'] = $product_arr[$k]['name'].' '.$product_info['meta_description'];
									$product_arr[$k]['meta_keywords'] = $product_arr[$k]['name'].' '.$product_info['meta_keywords'];
									$product_arr[$k++]['list_price'] = $product_info['list_price'];
								}
					
					
					$html->clear(); // подчищаем за собой
 					unset($html);
 					
 					
 					//=======================================================
 					

 					Serialize_arr_in_file($dir_name,  $file_name, $product_arr);  //--------- Упаковываем массив в файл для дальнейшей эксплуатации :)
					$product_arr =  UNserialize_arr_in_file($dir_name,  $file_name); //------------ Распаковка файла в массив для вывода и теста 
					 
					
 					
 					//=======================================================
 					/*echo '<pre>';
						 print_r($product_arr);
					echo '</pre>';*/
 					
 					
					/*$ret = $html->find('div[id=215]');
					$i=0;
					
					
					foreach($ret as $element)
					{		
						
						
						//-------------
						
						echo $element->children(2)->tag.'->';
						echo $element->children(2)->children(0)->tag.'->';
						echo $element->children(2)->children(0)->children(0)->tag.'->';
						echo $element->children(2)->children(0)->children(0)->children(0)->tag.'->';
						echo $element->children(2)->children(0)->children(0)->children(0)->children(0)->tag.'->';
						echo $element->children(2)->children(0)->children(0)->children(0)->children(0)->children(0)->tag.'<br>';
						
						echo $element->children(2)->children(0)->children(0)->children(0)->children(0)->children(0)->children(0)->href.'<br>';
						echo $element->children(2)->children(0)->children(0)->children(0)->children(0)->children(1)->children(0)->href.'<br>';
						echo $element->children(2)->children(0)->children(0)->children(0)->children(0)->children(2)->children(0)->href.'<br>';
						echo $element->children(2)->children(0)->children(0)->children(0)->children(1)->children(0)->children(0)->href.'<br>';
						echo $element->children(2)->children(0)->children(0)->children(0)->children(1)->children(1)->children(0)->href.'<br>';
						 echo '<pre>';
						       print_r($element->href);
						 echo '</pre>';
									
					    echo '$i='.$i++;
					}*/

					
				return  $product_arr;




}

function AddOptionInfo($optionID,$option_value, $option_type, $productID)
{
	
		$where_clause = " where optionID='".$optionID."' AND productID='".$productID."'";

		$q=db_query("select count(*) from ".PRODUCT_OPTIONS_VALUES_TABLE." ".
			$where_clause );

		$r = db_fetch_row($q);

		if ( $r[0]==1 ) // if row exists
		{
			db_query("update ".PRODUCT_OPTIONS_VALUES_TABLE." set option_value='".
				$option_value."', option_type='".$option_type."' ".
				$where_clause );
			/*echo "update ".PRODUCT_OPTIONS_VALUES_TABLE." set option_value='".
				$option_value."', option_type='".$option_type."' ".
				$where_clause.'<br>';*/
		}
		else // insert query
		{
			db_query("insert into ".
				PRODUCT_OPTIONS_VALUES_TABLE.
				"(optionID, productID, option_value, option_type)".
				"values ('".$optionID."', '".$productID."', '".$option_value.
					"', '".$option_type."')");
		}	
}

function AddProduct_from_another_site($product_arr)
 {
 	
 	
 	$q = db_query('SELECT * FROM '.PRODUCTS_TABLE.' WHERE url_name="'.$product_arr['url_name'].'"');
//echo 'SELECT * FROM '.PRODUCTS_TABLE.' WHERE url_name="'.$product_arr['url_name'].'"';
	if ( db_fetch_row($q ) )
	{
		return 0;
	}
	else 
	{
 		$productID = AddProduct(
								$product_arr['categoryID'],
								$product_arr['title_one'],
								$product_arr['title_two'],
								$product_arr['url_name'], 
								$product_arr['name'], 
								$product_arr['producer'],
								0, 
								$product_arr['price'], 
								'',
								50,
								0,
								$product_arr['brief_description'], 
								$product_arr['list_price'],
								$product_arr['product_code'], 
								0,
								0,
								"eproduct_filename",
								0,
								0,
								0, 
								$product_arr['meta_description'],
								$product_arr['meta_keywords'], 
								0,
								1, 
								0,
								0 );
								/*
								$_POST["categoryID"],
								$_POST["title_one"],
								$_POST["title_two"],
								$_POST["url_name"], 
								$_POST["name"], 
								$_POST["producer"],
								$_POST["num_topsale"], 
								$_POST["price"], 
								$_POST["description"],
								$_POST["in_stock"],
								$_POST["skidka"],
								$_POST["brief_description"], 
								$_POST["list_price"],
								$_POST["product_code"], 
								$_POST["sort_order"],
								isset($_POST["ProductIsProgram"]),
								"eproduct_filename",
								$_POST["eproduct_available_days"],
								$_POST["eproduct_download_times"],
								$_POST["weight"], 
								$_POST["meta_description"],
								$_POST["meta_keywords"], 
								isset($_POST["free_shipping"]),
								$_POST["min_order_amount"], 
								$_POST["shipping_freight"],
								$_POST["tax_class"] );*/
			//$_GET["productID"] = $productID;
			/*echo '<pre>';
				print_r($_POST);
			echo '</pre>';
			exit();*/
			$updatedValues = ScanPostVariableWithId( array( "option_value", "option_radio_type" ) );
			cfgUpdateOptionValue($productID, $updatedValues);
			//------------------ URL REWRITE-------------------------------------
				include_once("./public_scripts/call_url_rewrite.php");
			//------------------------------------------------------------------
 			return $productID;
	}
			
 }

function Serialize_arr_in_file($dir_name,  $file_name, $arr_to_serialize)
{
					// $dir_name = './images/gor/';
					 $b = serialize($arr_to_serialize);
					 $fp = fopen($dir_name.$file_name,"w");
					 fwrite($fp,$b);
					 fclose($fp);
					 unset($b);
			return;
}
function UNserialize_arr_in_file($dir_name,  $file_name)
{
					$file = file($dir_name.$file_name);
					 $str = implode("",$file);
					 $a = unserialize($str);
					 
			return  $a;
}

function AddProductPicturesSHORT($productID,$new_filename, $new_thumbnail, $new_enlarged)
{
	
		if ( $new_filename!="" )
		{
			db_query("insert into ".PRODUCT_PICTURES.
					 "(productID, filename, thumbnail, enlarged)".
					 "		values( ".
						$productID.", ".
						" '".$new_filename."', ".
						" '".$new_thumbnail."', ".
						" '".$new_enlarged."' ) " );
			
				$default_pictureID = db_insert_id();
				db_query("update ".PRODUCTS_TABLE." set default_picture = ".
					$default_pictureID." where productID='".$productID."'");
			
		}	
		
		return $default_pictureID;
	
}
/*
 Функция создает уменьшенные  картинки и сохраняет в $dir_name. тут  $small_size , например 100 или 150
 // $dir_name = './images/gor/main/';
*/
function MakeSmallImages($dir_name,$dir_name_main, $small_size)
{

	
					//================Создание уменьшенных картинок 100px =================================		  
										
										foreach (@scandir($dir_name_main) as $v)
										{
											 $path_img = $dir_name_main.$v;
							   
											$path_img_100 = $dir_name.$small_size.'_'.$v;
											if(!file_exists($path_img_100))
											{
													
			
													@copy($path_img, $path_img_100);
			
													$rimg1=new RESIZEIMAGE($path_img_100);
																		//echo $rimg->error();
															$rimg1->resize_limitwh($small_size,$small_size, 1);
															$rimg1->close();
											}
										}	
					//========================================================================================================================
					
					return ;
}

function translit($string_to_translit)
{
	
	$trans = array("а"=>"a","б"=>"b","в"=>"v","г"=>"g","д"=>"d","е"=>"e", "ё"=>"yo","ж"=>"j","з"=>"z","и"=>"i","й"=>"i","к"=>"k","л"=>"l", "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r","с"=>"s","т"=>"t", "у"=>"y","ф"=>"f","х"=>"h","ц"=>"c","ч"=>"ch", "ш"=>"sh","щ"=>"sh","ы"=>"i","э"=>"e","ю"=>"u","я"=>"ya",
  "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G","Д"=>"D","Е"=>"E", "Ё"=>"Yo","Ж"=>"J","З"=>"Z","И"=>"I","Й"=>"I","К"=>"K", "Л"=>"L","М"=>"M","Н"=>"N","О"=>"O","П"=>"P", "Р"=>"R","С"=>"S","Т"=>"T","У"=>"Y","Ф"=>"F", "Х"=>"H","Ц"=>"C","Ч"=>"Ch","Ш"=>"Sh","Щ"=>"Sh", "Ы"=>"I","Э"=>"E","Ю"=>"U","Я"=>"Ya",
  "ь"=>"","Ь"=>"","ъ"=>"","Ъ"=>"");
  return $translited  = strtr($string_to_translit, $trans);
}

//-------------- Функция вытягивает информацию о продукте - заточено для gorgany.com
function _GetProductInfo($url_http1, $dir_name, $dir_name_main)  
{
									 $html = file_get_html($url_http1);
									// $res1 = $h->find('table.main td')->outertext;
									$Product_arr = array();
									
									 
									  //==============  $ProductName =================================================================================
									  $ProductName = $html->find('.pageHeading h1',0)->plaintext;
									  $Product_arr['name'] = $ProductName;
									// echo '$ProductName = '.$ProductName.'<br>';
									$url_name = preg_replace('/[^A-Za-z0-9-]+/', '-', translit($ProductName));
									$Product_arr['url_name'] = $url_name;
									$Product_arr['product_code'] = $url_name;
									
									  
									  //==============  $ProductPrice =================================================================================
									  $ProductPrice = $html->find('.pageHeading span',0)->plaintext;
									 
									  $ProductPrice = preg_replace('/[^0-9]+/', '', $ProductPrice);
									   $Product_arr['price'] = $ProductPrice;
									//  echo '$ProductPrice = '.$ProductPrice.'<br>';
									  
									 //==============  $ProductInfo =================================================================================
									  $ProductInfo = $html->find('.main p',2)->innertext;
									 /* echo '<pre>';
									       print_r($ProductInfo);
									  echo '</pre>';*/
									 // echo '$ProductInfo = '.$ProductInfo.'<br>';
									  
									 
									  //=============== Product Images =================================================================================
									  $name_img = preg_replace('/[^A-Za-z0-9-]+/', '', $ProductName);
									//  echo '$name_img = '.$name_img.'<br>';
									 $res = $html->find('.main',0)->find('img');
									 $ProductMainImage = '';
									 $i=1;$y=1;
									 foreach($res as $div)
									 {
									 //	echo '$i'.$i.'<br>';
									 //	echo '$y'.$y.'<br>';
									 	if($y >3) //---------------3 картинка это логотип бренда, убераем его
									 	{
									 		
									 	/*echo '<pre>';
									       print_r($div->src);
										echo '</pre>';*/
									 	$src_orig = $div->src;
										//echo $div->src.'<br>';
										$div->src= strtok($div->src,'&');
										//echo $div->src.'<br>';
										$src_arr = explode('/',$div->src);
										/* echo '<pre>';
									       print_r($src_arr);
										echo '</pre>';*/
										$num_arr = count($src_arr)-1;
										//echo $src_arr[$num_arr].'<br>';  //-------------- clear image name.jpg
										
										$div->src = 'http://www.gorgany.com/images/'.$src_arr[$num_arr];
										
										
										
										
										
										
										$format = substr($div->src, -3);
										//echo '<br>';
										
										
										$name_img_new  = $name_img.'-'.$i++.'.'.$format;
										//echo $name_img_new;
									//	echo '<br>';
										//echo $div->src;
										
										//echo '<hr>';
										// $dir_name = './images/gor/';
										if(!file_exists($dir_name_main))
										mkdir($dir_name_main, 0700);
										if($y == '4')
										{
											//$ProductMainImage = $dir_name_plus.$src_arr[$num_arr];
											//$ProductMainImage = $dir_name_plus.$src_arr[$num_arr];
											//$ProductMainImage_100 = $dir_name_plus.'100_'.$src_arr[$num_arr];
											$Product_arr['ProductMainImage'] = $name_img_new;
											$Product_arr['ProductMainImage_100'] = '100_'.$name_img_new;
											
											$downlod_image = _getIMAGEandSAVE($div->src, $dir_name_main, $name_img_new);
											/*echo '$div->src = '.$div->src.'<br>';
											echo '$ProductMainImage = '.$ProductMainImage.'<br>';
											echo '$ProductMainImage_100 = '.$ProductMainImage_100.'<br>';
											echo '$name_img_new = '.$name_img_new.'<br>';
											exit();*/
										}
										else
										{
											//-----Скачивание картинок и переименование--------------------------
											$downlod_image = _getIMAGEandSAVE($div->src, $dir_name, $name_img_new);
											
											
											//=========================
											
					
											
											//======================================================================
										}
									// './images/pars/'.
									
									
									
									 //==============  $ProductInfo UPDATE=================================================================================
										//------------ замена src  картинок в $ProductInfo 
										
										$ProductInfo = str_replace($src_orig, $dir_name.$name_img_new, $ProductInfo);
										
										
									    //$ProductInfo = str_replace('http://www.gorgany.com/images/', './images/gor/', $ProductInfo);
									 	}
									 	
									 	$y++;
									 	 
									 		
									 	
									 }			 
								
										//echo '$ProductMainImage = '.$ProductMainImage.'<br>'; 
										
										
									
										
										
											  
									  //==============  $ProductInfo Чистка =================================================================================
									  
									  $ProductInfo = str_replace('Альтернативный цвет (кликнуть для увеличения):', '', $ProductInfo);
									  $ProductInfo = str_replace('<b> Схематическое изображение:  </b>', '', $ProductInfo);
									  $ProductInfo = str_replace('<span style="font-family: tahoma, arial; font-size: 12px"> Подробнее о стандарте и расшифровка обозначений.</span>', '', $ProductInfo);
									  $ProductInfo = str_replace('<a href="http://www.gorgany.com/contact_us.php/language/ru?osCsid=">Нужна помощь?</a>', '', $ProductInfo);
									  $ProductInfo = str_replace('Нужна помощь?', '', $ProductInfo);
									  $ProductInfo=preg_replace('/<a .*>(.*)<\/a>/Ui','\\1',$ProductInfo);  
									  $Product_arr['brief_description'] = $ProductInfo;
									  /* echo '<pre>';
									       print_r($Product_arr);
									  echo '</pre>';*/
									  
									  $html->clear(); // подчищаем за собой
					 					unset($html);
					 					
					 					
				return $Product_arr;					  
									 
}
//-------------- Функция вытягивает информацию о продукте - заточено для gorgany.com
function _GetProductInfo_TERMO($url_http1, $dir_name, $dir_name_main)  
{
									 $html = file_get_html($url_http1);
									// $res1 = $h->find('table.main td')->outertext;
									$Product_arr = array();
									
									 
									  //==============  $ProductName =================================================================================
									  $ProductName = $html->find('.pageHeading h1',0)->plaintext;
									  $Product_arr['name'] = $ProductName;
								//	echo '$ProductName = '.$ProductName.'<br>';
									$url_name = preg_replace('/[^A-Za-z0-9-]+/', '-', translit($ProductName));
									$Product_arr['url_name'] = $url_name;
									$Product_arr['product_code'] = $url_name;
									
									  
									  //==============  $ProductPrice =================================================================================
									  $ProductPrice = $html->find('.pageHeading span',0)->plaintext;
									 
									  $ProductPrice = preg_replace('/[^0-9]+/', '', $ProductPrice);
									   $Product_arr['price'] = $ProductPrice;
									// echo '$ProductPrice = '.$ProductPrice.'<br>';
									  
									 //==============  $ProductInfo =================================================================================
									  $ProductInfo = $html->find('.main p',2)->innertext;
									/*  echo '$ProductInfo<pre>';
									       print_r($ProductInfo);
									  echo '</pre>';
									 echo '$ProductInfo = '.$ProductInfo.'<br>';*/
									  
									 
									  //=============== Product Images =================================================================================
									  $name_img = preg_replace('/[^A-Za-z0-9-]+/', '', $ProductName);
									//  echo '$name_img = '.$name_img.'<br>';
									 $res = $html->find('.main',0)->find('img');
									 $ProductMainImage = '';
									 $i=1;$y=1;
									 foreach($res as $div)
									 {
									 //	echo '$i'.$i.'<br>';
									 //	echo '$y'.$y.'<br>';
									 	if($y >3) //---------------3 картинка это логотип бренда, убераем его
									 	{
									 		
									 	/*echo '<pre>';
									       print_r($div->src);
										echo '</pre>';*/
									 	$src_orig = $div->src;
										echo $div->src.'<br>';
										$div->src= strtok($div->src,'&');
										echo $div->src.'<br>';
										$src_arr = explode('/',$div->src);
										 echo '$src_arr <pre>';
									       print_r($src_arr);
										echo '</pre>';
										$num_arr = count($src_arr)-1;
										echo $src_arr[$num_arr].'<br>';  //-------------- clear image name.jpg
										
										$div->src = 'http://www.gorgany.com/images/'.$src_arr[$num_arr];
										
										echo '$div->src = '.$div->src.'<br>';
										
										
										
										
										$format = substr($div->src, -4);
										$format = str_replace('.', '', $format);
										echo '<br>$format ='.$format.'<br>';
										
										
										$name_img_new  = $name_img.'-'.$i++.'.'.$format;
									    echo $name_img_new;
									//	echo '<br>';
										//echo $div->src;
										
										//echo '<hr>';
										// $dir_name = './images/gor/';
										if(!file_exists($dir_name_main))
										mkdir($dir_name_main, 0700);
										if($y == '4')
										{
											//$ProductMainImage = $dir_name_plus.$src_arr[$num_arr];
											//$ProductMainImage = $dir_name_plus.$src_arr[$num_arr];
											//$ProductMainImage_100 = $dir_name_plus.'100_'.$src_arr[$num_arr];
											$Product_arr['ProductMainImage'] = $name_img_new;
											$Product_arr['ProductMainImage_100'] = '100_'.$name_img_new;
											
											$downlod_image = _getIMAGEandSAVE($div->src, $dir_name_main, $name_img_new);
											/*echo '$div->src = '.$div->src.'<br>';
											echo '$ProductMainImage = '.$ProductMainImage.'<br>';
											echo '$ProductMainImage_100 = '.$ProductMainImage_100.'<br>';
											echo '$name_img_new = '.$name_img_new.'<br>';
											exit();*/
										}
										else
										{
											//-----Скачивание картинок и переименование--------------------------
											$downlod_image = _getIMAGEandSAVE($div->src, $dir_name, $name_img_new);
											
											
											//=========================
											
					
											
											//======================================================================
										}
									// './images/pars/'.
									
									
									
									 //==============  $ProductInfo UPDATE=================================================================================
										//------------ замена src  картинок в $ProductInfo 
										
										$ProductInfo = str_replace($src_orig, $dir_name.$name_img_new, $ProductInfo);
										
										
									    //$ProductInfo = str_replace('http://www.gorgany.com/images/', './images/gor/', $ProductInfo);
									 	}
									 	
									 	$y++;
									 	 
									 		
									 	
									 }			 
								
										//echo '$ProductMainImage = '.$ProductMainImage.'<br>'; 
										
										
									
										
										
											  
									  //==============  $ProductInfo Чистка =================================================================================
									  
									  $ProductInfo = str_replace('Альтернативный цвет (кликнуть для увеличения):', '', $ProductInfo);
									  $ProductInfo = str_replace('<b> Схематическое изображение:  </b>', '', $ProductInfo);
									  $ProductInfo = str_replace('<span style="font-family: tahoma, arial; font-size: 12px"> Подробнее о стандарте и расшифровка обозначений.</span>', '', $ProductInfo);
									  $ProductInfo = str_replace('<a href="http://www.gorgany.com/contact_us.php/language/ru?osCsid=">Нужна помощь?</a>', '', $ProductInfo);
									  $ProductInfo=preg_replace('/<a .*>(.*)<\/a>/Ui','\\1',$ProductInfo);  
									  $Product_arr['brief_description'] = $ProductInfo;
									  /* echo '<pre>';
									       print_r($Product_arr);
									  echo '</pre>';*/
									  
									  $html->clear(); // подчищаем за собой
					 					unset($html);
					 					
					 					
				return $Product_arr;					  
									 
}

function _GetProductInfo_REUSCH($url_http1, $dir_name, $dir_name_main)  
{
									 $html = file_get_html($url_http1);
									// $res1 = $h->find('table.main td')->outertext;
									$Product_arr = array();
									
									 
									  //==============  $ProductName =================================================================================
									  $ProductName = $html->find('.pageHeading h1',0)->plaintext;
									  $Product_arr['name'] = $ProductName;
								//	echo '$ProductName = '.$ProductName.'<br>';
									$url_name = preg_replace('/[^A-Za-z0-9-]+/', '-', translit($ProductName));
									$Product_arr['url_name'] = $url_name;
									$Product_arr['product_code'] = $url_name;
									
									  
									  //==============  $ProductPrice =================================================================================
									  $ProductPrice = $html->find('.pageHeading span',0)->plaintext;
									 
									  $ProductPrice = preg_replace('/[^0-9]+/', '', $ProductPrice);
									   $Product_arr['price'] = $ProductPrice;
									// echo '$ProductPrice = '.$ProductPrice.'<br>';
									  
									 //==============  $ProductInfo =================================================================================
									  $ProductInfo = $html->find('.main p',2)->innertext;
									/*  echo '$ProductInfo<pre>';
									       print_r($ProductInfo);
									  echo '</pre>';
									 echo '$ProductInfo = '.$ProductInfo.'<br>';*/
									  
									 
									  //=============== Product Images =================================================================================
									  $name_img = preg_replace('/[^A-Za-z0-9-]+/', '', $ProductName);
									//  echo '$name_img = '.$name_img.'<br>';
									 $res = $html->find('.main',0)->find('img');
									 $ProductMainImage = '';
									 $i=1;$y=1;
									 foreach($res as $div)
									 {
									 //	echo '$i'.$i.'<br>';
									 //	echo '$y'.$y.'<br>';
									 	if($y >3) //---------------3 картинка это логотип бренда, убераем его
									 	{
									 		
									 	/*echo '<pre>';
									       print_r($div->src);
										echo '</pre>';*/
									 	$src_orig = $div->src;
										echo $div->src.'<br>';
										$div->src= strtok($div->src,'&');
										echo $div->src.'<br>';
										$src_arr = explode('/',$div->src);
										 echo '$src_arr <pre>';
									       print_r($src_arr);
										echo '</pre>';
										$num_arr = count($src_arr)-1;
										echo $src_arr[$num_arr].'<br>';  //-------------- clear image name.jpg
										
										$div->src = 'http://www.gorgany.com/images/'.$src_arr[$num_arr];
										
										echo '$div->src = '.$div->src.'<br>';
										
										
										
										
										$format = substr($div->src, -4);
										$format = str_replace('.', '', $format);
										echo '<br>$format ='.$format.'<br>';
										
										
										$name_img_new  = $name_img.'-'.$i++.'.'.$format;
									    echo $name_img_new;
									//	echo '<br>';
										//echo $div->src;
										
										//echo '<hr>';
										// $dir_name = './images/gor/';
										if(!file_exists($dir_name_main))
										mkdir($dir_name_main, 0700);
										if($y == '4')
										{
											//$ProductMainImage = $dir_name_plus.$src_arr[$num_arr];
											//$ProductMainImage = $dir_name_plus.$src_arr[$num_arr];
											//$ProductMainImage_100 = $dir_name_plus.'100_'.$src_arr[$num_arr];
											$Product_arr['ProductMainImage'] = $name_img_new;
											$Product_arr['ProductMainImage_100'] = '100_'.$name_img_new;
											
											$downlod_image = _getIMAGEandSAVE($div->src, $dir_name_main, $name_img_new);
											/*echo '$div->src = '.$div->src.'<br>';
											echo '$ProductMainImage = '.$ProductMainImage.'<br>';
											echo '$ProductMainImage_100 = '.$ProductMainImage_100.'<br>';
											echo '$name_img_new = '.$name_img_new.'<br>';
											exit();*/
										}
										else
										{
											//-----Скачивание картинок и переименование--------------------------
											$downlod_image = _getIMAGEandSAVE($div->src, $dir_name, $name_img_new);
											
											
											//=========================
											
					
											
											//======================================================================
										}
									// './images/pars/'.
									
									
									
									 //==============  $ProductInfo UPDATE=================================================================================
										//------------ замена src  картинок в $ProductInfo 
										
										$ProductInfo = str_replace($src_orig, $dir_name.$name_img_new, $ProductInfo);
										
										
									    //$ProductInfo = str_replace('http://www.gorgany.com/images/', './images/gor/', $ProductInfo);
									 	}
									 	
									 	$y++;
									 	 
									 		
									 	
									 }			 
								
										//echo '$ProductMainImage = '.$ProductMainImage.'<br>'; 
										
										
									
										
										
											  
									  //==============  $ProductInfo Чистка =================================================================================
									  
									  $ProductInfo = str_replace('Альтернативный цвет (кликнуть для увеличения):', '', $ProductInfo);
									  $ProductInfo = str_replace('<b> Схематическое изображение:  </b>', '', $ProductInfo);
									  $ProductInfo = str_replace('<span style="font-family: tahoma, arial; font-size: 12px"> Подробнее о стандарте и расшифровка обозначений.</span>', '', $ProductInfo);
									  $ProductInfo = str_replace('<a href="http://www.gorgany.com/contact_us.php/language/ru?osCsid=">Нужна помощь?</a>', '', $ProductInfo);
									  $ProductInfo=preg_replace('/<a .*>(.*)<\/a>/Ui','\\1',$ProductInfo);  
									  $Product_arr['brief_description'] = $ProductInfo;
									  /* echo '<pre>';
									       print_r($Product_arr);
									  echo '</pre>';*/
									  
									  $html->clear(); // подчищаем за собой
					 					unset($html);
					 					
					 					
				return $Product_arr;					  
									 
}

function _GetProductInfo_Craft_Russia($url_http1, $dir_name, $dir_name_main)  
{
									 $html = file_get_html($url_http1);
									// $res1 = $h->find('table.main td')->outertext;
									$Product_arr = array();
									
									 
									  //==============  $ProductName =================================================================================
								
						
									  $ProductName = $html->find('div.article',0)->plaintext;
									  $Product_arr['name'] = $ProductName;
							echo '$ProductName = '.$ProductName.'<br>';
									$url_name = preg_replace('/[^A-Za-z0-9-]+/', '-', translit($ProductName));
									$Product_arr['url_name'] = $url_name;
									$Product_arr['product_code'] = $url_name;
									
									  
									  //==============  $ProductPrice =================================================================================
									  
									   $Product_arr['price'] = '';
							echo '$ProductPrice = '.$ProductPrice.'<br>';
									  
									 //==============  $ProductInfo =================================================================================
									  $ProductInfo = $html->find('div.description',0)->innertext;
									/*  echo '$ProductInfo<pre>';
									       print_r($ProductInfo);
									  echo '</pre>';*/
							echo '$ProductInfo = '.$ProductInfo.'<br>';
									  
									 
									  //=============== Product Images =================================================================================
									  $name_img = preg_replace('/[^A-Za-z0-9-]+/', '', $ProductName);
									//  echo '$name_img = '.$name_img.'<br>';
									 $res = $html->find('.main',0)->find('img');
									 $ProductMainImage = '';
									 $i=1;$y=1;
									 foreach($res as $div)
									 {
									 //	echo '$i'.$i.'<br>';
									 //	echo '$y'.$y.'<br>';
									 	if($y >3) //---------------3 картинка это логотип бренда, убераем его
									 	{
									 		
									 	/*echo '<pre>';
									       print_r($div->src);
										echo '</pre>';*/
									 	$src_orig = $div->src;
										echo $div->src.'<br>';
										$div->src= strtok($div->src,'&');
										echo $div->src.'<br>';
										$src_arr = explode('/',$div->src);
										 echo '$src_arr <pre>';
									       print_r($src_arr);
										echo '</pre>';
										$num_arr = count($src_arr)-1;
										echo $src_arr[$num_arr].'<br>';  //-------------- clear image name.jpg
										
										$div->src = 'http://www.gorgany.com/images/'.$src_arr[$num_arr];
										
										echo '$div->src = '.$div->src.'<br>';
										
										
										
										
										$format = substr($div->src, -4);
										$format = str_replace('.', '', $format);
										echo '<br>$format ='.$format.'<br>';
										
										
										$name_img_new  = $name_img.'-'.$i++.'.'.$format;
									    echo $name_img_new;
									//	echo '<br>';
										//echo $div->src;
										
										//echo '<hr>';
										// $dir_name = './images/gor/';
										if(!file_exists($dir_name_main))
										mkdir($dir_name_main, 0700);
										if($y == '4')
										{
											//$ProductMainImage = $dir_name_plus.$src_arr[$num_arr];
											//$ProductMainImage = $dir_name_plus.$src_arr[$num_arr];
											//$ProductMainImage_100 = $dir_name_plus.'100_'.$src_arr[$num_arr];
											$Product_arr['ProductMainImage'] = $name_img_new;
											$Product_arr['ProductMainImage_100'] = '100_'.$name_img_new;
											
											$downlod_image = _getIMAGEandSAVE($div->src, $dir_name_main, $name_img_new);
											/*echo '$div->src = '.$div->src.'<br>';
											echo '$ProductMainImage = '.$ProductMainImage.'<br>';
											echo '$ProductMainImage_100 = '.$ProductMainImage_100.'<br>';
											echo '$name_img_new = '.$name_img_new.'<br>';
											exit();*/
										}
										else
										{
											//-----Скачивание картинок и переименование--------------------------
											$downlod_image = _getIMAGEandSAVE($div->src, $dir_name, $name_img_new);
											
											
											//=========================
											
					
											
											//======================================================================
										}
									// './images/pars/'.
									
									
									
									 //==============  $ProductInfo UPDATE=================================================================================
										//------------ замена src  картинок в $ProductInfo 
										
										$ProductInfo = str_replace($src_orig, $dir_name.$name_img_new, $ProductInfo);
										
										
									    //$ProductInfo = str_replace('http://www.gorgany.com/images/', './images/gor/', $ProductInfo);
									 	}
									 	
									 	$y++;
									 	 
									 		
									 	
									 }			 
								
										//echo '$ProductMainImage = '.$ProductMainImage.'<br>'; 
										
										
									
										
										
											  
									  //==============  $ProductInfo Чистка =================================================================================
									  
									  $ProductInfo = str_replace('Альтернативный цвет (кликнуть для увеличения):', '', $ProductInfo);
									  $ProductInfo = str_replace('<b> Схематическое изображение:  </b>', '', $ProductInfo);
									  $ProductInfo = str_replace('<span style="font-family: tahoma, arial; font-size: 12px"> Подробнее о стандарте и расшифровка обозначений.</span>', '', $ProductInfo);
									  $ProductInfo = str_replace('<a href="http://www.gorgany.com/contact_us.php/language/ru?osCsid=">Нужна помощь?</a>', '', $ProductInfo);
									  $ProductInfo=preg_replace('/<a .*>(.*)<\/a>/Ui','\\1',$ProductInfo);  
									  $Product_arr['brief_description'] = $ProductInfo;
									  /* echo '<pre>';
									       print_r($Product_arr);
									  echo '</pre>';*/
									  
									  $html->clear(); // подчищаем за собой
					 					unset($html);
					 					
					 					
				return $Product_arr;					  
									 
}

//-------------- Функция вытягивает информацию о продукте - заточено для PRIDBAY
function Craft_Russia_GetProductInfo_PRIDBAY($url_http1, $dir_name, $dir_name_main)  
{
									 $html = file_get_html($url_http1);
									// $res1 = $h->find('table.main td')->outertext;
									$Product_arr = array();
									
									 
									  //==============  $ProductName =================================================================================
									  $ProductName = $html->find('td h1.title',0)->plaintext;
									  $ProductName = trim(str_replace('Балаклава / Балаклава', '', $ProductName));
									  
									  $Product_arr['name'] = $ProductName;
							//	echo '$ProductName = '.$ProductName.'<br>';
									$url_name = preg_replace('/[^A-Za-z0-9-]+/', '-', translit($ProductName));
									//$format = substr($url_name, -4);
									$Product_arr['url_name'] = $url_name;
									$Product_arr['product_code'] = $url_name;
									
							//	echo '$url_name = '.$url_name.'<br>';	
									  
									  //==============  $ProductPrice =================================================================================
									  $ProductPrice = $html->find('font[color=#137D9B]',0)->plaintext;
									  $ProductPrice = trim(str_replace('у.е.', '', $ProductPrice));
									  //echo '$ProductPrice = '.$ProductPrice.'<br>';
									 // $ProductPrice = preg_replace('/[^0-9.]+/', '', $ProductPrice);
									  $ProductPrice = $ProductPrice*8.12;
							//	echo '$ProductPrice = '.$ProductPrice.'<br>';
									  //exit();
									  
									  
									   $Product_arr['price'] = $ProductPrice;
									// echo '$ProductPrice = '.$ProductPrice.'<br>';
									  
									 //==============  $ProductInfo =================================================================================
									  $ProductInfo = $html->find('div.productdescription h3',0)->plaintext;;
									/*  echo '$ProductInfo<pre>';
									       print_r($ProductInfo);
									  echo '</pre>';*/
							//	echo '$ProductInfo = '.$ProductInfo.'<br>';
									  
									 
									  //=============== Product Images =================================================================================
									  $name_img = preg_replace('/[^A-Za-z0-9-]+/', '', $ProductName);
									//  echo '$name_img = '.$name_img.'<br>';
									 $name_img_new = $name_img.'.jpg';
									
									$main_image_src = $html->find('a[rel=lightbox[product]]',0)->href;
									$main_image_src = str_replace('..', 'http://pridbay.com.ua', $main_image_src);
							//	echo '$main_image_src = '.$main_image_src.'<br>';
											//$ProductMainImage = $dir_name_plus.$src_arr[$num_arr];
											//$ProductMainImage = $dir_name_plus.$src_arr[$num_arr];
											//$ProductMainImage_100 = $dir_name_plus.'100_'.$src_arr[$num_arr];
											$Product_arr['ProductMainImage'] = $name_img_new;
											$Product_arr['ProductMainImage_100'] = '100_'.$name_img_new;
											
											$downlod_image = _getIMAGEandSAVE($main_image_src, $dir_name_main, $name_img_new);
											/*echo '$div->src = '.$div->src.'<br>';
											echo '$ProductMainImage = '.$ProductMainImage.'<br>';
											echo '$ProductMainImage_100 = '.$ProductMainImage_100.'<br>';
											echo '$name_img_new = '.$name_img_new.'<br>';
											exit();*/
										
									
									
									
									 		 
								
										//echo '$ProductMainImage = '.$ProductMainImage.'<br>'; 
										
										
									
										
										
											  
									  //==============  $ProductInfo Чистка =================================================================================
									  
									  $ProductInfo = str_replace('Альтернативный цвет (кликнуть для увеличения):', '', $ProductInfo);
									  $ProductInfo = str_replace('<b> Схематическое изображение:  </b>', '', $ProductInfo);
									  $ProductInfo = str_replace('<span style="font-family: tahoma, arial; font-size: 12px"> Подробнее о стандарте и расшифровка обозначений.</span>', '', $ProductInfo);
									 
									  $ProductInfo=preg_replace('/<a .*>(.*)<\/a>/Ui','\\1',$ProductInfo);  
									  $Product_arr['brief_description'] = $ProductInfo;
									  /* echo '<pre>';
									       print_r($Product_arr);
									  echo '</pre>';*/
									  
									  $html->clear(); // подчищаем за собой
					 					unset($html);
					 					
					 					
				return $Product_arr;					  
									 
}

function get_BIG_GOOGLE_imgs_txt_file_list($current, $all)
{
	
	$iteration = 1;
	//get_unique_in_txt_file('./images/pars/caft_akc.txt');
	//exit();
	$fp = fopen('./images/pars/caft_akc1.txt', "r"); // Открываем файл в режиме чтения

		if ($fp)
		{   
			while (!feof($fp))
			{ 
				$url = trim(fgets($fp, 4096));
				if($iteration == $current)
				{
					
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
					
					$info .= '$url = '.$url.'<br>';
					//$url = str_replace(' ', '+',  $url);
					//echo $url.'<br>';
					$url = preg_replace('/[^A-Za-zа-яА-ЯъьЬЪЮёЁїЇіІ0-9-+]+/', '+', $url);					
					$dir_name = preg_replace('/[^A-Za-zа-яА-ЯъьЬЪЮёЁїЇіІ0-9-]+/', '_', $url);
					
					
					$url = 'CRAFT+'.strtok($url, '+');
					$dir_name = 'CRAFT_'.strtok($dir_name, '_');
					
					
					/*echo $url.'<br>';
					echo $dir_name;
					exit();*/
					$info .= '$dir_name ='.$dir_name.'<br>';
					$info .= '$iteration = '.$iteration.'<br>';
					$iteration++;
					if(file_exists('./images/pars/'.$dir_name))
					{	
						
						
						
						$info .= '$current = '.$current.'<br>';
						$current++;
						
						$info .= 'Already parced<br>';
						echo '<script language="javascript"> var pars_info_div = document.getElementById("pars_info"); var all_pars_info = pars_info_div.innerHTML; pars_info_div.innerHTML ="'.$info.'"+all_pars_info;</script>';
						if($current <= $all)  //-----тут указывается количество файлов из pars-list.txt
							echo '<script language="javascript"> pars('.$current.','.$all.');</script>';
						echo ' <hr style="background-color: red; color: red; height: 2px; border: none;">';
						exit();
					
					}
					/*else 
					{
						 //========== TEST================================
							echo '$iteration= '.$iteration,'<br>';	
							echo '$current='.$current.' || $all='.$all;
							exit();
							//===============================================
					
					}*/
					$info .= $url.'<br>';
					
					
					$url_http = 'http://www.google.com.ua/search?num=10&hl=ru&site=imghp&tbm=isch&source=hp&biw=1280&bih=848&q='.$url.'&tbs=isz:m&tbm=isch&source=lnt&sa=X';
					$info .= $url_http.'<br>';
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
						   
						   $info .= $url.'<br>';
						    _getIMAGEandSAVE($url, $dir_name, $num_row++);
					    }
					}
					
					$current++;
					
					echo '<script language="javascript"> var pars_info_div = document.getElementById("pars_info"); var all_pars_info = pars_info_div.innerHTML; pars_info_div.innerHTML ="'.$info.'"+all_pars_info;</script>';
					if($current <= $all)  //-----тут указывается количество файлов из pars-list.txt
					 echo '<script language="javascript"> pars('.$current.','.$all.');</script>';
					 echo ' <hr style="background-color: red; color: red; height: 2px; border: none;">';
				}
				$iteration++;
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
		
			$ch = curl_init($element->src);
			if(!file_exists($dir_name))
				mkdir($dir_name, 0700);
			$path_name= $dir_name.'/'.$i.'.jpg';
			$fp = fopen($path_name, 'wb');
			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_exec($ch);
			curl_close($ch);
			fclose($fp);
		
		$i++;
	}
}
/*
 Функция вытягивает картинки по урлу и сохраняет в директорию	
*/
function _getIMAGEandSAVE($url_img_big, $dir_name, $name_img)
{	
	
		
	//----------------Вытягивание картинок----------------------------------------			
	
	      
	
		$ch = curl_init($url_img_big);
		if(!file_exists($dir_name))
			mkdir($dir_name, 0700);
		$path_name= $dir_name.'/'.$name_img;
		$fp = fopen($path_name, 'wb');
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);
	
		return 1;
}


?>