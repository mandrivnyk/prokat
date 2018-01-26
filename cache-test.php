<?

$from = iconv("UTF-8","windows-1251",$_GET['from']);
$shag = iconv("UTF-8","windows-1251",$_GET['shag']);
$to = $from + $shag;
echo 'tut';


	


?>