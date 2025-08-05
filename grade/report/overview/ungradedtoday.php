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

if(isset($_GET['qualification_name']))
{
	$list_all_courses = $DB->get_records_sql('SELECT `id` as courseid , `fullname` as coursename FROM {course} WHERE `shortname` LIKE "%'.$_GET['qualification_name'].'%" ORDER BY `id` DESC');
}
else
{
	$list_all_courses = $DB->get_records_sql('SELECT `id` as courseid , `fullname` as coursename FROM {course} WHERE 1 ORDER BY `id` DESC');
}
$coursestr = '';
foreach($list_all_courses as $list_all_courses)
{
    // $course_arr[] = array('coursename'=>$list_all_courses->coursename,'courseid'=>$list_all_courses->courseid);
    $coursestr = $coursestr.'"'.$list_all_courses->coursename."|".$list_all_courses->courseid.'",';
}
$coursestr = "[".$coursestr."]";

$unitcode = "['CEB','CB','DB','ADB','DT','CPD','CMB','CIT','DIB','DIT']";

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

if(isset($_POST['course_type']))
{
	$course_type = $_POST['course_type'];
	if($course_type=="SCV" || $course_type=="Supervised")
	{
		$clause2 = " AND mc.fullname LIKE '%".$course_type."%'";
	}
	else if($course_type=="Regular")
	{
		$clause2 = " AND mc.fullname NOT LIKE '%Supervised%' AND mc.fullname NOT LIKE '%SCV%' ";
	}
	else
	{
		$clause2 = " AND mc.fullname !='' ";
	}
}
else
{
	$course_type = '';
	$clause2 = " AND mc.fullname !='' ";
}

if(isset($_POST['submission_status']))
{
	$submission_status = $_POST['submission_status'];
	if($submission_status!="All" && $submission_status!="")
	{
		$clause1 = " AND z.status = '".$submission_status."'";
	}
	else
	{
		$clause1 = " AND z.status !='' ";
	}
}
else
{
	$submission_status = '';
	$clause1 = " AND z.status !='' ";
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
	if(isset($_REQUEST['qualification_name']))
	{
		$qualification_name = $_REQUEST['qualification_name'];
	}
	else
	{
		$qualification_name = '';
	}
	if(isset($_REQUEST['unit_id']))
	{
		$unit_id = $_REQUEST['unit_id'];
	}
	else
	{
		$unit_id = '';
	}
	if($qualification_name!='')
	{
		$clause3 = " AND mc.shortname LIKE '%".$qualification_name."%'";
	}
	else
	{
		$clause3 = " ";
	}
	//echo $clause3;
	//die;
	
	//die;
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

function array2readable($array, $separator, $last_separator) {                              
    $result = preg_replace(strrev("/$separator/"),strrev($last_separator),strrev(implode($separator, $array)), 1);
    return strrev($result);
}
 function unixTime2text($time) {
    $value['y'] = floor($time/31536000);
   // $value['w'] = floor(($time-($value['y']*31536000))/604800);
    $value['d'] = floor(($time-($value['y']*31536000+$value['w']*604800))/86400);
    $value['h'] = floor(($time-($value['y']*31536000+$value['w']*604800+$value['d']*86400))/3600);
    $value['m'] = floor(($time-($value['y']*31536000+$value['w']*604800+$value['d']*86400+$value['h']*3600))/60);
    $value['s'] = $time-($value['y']*31536000+$value['w']*604800+$value['d']*86400+$value['h']*3600+$value['m']*60);

    $unit['y'] = 'year' . ($value['y'] > 1 ? 's' : '');
  //  $unit['w'] = 'week' . ($value['w'] > 1 ? 's' : '');
    $unit['d'] = 'day' . ($value['d'] > 1 ? 's' : '');
    $unit['h'] = 'hour' . ($value['h'] > 1 ? 's' : '');
    $unit['m'] = 'minute' . ($value['m'] > 1 ? 's' : '');
    $unit['s'] = 'second' . ($value['s'] > 1 ? 's' : '');

    foreach($value as $key => $val) {
        if (!empty($val)) {
            $not_null_values[] .= $val . ' ' . $unit[$key];
        }
    }

    return array2readable($not_null_values, ', ', ' and ');
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



	
	//if($if_student==0) {
        
		
		//if(count($_SESSION->arr)==0)
		//{
         //$contextid = context_course::instance($val[$i][2]->id);
    $sql_all =   'SELECT z.id as rowid , z.status as status , z.timemodified as activitydate , mc.fullname , mc.shortname , mc.id as courseid , mc.startdate , 
	x.duedate as assessmentduedate , 
	x.cutoffdate as assessmentcutoffdate, x.gradingduedate as gradingduedate ,
	gg.usermodified , z.userid , y.firstname , y.lastname , ag.grade as recorded_grade , gi.iteminstance as assignmentid , ag.timemodified, ax.name as assignmentname, 
	ax.id as assignmentnameid , '
            . '  gi.courseid , gi.gradetype, gi.grademin,gi.grademax,gi.scaleid , y.username , ag.id as itemidother '
            . ' , count(app.id) as countsubmission FROM {assign_submission} as z '
            . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = z.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
               . ' LEFT JOIN {assign_grades} as ag ON  ag.assignment = z.assignment AND ag.userid = z.userid AND ag.id = (SELECT max(`id`) FROM {assign_grades} WHERE `assignment` = z.assignment AND `userid` = z.userid)'
            .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = z.userid  '
           . ' LEFT JOIN {assign} as ax ON  ax.id = z.assignment '
            .' LEFT JOIN {user} as y ON z.userid = y.id '   
			.'LEFT JOIN {course} as mc ON mc.id = ax.course '  
            .' LEFT JOIN {assign_submission} as app ON app.assignment = ax.id '               
            .' WHERE z.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = ax.id ) 
			AND YEAR(FROM_UNIXTIME(mc.startdate)) = '.$year.' '.$clause1.' '.$clause2.' '.$clause3.' '.$clause4.'
			GROUP BY z.assignment ORDER BY FIELD(z.status,"submitted","draft","reopened","new"), z.timemodified '.$date_sort;
			
			$SESSION->sql_all = $sql_all;
 
   
   /* $sql =    'SELECT z.id as rowid , z.status as status , z.userid , z.timemodified as activitydate , 
	mc.fullname , mc.id as courseid , mc.startdate , y.firstname , y.lastname , '
            . ' y.username '
            . '  FROM {assign_submission} as z '
            .' LEFT JOIN {assign} as ax ON  ax.id = z.assignment '
            .' LEFT JOIN {user} as y ON z.userid = y.id '
			.' JOIN {course} as mc ON mc.id = ax.course '  
                     
            .' WHERE YEAR(FROM_UNIXTIME(mc.startdate)) > 2017 '
			. ' ORDER BY FIELD(z.status,"submitted","draft","reopened","new")'; */
			
			
			
			
     $sql =    'SELECT z.id as rowid , z.status as status , gg.usermodified , z.userid , z.timemodified as activitydate , ax.duedate as assessmentduedate , 
	ax.cutoffdate as assessmentcutoffdate, ax.gradingduedate as gradingduedate , mc.fullname , mc.shortname , mc.id as courseid , 
	mc.startdate , y.firstname , y.lastname , ag.grade as recorded_grade , gi.iteminstance as assignmentid , 
	ag.timemodified, ax.name as assignmentname,  ax.id as assignmentnameid , '
            . '  gi.courseid , gi.gradetype, gi.grademax,gi.grademin,gi.scaleid , y.username , ag.id as itemidother, cm.id as coursemoduleid '
            . ' FROM {assign_submission} as z '
            . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = z.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
               . ' LEFT JOIN {assign_grades} as ag ON  ag.assignment = z.assignment AND ag.userid = z.userid AND ag.id = (SELECT max(`id`) FROM {assign_grades} WHERE `assignment` = z.assignment AND `userid` = z.userid)'
            .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = z.userid  '
           . ' LEFT JOIN {assign} as ax ON  ax.id = z.assignment '
            .' LEFT JOIN {user} as y ON z.userid = y.id '
			.' JOIN {course} as mc ON mc.id = ax.course '  
			
			
			
			
			
			.' LEFT JOIN {course_modules} as cm ON cm.course = ax.course AND cm.instance = gi.iteminstance AND cm.module = "29"'  
               	.' WHERE YEAR(FROM_UNIXTIME(mc.startdate)) = '.$year.' '.$clause1.' '.$clause2.' '.$clause3.' '.$clause4
				.' AND z.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = ax.id AND `userid` = z.userid) '
				




	.' ORDER BY FIELD(z.status,"submitted","draft","reopened","new") , z.timemodified '.$date_sort;
	
	$SESSION->sql = $sql;
		
    
   
    //if($sql!='' && $sql_all!='' && count($SESSION->arr)==0)
		if($sql!='' && $sql_all!='')
{
//echo $sql; die;
$sql_scale = "SELECT * FROM {scale}";
$list_scale = $DB->get_records_sql($sql_scale);
$scale_array = array();
foreach($list_scale as $key=>$val[$i])
{
    $scale_explode_array = explode(",",$val[$i]->scale);
    for($j=0;$j<count($scale_explode_array);$j++)
    {
        $scale_array[$key][$j+1]=$scale_explode_array[$j];
    }
    unset($scale_explode_array);
}
//echo '<pre>';
//print_r($scale_array);
//echo $sql_all;
//echo '<hr>';
//echo $sql;
//$list_all = $DB->get_records_sql($sql_all);

//$list_all_count = count($list_all);

$list = $DB->get_records_sql($sql);


//echo '<pre>';
//print_r($list);



foreach($list as $list)
{
    $sql_module = "SELECT id FROM {course_modules} WHERE course = '".$list->courseid."' AND instance = '".$list->assignmentid."'";
    $list_module = $DB->get_record_sql($sql_module);
    if($list->scaleid>0 && $list->gradetype!=1)
    {
    if(@$contextid->id!='' && @$contextid->id>0)
    {
        $sql_role = "SELECT roleid FROM {role_assignments} WHERE contextid = '".$contextid->id."' AND userid = '".$list->userid."'";
        $list_all_stu = $DB->get_record_sql($sql_role);
        
        
        if($list_all_stu->roleid!='' && $list_all_stu->roleid==5)
        {
            $grade_val = intval($list->recorded_grade); 
            @$scale_text = $scale_array[$list->scaleid][$grade_val];
            if(@$scale_text=='')
            {
                @$scale_text="NA / Not yet graded";
            }
            if($list->timemodified=='')
            {
                $timemodified = "NA";
            }
            else
            {
                $timemodified = @date("F j, Y, g:i a",$list->timemodified);
            }
            $grade_exists = '';
            $activitydate = @date("F j, Y, g:i a",$list->activitydate);
			if($scale_text == 'NA / Not yet graded' || $scale_text=="Not Satisfactory")
			{
				$context = context_course::instance($list->courseid);
$roles = get_user_roles($context, $list->userid, true);
$role = key($roles);
$rolename = $roles[$role]->shortname;
if($rolename=="student") {

            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'coursemoduleid'=>$list->coursemoduleid , 'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'fullname' => $list->fullname , 'courseid' => $list->courseid , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,
			"result"=>$scale_text,"status"=>$list->status,"itemidother"=>$list->itemidother,"moduleid"=>$list_module->id,'activitydate'=>$activitydate,
			'countsubmission'=>$list->countsubmission,'coursestartdate'=> @date("F j, Y, g:i a",$list->startdate),"assessmentduedate"=>$list->assessmentduedate
			,"assessmentcutoffdate"=>$list->assessmentcutoffdate,"gradingduedate"=>$list->gradingduedate);
}
			}
            unset($grade_val);
            unset($scale_text);
            unset($timemodified);
            unset($list_all_stu);
            unset($activitydate);       
			unset($context);
			unset($roles);
			unset($role);
			unset($rolename);
        }
    }
    else
    {
            $grade_val = intval($list->recorded_grade); 
            @$scale_text = $scale_array[$list->scaleid][$grade_val];
            if(@$scale_text=='')
            {
                @$scale_text="NA / Not yet graded";
            }
            if($list->timemodified=='')
            {
                $timemodified = "NA";
            }
            else
            {
                $timemodified = @date("F j, Y, g:i a",$list->timemodified);
            }
            $grade_exists = '';
            $activitydate = @date("F j, Y, g:i a",$list->activitydate);
			if($scale_text == 'NA / Not yet graded' || $scale_text=="Not Satisfactory")
			{
				
				$context = context_course::instance($list->courseid);
$roles = get_user_roles($context, $list->userid, true);
$role = key($roles);
$rolename = $roles[$role]->shortname;

if($rolename=="student") {

            $arr[]  = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'coursemoduleid'=>$list->coursemoduleid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'fullname' => $list->fullname , 'courseid' => $list->courseid , 
			'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,
			'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'',
			'gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"itemidother"=>$list->itemidother,
			"moduleid"=>$list_module->id,'activitydate'=>$activitydate,'countsubmission'=>$list->countsubmission,
			'coursestartdate'=> @date("F j, Y, g:i a",$list->startdate),"assessmentduedate"=>$list->assessmentduedate
			,"assessmentcutoffdate"=>$list->assessmentcutoffdate,"gradingduedate"=>$list->gradingduedate);
			}
			}
           unset($grade_val);
            unset($scale_text);
            unset($timemodified);   
            unset($activitydate); 
				unset($context);
			unset($roles);
			unset($role);
			unset($rolename);
    }
}
else if($list->scaleid=='' && $list->gradetype==1)
{
    if(@$contextid->id!='' && @$contextid->id>0)
    {
        $sql_role = "SELECT roleid FROM {role_assignments} WHERE contextid = '".$contextid->id."' AND userid = '".$list->userid."'";
        $list_all_stu = $DB->get_record_sql($sql_role);
        if($list_all_stu->roleid!='' && $list_all_stu->roleid==5)
        {
            $grade_val = intval($list->recorded_grade); 
            
            if($grade_val=='')
            {
                @$scale_text="NA / Not yet graded";
            }
            else
            {
                @$scale_text=$grade_val;
                if($list->feedback!='')
                {
                    @$scale_text = @$scale_text." - ".strip_tags($list->feedback);
                }
            }
            if($list->timemodified=='')
            {
                $timemodified = "NA";
            }
            else
            {
                $timemodified = @date("F j, Y, g:i a",$list->timemodified);
            }
            $grade_exists = '';
            $activitydate = @date("F j, Y, g:i a",$list->activitydate);
			if($scale_text == 'NA / Not yet graded' || $scale_text=="Not Satisfactory")
			{
				$context = context_course::instance($list->courseid);
$roles = get_user_roles($context, $list->userid, true);
$role = key($roles);
$rolename = $roles[$role]->shortname;

if($rolename=="student") {

            $arr[]  = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'coursemoduleid'=>$list->coursemoduleid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 
			'fullname' => $list->fullname , 'courseid' => $list->courseid , 'username'=>$list->username , 
			'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,
			'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,
			"itemidother"=>$list->itemidother,"moduleid"=>$list_module->id,'activitydate'=>$activitydate,'countsubmission'=>$list->countsubmission,
			'coursestartdate'=> @date("F j, Y, g:i a",$list->startdate),"assessmentduedate"=>$list->assessmentduedate
			,"assessmentcutoffdate"=>$list->assessmentcutoffdate,"gradingduedate"=>$list->gradingduedate);
			}
			}
           unset($grade_val);
            unset($scale_text);
            unset($timemodified);
            unset($list_all_stu);
            unset($activitydate);         
				unset($context);
			unset($roles);
			unset($role);
			unset($rolename);
        }
    }
    else
    {
            $grade_val = intval($list->recorded_grade); 
            
            if(@$grade_val=='')
            {
                @$scale_text="NA / Not yet graded";
            }
            else
            {
                @$scale_text=$grade_val;
                if($list->feedback!='')
                {
                    @$scale_text = @$scale_text." - ".strip_tags($list->feedback);
                }
            }
            if($list->timemodified=='')
            {
                $timemodified = "NA";
            }
            else
            {
                $timemodified = @date("F j, Y, g:i a",$list->timemodified);
            }
            $activitydate = @date("F j, Y, g:i a",$list->activitydate);
            $grade_exists = '';
			if($scale_text == 'NA / Not yet graded' || $scale_text=="Not Satisfactory")
			{
				
				$context = context_course::instance($list->courseid);
$roles = get_user_roles($context, $list->userid, true);
$role = key($roles);
$rolename = $roles[$role]->shortname;

if($rolename=="student") {

            $arr[]  = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'coursemoduleid'=>$list->coursemoduleid,'userid'=>$list->userid, 
			'name'=>$list->firstname.' '.$list->lastname, 'fullname' => $list->fullname , 'courseid' => $list->courseid , 
			'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,
			'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,
			"status"=>$list->status,"itemidother"=>$list->itemidother,"moduleid"=>$list_module->id,'activitydate'=>$activitydate,
			'countsubmission'=>$list->countsubmission,'coursestartdate'=> @date("F j, Y, g:i a",$list->startdate),"assessmentduedate"=>$list->assessmentduedate
			,"assessmentcutoffdate"=>$list->assessmentcutoffdate,"gradingduedate"=>$list->gradingduedate);
			}
			}
           unset($grade_val);
            unset($scale_text);
            unset($timemodified);           
            unset($activitydate);  
				unset($context);
			unset($roles);
			unset($role);
			unset($rolename);
    }
}
else
{
    
}
}
}



/*if(isset($SESSION->arr) && count($SESSION->arr)>0)
{
    $list_all_count = count($SESSION->arr);
}
*/

if(isset($arr))
{
    $list_all_count = count($arr);
}
            
         
         
         //echo '<pre>';
        // print_r($arr);

   // echo '<pre>';
   // print_r($arr);
    
	
	
	
	
	
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


/////

$sql_stu_list = "SELECT DISTINCT u.id AS userid, u.firstname as fname , u.lastname as lname 
FROM mdl_user u
JOIN mdl_user_enrolments ue ON ue.userid = u.id
JOIN mdl_enrol e ON e.id = ue.enrolid
JOIN mdl_role_assignments ra ON ra.userid = u.id
JOIN mdl_context ct ON ct.id = ra.contextid AND ct.contextlevel = 50
JOIN mdl_role r ON r.id = ra.roleid WHERE r.shortname = 'student'";
$stu_list = $DB->get_records_sql($sql_stu_list);



$st2 = '';
$ct = count($stu_list);
$cc = 0;

foreach($stu_list as $key=>$valu)
{
	if(($ct-$cc)>1)
	{
	$st2 = $st2 . '"'.$valu->fname.' '.$valu->lname.'|'.$valu->userid.'",';
	}
	else 
	{
		$st2 = $st2 . '"'.$valu->fname.' '.$valu->lname.'|'.$valu->userid.'"';
	}
	$cc++;
}
	
$stnew2 = "[".$st2."]"; 	
	
         ?>
		 <?php
		 if($if_student==0) { ?>
		 <div>
		 <form name="search" autocomplete="off" action="" method="POST">
   <table ><tr>
   
   <td><div class="autocomplete" style="width:220px;">
   <input id="stuname" type="text" name="stuname" placeholder="Type your Student / Student Name" value="" />
 <input id="stuid" type="hidden" name="stuid" value="" />
            </div></td>
   <td>&nbsp;&nbsp;</td>
   <td><div class="autocomplete" style="width:220px;">
   <input id="studentname" type="text" name="studentname" placeholder="Type your Trainer / Teachers Name" value="" />
 <input id="studentid" type="hidden" name="studentid" value="" />
            </div></td><td>&nbsp;&nbsp;&nbsp;</td>
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
			<td>&nbsp;&nbsp;</td>
			<td>Type</td>
			<td>&nbsp;</td>
			<td><select name="course_type" id="course_type">
			<option value="All" <?php if($course_type=="All") { ?> selected <?php } ?>>All</option>
			<option value="Regular" <?php if($course_type=="Regular") { ?> selected <?php } ?>>Regular</option>
			<option value="SCV" <?php if($course_type=="SCV") { ?> selected <?php } ?>>SCV</option>
			<option value="Supervised" <?php if($course_type=="Supervised") { ?> selected <?php } ?>>Supervised</option>
			</select></td>
			<td>&nbsp;&nbsp;</td>
			<td>&nbsp;&nbsp;</td>
			<td>Status</td>
			<td>&nbsp;</td>
			<td><select name="submission_status" id="submission_status">
			<option value="All" <?php if($submission_status=="All") { ?> selected <?php } ?>>All</option>
			<option value="New" <?php if($submission_status=="New") { ?> selected <?php } ?>>No Submission</option>
			<option value="Submitted" <?php if($submission_status=="Submitted") { ?> selected <?php } ?>>Submitted</option>
			<option value="Draft" <?php if($submission_status=="Draft") { ?> selected <?php } ?>>Draft</option>
			<option value="Reopened" <?php if($submission_status=="Reopened") { ?> selected <?php } ?>>Reopened</option>
			</select></td>
			<td>&nbsp;&nbsp;</td>
			<td><input type="submit" name="search" value=" Search " style="margin: 0 0 0px 0px!important;" /></td>
			<td>&nbsp;&nbsp;<input type="button" name="reset" value=" Show All " style="margin: 0 0 0px 0px!important;" onclick="window.location.href='ungraded.php';" /></td></tr>
			<tr>
			<td height="9" colspan="13">&nbsp;</td>
			</tr>
			<tr>

			<td colspan="11"><strong>-OR-</strong></td>
			
			</tr>
			
			</table></form>
			<form action="" name="fff" id="fff" method="POST">
			<input type="hidden" name="year" id="year" value="<?php echo $year; ?>" />
			<input type="hidden" name="course_type" id="course_type" value="<?php echo $course_type; ?>" />
			<input type="hidden" name="submission_status" id="submission_status" value="<?php echo $submission_status; ?>" />
			<input type="hidden" name="search_by_unit" id="search_by_unit" value="<?php echo $search_by_unit; ?>" />
			<input type="hidden" name="search_unit" id="search_unit" value="<?php echo $search_unit; ?>" />
			<input type="hidden" name="stuid" id="stuid" value="<?php echo $stuidpost; ?>" />
			<?php if($date_sort=="DESC") { ?>
			<input type="hidden" name="date_sort" id="date_sort" value="<?php echo "ASC"; ?>" />
			<?php } else { ?>
			<input type="hidden" name="date_sort" id="date_sort" value="<?php echo "DESC"; ?>" />
			<?php } ?>
			</form>
			<form name="search2" autocomplete="off" action="ungradedtoday.php?qualification_name=<?php echo $_GET['qualification_name']; ?>" method="POST">
			<table>
			<tr>
			<td>&nbsp;</td>
			<td></td>
			<td>Select Qualification</td>
			<td>&nbsp;&nbsp;</td>
			<td><div class="autocomplete" style="width:220px;">
   <input id="qualification_name" type="text" name="qualification_name" placeholder="Type your Qualification" <?php if(isset($_GET['qualification_name'])) { ?> value="<?php echo $_GET['qualification_name']; ?>" <?php } else { ?> value="" <?php } ?> />
 <input id="qualification_id" type="hidden" name="qualification_id" value="" />
            </div></td>
			<td>&nbsp;&nbsp;</td>
			
			
			
			<td>
			<div class="autocomplete" style="width:220px;">
   <input id="unit_name" type="text" name="unit_name" placeholder="Type your Unit"
 <?php if(isset($_REQUEST['unit_name'])) { ?> value="<?php echo $_REQUEST['unit_name']; ?>" <?php } else { ?> value="" <?php } ?>  />
 <input id="unit_id" type="hidden" name="unit_id" <?php if(isset($_REQUEST['unit_id'])) { ?> value="<?php echo $_REQUEST['unit_id']; ?>" <?php } else { ?> value="" <?php } ?> />
            </div>
			</td>
			<td>&nbsp;&nbsp;</td>
			<td><select name="year" id="year">
			<?php for($k=2010;$k<=@date('Y');$k++)
			{
				?>
			<option value="<?php echo $k; ?>" <?php if($year==$k) { ?> selected <?php } ?>><?php echo $k; ?></option>
			<?php } ?>
			
			</select></td>
			<td>&nbsp;&nbsp;<input type="submit" name="search" value=" Search " style="margin: 0 0 0px 0px!important;" /></td>
			<td>&nbsp;&nbsp;<input type="button" name="reset" value=" Show All " style="margin: 0 0 0px 0px!important;" onclick="window.location.href='ungraded.php';" /></td>
			</tr>
			</table>
			<input type="hidden" name="search_by_unit" id="search_by_unit" value="1" />
			<?php if(isset($_GET['qualification_name'])) { ?>
			<input type="hidden" name="qualification_name" id="qualification_name" value="<?php echo $_GET['qualification_name']; ?>" />
			<?php } ?>
			</form>
		 
		 </div>
		 <?php
		 }
		 if(isset($userdetails) && $if_student==0 && isset($_POST['search']) && $_POST['studentid']>0) { 
            
              $other_details1 = $DB->get_record_sql("SELECT `data`  FROM `mdl_user_info_data` WHERE `userid` = '".$userdetails->id."' AND `fieldid` IN('15')");
            $other_details2 = $DB->get_record_sql("SELECT `data`  FROM `mdl_user_info_data` WHERE `userid` = '".$userdetails->id."' AND `fieldid` IN('1')");
            echo '&nbsp;Search results for <a href="'.$CFG->wwwroot.'/user/profile.php?id='.$userdetails->id.'" target="_blank"><strong>'.@$userdetails->firstname.' '.@$userdetails->lastname.'</strong></a>'
                    .'<div style="border-radius: 17px;
  border: 2px solid #73AD21;
  padding: 14px; 
  margin-top:13px;
  margin-bottom:18px;
  width: 100%;
  height: 114px;"><strong>Email</strong> : <a href="mailto: '.$userdetails->email.'">'.$userdetails->email.'</a><br><div style="height:6px;"></div>'.
                    '<strong>Gender</strong>: '; if($other_details1->data!='') { echo $other_details1->data; } else { echo 'NA'; } echo '<br><div style="height:6px;"></div>'.
        '<strong>Phone</strong>: '; if($other_details2->data!='') { echo $other_details2->data; } else { echo 'NA'; } echo '</div>'; }
            ?>
			<?php
			if($list_all_count>0) {
				?>
			<br/>
       <div style="float: right;"><a href="ungraded2.php" class="button-success" style="font-size: 18px!important;">View Fresh Records to be Graded</a>
	   &nbsp;&nbsp;<a href="ungradedexcelfilter.php" class="button-success" style="font-size: 18px!important;">Download Excel</a></div>
	   
	   <div style="float: right;"></div>

<br/><br/>
<?php
$total_c=0;
foreach($arr as $key=>$val) {

	//if($val[$i]['result'] == 'NA / Not yet graded' || ( $val[$i]['countsubmission']>1 && $val[$i]['result']=="Not Satisfactory"))
	 if($val['result'] == 'NA / Not yet graded' || $val['result']=="Not Satisfactory")
	{
		
		if((strtolower($val['status'])=="new" && strpos(strtolower($val['fullname']),"scv")==false && strpos(strtolower($val['fullname']),"supervised")==false
   && (($val['duedate']<$current_timestamp && $val['assessmentcutofffdate']==0) || ($val['duedate']<$current_timestamp && $val['assessmentcutofffdate']<$current_timestamp))
   && $val['result']=='NA / Not yet graded')
   || ( strtotime($val['activitydate'])>strtotime($val['timemodified']) && strtolower($val['status'])!='new' && strtolower($val['status'])!='reopened') 
   || ( $val['activitydate']==$val['timemodified'] && strtolower($val['status'])!='new')) { 
   
		
		
		
		$total_c++;
   }
	}
}
?>
<div>Total records found: <?php echo $total_c; ?></div>
<br/>
         <table id="customers">
  <tr>
  <th>Student</th>
    <th>Assignment</th>
	<th>Course</th>
	<th>Start Date</th>
	<th>Trainer(s)</th>
    <th>Status</th>
    <th>
	<a style="color: white!important;" href="#" onclick="javascript: document.fff.submit();">
	Last Updated</a></th>
   <th>Graded On</th>
   <th>Delay</th>
	<th>Action</th>
  </tr>
  <?php 

  

 // date_default_timezone_set('Asia/Kolkata');
$current_timestamp=time();
  $counter = 0;
  $cn = 0; $ct = 0; $rate_arr=array(); 
  //for($i=$pageLimit;$i<($pageLimit+$setLimit);$i++) { 
  
  foreach($arr as $key=>$val) {

	//if($val[$i]['result'] == 'NA / Not yet graded' || ( $val[$i]['countsubmission']>1 && $val[$i]['result']=="Not Satisfactory"))
	 if($val['result'] == 'NA / Not yet graded' || $val['result']=="Not Satisfactory")
	{
  
	
	$sql_trainers = "SELECT DISTINCT u.id AS userid, u.firstname as fname , u.lastname as lname , c.id AS courseid
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
if((isset($_POST['search']) && in_array($_POST['studentid'],$arr_check_tr)==true) || (!isset($_POST['search']) && $if_student==0) || (!isset($_POST['search']) && $if_student==1 && in_array($USER->id,$arr_check_tr)==true) || (isset($_POST['search']) && $_POST['studentid']=='' && $if_student==0))
{



if(($val['result']=='Not Satisfactory' || $val['result']=='NA / Not yet graded')  && ( strpos(strtolower($val['fullname']),"scv")==false && strpos(strtolower($val['fullname']),"supervised")==false)) { $color = "#c4ebed"; }
//if($val[$i]['result']=='Not Satisfactory' && ( strpos(strtolower(val['fullname']),"scv")==true || strpos(strtolower($val[$i]['fullname']),"supervised")==true)) { $color = "red"; }
if(($val['result']=='Not Satisfactory' || $val['result']=='NA / Not yet graded') && ( strpos(strtolower($val['fullname']),"scv")==true && strpos(strtolower($val['fullname']),"supervised")==false)) { $color = "#bdf2b8"; }
if(($val['result']=='Not Satisfactory' || $val['result']=='NA / Not yet graded') && ( strpos(strtolower($val['fullname']),"scv")==false && strpos(strtolower($val['fullname']),"supervised")==true)) { $color = "#f2dea4"; }

if(strtotime($val['activitydate'])>0 && strtotime($val['activitydate'])>strtotime($val['timemodified']) && strtolower($val['status'])!="new")
{
  $timestamp_diff_grading = ( $current_timestamp - strtotime($val['activitydate']));
}



/*if($val[$i]['duedate']!='' && $val[$i]['duedate']>0 && $val[$i]['assessmentcutoffdate']==0 && $val[$i]['duedate']<$current_timestamp && strtolower($val[$i]['status'])=="new")
{
	 $timestamp_diff_grading = ( $current_timestamp - $val[$i]['duedate']);
}

if($val[$i]['assessmentcutoffdate']!='' && $val[$i]['assessmentcutoffdate']>0 && $val[$i]['assessmentcutoffdate']<$current_timestamp)
{
	 $timestamp_diff_grading = ( $current_timestamp - $val[$i]['assessmentcutoffdate']);
}
*/



  ?>
  <tr style="background-color: <?php echo $color; ?>; <?php if($unit_id>0 && $val['courseid']!=$unit_id) { ?> display: none; <?php } ?>">
  <td><?php echo $val['name']; ?>
      <td><?php if($val['countsubmission']>1) { ?> <img style="height:23px; width: 20px;" alt="It has multiple submission! Please check." title="It has multiple submission! Please check." src="<?php echo $CFG->wwwroot; ?>/pix/exclamation.png" border="0" /> <?php } ?> 
	 

     <?php if($if_student==0) { ?><a href="<?php echo $CFG->wwwroot; ?>/mod/assign/view.php?id=<?php echo $val['moduleid']; ?>&action=grading" target="_blank"><?php echo $val['assignmentname']; ?></a><?php } else { ?><?php echo $val['assignmentname']; ?><?php } ?></td>
    <td><?php echo $val['fullname']; ?></td>
	<td><?php echo $val['coursestartdate']; ?></td>
	<td><?php foreach($trainers as $key=>$val5) { echo "<a href='http://localhost/accit-moodle/accit/user/profile.php?id=".$val5->userid."' target='_blank'>".$val5->fname." ".$val5->lname."</a>"; echo '<hr>'; }?></td>
	<td><?php if(strtolower($val['status'])=="new") { echo 'No Submission'; } else { echo $val['status']; } ?></td>
   
	<td><?php echo $val['activitydate']; ?></td>
    <td><?php //if($val['activitydate']!=$val['timemodified']) { echo $val['timemodified']; } else { echo 'NA'; } 
	
	echo $val['timemodified']; 
	if($val['activitydate']==$val['timemodified'])
	{
		echo '****';
	}
	?></td> 
  <!--  <td><?php //echo $val[$i]['result']; ?></td> -->
  <td><?php 
  //if($timestamp_diff_grading>0) {
  echo unixTime2text($timestamp_diff_grading);
  //}
//  else { echo 'NA'; }
?></td>
   <td>
  
   <?php if((strtolower($val['status'])=="new" && strpos(strtolower($val['fullname']),"scv")==false && strpos(strtolower($val['fullname']),"supervised")==false
   && (($val['duedate']<$current_timestamp && $val['assessmentcutofffdate']==0) || ($val['duedate']<$current_timestamp && $val['assessmentcutofffdate']<$current_timestamp))
   && $val['result']=='NA / Not yet graded')
   || ( strtotime($val['activitydate'])>strtotime($val['timemodified']) && strtolower($val['status'])!='new' && strtolower($val['status'])!='reopened') 
   || ( $val['activitydate']==$val['timemodified'] && strtolower($val['status'])!='new')) { 
   
   ?>
   
   <a target="_blank" href="<?php echo $CFG->wwwroot; ?>/mod/assign/view.php?id=<?php echo $val['moduleid']; ?>&rownum=0&action=grader&userid=<?php echo $val['userid']; ?>" class="button-success">GRADE</a>
   
   <?php }
   
   else if( strtolower($val['status'])=='reopened' && 
   (( $val['duedate']<$current_timestamp && $val['assessmentcutofffdate']==0) || ($val['duedate']<$current_timestamp && $val['assessmentcutofffdate']<$current_timestamp)))
   {
	   ?>
	   Waiting for Submission
	   <?php
   }
else {   ?>
No Action Needed
<?php } ?>
   </td>
 
        
      </tr>
	   <?php if(strpos(strtolower($val['fullname']),"scv")==false && strpos(strtolower($val['fullname']),"supervised")==false && $val['result']=="Not Satisfactory")
	    { ?>
   <tr style="border: 3pt solid red; <?php if($unit_id>0 && $val['courseid']!=$unit_id) { ?> display: none; <?php } ?>">
   <td colspan="12">&nbsp;Please check if the student has passed in Supervised/SCV</td>
   </tr>
   
   <?php } 
   
   else if(strtolower($val['status'])=='draft' && strtotime($val['timemodified'])>strtotime($val['activitydate']))
	   
	   {
   
   ?>
<tr style="border: 3pt solid red; <?php if($unit_id>0 && $val['courseid']!=$unit_id) { ?> display: none; <?php } ?>">
   <td colspan="12">&nbsp;Please contact to the student.</td>
   </tr>
	   <?php } 
	   
	    else if(strtolower($val['status'])=='reopened' && strtotime($val['activitydate'])>strtotime($val['timemodified']))
	   
	   {
   ?>
   <tr style="border: 3pt solid red; <?php if($unit_id>0 && $val['courseid']!=$unit_id) { ?> display: none; <?php } ?>">
   <td colspan="12">&nbsp;Please check if the student has passed in Supervised/Reassessment/SCV or Please contact the student for submission.</td>
   </tr>
   <?php
	   } 
	   else { } ?>
  <tr <?php if($unit_id>0 && $val['courseid']!=$unit_id) { ?> style="display: none;" <?php } ?>>
  <td colspan="12" style="background-color: white!important;">


  
  </td>
  </tr>
  <?php unset($color); }   unset($trainers); unset($sql_trainers); unset($timestamp_diff_grading); } } ?>
  
  
</table>
<?php } else { echo '&nbsp;&nbsp;<div style="color: red!important; font-weight: bold!important;" class="info-msg">No Records are Found!</div>'; }
         
    
         ?>
     
     </p>
  </div>
  
</div>
   
<?php
//echo '<br/><br/>';
  //  echo displayPaginationHere(count($SESSION->arr),$setLimit,$page); // Call the Pagination Function to display Pagination.
?>
<?php

	//}
	//else
	//{
		?>
	<!--	 <div class="container">
 
  <div class="alert alert-danger">
    <strong>Unauthorized!</strong> You are not allowed to access !
  </div>
</div> -->
	  
	<?php //}
                
           

//$event = \gradereport_overview\event\grade_report_viewed::create(
//    array(
//        'context' => $context,
//        'courseid' => $courseid,
//        'relateduserid' => $userid,
//    )
//);
//$event->trigger();
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
				else if(type==3)
                {
                    inp.value = this.getElementsByTagName("input")[1].value; 
                    document.getElementById("unit_id").value = this.getElementsByTagName("input")[0].value; 
                }
				else if(type==4)
                {
                    inp.value = this.getElementsByTagName("input")[1].value; 
                   // document.getElementById("qualification_id").value = this.getElementsByTagName("input")[0].value; 
					window.location.href='ungradedtoday.php?qualification_name='+this.getElementsByTagName("input")[1].value; 
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
var final_arr = <?php echo $coursestr; ?> ; 
var final_arr_stu = <?php echo $stnew; ?> ; 
var final_arr_user = <?php echo $stnew2; ?> ; 
var final_arr_qualification = <?php echo $unitcode; ?> ;
//var course_arr = ["Afgha sdewwew 456456 nistan-20","Albania-21","Malaysia-33",];
//var arr = '';
//{{#course_arr}}
//var arr = arr+'"{{{coursename}}}*{{{courseid}}}"'+",";
//{{/course_arr}}
//var final_arr = "["+arr+"]";
//alert(final_arr);
autocomplete(document.getElementById("unit_name"), final_arr,'3');
autocomplete(document.getElementById("studentname"), final_arr_stu,'2');
autocomplete(document.getElementById("stuname"), final_arr_user,'1');
autocomplete(document.getElementById("qualification_name"), final_arr_qualification,'4');
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