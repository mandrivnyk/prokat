<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
require('./classes/class.ivalidator.php');

session_start();

$i = new IValidator();
$i->FontsDir = './temp';
$i->generateImage();
?>