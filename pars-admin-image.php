
<head>

<script type="text/javascript" src="./js/prototype.js"></script>

<script>

//alert(num_rows);
  var gUrl = './pars-admin-image-SRV.php';  

    function togglevis_img_in_div(DIV)

  {

   // alert(DIV);

    var div_elem = document.getElementById(DIV);

    div_elem.innerHTML ='<span style="padding-left: 0px;"><img width="16" height="16"  src="/images/loading_ajax.gif" border="0" /></span>';

  }

function del_img_remote(DIR_NAME, IMG_PATH) // ----------

  {
  	
		// togglevis_img_in_div('el_pars');
		 var today = new Date();
	     var time  =   today.getTime();
	     
		 var myAjax = new Ajax.Updater('pars_info', gUrl, {method: 'get', asynchronous: true, parameters: {times:time, dir_name:DIR_NAME, img_path:IMG_PATH}, evalScripts: true});     	


  }

-->

</script>

<title>Админ-е картинок скачанных с инета. </title>

</head>

<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" style="padding:50;">

Нажмите на картинку чтоб удалить с сервера: <br>
<span id="el_pars"><img border="0" onclick="pars(1, num_rows)" title="" src="/images/backend/enable-no.jpg" id="img_pars"></span>

<div id="pars_info"> INFO:</div>

<table>
<tr><td>



<?php
$i = 0;



//
		//==============================================
		
					$y = 1;
						$handle = opendir ('./images/pars/');
						while($file = readdir($handle))
						{
							if ($file != '.' && $file != '..')
  							{
							if($y ==150)
							exit();
   							 $func[$i] = $file;
							
   							 //==============================================	
   							$fp = fopen('./images/pars/caft_akc1.txt', "r"); // Открываем файл в режиме чтения

							if ($fp)
							{   
								while (!feof($fp))
								{ 
									$url = trim(fgets($fp, 4096));
										
										
										
										$url_orig = $url;
										//$url = str_replace(' ', '+',  $url);
										//echo $info.'<br>';
										$url = preg_replace('/[^A-Za-zа-яА-ЯъьЬЪЮёЁїЇіІ0-9-+]+/', '+', $url);
										$dir_name = preg_replace('/[^A-Za-zа-яА-ЯъьЬЪЮёЁїЇіІ0-9-]+/', '_', $url);
										$url = 'CRAFT+'.strtok($url, '+');
										$dir_name = 'CRAFT_'.strtok($dir_name, '_');
										if($dir_name == $func[$i])
											echo '$url_orig = '.$url_orig.'<br>';
								}
							}
							else echo "Ошибка при открытии файла";
							fclose($fp);
							//================================================
							
								
							echo  $func[$i].'<br>';
							$k = 1;
							echo '<table border="1"><tr>';
							foreach (@scandir('./images/pars/'.$func[$i]) as $v)
							{
								echo '<td>';
							    if ($v == '.' || $v == '..') continue;
							    $path_img = './images/CRAFT_ALL/'.$v;
							    $path_img_150 = './images/CRAFT_ALL/150_'.$v;
							    //echo '$path_img = '.$path_img.'<br>';
							    echo $v.'<br>';
							    //===================================
							   if(!file_exists($path_img_150))
								{
										require_once('./resizeimage.inc.php');

										copy($path_img, $path_img_150);

										$rimg1=new RESIZEIMAGE($path_img_150);
															//echo $rimg->error();
												$rimg1->resize_limitwh(150,150, 1);
												$rimg1->close();
		
								}
								$src = @imagecreatefromjpeg($path_img);
								if($src !== false)
								{
									$w_src = imagesx($src);
									$h_src = imagesy($src);
									
									echo 'ширина - '.$w_src;
									echo '</br>';
									echo 'высота - '. $h_src;
									echo '</br>';
									echo '<span id="info_'.$func[$i].'_'.$k.'" style="color:red; font-size: 14px;" ><img width="150px" src="'.$path_img_150.'" id="img_'.$func[$i].'" onclick="del_img_remote(\''.$func[$i].'_'.$k.'\', \''.$path_img_150.'\')"></div>';
									
									//======================================= создание картинок 150 в ширину
									
									
									
									
									//=====================================
									
									
								}
								else 
								{
									
									
									unlink($path_img);
									echo 'DELETED:false image properties<br>';
								}
								echo '</td>';
							    //===================================
							    
							   $k++; 
							}
							echo '</tr></table>';
							echo ' <hr style="background-color: red; color: red; height: 2px; border: none;">';
							echo '<br>'; 
							$y++; 
  							}
						}
					echo 'www----------------'.$y;
					
		//=================================================			
					
					
					
					
					
					//$info .= '$dir_name ='.$dir_name.'<br>';
	
?>
<!-- enable(1,5) - тут 1- с 1 файла начать, 5 - это шаг  -->

</td></tr>
</table>


</body>

</html>
