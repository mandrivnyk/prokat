<?php
if(isset($_GET['productID']))
    $productID = iconv("UTF-8","windows-1251",$_GET['productID']);
else
    $productID = 0;
if(isset($_GET['last']))
    $last = iconv("UTF-8","windows-1251",$_GET['last']);
else
    $last = 0;

require_once('../classes/class.comments.php');

$CommentsObj = new comments($_SERVER['DOCUMENT_ROOT'].'/comments/', 'easier@ukr.net', $_SERVER['SERVER_NAME']);
$CommentsObj->GetComment($productID, $last);
?>