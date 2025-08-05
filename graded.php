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

require_once '../../../config.php';
require_once $CFG->libdir.'/gradelib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/report/overview/lib.php';
require_once $CFG->dirroot.'/grade/report/user/lib.php';
$courseid = optional_param('id', SITEID, PARAM_INT);
$userid   = optional_param('userid', $USER->id, PARAM_INT);
require_once('pagination.php');
global $USER;
global $CFG;
global $SESSION;

/*if(count($SESSION->arr)==0)
{
$arr = array();
	$SESSION->arr = $arr;
}
*/
$admins = get_admins();
$if_student = 1;
foreach($admins as $admin) {
    if ($USER->id == $admin->id) {
        $if_student = 0;
        break;
    }
    
}
if(!isset($_POST['search_by_unit']) || ($_POST['search_by_unit']!=1 && isset($_POST['search_by_unit'])))
{
if(isset($_POST['year']))
{
	$year = $_POST['year'];
	$clause1 = " AND YEAR(FROM_UNIXTIME(mc.startdate)) = '".$year."' ";
}
else
{
	$year = @date('Y');
	$clause1 = " AND YEAR(FROM_UNIXTIME(mc.startdate)) = '".$year."' ";
}

if(isset($_POST['studentid']) && $_POST['studentid']>0)
{
	$stuidpost = $_POST['studentid'];
	
}
else
{
	$stuidpost = '';
	
	
}

//DATE RANGE

if(isset($_POST['from_date']) && $_POST['from_date']!='')
{
	$from_date = $_POST['from_date'];
	
}
else
{
	$from_date = '';
}

if(isset($_POST['to_date']) && $_POST['to_date']!='')
{
	$to_date = $_POST['to_date'];
	
}
else
{
	$to_date = '';
}

if($from_date!='' && $to_date!='')
{
	$clause1  = " AND YEAR(FROM_UNIXTIME(mc.startdate))!= '' ";
	$clause2 = " AND ag.timecreated BETWEEN UNIX_TIMESTAMP('".$from_date."') AND UNIX_TIMESTAMP('".$to_date."') ";
}
else
{
	$clause1 = " AND YEAR(FROM_UNIXTIME(mc.startdate)) = '".$year."' ";
	$clause2 = " AND ag.timecreated != '' ";
}
///END

if(isset($_POST['date_sort']))
{
	
	$date_sort = $_POST['date_sort'];
	
}
else
{
	$date_sort = "DESC";
}

if(isset($_POST['assnum']))
{
	$assnum = $_POST['assnum'];
}
else
{
	$assnum = "Assessment 1";
}
$search_by_unit='';
}


if(isset($_POST['search_by_unit']) && $_POST['search_by_unit']==1)
{
	$search_by_unit = $_POST['search_by_unit'];
	if(isset($_POST['year']))
	{
		$year = $_POST['year'];
	}
	else
	{
		$year = @date('Y');
	}
	
	if(isset($_POST['stuid']) && $_POST['stuid']>0)
	{
		$stuidpost = $_POST['stuid'];
		$clause4 = ' AND z.userid = '.$stuidpost;
	}
	else
	{
		$stuidpost = '';
		$clause4 = ' AND z.userid > 0';
	}
	
	if(isset($_POST['date_sort']))
	{
	
	$date_sort = $_POST['date_sort'];
	
	}
	else
	{
		$date_sort = "DESC";
	}
	if(isset($_POST['search_unit']))
	{
		$search_unit = $_POST['search_unit'];
	}
	else
	{
		$search_unit = 'CIT02';
	}
	$clause3 = " AND mc.shortname LIKE '%".$search_unit."%' ";
	$clause2 = " AND mc.fullname !='' ";
	$clause1 = " AND z.status !='' ";
}


if(isset($_REQUEST["page"]))
$page = (int)$_REQUEST["page"];
else
$page = 1;
$setLimit = 10;
$pageLimit = ($page * $setLimit) - $setLimit;


if($USER->id==74)
{
	$if_student = 0;
}

$scv_cat_array=array('138','137','114','128','117','115','113');
$supervised_cat_array=array('116','112','104','98','97');

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('nocourseid');
}
require_login(null, false);
$PAGE->set_course($course);

$context = context_course::instance($course->id);
$systemcontext = context_system::instance();
$personalcontext = null;
if(isset($_POST['search']) && $_POST['studentid']>0)
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


echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>';
echo '<style>
#myProgress {
  width: 100%;
  background-color: #ddd;
}

#myBar {

  height: 30px;
  background-color: #4CAF50;
  text-align: center;
  padding-top:5px;
}
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

/* The Modal (background) */
.modal {
  display: none; /* Hidden by default */
  position: fixed; /* Stay in place */
  z-index: 1; /* Sit on top */
  padding-top: 100px; /* Location of the box */
  left: 0;
  top: 0;
  width: 100%; /* Full width */
  height: 100%; /* Full height */
  overflow: auto; /* Enable scroll if needed */
  background-color: rgb(0,0,0); /* Fallback color */
  background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
}

/* Modal Content */
.modal-content {
  background-color: #fefefe;
  margin: auto;
  padding: 20px;
  border: 1px solid #888;
  
}

/* The Close Button */
.close {
  color: #aaaaaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
}

.close:hover,
.close:focus {
  color: #000;
  text-decoration: none;
  cursor: pointer;
}
</style>';


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
    width: 100%!important;
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
  height: auto;
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
}


</style>';




 $report = new grade_report_overview($userid, $gpr, $context);
        
        print_grade_page_head($courseid, 'report', 'overview', get_string('pluginname', 'gradereport_overview') .
                ' - ' . fullname($report->user), false, false, false, null, null, $report->user);
        groups_print_course_menu($course, $gpr->get_return_url('studentgrades.php?id='.$courseid, array('userid'=>0)));



	
		 //ASSESSMENT NUMBER
		 if($stuidpost>0)
		 {
		 $sql_c = "SELECT ma.id as assignmentid , mc.id as courseid , ma.name as assessmentname, ma.cutoffdate, ma.duedate , 
		 mc.shortname FROM {course} as mc 
		 LEFT JOIN {assign} as ma ON ma.course = mc.id 
		 WHERE ma.name LIKE '%".strtolower($assnum)."%' 
		 ".$clause1." GROUP BY mc.shortname";
		 
		 $list = $DB->get_records_sql($sql_c);
		 $list_count = array();
		 
		 foreach($list as $key=>$val)
		 {
			 $sql_grade_count = "SELECT COUNT(ag.`id`) as gradecount FROM {assign_grades} as ag
			 
			 WHERE ag.`assignment` = '".$val->assignmentid."' ".$clause2." AND ag.`grader` = '".$stuidpost."'";
			 $record_count = $DB->get_record_sql($sql_grade_count);
			 
			 
			 $list_count[$val->assignmentid]['courseid'] = $val->courseid;
			 $list_count[$val->assignmentid]['courseshortname'] = $val->shortname;
			 $list_count[$val->assignmentid]['assessmentid'] = $val->assignmentid;
			 $list_count[$val->assignmentid]['assessmentname'] = $val->assessmentname;
			 $list_count[$val->assignmentid]['assessmentduedate'] = $val->duedate;
			 $list_count[$val->assignmentid]['assessmentcutoffdate'] = $val->cutoffdate;
			 $list_count[$val->assignmentid]['totalgraded'] = $record_count->gradecount;
			 
			 if($record_count->gradecount>0)
			 {
				 $sql_grade_details = "SELECT * FROM {assign_grades} as ag			 
				 WHERE ag.`assignment` = '".$val->assignmentid."' ".$clause2." AND ag.`grader` = '".$stuidpost."'";
				 $record_grade_details = $DB->get_records_sql($sql_grade_details);
				 foreach($record_grade_details as $key11=>$val11)
				 {
					 $list_count[$val->assignmentid]['gradingdate'][] = $val11->timecreated;
					 
					 
					 $sql_submission_details = "SELECT * FROM {assign_submission} as asub WHERE asub.`assignment` = '".$val11->assignment."'	 
					 AND asub.`userid` = '".$val11->userid."'";
					 $submission_details = $DB->get_record_sql($sql_submission_details);
					 if(count($submission_details)>0)
					 {
						 $list_count[$val->assignmentid]['submissiondetails'][$val11->userid]['status'] = $submission_details->status;
						 $list_count[$val->assignmentid]['submissiondetails'][$val11->userid]['timecreated'] = $submission_details->timecreated;					 						 
					 }
					 else
					 {
						 $list_count[$val->assignmentid]['submissiondetails'][$val11->userid]['status'] = '';
						 $list_count[$val->assignmentid]['submissiondetails'][$val11->userid]['timecreated'] = '';					 						 
					 }
				 }				 				 
			 }		 
		 }
		 }
		 echo '<pre>';
		 print_r($list_count);
	
	
	$sql_trainers_list = "SELECT DISTINCT u.id AS userid, u.firstname as fname , u.lastname as lname 
FROM mdl_user u
JOIN mdl_user_enrolments ue ON ue.userid = u.id
JOIN mdl_enrol e ON e.id = ue.enrolid
JOIN mdl_role_assignments ra ON ra.userid = u.id
JOIN mdl_context ct ON ct.id = ra.contextid AND ct.contextlevel = 50
JOIN mdl_role r ON r.id = ra.roleid WHERE r.shortname != 'student'";
$trainers_list = $DB->get_records_sql($sql_trainers_list);

$arr_check_tr=array();

$st = '';
$ct = count($trainers_list);
$cc = 0;

foreach($trainers_list as $key=>$valu)
{
	if(($ct-$cc)>1)
	{
	$st = $st . '"'.$valu->fname.' '.$valu->lname.'|'.$valu->userid.'",';
	}
	else 
	{
		$st = $st . '"'.$valu->fname.' '.$valu->lname.'|'.$valu->userid.'"';
	}
	$cc++;
}
	
$stnew = "[".$st."]"; 


	
	
         
		 if($if_student==0) { ?>
		 <div>
		 <form name="search" autocomplete="off" action="" method="POST">
   <table ><tr>
   
   <td></td>
   <td>&nbsp;&nbsp;</td>
   <td><div class="autocomplete" style="width:220px;">
   <input id="studentname" type="text" name="studentname" placeholder="Type your Trainer / Teachers Name" value="" />
 <input id="studentid" type="hidden" name="studentid" value="" />
            </div></td><td>&nbsp;&nbsp;</td>
			<td>Year</td>
			<td>&nbsp;</td>
			<td><select name="year" id="year">
			<?php for($k=2010;$k<=@date('Y');$k++)
			{
				?>
			<option value="<?php echo $k; ?>" <?php if($year==$k) { ?> selected <?php } ?>><?php echo $k; ?></option>
			<?php } ?>
			
			</select></td>
			<td>&nbsp;&nbsp;</td>
			
			<td>Assessment</td>
			<td>&nbsp;</td>
			
			<td>
			<select name="assnum" id="assnum">
			<option value="Assessment 1" <?php if($assnum=="Assessment 1") { ?> selected <?php } ?>>Assessment 1</option>
			<option value="Assessment 2" <?php if($assnum=="Assessment 2") { ?> selected <?php } ?>>Assessment 2</option>
			<option value="Assessment 3" <?php if($assnum=="Assessment 3") { ?> selected <?php } ?>>Assessment 3</option>
			<option value="Assessment 4" <?php if($assnum=="Assessment 4") { ?> selected <?php } ?>>Assessment 4</option>
			</select>
			</td>
			<td>&nbsp;&nbsp;</td>
			<td>&nbsp;From:  <input type="date" id="from_date" name="from_date"></td>
			<td>&nbsp;To:  <input type="date" id="to_date" name="to_date"></td>
			<td>&nbsp;</td>
			<td><input type="submit" name="search" value=" Search " style="margin: 0 0 0px 0px!important;" /></td>
			<td>&nbsp;&nbsp;<input type="button" name="reset" value=" Reset " style="margin: 0 0 0px 0px!important;" onclick="window.location.href='graded.php';" /></td></tr>
			</table>
			</form>
		 <?php
		 }
		 
            
			
				?>
			<br/>
       <!-- <div style="float: right;"><a href="ungraded2.php" class="button-success" style="font-size: 18px!important;">View Records only to be Graded</a></div>
<br/><br/> -->
<div>Total records found: <?php echo count($list_count); ?></div>
<br/>
         <table id="customers">
  <tr>
  <th>Course</th>
    <th><?php echo ucfirst($assnum); ?></th>
	<th>Total Marked</th>
	<th>On Time Marked</th>
	<th>Late Marked</th>
	<th>Time Taken</th>
	<th>IP Details</th>
  </tr>
  <?php 
$k=0;
  if(count($list_count)>0) 
  {
	  
  $color ="#f3eff7";
 foreach($list_count as $key=>$val)
  {
	  $k++;
if($color=="#f3eff7")
{
	$color = "#f9f9f9";
}
else
	
	{
		$color = "#f3eff7";
	}
	
	 $sql_trainers = "SELECT DISTINCT u.id AS userid
FROM mdl_user u
JOIN mdl_user_enrolments ue ON ue.userid = u.id
JOIN mdl_enrol e ON e.id = ue.enrolid
JOIN mdl_role_assignments ra ON ra.userid = u.id
JOIN mdl_context ct ON ct.id = ra.contextid AND ct.contextlevel = 50
JOIN mdl_course c ON c.id = ct.instanceid AND e.courseid = c.id
JOIN mdl_role r ON r.id = ra.roleid AND r.shortname != 'student'
WHERE c.id = '".$val['courseid']."'";
$trainers = $DB->get_records_sql($sql_trainers);

unset($arr_check_tr);
$arr_check_tr = array();
foreach($trainers as $key666=>$val666) {
	
	$arr_check_tr[]=$val666->userid;
}

if(in_array($stuidpost,$arr_check_tr)==true)
{
  ?>
  <tr style="background-color: <?php echo $color; ?>">
  <td><a href=""><?php
  
echo $val['courseshortname']; 
  
  ?>
</a>  </td>
  <td><a href=""><?php
  
echo $val['assessmentname']; 
  
  ?></a>
 


 
 
 
  </td>
      
	<td><?php
  
echo $val['totalgraded']; 
  
  ?>
  
  
 

  
  
  </td>
	<td></td>
	<td></td>
   <td></td>
	<td> 
	<?php
	if($val['totalgraded']>0)
	{
		?>
	<!-- Trigger/Open The Modal -->
<button id="myBtn<?php echo $k; ?>">View</button>

<!-- The Modal -->
<div id="myModal<?php echo $k; ?>" class="modal">

  <!-- Modal content -->
  <div class="modal-content" style="width:390px!important; height:auto!important;">
    <span class="close" id="closenow<?php echo $k; ?>">&times;</span>
    <p>
	103.249.39.13
	</p>
	<p>
	103.249.39.13
	</p>
	<p>
	103.76.82.227
	</p>
	<p>
	103.249.39.13
	</p>
  </div>

</div>

<?php
	}
	else
	{
	?>
	NA
	<?php } ?>
</td>
      </tr>
	   

 <script>
// Get the modal
var modal = document.getElementById("myModal<?php echo $k; ?>");

// Get the button that opens the modal
var btn = document.getElementById("myBtn<?php echo $k; ?>");

// Get the <span> element that closes the modal
var span = document.getElementById("closenow<?php echo $k; ?>");

// When the user clicks the button, open the modal 
btn.onclick = function() {
  modal.style.display = "block";
}

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
  modal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}
</script> 
  
  
  

  <?php } unset($sql_trainers); } } else { echo '<tr><td colspan="8">&nbsp;&nbsp;<div style="color: red!important; font-weight: bold!important;" class="info-msg">No Records are Found!</div></td></tr>'; }
         
    
         ?>
     </table>
     </p>
  </div>
  
</div>
   
<?php
    if($if_student==0) {
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
                    inp.value = this.getElementsByTagName("input")[1].value; 
                    document.getElementById("stuid").value = this.getElementsByTagName("input")[0].value; 
                }
                else if(type==2)
                {
                    inp.value = this.getElementsByTagName("input")[1].value; 
                    document.getElementById("studentid").value = this.getElementsByTagName("input")[0].value; 
                }
                else
                {
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
//var final_arr = {{{coursestr}}}; 
var final_arr_stu = <?php echo $stnew; ?> ; 
//var final_arr_user = <?php echo $stnew2; ?> ; 
//var course_arr = ["Afgha sdewwew 456456 nistan-20","Albania-21","Malaysia-33",];
//var arr = '';
//{{#course_arr}}
//var arr = arr+'"{{{coursename}}}*{{{courseid}}}"'+",";
//{{/course_arr}}
//var final_arr = "["+arr+"]";
//alert(final_arr);
//autocomplete(document.getElementById("coursename"), final_arr,'1');
autocomplete(document.getElementById("studentname"), final_arr_stu,'2');
autocomplete(document.getElementById("stuname"), final_arr_user,'1');
</script>
    <?php } ?>
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