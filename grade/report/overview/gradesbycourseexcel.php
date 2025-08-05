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
 * Moodle frontpage.
 *
 * @package    core
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../../config.php';
require_once $CFG->libdir.'/gradelib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/report/overview/lib.php';
require_once $CFG->dirroot.'/grade/report/user/lib.php';
$courseid = optional_param('id', SITEID, PARAM_INT);
$userid   = optional_param('userid', $USER->id, PARAM_INT);
global $USER;
global $CFG;



if(isset($_GET['categoryid']) && $_GET['categoryid']>0)
{
	$categoryid = $_GET['categoryid'];

$arrnew = $DB->get_records_sql("SELECT ue.id, c.id AS courseid, c.fullname as course_name, c.idnumber as module, 
c.timemodified as dateshow , u.id AS userid , u.firstname as fname , u.lastname as lname , u.username as studentno FROM mdl_user u 
JOIN mdl_user_enrolments ue ON ue.userid = u.id 
JOIN mdl_enrol e ON e.id = ue.enrolid 
JOIN mdl_role_assignments ra ON ra.userid = u.id 
JOIN mdl_context ct ON ct.id = ra.contextid AND ct.contextlevel = 50 
JOIN mdl_course c ON c.id = ct.instanceid AND e.courseid = c.id 
JOIN mdl_role r ON r.id = ra.roleid AND r.shortname = 'student' 
WHERE e.status = 0 AND ue.status = 0 AND c.category = '".$categoryid."'");
}

echo '<style>
#customers {
  font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

#customers td, #customers th {
  border: 1px solid #ddd;
  padding: 8px;
}

#customers tr:nth-child(even){background-color: #f2f2f2;}

#customers tr:hover {background-color: #ddd;}

#customers th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #85adf2;
  color: white;
}
</style>';


// Prevent caching of this page to stop confusion when changing page after making AJAX changes.
$PAGE->set_cacheable(false);

?>

<br/><br/>
<?php

$html_excel = '<table id="customers" style="width:100%!important;">
<thead>
<tr style="width:100%!important; background-color: blue; color: white;">
<th>Student No</th>
<th>Name</th>
<th>Course Code</th>
<th>Unit Code</th>
<th>Result</th>
<th>Supervised</th>
<th>SCV</th>
<th>Date</th>
</tr>
</thead>
<tbody>';
     $course_name = array(); foreach($arrnew as $key=>$val) { $course_name[] = $val->course_name;  
	if($val->id>0) {
		$course_explode_arr = explode(" ",$val->course_name);
	$html_excel = $html_excel.'<tr><td>'.$val->studentno.'</td>
	<td>'.$val->fname." ".$val->lname.'</td>
	<td>'.$course_explode_arr[0].'</td>
        <td>'.$course_explode_arr[1].'</td>';
		
         
		 
         
         
//         $context = context_course::instance($val[2]->id);
//         $gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'overview', 'courseid'=>$val[2]->id, 'userid'=>$userid));
//     $report = new grade_report_user($val[2]->id, $gpr, $context, $userid);
//    
//       
//
//        if ($currentgroup and !groups_is_member($currentgroup, $userid)) {
//            echo $OUTPUT->notification(get_string('groupusernotmember', 'error'));
//        } else {
//            if ($report->fill_table()) {
//               // echo '<br />'.$report->print_table(true);
//            }
//        }
         
         
         $contextid = get_context_instance(CONTEXT_COURSE, $val->courseid);
    $sql_all =   'SELECT z.id as rowid , z.status as status , z.timemodified as activitydate , gg.usermodified , gg.feedback , gg.timecreated as feedbackposted1 , gg.timemodified as feedbackposted2 , z.userid , y.firstname , y.lastname , ag.grade as recorded_grade , gi.iteminstance as assignmentid , ag.timemodified, ax.name as assignmentname, ax.id as assignmentnameid , '
            . '  gi.courseid , gi.gradetype, gi.grademin,gi.grademax,gi.scaleid , y.username , ag.id as itemidother '
            . ' , count(app.id) as countsubmission FROM {assign_submission} as z '
            . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = z.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
               . ' LEFT JOIN {assign_grades} as ag ON  ag.assignment = z.assignment AND ag.userid = z.userid AND ag.id = (SELECT max(`id`) FROM {assign_grades} WHERE `assignment` = z.assignment AND `userid` = z.userid)'
            .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = z.userid  '
           . ' LEFT JOIN {assign} as ax ON  ax.id = z.assignment '
            .' LEFT JOIN {user} as y ON z.userid = y.id '   
            .' LEFT JOIN {assign_submission} as app ON app.assignment = ax.id AND app.userid = '.$val->userid                   
            .' WHERE z.userid = '.$val->userid.' AND ax.course = '.$val->courseid.' and z.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = ax.id AND `userid` = '.$val->userid.') GROUP BY z.assignment , z.userid ';
 
   
    $sql =    'SELECT z.id as rowid , z.status as status , gg.usermodified , z.userid , z.timemodified as activitydate , gg.feedback , gg.timecreated as feedbackposted1 , gg.timemodified as feedbackposted1 , y.firstname , y.lastname , ag.grade as recorded_grade , gi.iteminstance as assignmentid , ag.timemodified, ax.name as assignmentname,  ax.id as assignmentnameid , '
            . '  gi.courseid , gi.gradetype, gi.grademax,gi.grademin,gi.scaleid , y.username , ag.id as itemidother'
            . ' , count(app.id) as countsubmission FROM {assign_submission} as z '
            . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = z.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
               . ' LEFT JOIN {assign_grades} as ag ON  ag.assignment = z.assignment AND ag.userid = z.userid AND ag.id = (SELECT max(`id`) FROM {assign_grades} WHERE `assignment` = z.assignment AND `userid` = z.userid)'
            .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = z.userid  '
           . ' LEFT JOIN {assign} as ax ON  ax.id = z.assignment '
            .' LEFT JOIN {user} as y ON z.userid = y.id '
                 .' LEFT JOIN {assign_submission} as app ON app.assignment = ax.id AND app.userid = '.$val->userid                   
            .' WHERE z.userid = '.$val->userid.' AND ax.course = '.$val->courseid.' and z.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = ax.id AND `userid` = '.$val->userid.') GROUP BY z.assignment , z.userid ';
    
    
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
//echo '<pre>';
//print_r($list); 
$pass = true;
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
				$timemodified_timestamp = $list->timemodified;
            }
            $grade_exists = '';
            $activitydate = @date("F j, Y, g:i a",$list->activitydate);
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'timemodified_timestamp'=>$timemodified_timestamp,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"feedback"=>$list->feedback,"itemidother"=>$list->itemidother,"feedbackposted1"=>$list->feedbackposted1,"feedbackposted2"=>$list->feedbackposted2,"moduleid"=>$list_module->id,'activitydate'=>$activitydate,'countsubmission'=>$list->countsubmission);
            unset($grade_val);
            unset($scale_text);
            unset($timemodified);
			unset($timemodified_timestamp);
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
				$timemodified_timestamp = $list->timemodified;
            }
            $grade_exists = '';
            $activitydate = @date("F j, Y, g:i a",$list->activitydate);
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'timemodified_timestamp'=>$timemodified_timestamp,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"feedback"=>$list->feedback,"itemidother"=>$list->itemidother,"feedbackposted1"=>$list->feedbackposted1,"feedbackposted2"=>$list->feedbackposted2,"moduleid"=>$list_module->id,'activitydate'=>$activitydate,'countsubmission'=>$list->countsubmission);
           unset($grade_val);
            unset($scale_text);
            unset($timemodified);   
			unset($timemodified_timestamp);
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
				$timemodified_timestamp = $list->timemodified;
            }
            $grade_exists = '';
            $activitydate = @date("F j, Y, g:i a",$list->activitydate);
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'timemodified_timestamp'=>$timemodified_timestamp,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"feedback"=>$list->feedback,"itemidother"=>$list->itemidother,"feedbackposted1"=>$list->feedbackposted1,"feedbackposted2"=>$list->feedbackposted2,"moduleid"=>$list_module->id,'activitydate'=>$activitydate,'countsubmission'=>$list->countsubmission);
            unset($grade_val);
            unset($scale_text);
            unset($timemodified);
			unset($timemodified_timestamp);
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
				$timemodified_timestamp = $list->timemodified;
            }
            $activitydate = @date("F j, Y, g:i a",$list->activitydate);
            $grade_exists = '';
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'timemodified_timestamp'=>$timemodified_timestamp,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"feedback"=>$list->feedback,"itemidother"=>$list->itemidother,"feedbackposted1"=>$list->feedbackposted1,"feedbackposted2"=>$list->feedbackposted2,"moduleid"=>$list_module->id,'activitydate'=>$activitydate,'countsubmission'=>$list->countsubmission);
           unset($grade_val);
            unset($scale_text);
            unset($timemodified); 
				unset($timemodified_timestamp);
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
            
         
         
        // echo '<pre>';
        // print_r($arr);
if($list_all_count>0) {
    //echo '<pre>';
    //print_r($arr);
    
         $fail=0;
		 $time_of_grade = array();
          $cn = 0; foreach($arr as $key=>$val) 
		  { 
		  $time_of_grade[] = $val['timemodified_timestamp'];
		  if(strtolower(preg_replace('/\s+/', '', $val['result']))=='notsatisfactory' && strtolower(preg_replace('/\s+/', '', $val['result']))!='na/notyetgraded')
  { 


$fail++;
} 
 if(strtolower(preg_replace('/\s+/', '', $val['result']))=='na/notyetgraded')
  { 


$fail--;
} 

 

}


if($fail>0)
{
	$extra_course1 = $DB->get_record_sql("SELECT a.fullname, a.id as courseid , e.id as enrollid , ue.* FROM `mdl_course` a LEFT JOIN 
	`mdl_enrol` e ON e.courseid = a.id LEFT JOIN `mdl_user_enrolments` ue ON ue.enrolid = e.id AND ue.userid = '".$list->userid."' 
	WHERE a.fullname LIKE '%".$course_explode_arr[1]."%' AND a.fullname LIKE '%- Supervised%' AND 
	a.fullname LIKE '%".$course_explode_arr[0]."%'");
	
	$extra_course2=$DB->get_record_sql("SELECT a.fullname, a.id as courseid , e.id as enrollid , ue.* FROM `mdl_course` a LEFT JOIN 
	`mdl_enrol` e ON e.courseid = a.id LEFT JOIN `mdl_user_enrolments` ue ON ue.enrolid = e.id AND ue.userid = '".$list->userid."' 
	WHERE a.fullname LIKE '%".$course_explode_arr[1]."%' AND a.fullname LIKE '%- SCV%' AND 
	a.fullname LIKE '%".$course_explode_arr[0]."%'");
	
}
$extra_result1 =$DB->get_records_sql("SELECT c.fullname AS 'Course', u.firstname AS 'Firstname', u.lastname AS 'Lastname', gh.finalgrade AS 'recorded_grade', gi.itemname AS 'Item Name'
FROM mdl_user u
JOIN mdl_grade_grades_history gh ON u.id=gh.userid
LEFT JOIN mdl_grade_items gi ON gh.itemid=gi.id
JOIN mdl_course c ON gi.courseid=c.id
WHERE u.id='".$list->userid."' AND c.id = '".$extra_course1->courseid."' and gh.finalgrade IS NOT NULL and gi.itemname IS NOT NULL
ORDER BY c.id");

if(count($extra_result1)>0)
{
$sup_pass = 1;
foreach($extra_result1 as $key11=>$val11)
{
	if(intval($val11->recorded_grade)==0 || intval($val11->recorded_grade)<2)
	{
		$sup_pass=0;
		
		break;
	}
}

}


$extra_result2 =$DB->get_records_sql("SELECT c.fullname AS 'Course', u.firstname AS 'Firstname', u.lastname AS 'Lastname', gh.finalgrade AS 'recorded_grade', gi.itemname AS 'Item Name'
FROM mdl_user u
JOIN mdl_grade_grades_history gh ON u.id=gh.userid
LEFT JOIN mdl_grade_items gi ON gh.itemid=gi.id
JOIN mdl_course c ON gi.courseid=c.id
WHERE u.id='".$list->userid."' AND c.id = '".$extra_course2->courseid."' and gh.finalgrade IS NOT NULL and gi.itemname IS NOT NULL
ORDER BY c.id");


if(count($extra_result2)>0)
{
	$scv_pass = 1;
foreach($extra_result2 as $key22=>$val22)
{
	if(intval($val22->recorded_grade)==0 || intval($val22->recorded_grade)<2)
	{
		$scv_pass=0;
		break;
	}
}
}

$total_result1 = $DB->get_record_sql("SELECT 
ROUND(gg.finalgrade,2) grade,
FROM_UNIXTIME(gi.timemodified) TimeModified
FROM mdl_course AS c
JOIN mdl_context AS ctx ON c.id = ctx.instanceid
JOIN mdl_role_assignments AS ra ON ra.contextid = ctx.id
JOIN mdl_user AS u ON u.id = ra.userid
JOIN mdl_grade_grades AS gg ON gg.userid = u.id
JOIN mdl_grade_items AS gi ON gi.id = gg.itemid
JOIN mdl_course_categories AS cc ON cc.id = c.category
WHERE gi.courseid = c.id AND gi.itemtype = 'course' AND c.id = '".$extra_course1->courseid."' AND u.id = '".$list->userid."'");


$total_result2 = $DB->get_record_sql("SELECT 
ROUND(gg.finalgrade,2) grade,
FROM_UNIXTIME(gi.timemodified) TimeModified
FROM mdl_course AS c
JOIN mdl_context AS ctx ON c.id = ctx.instanceid
JOIN mdl_role_assignments AS ra ON ra.contextid = ctx.id
JOIN mdl_user AS u ON u.id = ra.userid
JOIN mdl_grade_grades AS gg ON gg.userid = u.id
JOIN mdl_grade_items AS gi ON gi.id = gg.itemid
JOIN mdl_course_categories AS cc ON cc.id = c.category
WHERE gi.courseid = c.id AND gi.itemtype = 'course' AND c.id = '".$extra_course2->courseid."' AND u.id = '".$list->userid."'");

$allset_sup=""; 
$allset_scv=""; 
	if($fail>0)
	 {	 
	if(($scv_pass==1 && @intval($total_result2->grade)==0) || ($scv_pass!=1 && @intval($total_result2->grade)>0))
	{
		$allset_scv="style='background-color: #f79ea4;'";
	}		
	if(($sup_pass==1 && @intval($total_result1->grade)==0) || ($sup_pass!=1 && @intval($total_result1->grade)>0))
	{
		$allset_sup="style='background-color: #f79ea4;'";
	}
	 }
	 else{
		 $allset_sup="style='background-color: #fff;'";
		 $allset_scv="style='background-color: #fff;'";
	 }




     $html_excel = $html_excel.'<td>'; if($fail>0) { $html_excel = $html_excel.'Not Competent'; } else if($fail==0) { $html_excel = $html_excel.'Competent'; } 
	 else { $html_excel = $html_excel.'<font style="color: red;"><i>NA</i></font>'; } 
	 $fail = 0; 
	 $html_excel = $html_excel.'</td>';
	 
	 
	$html_excel = $html_excel.'<td '.$allset_sup.'>';
	if(count($extra_result1)>0) { if($sup_pass==1) { $html_excel = $html_excel.'<font style="color: green;">Competent</font>'; } 
	else { $html_excel = $html_excel.'<font style="color:red;">Not Competent</font>'; } } else { $html_excel = $html_excel.'NA'; } 
	$html_excel = $html_excel.'</td>';
	
 $html_excel = $html_excel.'<td '.$allset_scv.'>';
 if(count($extra_result2)>0) { if($scv_pass==1) { $html_excel = $html_excel.'<font style="color: green;">Competent</font>'; } 
 else { $html_excel = $html_excel.'<font style="color:red;">Not Competent</font>'; } } else { $html_excel = $html_excel.'NA'; }
 $html_excel = $html_excel.'</td>';
$html_excel = $html_excel.'<td>';
if(max($time_of_grade)>0) { $html_excel = $html_excel.@date("F j, Y, g:i a",max($time_of_grade)); } else { 
$html_excel = $html_excel.'<font style="color: red;"><i>NA</i></font>'; } 
$html_excel = $html_excel.'</td>';
 
	 unset($extra_result2); unset($extra_result1); unset($sup_pass); unset($scv_pass); 
	 
	 
	 
	 
	$html_excel=$html_excel.'</tr>';

  unset($time_of_grade); unset($extra_course); unset($allset_scv); unset($allset_sup);
  

 } else { 


$html_excel = $html_excel.'<td>&nbsp;&nbsp;<span style="color: red!important; font-weight: bold!important;">NA</span></td>
<td style="color: red!important; font-weight: bold!important;">NA</td>'; }
         
    
          unset($context);  } }
$html_excel = $html_excel.'</tbody></table>';




       
            
$file_name ="grades.xls";
$excel_file=$html_excel;
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=$file_name");
echo $excel_file;
die; 