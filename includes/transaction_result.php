<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php

if(isset($_GET['transaction_result']))
    $transaction_result=$_GET['transaction_result'];
else
    if(isset($_POST['transaction_result']))
        $transaction_result=$_POST['transaction_result'];

if(!isset($transaction_result))return '';

switch ($transaction_result){
	
	case 'success':
	case 'failure':
		$smarty->assign('TransactionResult', $transaction_result);
		$smarty->assign( "main_content_template", "transaction_result.tpl.html");
		break;
}
?>