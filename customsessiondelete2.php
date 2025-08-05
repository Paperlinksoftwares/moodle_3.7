<?php
require_once('config.php');

global $DB;
global $CFG;

function secondsToTime($seconds) {

  // extract hours
  $hours = floor($seconds / (60 * 60));

  // extract minutes
  $divisor_for_minutes = $seconds % (60 * 60);
  $minutes = floor($divisor_for_minutes / 60);

  // extract the remaining seconds
  $divisor_for_seconds = $divisor_for_minutes % 60;
  $seconds = ceil($divisor_for_seconds);

  // return the final array
  $timearr = array(
      "h" => (int) $hours,
      "m" => (int) $minutes,
      "s" => (int) $seconds,
   );

  return $timearr;
}
if(isset($_GET['courseid']) && $_GET['courseid']!='' && isset($_GET['userid']) && $_GET['userid']!='' && isset($_GET['registerid']) && $_GET['registerid']!='')
{
    $sql_fetch_session = "SELECT * FROM {attendanceregister_session} WHERE `register` = '".$_GET['registerid']."' AND `userid` = '".$_GET['userid']."' ORDER BY `login` DESC";
    
    $session_all = $DB->get_records_sql($sql_fetch_session);
	
    
}
if($_GET['registerid']=='')
{
	       $urltogo= $CFG->wwwroot.'/customsessiondelete.php?courseid='.$_GET['courseid'];
       
	?>
	<script> 
	alert('No Data Found!');
	window.location.href='<?php echo $urltogo; ?>'; </script>
	<?php
}
//Session update

if(isset($_GET['action']) && $_GET['action']=='delete')
{
	
	$registerid = $_GET['registerid'];
	$courseid = $_GET['courseid'];
	$userid = $_GET['userid'];
	$delid = $_GET['delid'];
	$duration_now = $_GET['duration_now'];
	
	$u1 = $DB->get_records_sql("SELECT `duration` FROM {attendanceregister_aggregate} WHERE register = '".$registerid."' AND userid = '".$userid."' limit 0 , 1");
    foreach($u1 as  $u1)
    {
        $arr[] = $u1->duration;
    }
    $total_duration = @$arr[0]-$duration_now;
	
	if($total_duration>0)
	{
		$u3 = $DB->execute("UPDATE {attendanceregister_aggregate} SET `duration` = '".$total_duration."' WHERE `register` = '".$registerid."' AND `userid` = '".$userid."'");
	}
	else
	{
  
		$del1 = $DB->execute("DELETE FROM {attendanceregister_aggregate} WHERE `register` = '".$registerid."' AND `userid` = '".$userid."'");
	}
	
    $del2 = $DB->execute("DELETE FROM {attendanceregister_session} WHERE `register` = '".$registerid."' AND `userid` = '".$userid."' AND `id` = '".$delid."'"); 
                
   if($del2)
   {
       $urltogo= $CFG->wwwroot.'/customsessiondelete2.php?update=1&courseid='.$courseid.'&userid='.$userid.'&registerid='.$registerid;
       ?>
        <div style="padding-left: 412px; padding-top: 181px;"><img src='<?php echo $CFG->wwwroot; ?>/mod/attendanceregister/images/loader.gif' border='0'></div>
        <script> window.location.href='<?php echo $urltogo; ?>'; </script>
       <?php
       exit();        
   }
   
else
{
    // Redirection after failed execution or unsuccessful validation attempt
      $urltogo= $CFG->wwwroot.'/customsessiondelete2.php?update=2&courseid='.$courseid.'&userid='.$userid.'&registerid='.$registerid;
     
    ?>
<div style="padding-left: 412px; padding-top: 181px;"><img src='<?php echo $CFG->wwwroot; ?>/mod/attendanceregister/images/loader.gif' border='0'></div>
<script> window.location.href='<?php echo $urltogo; ?>'; </script>
<?php
exit();
}
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
<style>
* {
    box-sizing: border-box;
}

input[type=text], select, textarea {
    width: 65%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    resize: vertical;
}
input[type=number] {
    width: 65%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    resize: vertical;
}
label {
    padding: 12px 12px 12px 0;
    display: inline-block;
    font-family: sans-serif;
}

input[type=submit] {
    background-color: #4CAF50;
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    float: none;
    margin-top: 15px;
}

input[type=submit]:hover {
    background-color: #45a049;
}

.container {
    border-radius: 5px;
    background-color: #f2f2f2;
    padding: 20px;
}

.col-25 {
    float: left;
    width: 25%;
    margin-top: 6px;
}

.col-75 {
    float: left;
    width: 75%;
    margin-top: 6px;
}

/* Clear floats after the columns */
.row:after {
    content: "";
    display: table;
    clear: both;
}

/* Responsive layout - when the screen is less than 600px wide, make the two columns stack on top of each other instead of next to each other */
@media screen and (max-width: 600px) {
    .col-25, .col-75, input[type=submit] {
        width: 100%;
        margin-top: 0;
    }
}
.autocomplete {
  /*the container must be positioned relative:*/
  position: relative;
  display: inline-block;
}
.autocomplete-items {
  
  border: 1px solid #d4d4d4;
  border-bottom: none;
  border-top: none;
  z-index: 99;
  /*position the autocomplete items to be the same width as the container:*/
  top: 100%;
  left: 110px;
  right: 0;
  width: 73%;
}
.autocomplete-items div {
  padding: 10px;
  cursor: pointer;
  background-color:#c4edd4; 
  border-bottom: 1px solid #d4d4d4; 
}
.autocomplete-items div:hover {
  /*when hovering an item:*/
  background-color: #e9e9e9; 
}
.autocomplete-active {
  /*when navigating through the items using the arrow keys:*/
  background-color: DodgerBlue !important; 
  color: #ffffff; 
}
#customers {
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
    border-collapse: collapse;
    width: 100%;
}
#customersinfo {
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
    border-collapse: collapse;
    width: 100%;
}
#info {
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
    border-collapse: collapse;
    width: 60%;
}

#customers td, #customers th {
    border: 1px solid #aaa;
    padding: 21px;
}
#customersinfo td, #customersinfo th {
    border: 1px solid #aaa;
    padding: 21px;
}
#info td, #info th {
    border: 1px solid #aaa;
    padding: 8px;
}

#customers tr:nth-child(even){background-color: #f2f2f2;}

#customers tr:hover {background-color: #ddd;}

#customers th {
    padding-top: 12px;
    padding-bottom: 12px;
    text-align: left;
    background-color: #193779;
    color: white;
}
#info tr:nth-child(even){background-color: #f2f2f2;}

#info tr:hover {background-color: #ddd;}

#info th {
    padding-top: 12px;
    padding-bottom: 12px;
    text-align: left;
    background-color: #4CAF50;
    color: white;
}
</style>
  <title>Custom Session updates of Students</title>
  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <link rel="stylesheet" media="all" type="text/css" href="http://code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css" />
<link rel="stylesheet" media="all" type="text/css" href="http://moodle2.accit.nsw.edu.au:8080/admin/tool/timestamp/templates/css/jquery-ui-timepicker-addon.css" />

 <script>

function doDelete(delid,courseid,userid,registerid,duration_now)
{
	var p = confirm("Are you sure you want to delete?");
	if(p==true)
	{
		window.location.href='customsessiondelete2.php?action=delete&courseid='+courseid+'&userid='+userid+'&registerid='+registerid+'&delid='+delid+'&duration_now='+duration_now;
   
	}
	else
	{
		return false;
	}
}
</script>
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
 


 <style>
     .info-msg,
.success-msg,
.warning-msg,
.error-msg {
  margin: 10px 0;
  padding: 10px;
  border-radius: 3px 3px 3px 3px;
  font-size: 14px;
}
.info-msg {
  color: #059;
  background-color: #BEF;
}
.success-msg {
  color: #270;
  background-color: #DFF2BF;
}
.warning-msg {
  color: #9F6000;
  background-color: #FEEFB3;
}
.error-msg {
  color: #D8000C;
  background-color: #FFBABA;
  font-size: 14px;
}

.btn {
  background: #3498db;
  background-image: -webkit-linear-gradient(top, #3498db, #2980b9);
  background-image: -moz-linear-gradient(top, #3498db, #2980b9);
  background-image: -ms-linear-gradient(top, #3498db, #2980b9);
  background-image: -o-linear-gradient(top, #3498db, #2980b9);
  background-image: linear-gradient(to bottom, #3498db, #2980b9);
  -webkit-border-radius: 7;
  -moz-border-radius: 7;
  border-radius: 7px;
  font-family: Arial;
  color: #ffffff;
  font-size: 17px;
  padding: 8px 15px 9px 16px;
  text-decoration: none;
}

.btn:hover {
  background: #3cb0fd;
  background-image: -webkit-linear-gradient(top, #3cb0fd, #3498db);
  background-image: -moz-linear-gradient(top, #3cb0fd, #3498db);
  background-image: -ms-linear-gradient(top, #3cb0fd, #3498db);
  background-image: -o-linear-gradient(top, #3cb0fd, #3498db);
  background-image: linear-gradient(to bottom, #3cb0fd, #3498db);
  text-decoration: none;
}
 </style>
 
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body >
<center><br/><div><a href="<?php echo $CFG->wwwroot; ?>/customsessiondelete.php?courseid=<?php echo $_GET['courseid']; ?>" style="font-family:sans-serif; text-decoration: underline;">Go Back</a></div>
      <br/>  <?php
        if(isset($_GET['update'])) {
if($_GET['update']==1)
   { 
       echo '<div class="success-msg">
  <i class="fa fa-check"></i>
  The data has been successfully deleted!
   </div>'; }
   else if($_GET['update']==2)
       {
       echo '<div class="error-msg">
  <i class="fa fa-times-circle"></i>
  Some error!
</div>';
   }
   else if($_GET['update']==3)
       {
       echo '<div class="error-msg">
  <i class="fa fa-times-circle"></i>
  No student selected! Please select student.
</div>';
   }
   else 
       {
       echo '<div class="error-msg">
  <i class="fa fa-times-circle"></i>
  Some error occured or no student selected! Please try again later or contact to a technical assistant.
</div>';
   }
}
        ?>
            <div class="container">
  <?php

$sql_fetch_courses = "SELECT id, fullname, shortname from {course} WHERE `id` = '".$_GET['courseid']."'";
$courselist = $DB->get_record_sql($sql_fetch_courses);

$sql_fetch_student = "SELECT id, firstname, lastname from {user} WHERE `id` = '".$_GET['userid']."'";
$studentlist = $DB->get_record_sql($sql_fetch_student);

$sql_fetch_session_total = "SELECT duration from {attendanceregister_aggregate} WHERE `userid` = '".$_GET['userid']."' AND `register` = '".$_GET['registerid']."' ORDER BY `id` DESC LIMIT 0,1";
$sessionlist = $DB->get_record_sql($sql_fetch_session_total);
  
  ?>
    <table style="width:70%!important; background-color: #9bf7c1!important;" cellspacing="5" cellpadding="5" id="customersinfo">
	
	<tr>
	<td>Course</td>
	<td><a href="<?php echo $CFG->wwwroot; ?>/course/view.php?id=<?php echo $_GET['courseid']; ?>" target="_blank"><?php echo $courselist->fullname; ?></a></td>
	</tr>
	<tr>
	<td>Student Name</td>
	<td><a href="<?php echo $CFG->wwwroot; ?>/user/profile.php?id=<?php echo $studentlist->id; ?>" target="_blank"><?php echo $studentlist->firstname." ".$studentlist->lastname; ?></a></td>
	</tr>
	<tr>
	<td>Total Offline/Online session </td>
	<td><?php $duration_total_session = secondsToTime($sessionlist->duration); echo $duration_total_session['h']." Hours ".$duration_total_session['m']." Minutes ".$duration_total_session['s']." Seconds "; ?></td>
	</tr>
	</table>
  
	 <div>&nbsp;</div>
	  <div>&nbsp;</div>
       <?php if(count($session_all)>0) {  ?>
    <div class="row">
      
      <div >
	  
          <table id="customers" style="width: 81%!important;" cellspacing="19" cellpadding="11">
              <tr>
			  <th>Sl.No.</th>
			  <th>Start</th>
			  <th>End</th>
			  <th>Online/Offline</th>
			  <th>Action</th>
			  </tr>
                                    <?php 
									
									$cc=1;
									foreach ($session_all as $session_all) 
									{
											$duration = secondsToTime($session_all->duration);
									?>
              <tr>
			  <td><?php echo $cc; ?></td>
			  <td><?php echo date("F j, Y, g:i a",$session_all->login); ?></td>
			  <td><?php echo date("F j, Y, g:i a",$session_all->logout); ?></td>
			  <td><?php echo $duration['h']." Hours ".$duration['m']." Minutes ".$duration['s']." Seconds "; ?></td>
			  <td><a 
			  onclick="javascript: doDelete(<?php echo $session_all->id; ?>,<?php echo $_GET['courseid']; ?>,<?php echo $_GET['userid']; ?>,<?php echo $_GET['registerid']; ?>,<?php echo $session_all->duration; ?>);" href="#" class="btn">Delete</a></td>
			  </tr>
											<?php $cc++; unset($duration); } ?>
              </table>    
      </div>
    </div>
       <?php } else { 
	     echo '<div class="error-msg">
  <i class="fa fa-times-circle"></i>
  No Online session found!
</div>';
      
    
	    } ?>
    </div>
   
 
</div>
    </center>
  </body>
</html>