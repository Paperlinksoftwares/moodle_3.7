<?php
$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$new_url = $url.'&s=1';

	
$exclude_students_payment1 = array();
$exclude_students_payment2 = array();

$sql_users_restricted_1 = "SELECT * FROM `mdl_users_restricted` WHERE `type_of_restriction` = '1' ORDER BY `id` DESC";
$sql_users_restricted_list_1 = $DB->get_records_sql($sql_users_restricted_1); 

foreach($sql_users_restricted_list_1 as $k=>$v)
{
	$exclude_students_payment1[]=$v->user_id;
} 



$sql_users_restricted_2 = "SELECT * FROM `mdl_users_restricted` WHERE `type_of_restriction` = '2' ORDER BY `id` DESC";
$sql_users_restricted_list_2 = $DB->get_records_sql($sql_users_restricted_2); 

foreach($sql_users_restricted_list_2 as $k2=>$v2)
{
	$exclude_students_payment2[]=$v2->user_id;
	
}

if(in_array($USER->id,$exclude_students_payment1)==true && in_array($USER->id,$exclude_students_payment2)==false)
{
	header('Location: http://localhost/accit-moodle/accit/accountrestriction/index_1.php?current_term='.$current_term.'&user_id='.$USER->id);
	exit();
}

else if(in_array($USER->id,$exclude_students_payment1)==false && in_array($USER->id,$exclude_students_payment2)==true)
{
	header('Location: http://localhost/accit-moodle/accit/accountrestriction/index_2.php?current_term='.$current_term.'&user_id='.$USER->id);
	exit();
}
else if(in_array($USER->id,$exclude_students_payment1)==true && in_array($USER->id,$exclude_students_payment2)==true)
{
	header('Location: http://localhost/accit-moodle/accit/accountrestriction/index_2.php?current_term='.$current_term.'&user_id='.$USER->id);
	exit();
}
else
{
	header('Location: '.$new_url);
	exit();
}





