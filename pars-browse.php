
<head>
<link href="/js/highslide/highslide.css" type="text/css" rel="stylesheet">
<link charset="windows-1251" type="text/css" rel="stylesheet" href="/titan1.css">


<script src="/js/highslide/highslide.js" type="text/javascript">
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

<title>�����-� �������� ��������� � �����. </title>

</head>

<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" style="padding:50;">

��������� ������ Craft ��������� 2007-2011: ����������, ������ ��� ���, �������, ����, ����������, ����� � ��. <br>
<table width="100%" border="1">
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
							
   							 $func[$i] = $file;
							
   							 //==============================================	
   							$fp = fopen('./images/pars/caft_akc1.txt', "r"); // ��������� ���� � ������ ������
                            $prod_name = '';
							if ($fp)
							{   
								while (!feof($fp))
								{ 
									$url = trim(fgets($fp, 4096));
									//echo 'CRAFT '.$url.'<br>';
										
										
										$url_orig = $url;
										//echo $url_orig1.'<br>';
										//$url = str_replace(' ', '+',  $url);
										//echo $info.'<br>';
										$url = preg_replace('/[^A-Za-z�-��-�����޸�����0-9-+]+/', '+', $url);
										$dir_name = preg_replace('/[^A-Za-z�-��-�����޸�����0-9-]+/', '_', $url);
										$url = 'CRAFT+'.strtok($url, '+');
										$dir_name = 'CRAFT_'.strtok($dir_name, '_');
										if($dir_name == $func[$i])
										{	echo 'CRAFT '.$url_orig.'<br>';
											$prod_name .= 'CRAFT '.$url_orig.'<br>';
										}
								}
							}
							else echo "������ ��� �������� �����";
							fclose($fp);
							//================================================
							
							//echo  $func[$i].'<br>';
							$k = 1;
							echo '<table border="0" width="500px;"><tr>';
							foreach (@scandir('./images/pars/'.$func[$i]) as $v)
							{
								echo '<td>';
							    if ($v == '.' || $v == '..') continue;
							    $path_img = './images/CRAFT_ALL/'.$v;
							     $path_img_150 = './images/CRAFT_ALL/150_'.$v;
							    //echo '$path_img = '.$path_img.'<br>';
							    // echo $v.'<br>';
							    //===================================
							   
								
								if($src !== false)
								{
								
									echo '<div id="samples-wrapper" class="section">
<div class="thumbwrapper"><div style="border: 0px solid blue; position: relative;">									
<a style="" href="'.$path_img.'" class="highslide" onclick="return hs.expand(this,{ outlineType: \'rounded-white\' })" title="�������, ���� ���������">
<img border="0" alt="CRAFT '.$prod_name.'" src="'.$path_img_150.'" width="120px" id="img_'.$func[$i].'"></a><div class="highslide-caption">CRAFT '.$prod_name.'</div></div</div</div>';
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
					
		//=================================================			
					
					
					
					
					
					//$info .= '$dir_name ='.$dir_name.'<br>';
	
?>
<!-- enable(1,5) - ��� 1- � 1 ����� ������, 5 - ��� ���  -->


</td></tr>
</table>

</body>

</html>
