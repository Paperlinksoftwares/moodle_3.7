<?php
require_once('config.php');
global $DB;
global $USER;
if($USER->id!='')
{
	$u1 = $DB->execute("INSERT INTO `mdl_course_attendance_notificaion_cancels`(`id`,`user_id`,`course_id`,`attendance_id`,`date`) 
	VALUES (NULL,'".$_GET['user_id']."', '".$_GET['course_id']."' , '".$_GET['attendance_id']."' , '".@date('Y-m-d h:i:s')."')");        
}    

	header("Location: index.php");
	exit();
