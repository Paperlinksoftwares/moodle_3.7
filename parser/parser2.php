<?php
require_once('../config.php');
global $DB;
global $SESSION;
ini_set('error_reporting', E_ALL);
ini_set('display_errors', false);
//echo '<pre>';
//print_r($SESSION->compiled_array);
?>
<html>
<head>
<title>
Star Software Data Parser and Comparison with Moodle | ACCIT
</title>
</head>
<center>
<body><br/>
<a href="parser1.php" title="Back">Back</a><br/><br/>
Data for Term : <?php echo $SESSION->term; ?> | Year : <?php echo $SESSION->year; ?><br/><br/>
<table style="width:95%; border: 1px solid #ccc;" cellspacing="3" cellpadding="3">
<thead>
<tr style="background-color:#aa3311; color: white; font-weight: bold;">
<td>SL NO</td>
<td>Module</td>
<td>Course</td>
<td>Student</td>
<td>Excel Result</td>
<td>Moodle Result</td>
</tr>
</thead>
  <tbody>

<?php for($k=0;$k<count($SESSION->compiled_array['userid']);$k++)
{
		$sql_user = "SELECT `firstname` , `lastname` , `username` from {user} WHERE `id` = '".$SESSION->compiled_array['userid'][$k]."'";
		$user_array = $DB->get_record_sql($sql_user);
		if($SESSION->compiled_array['courseid'][$k]>0)
		{
			$sql_course = "SELECT `fullname` from {course} WHERE `id` = '".$SESSION->compiled_array['courseid'][$k]."'"; 
			$course_array = $DB->get_record_sql($sql_course);
			$course_fullname = $course_array->fullname;
		}
		else
		{
			$course_fullname="No Course / Unit Found under ".$SESSION->compiled_array['module'][$k]."
	for Term ".$SESSION->term." - ".$SESSION->year." !";
		}
	?>
	<tr>
	<td><?php echo ($k+1); ?></td>
<td><?php echo $SESSION->compiled_array['module'][$k]; ?></td>
<td><?php echo $course_fullname; ?></td>
<td><?php echo $user_array->firstname." ".$user_array->lastname." | ".$user_array->username; ?></td>
<td><?php echo $SESSION->compiled_array['excelresult'][$k]; ?></td>
<td><?php if($SESSION->compiled_array['moodleresult'][$k]=='' || ( $SESSION->compiled_array['moodleresult'][$k]!=$SESSION->compiled_array['excelresult'][$k]
&& strtolower($SESSION->compiled_array['excelresult'][$k])!='withdrawn/discontinued')) { echo '<font style="color:red;"><strong>ATTENTION!</strong></font>&nbsp;'.$SESSION->compiled_array['moodleresult'][$k]; } else { echo $SESSION->compiled_array['moodleresult'][$k]; } ?></td>
</tr>
<?php unset($course_fullname); } ?>
</tbody>
</table>
</center>
</body>
</html>