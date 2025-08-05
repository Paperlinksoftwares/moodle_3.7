<?php
require_once('../config.php');
global $DB;
global $USER;
if(isset($_POST['confirm']) && $_POST['confirm']==1)
{
	$count_config = $DB->count_records_sql("SELECT COUNT(*) FROM `mdl_user_course_confirmation` WHERE `user_id` = '".$USER->id."' AND `course_id` = '".$course->id."'");        
	
if($USER->id>0 && $count_config==0)
{
	$u1 = $DB->execute("INSERT INTO `mdl_user_course_confirmation`(`id`,`user_id`,`course_id`,`date_update`) VALUES (NULL,'".$USER->id."','".$_POST['course_id']."','".@date('Y-m-d h:i:s')."')");        
}    
}
if(isset($_POST['open']) && $_POST['open']==1)
{
	$open=1;
}
else
{
	$open=0;
}
header("Location: http://localhost/accit-moodle/accit/course/view2.php?id=".$_POST['course_id']."&open=".$open);
exit();
