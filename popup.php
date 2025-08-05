<?php
require_once('config.php');
global $DB;
global $USER;
if($USER->id!='')
{
	$u1 = $DB->execute("INSERT INTO `mdl_popups`(`id`,`user_id`,`type`,`date` , `status`) VALUES (NULL,'".$USER->id."','1', '".@date('Y-m-d h:i:s')."' , '1')");        
}    

	header("Location: index.php");
	exit();
