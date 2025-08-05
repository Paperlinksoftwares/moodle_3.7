<?php

$conn = mysqli_connect("192.168.1.12","moodle_new2019","fa6MNExR4KehLj1A","moodle");

$final_result = '';

$now = new DateTime(); //now
$t1 = $now->format('Y-m-d H:i:s');

$hours = 3; // hours amount (integer) you want to add
$modified = (clone $now)->sub(new DateInterval("PT{$hours}H")); // use clone to avoid modification of $now object
$t2 = $modified->format('Y-m-d H:i:s');

$t1_obj = new DateTime($t1);
$t2_obj = new DateTime($t2);

$to_timestamp = $t1_obj->getTimestamp();
$from_timestamp = $t2_obj->getTimestamp();


$sql_grades = "SELECT a.id , a.grader, a.grade, a.assignment, a.userid, b.course , b.name as assignment_name , c.fullname , u.email, 
u.phone1, u.phone2 , u.firstname , u.lastname FROM `mdl_assign_grades` as a 
LEFT JOIN `mdl_user` as u ON a.userid = u.id
LEFT JOIN `mdl_assign` as b ON a.assignment = b.id
LEFT JOIN `mdl_course` as c ON b.course = c.id
 WHERE a.`timemodified` >= '".$from_timestamp."' AND a.`timemodified` <= '".$to_timestamp."' AND a.`grader` > 0";

$result = mysqli_query($conn, $sql_grades);

$subject = "ACCIT | Notification of Grading - ".@date('Y');
			 $headers  = "From: ACCIT Admin <moodle.a@accit.nsw.edu.au>\n";
			$headers .= "Cc: ACCIT Admin <moodle.a@accit.nsw.edu.au>\n"; 
			$headers .= "X-Sender: ACCIT Admin <moodle.a@accit.nsw.edu.au>\n";
			$headers .= 'X-Mailer: PHP/' . phpversion();
			$headers .= "X-Priority: 1\n"; // Urgent message!
			$headers .= "Return-Path: moodle.a@accit.nsw.edu.au\n"; // Return path for errors
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=iso-8859-1\n";
			
$msg_include = '';
while($row_result = mysqli_fetch_assoc($result))
{
	if($row_result['email']!='')
			{
				if(intval($row_result['grade'])==3)
				{
					$final_result = "Outstanding";
					$msg_include = "<strong>Congrats!</strong>";
				}
				else if(intval($row_result['grade'])==2)
				{
					$final_result = "Satisfactory";
					$msg_include = "<strong>Well done!</strong>";
				}
				else if(intval($row_result['grade'])==1)
				{
					$final_result = "Not Satisfactory";
					$msg_include = "<strong>Sorry!</strong>";
				}
				else
				{
					$final_result = "Reset/No Grade";
					$msg_include = "<strong>Sorry!</strong>";
				}
				
			$messageHtml = 'Dear '.$row_result['firstname'].' '.$row_result['lastname'].',';
			
			$messageHtml = $messageHtml.'<br/><br/>'.$msg_include.' Your submission for the assignment <a href="http://localhost/accit-moodle/accit/mod/quiz/view.php?id='.$row_result['id'].'">'.$row_result['assignment_name'].'</a> has been graded as <strong>'.$final_result.'</strong>';
			$messageHtml = $messageHtml.'<br/><br/>Thanks</br/>Academic Team<br/>ACCIT<br/>Sydney, Australia';
			 

		@mail($row_result['email'],$subject,$messageHtml,$headers);


			unset($messageHtml);
			unset($final_result);
			
			unset($msg_include);
			}
}
die;
?>