<?php

$conn = mysqli_connect("192.168.1.12","moodle_new2019","fa6MNExR4KehLj1A","moodle");

function SendMessage($host, $port, $userName, $password, $number, $message)
{

    /* Create the HTTP API query string */
    $query = 'http://'.$host.':'.$port;
    $query .= '/http/send-message/';
    $query .= '?username='.urlencode($userName);
    $query .= '&password='.urlencode($password);
    $query .= '&to='.urlencode($number);
    $query .= '&message='.urlencode($message);
      
    /* Send the HTTP API request and return the response */
    return file_get_contents($query);  
}
 

$activity_arr_forum = array();
$final_arr = array();

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
			$sql_form_check_forum_popup = "SELECT a.id , a.name , a.duedate , a.cutoffdate , b.firstpost, b.userid , c.id as moduleid ,
			u.email, u.firstname, u.lastname, u.phone1, u.username, u.phone2
			FROM `mdl_forum` as a 
	LEFT JOIN `mdl_forum_discussions` as b
	ON a.`id` = b.`forum` 
	LEFT JOIN `mdl_course_modules` as c ON c.`course` = '".$row_result['courseid']."' AND c.`instance` = a.`id` AND c.`module` = '5'
	LEFT JOIN `mdl_user` as u ON u.`id` = '".$row_enrol['userid']."'
	WHERE a.`course` = ".$row_result['courseid']." AND a.`type` = 'general' AND a.duedate != 0 
	AND FROM_UNIXTIME(a.duedate,'%Y-%m-%d %h %i %s') > NOW() 
    AND FROM_UNIXTIME(a.duedate,'%Y-%m-%d %h %i %s')< (NOW() + INTERVAL 2 DAY) AND b.`firstpost` IS NULL AND ( b.`userid` = '".$row_enrol['userid']."' OR b.`userid` IS NULL)";
	 
	 
	 $form_check_forum_popup_result = mysqli_query($conn, $sql_form_check_forum_popup);
	   
		//$form_check_assign_popup = $DB->get_records_sql($sql_form_check_assign_popup);
		

		if(mysqli_num_rows($form_check_forum_popup_result)>0)
		{
			while ($row = mysqli_fetch_array($form_check_forum_popup_result)) 
			{
				
					
					
					$activity_arr_forum['id']=$row['id'];
					$activity_arr_forum['moduleid']=$row['moduleid'];	
					$activity_arr_forum['name']=$row['name'];	
					$activity_arr_forum['userid']=$row['userid'];	
					$activity_arr_forum['username']=$row['username'];	
					$activity_arr_forum['firstname']=$row['firstname'];	
					$activity_arr_forum['lastname']=$row['lastname'];	
					$activity_arr_forum['phone1']=$row['phone1'];	
					$activity_arr_forum['phone2']=$row['phone2'];	
					$activity_arr_forum['email']=$row['email'];	
					
				
				$final_arr[$row_result['courseid']][] = $activity_arr_forum;
				unset($activity_arr_forum);
			}
		}
		unset($sql_form_check_forum_popup);
	}
		
	}
}

//echo '<pre>';

//print_r($final_arr);
//die;


$host = 'sms.accit.nsw.edu.au';
    $port = '9001';
    $userName = 'admin';
    $password = 'admin@123';
			
			
		

	foreach($final_arr as $m=>$e)
	{
		foreach($e as $c=>$t)
		{
			if($t['phone1']!='' || $t['phone2']!='')
			{
				if($t['phone1']!='')
				{
					$number = $t['phone1'];
				}
				else if($t['phone2']!='')
				{
					$number = $t['phone2'];
				}
				else
				{
					$number = '';
				}
				if($number!= '')
				{
			$message = 'Dear '.$t['firstname'].' '.$t['lastname'].',';
			$message = $message.'please click the link to access your Online Forum for this week - ';
			$message = $message.'http://localhost/accit-moodle/accit/mod/forum/view.php?id='.$t['moduleid'];
			
			 

		SendMessage($host, $port, $userName, $password, $number, $message);
				}


			unset($message);
			unset($number);
			
			
			}
		}			 
		
	}
	die;
?>