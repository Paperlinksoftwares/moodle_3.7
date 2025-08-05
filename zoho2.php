<?php
require_once('config.php');
global $DB;
global $USER;
$current_term = 3;
if($USER->id!='')
{
	$u1 = $DB->execute("INSERT INTO `zoho_forms`(`id`,`form_name`,`term`,`userid`,`date`) VALUES (NULL,'FeedbackForm','".@date('Y')."|".$current_term."','".$USER->id."', '".@date('Y-m-d h:i:s')."')");        
}    

	header("Location: index.php");
	exit();
