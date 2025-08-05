<?php
require_once('config.php');
global $DB;
global $USER;
if($USER->id!='')
{
	$u1 = $DB->execute("INSERT INTO `mdl_user_popuprejoin`(`id`,`user_id`,`year`,`term`,`date`) 
	VALUES (NULL,'".$_GET['user_id']."', '".$_GET['year']."' , '".$_GET['term']."' , '".@date('Y-m-d h:i:s')."')");        
}    

	header("Location: index.php");
	exit();
