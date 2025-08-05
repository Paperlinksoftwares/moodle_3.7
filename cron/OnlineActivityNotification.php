<?php

$conn = mysqli_connect("192.168.1.12","moodle_new2019","fa6MNExR4KehLj1A","moodle");


$activity_arr_assignment = array();
$final_arr = array();
$show_assign_popup = 0;
$sql_course = "SELECT course.id as courseid , course.shortname from `mdl_course` as course WHERE ( MONTH(FROM_UNIXTIME(course.startdate)) = '7' OR MONTH(FROM_UNIXTIME(course.startdate)) = '8' 
OR MONTH(FROM_UNIXTIME(course.startdate)) = '9') AND YEAR(FROM_UNIXTIME(course.startdate)) = '".@date('Y')."'";

$result = mysqli_query($conn, $sql_course);


while($row_result = mysqli_fetch_array($result))
{
	$sql_enrol = "SELECT uen.userid from mdl_user_enrolments as uen LEFT JOIN mdl_enrol as en ON en.id = uen.enrolid WHERE en.courseid = '".$row_result['courseid']."'";
	
	$user_enrol_result = mysqli_query($conn, $sql_enrol);
	
	while($row_enrol = mysqli_fetch_array($user_enrol_result))
	{
		$sql_role = "SELECT r.id, r.shortname 
        FROM mdl_role_assignments AS ra 
       LEFT JOIN mdl_user_enrolments AS ue ON ra.userid = ue.userid 
       LEFT JOIN mdl_role AS r ON ra.roleid = r.id 
       LEFT JOIN mdl_context AS c ON c.id = ra.contextid 
       LEFT JOIN mdl_enrol AS e ON e.courseid = c.instanceid AND ue.enrolid = e.id 
       WHERE ue.userid = '".$row_enrol['userid']."' AND e.courseid = '".$row_result['courseid']."'";
	   $sql_role_result = mysqli_query($conn, $sql_role);
	   $row_role = mysqli_fetch_array($sql_role_result);
	//  echo $row_role['shortname'].'-'.$row_enrol['userid'];
	 // echo '<hr>';
	   
		if($row_role['shortname'] == "student")
		{
			$sql_form_check_assign_popup = "SELECT a.id , a.name , a.grade , a.duedate , a.cutoffdate , b.status , b.`userid` , c.id as moduleid , 
			u.email, u.firstname, u.lastname, u.phone1, u.username
			, u.phone2
		FROM `mdl_assign` as a 
		LEFT JOIN `mdl_assign_submission` as b ON a.`id` = b.`assignment` 
		AND b.`status` IS NOT NULL AND b.`status` = 'submitted' AND b.`userid` = '".$row_enrol['userid']."'
		LEFT JOIN `mdl_course_modules` as c ON c.`course` = '".$row_result['courseid']."' AND c.`instance` = a.`id` AND c.`module` = '29'
		LEFT JOIN `mdl_user` as u ON u.`id` = '".$row_enrol['userid']."'
	 WHERE a.`course` = ".$row_result['courseid']." AND a.`grade` = 0 AND a.duedate != 0 AND FROM_UNIXTIME(a.duedate,'%Y-%m-%d %h %i %s') > NOW() 
	 AND FROM_UNIXTIME(a.duedate,'%Y-%m-%d %h %i %s')< (NOW() + INTERVAL 3 DAY) ";
	 
	 //echo '<br/>';
	 $sql_form_check_assign_popup_result = mysqli_query($conn, $sql_form_check_assign_popup);
	   
		//$form_check_assign_popup = $DB->get_records_sql($sql_form_check_assign_popup);
		

		if(mysqli_num_rows($sql_form_check_assign_popup_result)>0)
		{
			while ($row = mysqli_fetch_array($sql_form_check_assign_popup_result)) 
			{
				
				if(trim($row['status']) != 'submitted')
				{
					
					$show_assign_popup = 1;
					$activity_arr_assignment['id']=$row['id'];
					$activity_arr_assignment['moduleid']=$row['moduleid'];	
					$activity_arr_assignment['name']=$row['name'];	
					$activity_arr_assignment['userid']=$row['userid'];	
					$activity_arr_assignment['username']=$row['username'];	
					$activity_arr_assignment['firstname']=$row['firstname'];	
					$activity_arr_assignment['lastname']=$row['lastname'];	
					$activity_arr_assignment['phone1']=$row['phone1'];	
					$activity_arr_assignment['phone2']=$row['phone2'];	
					$activity_arr_assignment['email']=$row['email'];	
					
				}
				$final_arr[$row_result['courseid']][] = $activity_arr_assignment;
				unset($activity_arr_assignment);
			}
		}
		unset($form_check_assign_popup);
	}
		
	}
}

//echo '<pre>';
//print_r($final_arr);
//die;
date_default_timezone_set('Etc/UTC');


			

			$subject = "ACCIT | Notification for Online Activity Assignment - ".@date('Y');
			 $headers  = "From: ACCIT Admin <moodle.a@accit.nsw.edu.au>\n";
			$headers .= "Cc: ACCIT Admin <moodle.a@accit.nsw.edu.au>\n"; 
			$headers .= "X-Sender: ACCIT Admin <moodle.a@accit.nsw.edu.au>\n";
			$headers .= 'X-Mailer: PHP/' . phpversion();
			$headers .= "X-Priority: 1\n"; // Urgent message!
			$headers .= "Return-Path: moodle.a@accit.nsw.edu.au\n"; // Return path for errors
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=iso-8859-1\n";
			
		

	foreach($final_arr as $m=>$e)
	{
		foreach($e as $c=>$t)
		{
			if($t['email']!='')
			{
			$messageHtml = 'Dear '.$t['firstname'].' '.$t['lastname'].',';
			$messageHtml = $messageHtml.'<br/><br/>Please click the below link to access your Online Activity for this week - <br/>';
			$messageHtml = $messageHtml.'<br/><a href="http://localhost/accit-moodle/accit/mod/assign/view.php?id='.$t['moduleid'].'">'.$t['name'].'</a>';
			
			 

		@mail($t['email'],$subject,$messageHtml,$headers);


			unset($messageHtml);
			
			
			}
		}			 
		
	}
	die;
?>