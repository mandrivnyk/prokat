<?php

$dir_name = iconv("UTF-8","windows-1251",$_GET['dir_name']);
$img_path = iconv("UTF-8","windows-1251",$_GET['img_path']);
/*echo '$dir_name ='.$dir_name;
echo '<br>$img_path = '.$img_path;
exit();*/
if(unlink($img_path))
	$info =  'удалена картинка '.$img_path;
echo '<script language="javascript">var pars_info_div = document.getElementById("info_'.$dir_name.'"); pars_info_div.innerHTML ="'.$info.'";</script>';

?>