<?php

require_once('../classes/class.comments.php');

$CommentsObj = new comments($_SERVER['DOCUMENT_ROOT'].'/comments/', 'easier@ukr.net', $_SERVER['SERVER_NAME']);
$CommentsObj->SaveComment();

?>