<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * The gradebook overview report
 *
 * @package   gradereport_overview
 * @copyright 2007 Nicolas Connault
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../config.php';
require_once $CFG->libdir.'/gradelib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/report/overview/lib.php';
require_once $CFG->dirroot.'/grade/report/user/lib.php';
$courseid = optional_param('id', SITEID, PARAM_INT);
$userid   = optional_param('userid', $USER->id, PARAM_INT);
global $USER;
global $CFG;
global $PAGE;
global $SESSION;


$sql_fetch_courses = "SELECT id, fullname, shortname from {course} order by `timecreated` DESC";
$courselist = $DB->get_records_sql($sql_fetch_courses);
$courselistname = '';
foreach($courselist as $courselist)
{
    // $course_arr[] = array('coursename'=>$list_all_courses->coursename,'courseid'=>$list_all_courses->courseid);
    $courselistname = $courselistname.'"'.$courselist->fullname."|".$courselist->id.'",';
}
$courselistname = "[".$courselistname."]";


$admins = get_admins();
$if_student = 1;
foreach($admins as $admin) {
    if ($USER->id == $admin->id) {
        $if_student = 0;
        break;
    }
    
}
if($_POST['studentid']=='' && isset($_POST['search']))
{
	?>
	<script>
	alert('please select the student from the list only!');
	window.location.href='studentgrades.php';
	</script>
	<?php
}
?>
<script>
    function generateReport(type)
    {
        if(type!='')
        {
            //window.location.href='studentgradereport.php?type='+type;
            //var win = window.open('studentgradereport.php?type='+type, '_blank');
            //win.focus();
            document.getElementById('type').value=type;
            document.getElementById('f1').submit();
        }
    }
    function sendSMS(phone,name)
    {
        
            document.getElementById('phone').value=phone;
            document.getElementById('name').value=name;
            document.getElementById('type').value=3;
            document.getElementById('f1').submit();
       
    }
    </script>
    <?php
$PAGE->set_url(new moodle_url('/mod/attendances/studentattendancesbycourse.php', array()));


////TABLE GRID FOR ATTENDNACES

?>
<!--===============================================================================================-->	
	
<!--===============================================================================================-->
	<!--===============================================================================================-->

<!--===============================================================================================-->
<!--===============================================================================================-->
		<link rel="stylesheet" type="text/css" href="tablegrid/css/main.css">
<?php

///END///


echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>';
echo '<style>
#customers {
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
    padding: 8px;
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
</style>';
echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.0.0/jquery.min.js"></script>';
echo '<style>

ul.pagination {
    display: inline-block;
    padding: 0;
    margin: 0;
}
ul.pagination li a.current_page {
    background-color: #4CAF50;
    color: white;
}
ul.pagination li.dot {
   
    color: #000;
}
ul.pagination li {display: inline;}

ul.pagination li a {
    color: black;
    float: left;
    padding: 8px 16px;
    text-decoration: none;
    border: 1px solid #ddd; /* Gray */
 margin: 0 4px; /* 0 is for top and bottom. Feel free to change it */
}
ul.pagination li a:hover:not(.active) {background-color: #ddd;}
.info-msg,
.success-msg,
.warning-msg,
.error-msg {
  margin: 10px 0;
  padding: 10px;
  border-radius: 3px 3px 3px 3px;
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
}

.autocomplete {
  /*the container must be positioned relative:*/
  position: relative;
  display: inline-block;
}

.autocomplete-items {
  position: absolute;
  border: 1px solid #d4d4d4;
  border-bottom: none;
  border-top: none;
  z-index: 99;
  /*position the autocomplete items to be the same width as the container:*/
  top: 100%;
  left: 0;
  right: 0;
}
.autocomplete-items div {
  padding: 10px;
  cursor: pointer;
  background-color: #cef9dd; 
  border-bottom: 1px solid #d4d4d4; 
}
.autocomplete-items div:hover {
  /*when hovering an item:*/
  background-color: #fff; 
}
.autocomplete-active {
  /*when navigating through the items using the arrow keys:*/
  background-color: DodgerBlue !important; 
  color: #ffffff; 
}
input[type=text] {
    width: 99%!important;
    padding: 4px 7px!important;
    margin: 3px 0!important;
    box-sizing: border-box!important;
    border: 1px solid #555!important;
    outline: none!important;
    height:35px!important;
}

input[type=text]:focus {
    background-color: #d9f1fc!important;
}
.accordion_container {
  width: 100%;
}

.accordion_head {
 background-color: #dae2ef;
    color: #020201;
    cursor: pointer;
    font-family: arial;
    font-size: 16px;
    margin: 0 0 1px 0;
    padding: 7px 11px;
    border-radius: 12px;
    border: 1px solid #6f7aca;
    /* padding: 20px; */
    width: 100%;
    height: 50px;
    /* padding-bottom: 11px; */
    padding-top: 11px;
    margin-top: 11px;
  padding-top: 15px;
}

.accordion_body {
  background: #fff;
}

.accordion_body p {
  padding: 18px 5px;
  margin: 0px;
}

.plusminus {
  float: right;
  font-size: 22px;
}
 .button-success,
        .button-error,
        .button-warning,
        .button-secondary {
            color: white!important;
            border-radius: 4px!important;
            text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2)!important;
            padding: 8px 13px!important;
            text-decoration: none!important;
        }

        .button-success {
            background: rgb(28, 184, 65)!important; 
        }
        .styled select {
   background: transparent;
   width: 150px;
   font-size: 16px;
   border: 1px solid #ccc;
   height: 34px; 
} 

.styled{
   float: right;
   width: 136px;
   height: 34px;
   border: 1px solid #111;
   border-radius: 3px;
   overflow: hidden;
   background: url(https://www.stackoverflow.com/favicon.ico) 96% / 20% no-repeat #ddd;
}
.dot1 {
  height: 17px;
  width: 17px;
  background-color: green;
  border-radius: 50%;
  display: inline-block;
}
.dot2 {
  height: 17px;
  width: 17px;
  background-color: red;
  border-radius: 50%;
  display: inline-block;
}
.dot0 {
  height: 17px;
  width: 17px;
  background-color: #ccc;
  border-radius: 50%;
  display: inline-block;
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
  left: 0px;
  right: 0;
  width: 100%;
}
.autocomplete-items div {
  padding: 10px;
  cursor: pointer;
  background-color:#85f28c; 
  border-bottom: 1px solid #d4d4d4; 
}
.autocomplete-items div:hover {
  /*when hovering an item:*/
  background-color: #aedeef; 
}
.autocomplete-active {
  /*when navigating through the items using the arrow keys:*/
  background-color: DodgerBlue !important; 
  color: #ffffff; 
}
#region-main
{
	overflow-x: visible!important;
}
.testbutton {
  font-family: arial;
  font-weight: bold;
  color: #FFFFFF !important;
  font-size: 16px;
  text-shadow: 1px 1px 1px #7CACDE;
  box-shadow: 1px 1px 1px #BEE2F9;
  padding: 10px 25px;
  -moz-border-radius: 10px;
  -webkit-border-radius: 10px;
  border-radius: 10px;
  border: 2px solid #3866A3;
  background: #63B8EE;
  background: linear-gradient(top,  #63B8EE,  #468CCF);
  background: -ms-linear-gradient(top,  #63B8EE,  #468CCF);
  background: -webkit-gradient(linear, left top, left bottom, from(#63B8EE), to(#468CCF));
  background: -moz-linear-gradient(top,  #63B8EE,  #468CCF);
}
.testbutton:hover {
  color: #fff !important;
  background: #468CCF;
  background: linear-gradient(top,  #468CCF,  #63B8EE);
  background: -ms-linear-gradient(top,  #468CCF,  #63B8EE);
  background: -webkit-gradient(linear, left top, left bottom, from(#468CCF), to(#63B8EE));
  background: -moz-linear-gradient(top,  #468CCF,  #63B8EE);
}
</style>';


require_login(null, false);


if(isset($_POST['search']) && $_POST['search'])
{   
   $userid = $_POST['studentid'];
   $userdetails = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
   $_SESSION['userdetails'] = $userdetails;
}
 else {
     $userdetails = $DB->get_record('user', array('id' => $USER->id), '*', MUST_EXIST);
   $_SESSION['userdetails'] = $userdetails;
    
}
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('nocourseid');
}
require_login(null, false);
$PAGE->set_course($course);

$context = context_course::instance($course->id);
$systemcontext = context_system::instance();
$personalcontext = null;
if(isset($_POST['search']) && $_POST['search'])
{   
   $userid = $_POST['studentid'];
   $userdetails = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
   $_SESSION['userdetails'] = $userdetails;
}
 else {
     $userdetails = $DB->get_record('user', array('id' => $USER->id), '*', MUST_EXIST);
   $_SESSION['userdetails'] = $userdetails;
    
}

// If we are accessing the page from a site context then ignore this check.
if ($courseid != SITEID) {
    require_capability('gradereport/overview:view', $context);
}

if (empty($userid)) {
    require_capability('moodle/grade:viewall', $context);

} else {
    if (!$DB->get_record('user', array('id'=>$userid, 'deleted'=>0)) or isguestuser($userid)) {
        print_error('invaliduserid');
    }
    $personalcontext = context_user::instance($userid);
}

if (isset($personalcontext) && $courseid == SITEID) {
    $PAGE->set_context($personalcontext);
} else {
    $PAGE->set_context($context);
}
if ($userid == $USER->id) {
    $settings = $PAGE->settingsnav->find('mygrades', null);
    $settings->make_active();
} else if ($courseid != SITEID && $userid) {
    // Show some other navbar thing.
    $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
    $PAGE->navigation->extend_for_user($user);
}

$access = false;
if (has_capability('moodle/grade:viewall', $systemcontext)) {
    // Ok - can view all course grades.
    $access = true;

} else if (has_capability('moodle/grade:viewall', $context)) {
    // Ok - can view any grades in context.
    $access = true;

} else if ($userid == $USER->id and ((has_capability('moodle/grade:view', $context) and $course->showgrades)
        || $courseid == SITEID)) {
    // Ok - can view own course grades.
    $access = true;

} else if (has_capability('moodle/grade:viewall', $personalcontext) and $course->showgrades) {
    // Ok - can view grades of this user - parent most probably.
    $access = true;
} else if (has_capability('moodle/user:viewuseractivitiesreport', $personalcontext) and $course->showgrades) {
    // Ok - can view grades of this user - parent most probably.
    $access = true;
}

if (!$access) {
    // no access to grades!
    print_error('nopermissiontoviewgrades', 'error',  $CFG->wwwroot.'/course/view.php?id='.$courseid);
}

/// return tracking object
$gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'overview', 'courseid'=>$course->id, 'userid'=>$userid));

/// last selected report session tracking
if (!isset($USER->grade_last_report)) {
    $USER->grade_last_report = array();
}
$USER->grade_last_report[$course->id] = 'overview';

// First make sure we have proper final grades.
grade_regrade_final_grades_if_required($course);




?>
 
<?php
    // Please note this would be extremely slow if we wanted to implement this properly for all teachers.
    $groupmode    = groups_get_course_groupmode($course);   // Groups are being used
    $currentgroup = groups_get_course_group($course, true);

    if (!$currentgroup) {      // To make some other functions work better later
        $currentgroup = NULL;
    }

    $isseparategroups = ($course->groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context));

    if ($isseparategroups and (!$currentgroup)) {
        // no separate group access, can view only self
        $userid = $USER->id;
        $user_selector = false;
    } else {
        $user_selector = true;
    }
 $report = new grade_report_overview($userid, $gpr, $context);
        
        print_grade_page_head($courseid, 'report', 'overview', get_string('pluginname', 'gradereport_overview') .
                ' - ' . fullname($report->user), false, false, false, null, null, $report->user);
        groups_print_course_menu($course, $gpr->get_return_url('studentgrades.php?id='.$courseid, array('userid'=>0)));

				$data_all = array();
				
				
				/// course data
				
				?>
				<form action="" name="f1" id="f1" method="POST" autocomplete="off">
           <table style="width:100%;"><tr><td style="width:15%;">Select Course</td>
     <td style="width:85%;">
     
        <div class="autocomplete" style="width:75%;">
            <input required id="coursename" type="text" name="coursename" placeholder="Type your Course Name" <?php if(isset($_GET['coursename']) && $_GET['coursename']!='' && isset($studentlist) && count($studentlist)>0) { ?> value="<?php echo $_GET['coursename']; ?>" <?php } else { ?> value="" <?php } ?> >
 <input id="course" type="hidden" name="course" <?php if(isset($_GET['courseid']) && $_GET['courseid']!='') { ?> value="<?php echo $_GET['courseid']; ?>" <?php } else { ?> value="" <?php } ?> />
  </div></td></tr></table>
    
	</form>
				<?php
				
				$course_info = $DB->get_records_sql("SELECT * FROM mdl_course WHERE `id` = '".$_GET['courseid']."'");
  
				//echo '<pre>';
				//print_r($course_extra);
				
				
				$sessions = $DB->get_records_sql("SELECT session.* , session.id as sessionid, attendance.id as aid, attendance.course as a_course 
				FROM mdl_attendance_sessions as session 
				LEFT JOIN mdl_attendance as attendance
ON session.attendanceid = attendance.id WHERE attendance.course = '".$_GET['courseid']."'" );
   
  // echo '<pre>';
	//			print_r($session_details);
				
   
   		
				
		$_SESSION['sessions'] = $sessions;
              //  echo '<pre>';
              //  print_r($data_all);
			  
			  if(count($_SESSION['sessions'])>0) {
                ?>
<br/>
<div>Attendances for &nbsp;<font style="color: black!important; font-weight:bold;"><a target="_blank" href="<?php echo $CFG->wwwroot; ?>/course/view.php?id=<?php echo $_GET['courseid']; ?>"><?php echo $_GET['coursename']; ?></a></font></div><br><br>
<div style="float: right;"><a href="#" 
	onclick="javascript: window.open('<?php echo $CFG->wwwroot; ?>/mod/attendance/studentattendancesbycourseexcel.php?courseid=<?php echo $_GET['courseid']; ?>&coursename=<?php echo $_GET['coursename']; ?>','_blank');" 
	class="testbutton">Download Excel</a></div><br/><br/>

<form action="studentattendancereport.php" name="f1" id="f1" method="post" target="_blank">
<div class="accordion_container">
    <?php  foreach($_SESSION['sessions'] as $key=>$val) {  
	
	?>
    
        <div class="accordion_head">
		<!--
		<input style="outline: 1px solid black; height: 15px; width:15px; border: none;" 
		type="checkbox" name="select_course[]" id="select_course[]" value="<?php //echo $val->sessionid; ?>" />&nbsp;&nbsp; -->
		<a href="#" target="_blank" ><?php echo gmdate("jS F Y", $val->sessdate); ?></a>&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #6aad1d; font-weight: bold;">
		<?php //echo $val[1]; ?></span><span class="plusminus">+</span></div>
 <div class="accordion_body" style="display: none;">
    
<?php	
	//NO LEFT JOIN ONLY TO STUDENTS SPECIFIC RECORDS- ADDING LEFT IN JOIN IN LOG OURS SHOW ENTIRE DATA with student as well.
       $session_details = $DB->get_records_sql('SELECT loghours.* , session.id as sessionid, session.duration as duration, users.* , loghours.hours as hours , 
	   loghours.absenthours as absenthours , loghours.comments as comments FROM mdl_attendance_log_hours as loghours LEFT JOIN mdl_attendance_sessions as session 
	   ON loghours.sessionid = session.id 
	   LEFT JOIN mdl_user as users ON loghours.studentid = users.id 
WHERE session.id = '.$val->sessionid);
   $session_time_max = $DB->get_record_sql('SELECT max(session.duration) as sess_duration FROM mdl_attendance_sessions as session JOIN mdl_attendance as attendance
ON session.attendanceid = attendance.id WHERE session.id = '.$val->sessionid);

$multiple_hours = ($session_time_max->sess_duration/3600);
$if_fraction=0;
if(fmod($multiple_hours, 1) !== 0.00){ $if_fraction=1; }
 
   $decimal = $multiple_hours - floor($multiple_hours);
	
	
	?>
	
	
	
	
	
        <div class="limiter">
		<div style="height:16px;"></div>
		<table style="width:200px!important;">
		<tr><td>&nbsp;</td><td><span class='dot1'></span></td><td>Present</td></tr>
		<tr><td colspan="3">&nbsp;</td></tr>
		<tr><td>&nbsp;</td><td><span class='dot2'></span></td><td>Absent</td></tr>
		<tr><td colspan="3">&nbsp;</td></tr>
		<tr><td>&nbsp;</td><td><span class='dot0'></span></td><td>Not yet marked</td></tr> 
		</table>
		
		<div class="container-table100">
			<div class="wrap-table100">
				
				<div class="table100 ver5 m-b-110">
					<table data-vertable="ver5" style="border: 1px solid #aaa!important;">
						<thead>
							<tr class="row100 head">
							<th class="column100 column0" data-column="column0"></th>
							<?php for($k=1;$k<(intval($multiple_hours)+1);$k++)
							{
								?>
								<th class="column100 column<?php echo $k; ?>" data-column="column<?php echo $k; ?>" style="text-align: center!important;">Hour <?php echo $k; ?></th>
							<?php }
							if($if_fraction==1) { ?>
								<th class="column100 column<?php echo $k; ?>" data-column="column<?php echo $k; ?>" style="text-align: center!important;">Hour<br/><?php echo $decimal; ?></th>
							<?php } ?>
							<th class="column100 column<?php echo ($k+1); ?>" data-column="column<?php echo ($k+1); ?>" style="text-align: center!important;">Absent Hours</th>
							<!--<th class="column100 column<?php echo ($k+1); ?>" data-column="column<?php //echo ($k+2); ?>" style="text-align: center!important;">Present Hours</th>-->
							
							<!--<th class="column100 column<?php echo ($k+1); ?>" data-column="column<?php //echo ($k+3); ?>" style="text-align: center!important;">Online Presence</th>-->
							<th class="column100 column<?php echo ($k+1); ?>" data-column="column<?php echo ($k+2); ?>" style="text-align: center!important;">Online Absent Hours</th>
							
							<!--<th class="column100 column<?php echo ($k+1); ?>" data-column="column<?php //echo ($k+5); ?>" style="text-align: center!important;">Online Presence Net</th>-->
							<th class="column100 column<?php echo ($k+1); ?>" data-column="column<?php echo ($k+3); ?>" style="text-align: center!important;">Star Entry</th>
							
							<th class="column100 column<?php echo ($k+2); ?>" data-column="column<?php echo ($k+4); ?>" style="text-align: center!important;">Remark</th>
							
							</tr>
						</thead>
						<tbody>
						<?php 
						if(count($session_details)>0)
						{
						foreach($session_details as $key=>$sess) { 
						
						$count_row = $DB->count_records_sql("SELECT COUNT(`id`) FROM {attendance_log_hours} WHERE `sessionid`='".$sess->sessionid."'");
						
						//online attendnace details 
						
						//$online_att = $DB->get_record_sql("select ar.course, ar.id as ar_id, aragg.duration as duration FROM mdl_attendanceregister as ar 
						//LEFT JOIN mdl_attendanceregister_aggregate as aragg ON aragg.register = ar.id 
						//WHERE aragg.userid = '".$sess->studentid."' AND ar.course = '".$_GET['courseid']."' ORDER BY aragg.id DESC LIMIT 0,1");
						
						//$dates_arr = _getWeekStartEndDatesByDate(@date("Y-m-d",$val->sessdate));
						
						
						$dates_arr = array();
						$dates_arr['start'] = strtotime('Last Monday', $val->sessdate);
						$dates_arr['end'] = strtotime('Next Sunday', $val->sessdate);
						//echo '<pre>';
						//print_r($dates_arr);
						//echo '<hr>';
						

						$online_att = $DB->get_record_sql("select ar.course, ar.id as ar_id, SUM(ass.duration) as duration FROM mdl_attendanceregister as ar 
						LEFT JOIN mdl_attendanceregister_session as ass ON ass.register = ar.id AND ass.userid = '".$sess->studentid."'
						WHERE ( ass.login BETWEEN '".$dates_arr['start']."' AND '".$dates_arr['end']."' ) 
						AND ( ass.logout BETWEEN '".$dates_arr['start']."' AND '".$dates_arr['end']."' ) AND ar.course = '".$_GET['courseid']."'");


						$online_att = (array)$online_att;
						if(@$online_att['duration']>0) { 
						$init = $online_att['duration'];
						$online_hours = floor($init / 3600);
						$online_minutes = floor(($init / 60) % 60);
						$online_seconds = $init % 60;
						$online_attendnace = $online_hours ." hours ".$online_minutes." Minutes ".$online_seconds." Seconds";
						$online_attendnace_net = $online_hours + round(($online_minutes/60),2);
						$online_absence_net = 5-$online_attendnace_net;
						if($online_absence_net<0)
						{
							$online_absence_net=0;
						}
						$online_diff = 18000 - $init;
						if($online_diff<0)
						{
							$online_absence = 0;
						}
						else
						{
							$online_absence = floor($online_diff / 3600) ." hours ".(floor(($online_diff / 60) % 60))." Minutes ".($online_diff % 60)." Seconds";
						
						}
						
						} else { $online_attendnace = 0; $online_absence = 5; $online_absence_net=5; }
						$duration_hours = ($sess->duration/3600); 
						$if_fraction_show = 0;
						if(fmod($duration_hours, 1) !== 0.00){ $if_fraction_show=1; }

   $decimal_show = $duration_hours - floor($duration_hours);
						?>
							<tr class="row100">
								<td class="column100 column1" data-column="column1" >
								<font style="color: #000!important; font-weight: bold!important;">
								
								<?php echo $sess->firstname." ".$sess->lastname; ?></font>

							</td>
							<?php 
							//echo '<pre>';
							//print_r($session_details);
							$abs_count=0;
							$pres_count = 0;
							for($k=1;$k<(intval($multiple_hours)+1);$k++)
							{
								$show='';
								$show_decimal='';
								$hour_arr = array();
								if($sess->hours!='')
								{
									$hour_arr=explode(",",@$sess->hours);
								}
								
								if($k<intval($duration_hours) || $k==intval($duration_hours))
								{
								if($count_row>0)
								{
								
								if(in_array($k,$hour_arr)==true)
								{
									$show ="<span class='dot1'></span>";
									$pres_count++;
								}
								else
								{
									$show ="<span class='dot2'></span>";
									$abs_count++;
								}
								}
								else
								{
									$show ="<span class='dot0'></span>";
								}
								}
								else
								{
									$show ="NA";
								}
								?>
								<td class="column100 column<?php echo $k; ?>" data-column="column<?php echo $k; ?>" style="text-align: center!important;"><?php echo $show; ?></td>
							<?php }
							if($if_fraction_show==1) { 
							if($count_row>0)
								{ if(in_array($decimal_show,$hour_arr)==true) { $show_decimal = "<span class='dot1'></span>" ; $pres_count = $pres_count+$decimal_show; } else { $show_decimal = "<span class='dot2'></span>"; $abs_count = ($abs_count+$decimal_show);  } } else { $show_decimal = "<span class='dot0'></span>";  }
							?>
								<td class="column100 column<?php echo $k; ?>" data-column="column<?php echo $k; ?>" style="text-align: center!important;"><?php echo $show_decimal; ?></td>
							<?php } ?>
							<td style="text-align: center!important;"><?php echo $abs_count; ?></td>
							<!--<td style="text-align: center!important;"><?php //echo $pres_count; ?></td>-->
							
							<!--<td style="text-align: center!important;"><?php //echo $online_attendnace; ?></td>-->
							<td style="text-align: center!important;"><?php echo $online_absence_net; ?></td>
							
							<!--<td style="text-align: center!important;"><?php //if($online_attendnace_net>0) { echo $online_attendnace_net; } else { echo '0'; } ?></td>-->
							<td style="text-align: center!important;"><?php echo ($abs_count+$online_absence_net); ?></td>
							
							<td><?php echo $sess->comments; ?></td>
								</tr>
						<?php 
						unset($online_att);
						unset($online_hours); 
						unset($online_minutes);
						unset($online_seconds);
						unset($init);
						unset($online_attendnace);
						unset($online_attendnace_net);
						unset($online_absence_net);
						} } else { ?>
						<tr><td>&nbsp;</td><td colspan="100">&nbsp;<div class="info-msg">No session hours are found!</font></div></td></tr>
						<tr><td style="background-color: white!important;"></td><td colspan="100" style="background-color: white!important;">&nbsp;</td></tr>
	
						<?php } ?>
						
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>



  
  
		
		
								
						
						
				</div>
			


			  <?php } ?>
     
  </div>

    <input type="hidden" name="type" id="type" value="" />
    <input type="hidden" name="phone" id="phone" value="" />
        <input type="hidden" name="name" id="name" value="" />
    </form>
<br/><br/>
<?php
                
            }
			
			
			  else { if(isset($_GET['courseid']) && $_GET['courseid']>0)
			  { ?><div style="height:9px;"></div>
<div class="info-msg">No attendances are found under <font style="color: black!important; font-weight:bold;"><?php echo $_GET['coursename']; ?>!</font></div>
		 <div style="height:3px;"></div>
			  <?php } }
?>
<script>
function autocomplete(inp, arr, type) { 


  /*the autocomplete function takes two arguments,
  the text field element and an array of possible autocompleted values:*/
  var currentFocus;
  /*execute a function when someone writes in the text field:*/
  inp.addEventListener("input", function(e) { 
      var a, b, i, val = this.value;
      /*close any already open lists of autocompleted values*/
      closeAllLists();
      if (!val) { return false;}
      currentFocus = -1;
      /*create a DIV element that will contain the items (values):*/
      a = document.createElement("DIV");
      a.setAttribute("id", this.id + "autocomplete-list");
      a.setAttribute("class", "autocomplete-items");
      /*append the DIV element as a child of the autocomplete container:*/
      this.parentNode.appendChild(a);
      /*for each item in the array...*/
     
      for (i = 0; i < arr.length; i++) {
var res = arr[i].split("|"); 
var idval = res[1]; 
var coursename = res[0];

//document.getElementById('coursename').value=coursename;
        /*check if the item starts with the same letters as the text field value:*/
        if (coursename.substr(0, val.length).toUpperCase() == val.toUpperCase()) {
          /*create a DIV element for each matching element:*/
          b = document.createElement("DIV");
          /*make the matching letters bold:*/
         
          b.innerHTML = "<strong>" + coursename.substr(0, val.length) + "</strong>";
          b.innerHTML += coursename.substr(val.length);
          /*insert a input field that will hold the current array item's value:*/
          b.innerHTML += "<input name='stuid' id='stuid' type='hidden' value='" + idval + "'>";
          b.innerHTML += "<input name='stuname' id='stuname' type='hidden' value='" + coursename + "'>";
          /*execute a function when someone clicks on the item value (DIV element):*/
              b.addEventListener("click", function(e) { 
              /*insert the value for the autocomplete text field:*/
             // inp.value = this.getElementsByTagName("input")[0].value; 
                if(type==1)
                {
                    window.location.href="studentattendancesbycourse.php?courseid="+this.getElementsByTagName("input")[0].value+'&coursename='+this.getElementsByTagName("input")[1].value;
                    inp.value = this.getElementsByTagName("input")[1].value; 
                    document.getElementById("course").value = this.getElementsByTagName("input")[0].value; 
                }
                else if(type==2)
                {
                    inp.value = this.getElementsByTagName("input")[1].value; 
                    document.getElementById("studentid").value = this.getElementsByTagName("input")[0].value; 
                }
                else
                {
                    inp.value = this.getElementsByTagName("input")[1].value; 
                    document.getElementById("studentuserid").value = this.getElementsByTagName("input")[0].value; 
                }
              /*close the list of autocompleted values,
              (or any other open lists of autocompleted values:*/
              closeAllLists();
          });
          a.appendChild(b);
        }
var res='';
var studentname = '';
var idval = '';
      }
  });
  /*execute a function presses a key on the keyboard:*/
  inp.addEventListener("keydown", function(e) {
      var x = document.getElementById(this.id + "autocomplete-list");
      if (x) x = x.getElementsByTagName("div");
      if (e.keyCode == 40) {
        /*If the arrow DOWN key is pressed,
        increase the currentFocus variable:*/
        currentFocus++;
        /*and and make the current item more visible:*/
        addActive(x);
      } else if (e.keyCode == 38) { //up
        /*If the arrow UP key is pressed,
        decrease the currentFocus variable:*/
        currentFocus--;
        /*and and make the current item more visible:*/
        addActive(x);
      } else if (e.keyCode == 13) {
        /*If the ENTER key is pressed, prevent the form from being submitted,*/
        e.preventDefault();
        if (currentFocus > -1) {
          /*and simulate a click on the "active" item:*/
          if (x) x[currentFocus].click();
        }
      }
  });
  function addActive(x) {
    /*a function to classify an item as "active":*/
    if (!x) return false;
    /*start by removing the "active" class on all items:*/
    removeActive(x);
    if (currentFocus >= x.length) currentFocus = 0;
    if (currentFocus < 0) currentFocus = (x.length - 1);
    /*add class "autocomplete-active":*/
    x[currentFocus].classList.add("autocomplete-active");
  }
  function removeActive(x) {
    /*a function to remove the "active" class from all autocomplete items:*/
    for (var i = 0; i < x.length; i++) {
      x[i].classList.remove("autocomplete-active");
    }
  }
  function closeAllLists(elmnt) {
    /*close all autocomplete lists in the document,
    except the one passed as an argument:*/
    var x = document.getElementsByClassName("autocomplete-items");
    for (var i = 0; i < x.length; i++) {
      if (elmnt != x[i] && elmnt != inp) {
      x[i].parentNode.removeChild(x[i]);
    }
  }
}
/*execute a function when someone clicks in the document:*/
document.addEventListener("click", function (e) {
    closeAllLists(e.target);
});
}
var final_arr = <?php print $courselistname; ?>;

//var course_arr = ["Afgha sdewwew 456456 nistan-20","Albania-21","Malaysia-33",];
//var arr = '';
//{{#course_arr}}
//var arr = arr+'"{{{coursename}}}*{{{courseid}}}"'+",";
//{{/course_arr}}
//var final_arr = "["+arr+"]";
//alert(final_arr);
autocomplete(document.getElementById("coursename"), final_arr,'1');

</script>
<script>
$(document).ready(function() {
  //toggle the component with class accordion_body
  $(".accordion_head").click(function() {
    if ($('.accordion_body').is(':visible')) {
      $(".accordion_body").slideUp(300);
      $(".plusminus").text('+');
    }
    if ($(this).next(".accordion_body").is(':visible')) {
      $(this).next(".accordion_body").slideUp(300);
      $(this).children(".plusminus").text('+');
    } else {
      $(this).next(".accordion_body").slideDown(300);
      $(this).children(".plusminus").text('-');
    }
  });
});
</script>

<?php
echo $OUTPUT->footer();