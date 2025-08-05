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

global $USER;
global $SESSION;
global $CFG;
$admins = get_admins();
$if_student = 1;
foreach($admins as $admin) {
    if ($USER->id == $admin->id) {
        $if_student = 0;
        break;
    }
    
}

if(isset($_GET['sorting']))
{
	if($_GET['sorting']==1)
	{
		$sorting = " mc.startdate DESC ";
		$sortval = 2;
	}
	else
	{
		$sorting = " mc.startdate ASC ";
		$sortval = 1;
	}
}
else
{
	$sorting = ' FIELD(z.status,"submitted","draft","reopened","new") ';
	$sortval = 1;
}





	
	//if($if_student==0) {
        
         //$contextid = context_course::instance($val[2]->id);
   /* $sql_all =   'SELECT z.id as rowid , z.status as status , z.timemodified as activitydate , mc.fullname , mc.id as courseid , mc.startdate , gg.usermodified , z.userid , y.firstname , y.lastname , ag.grade as recorded_grade , gi.iteminstance as assignmentid , ag.timemodified, ax.name as assignmentname, ax.id as assignmentnameid , '
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
			AND YEAR(FROM_UNIXTIME(mc.startdate)) > 2018
			GROUP BY z.assignment ORDER BY '.$sorting; */
			
		 $sql_all = $SESSION->sql_all;
 
   
   /* $sql =    'SELECT z.id as rowid , z.status as status , z.userid , z.timemodified as activitydate , 
	mc.fullname , mc.id as courseid , mc.startdate , y.firstname , y.lastname , '
            . ' y.username '
            . '  FROM {assign_submission} as z '
            .' LEFT JOIN {assign} as ax ON  ax.id = z.assignment '
            .' LEFT JOIN {user} as y ON z.userid = y.id '
			.' JOIN {course} as mc ON mc.id = ax.course '  
                     
            .' WHERE YEAR(FROM_UNIXTIME(mc.startdate)) > 2017 '
			. ' ORDER BY FIELD(z.status,"submitted","draft","reopened","new")'; */
			
			
			
			
  /*  $sql =    'SELECT z.id as rowid , z.status as status , gg.usermodified , z.userid , z.timemodified as activitydate , mc.fullname , mc.id as courseid , 
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
			.' JOIN {course_modules} as cm ON cm.course = ax.course AND cm.instance = gi.iteminstance AND cm.module = "29"'  
               	.' WHERE YEAR(FROM_UNIXTIME(mc.startdate)) > 2018 '
				.' AND z.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = ax.id AND `userid` = z.userid) '
			.' ORDER BY  '.$sorting; */
    
    $sql = 	$SESSION->sql;
 
    
    if($sql!='' && $sql_all!='')
{
//echo $sql; die;
$sql_scale = "SELECT * FROM {scale}";
$list_scale = $DB->get_records_sql($sql_scale);
$scale_array = array();
foreach($list_scale as $key=>$val)
{
    $scale_explode_array = explode(",",$val->scale);
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

$arr = array();



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
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'coursemoduleid'=>$list->coursemoduleid , 'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'fullname' => $list->fullname , 'courseid' => $list->courseid , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"itemidother"=>$list->itemidother,"moduleid"=>$list_module->id,'activitydate'=>$activitydate,'countsubmission'=>$list->countsubmission,'coursestartdate'=> @date("F j, Y, g:i a",$list->startdate));
            unset($grade_val);
            unset($scale_text);
            unset($timemodified);
            unset($list_all_stu);
            unset($activitydate);          
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
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'coursemoduleid'=>$list->coursemoduleid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'fullname' => $list->fullname , 'courseid' => $list->courseid , 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"itemidother"=>$list->itemidother,"moduleid"=>$list_module->id,'activitydate'=>$activitydate,'countsubmission'=>$list->countsubmission,'coursestartdate'=> @date("F j, Y, g:i a",$list->startdate));
           unset($grade_val);
            unset($scale_text);
            unset($timemodified);   
            unset($activitydate);          
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
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'coursemoduleid'=>$list->coursemoduleid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'fullname' => $list->fullname , 'courseid' => $list->courseid , 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"itemidother"=>$list->itemidother,"moduleid"=>$list_module->id,'activitydate'=>$activitydate,'countsubmission'=>$list->countsubmission,'coursestartdate'=> @date("F j, Y, g:i a",$list->startdate));
           unset($grade_val);
            unset($scale_text);
            unset($timemodified);
            unset($list_all_stu);
            unset($activitydate);          
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
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'coursemoduleid'=>$list->coursemoduleid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'fullname' => $list->fullname , 'courseid' => $list->courseid , 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"itemidother"=>$list->itemidother,"moduleid"=>$list_module->id,'activitydate'=>$activitydate,'countsubmission'=>$list->countsubmission,'coursestartdate'=> @date("F j, Y, g:i a",$list->startdate));
           unset($grade_val);
            unset($scale_text);
            unset($timemodified);           
            unset($activitydate);           
    }
}
else
{
    
}
}
}
if(isset($arr))
{
    $list_all_count = count($arr);
}
            
         
         
         //echo '<pre>';
        // print_r($arr);
if($list_all_count>0) {
 //   echo '<pre>';
 //   print_r($arr);
    
         
		$html_excel ='<table id="customers">
  <tr>
  <th>Student</th>
    <th>Assignment</th>
	<th>Course</th>
	<th>Start Date</th>

    <th>Status</th>
    <th>Last Updated</th>
   <!-- <th>Graded On</th -->
   <!-- <th>Result</th> -->
	<th>Action</th>
  </tr>';
  $cn = 0; $ct = 0; $rate_arr=array(); foreach($arr as $key=>$val) { 

//	if($val['result'] == 'NA / Not yet graded' || ( $val['countsubmission']>1 && $val['result']=="Not Satisfactory"))
	if($val['result'] == 'NA / Not yet graded' || $val['result']=="Not Satisfactory")
	{
  $context = context_course::instance($val['courseid']);
$roles = get_user_roles($context, $val['userid'], true);
$role = key($roles);
$rolename = $roles[$role]->shortname;




if($rolename=="student")
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

if((isset($_POST['search']) && in_array($_POST['studentid'],$arr_check_tr)==true) || (!isset($_POST['search']) && $if_student==0) || (!isset($_POST['search']) && $if_student==1 && in_array($USER->id,$arr_check_tr)==true))
{

if(($val['result']=='Not Satisfactory' || $val['result']=='NA / Not yet graded')  && ( strpos(strtolower(val['fullname']),"scv")==false && strpos(strtolower($val['fullname']),"supervised")==false)) { $color = "#c4ebed"; }
//if($val['result']=='Not Satisfactory' && ( strpos(strtolower(val['fullname']),"scv")==true || strpos(strtolower($val['fullname']),"supervised")==true)) { $color = "red"; }
if(($val['result']=='Not Satisfactory' || $val['result']=='NA / Not yet graded') && ( strpos(strtolower(val['fullname']),"scv")==true && strpos(strtolower($val['fullname']),"supervised")==false)) { $color = "#bdf2b8"; }
if(($val['result']=='Not Satisfactory' || $val['result']=='NA / Not yet graded') && ( strpos(strtolower(val['fullname']),"scv")==false && strpos(strtolower($val['fullname']),"supervised")==true)) { $color = "#f2dea4"; }
if((strtolower($val['status'])=="new" && strpos(strtolower(val['fullname']),"scv")==false && strpos(strtolower(val['fullname']),"supervised")==false)
   || ( strtolower($val['status'])!='reopened' && strtotime($val['activitydate'])>strtotime($val['timemodified']))) { 
   
  $html_excel = $html_excel.'<tr style="background-color: '.$color.'">
  <td>'.$val['name'].
      '<td>'.$val['assignmentname'].'</td>
<td>'.$val['fullname'].'</td>
	<td>'.$val['coursestartdate'].'</td>';
	
	 
	if(strtolower($val['status'])=="new") {
	$html_excel = $html_excel.'<td>No Submission</td>'; } else { 
	$html_excel = $html_excel.'<td>'.$val['status'].'</td>'; }
   
	$html_excel = $html_excel.'<td>'.$val['activitydate'].'</td>';
 $html_excel = $html_excel.'<td><a target="_blank" href="'.$CFG->wwwroot.'/mod/assign/view.php?id='.$val['moduleid'].'&rownum=0&action=grader&userid='.$val['userid'].'" 
 class="button-success">GRADE</a></td>';
   
$html_excel = $html_excel.'
 
        
      </tr>';
unset($color); } }  } unset($rolename); unset($context); unset($roles); unset($role); unset($trainers); unset($sql_trainers); } } 
  
$html_excel = $html_excel.  '</table>';
 } else { $html_excel = $html_excel. '&nbsp;&nbsp;<span style="color: red!important; font-weight: bold!important;">Student did not open the submission link yet!</span>'; }
         
    
         $html_excel=$html_excel.
'     
     </p>
  </div>
  
</div>';

            
$file_name ="ungraded.xls";
$excel_file=$html_excel;
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=$file_name");
echo $excel_file;
die; 