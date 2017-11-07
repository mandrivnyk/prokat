<?php
$productCode = 0;

if(isset($_GET['productCode']))
    $productCode = iconv("UTF-8","windows-1251",$_GET['productCode']);

require_once('../classes/class.sklad.php');

$sklad = new sklad();
$sklad->getVariants($productCode);