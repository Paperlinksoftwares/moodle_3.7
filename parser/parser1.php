<html>
<head>
<title>Star Software Data Parser and Comparison with Moodle | ACCIT</title>
<style>
.btn {
  background: #3498db;
  background-image: -webkit-linear-gradient(top, #3498db, #2980b9);
  background-image: -moz-linear-gradient(top, #3498db, #2980b9);
  background-image: -ms-linear-gradient(top, #3498db, #2980b9);
  background-image: -o-linear-gradient(top, #3498db, #2980b9);
  background-image: linear-gradient(to bottom, #3498db, #2980b9);
  -webkit-border-radius: 28;
  -moz-border-radius: 28;
  border-radius: 28px;
  text-shadow: 0px 0px 0px #666666;
  -webkit-box-shadow: 0px 1px 7px #666666;
  -moz-box-shadow: 0px 1px 7px #666666;
  box-shadow: 0px 1px 7px #666666;
  font-family: Arial;
  color: #ffffff;
  font-size: 13px;
  padding: 9px 16px 8px 16px;
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
<script>
function doShow()
{
	document.getElementById('loader').style.display="block";
}
</script>
</head>
<body>
<center>
<br/>
<form name="f1" action="parser1.php" method="post">
<table bgcolor="yellow" style="width: 310px; height: 110px;" cellspacing="9" cellpadding="9">
<tr>
<td>Select Term</td>
<td><select name="term" id="term">
<option value="0">Select Term</option>
 <option value="1" <?php if(isset($_POST['term']) && $_POST['term']==1) { ?> selected <?php } ?> >1</option>
<option value="2" <?php if(isset($_POST['term']) && $_POST['term']==2) { ?> selected <?php } ?>>2</option>
<option value="3" <?php if(isset($_POST['term']) && $_POST['term']==3) { ?> selected <?php } ?>>3</option>
<option value="4" <?php if(isset($_POST['term']) && $_POST['term']==4) { ?> selected <?php } ?>>4</option>
</select></td>
<tr>
<td>Select Year</td>
<td><select name="year" id="year">
<option value="0">Select Year</option>
<option value="2017" <?php if(isset($_POST['year']) && $_POST['year']==2017) { ?> selected <?php } ?>>2017</option>
<option value="2018" <?php if(isset($_POST['year']) && $_POST['year']==2018) { ?> selected <?php } ?>>2018</option>
<option value="2019" <?php if(isset($_POST['year']) && $_POST['year']==2019) { ?> selected <?php } ?>>2019</option>
<option value="2020" <?php if(isset($_POST['year']) && $_POST['year']==2020) { ?> selected <?php } ?>>2020</option>
<option value="2021" <?php if(isset($_POST['year']) && $_POST['year']==2021) { ?> selected <?php } ?>>2021</option>
<option value="2022" <?php if(isset($_POST['year']) && $_POST['year']==2022) { ?> selected <?php } ?>>2022</option>
<option value="2023" <?php if(isset($_POST['year']) && $_POST['year']==2023) { ?> selected <?php } ?>>2023</option>

</select></td>
</tr>
<tr>
<td colspan="2" align="center"><input class="btn" type="submit" name=" Submit " name="sub" id="sub" value=" Submit " onclick="javascript: doShow();" />
<input type="hidden" name="postval" id="postval" value="1" /></td>
</tr>
<table>
</form>
</center>
<br/>
<div id="loader" style="height:30px; width:100%; display: none;"><img src="http://localhost/accit-moodle/accit/parser/loader.gif"></div>
<?php
require_once('../config.php');
global $DB;
global $SESSION;
ini_set('error_reporting', 0);
ini_set('display_errors', false);

if(isset($_POST['postval']) && $_POST['postval']==1)
{
	if(isset($_POST['year']) && $_POST['year']>0 && isset($_POST['term']) && $_POST['term']>0)
	{
		unset($SESSION->compiled_array);
require_once 'SimpleXLSX.php';

$file = $_POST['year']."_".$_POST['term'].".xlsx";
if ( $xlsx = SimpleXLSX::parse($file)) {
	// ->rows()
	//echo '<h2>$xlsx->rows()</h2>';
	//echo '<pre>';
	//print_r( $xlsx->rows() );
	//echo '</pre>';
	
	$data_array = $xlsx->rows();
	
	//loop
	$new_array = array();
	for($k=1;$k<count($data_array);$k++)
	{
		$sql_user = "SELECT `id` from {user} WHERE `username` = '".$data_array[$k][0]."'";
		$user_array = $DB->get_record_sql($sql_user);
		$new_array['module'][]=$data_array[$k][4];
		
		$sql_course = "SELECT `id`  FROM {course} WHERE `fullname` LIKE '%".$data_array[$k][4]."%'
		AND (`fullname` LIKE '%T".$_POST['term']."/".$_POST['year']."%' OR `fullname` LIKE '%T".$_POST['term'].",".$_POST['year']."%'
		OR `fullname` LIKE '%Term ".$_POST['term'].", ".$_POST['year']."%' OR `fullname` LIKE '%T".$_POST['term'].", ".$_POST['year']."%') ORDER BY `id`  DESC";
		$course_array = $DB->get_record_sql($sql_course);
		//echo '<pre>';
		//print_r($course_array);
		if(isset($course_array->id) && $course_array->id>0) {
		
		$new_array['courseid'][]=$course_array->id;
		} else
		{ $msg = "<div style='color: #fff;font-weight:bold; height:29px; width:100%;background-color: red;'>No Course / Unit Found under ".$data_array[$k][4]."
	for Term ".$_POST['term']." - ".$_POST['year']." !</div>"; $new_array['courseid'][]=$msg;}
		
		$new_array['userid'][]=$user_array->id;
		
		////Result data fetch for the comparison 
		
		$sql_result = "
SELECT z.id as rowid , z.status as status , gg.usermodified , 
z.userid , z.timemodified as activitydate , gg.feedback , 
gg.timecreated as feedbackposted1 , gg.timemodified as feedbackposted1 , 
y.firstname , y.lastname , ag.grade as recorded_grade , 
gi.iteminstance as assignmentid , ag.timemodified, ax.name as assignmentname, 
ax.id as assignmentnameid , gi.courseid , gi.gradetype, gi.grademax,gi.grademin,gi.scaleid , 
y.username , ag.id as itemidother , count(app.id) as countsubmission FROM mdl_assign_submission as z 
LEFT JOIN mdl_grade_items as gi ON gi.iteminstance = z.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = 'assign' 
LEFT JOIN mdl_assign_grades as ag ON ag.assignment = z.assignment AND ag.userid = z.userid 
AND ag.id = (SELECT max(`id`) FROM mdl_assign_grades WHERE `assignment` = z.assignment 
AND `userid` = z.userid) LEFT JOIN mdl_grade_grades as gg ON gg.itemid = gi.id AND gg.userid = z.userid 
LEFT JOIN mdl_assign as ax ON ax.id = z.assignment LEFT JOIN mdl_user as y ON z.userid = y.id 
LEFT JOIN mdl_assign_submission as app ON app.assignment = ax.id AND app.userid = '".$user_array->id."' 
WHERE z.userid = '".$user_array->id."' AND ax.course = '".$course_array->id."' 
and z.timemodified = (SELECT max(`timemodified`) FROM mdl_assign_submission 
WHERE `assignment` = ax.id AND `userid` = '".$user_array->id."') GROUP BY z.assignment , z.userid";
	
	$list = $DB->get_records_sql($sql_result);
	//echo '<pre>';
	//print_r($list);

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
	
	//Calculating final grade / result of an Unit
	$arr = array();
	if(count($list)>0)
	{
		
		$contextid = context_course::instance($course_array->id);
		
		
		
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
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"feedback"=>$list->feedback,"itemidother"=>$list->itemidother,"feedbackposted1"=>@$list->feedbackposted1,"feedbackposted2"=>@$list->feedbackposted2,"moduleid"=>$list_module->id,'activitydate'=>$activitydate,'countsubmission'=>$list->countsubmission);
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
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"feedback"=>$list->feedback,"itemidother"=>$list->itemidother,"feedbackposted1"=>$list->feedbackposted1,"feedbackposted2"=>$list->feedbackposted2,"moduleid"=>$list_module->id,'activitydate'=>$activitydate,'countsubmission'=>$list->countsubmission);
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
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"feedback"=>$list->feedback,"itemidother"=>$list->itemidother,"feedbackposted1"=>$list->feedbackposted1,"feedbackposted2"=>$list->feedbackposted2,"moduleid"=>$list_module->id,'activitydate'=>$activitydate,'countsubmission'=>$list->countsubmission);
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
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"feedback"=>$list->feedback,"itemidother"=>$list->itemidother,"feedbackposted1"=>$list->feedbackposted1,"feedbackposted2"=>$list->feedbackposted2,"moduleid"=>$list_module->id,'activitydate'=>$activitydate,'countsubmission'=>$list->countsubmission);
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
	$new_array['excelresult'][] = $data_array[$k][5];
//echo '<pre>Actual array';
//print_r($arr);
	if(count($arr)>0)
	{
		$arr_secondary = [];
		$arr_secondary2 = [];
		$sup_pass='';
		$scv_pass='';
		foreach($arr as $key=>$val) {
			$moodle_result='';
		//	echo $val['result']." || Excel Result : ".$data_array[$k][7];
			//$new_array['excelresult'][] = $data_array[$k][7];
			if(strtolower(trim($val['result']))=='satisfactory' || strtolower(trim($val['result']))=='outstanding')
  { 
			$arr_secondary[] = $val['result'];
  }

			
			if(strtolower(trim($val['result']))=='not satisfactory')
  { 


$extra_course1 = $DB->get_record_sql("SELECT a.id as courseid FROM `mdl_course` a LEFT JOIN 
	`mdl_enrol` e ON e.courseid = a.id LEFT JOIN `mdl_user_enrolments` ue ON ue.enrolid = e.id AND ue.userid = '".$user_array->id."' 
	WHERE a.fullname LIKE '%- Supervised%' AND 
	a.fullname LIKE '%".$data_array[$k][4]."%' AND a.fullname NOT LIKE '%".$_POST['year']."%' LIMIT 0,1");
	
	
	
	
	$extra_course2=$DB->get_record_sql("SELECT a.id as courseid FROM `mdl_course` a LEFT JOIN 
	`mdl_enrol` e ON e.courseid = a.id LEFT JOIN `mdl_user_enrolments` ue ON ue.enrolid = e.id AND ue.userid = '".$user_array->id."' 
	WHERE a.fullname LIKE '%- SCV%' AND 
	a.fullname LIKE '%".$data_array[$k][4]."%' AND a.fullname NOT LIKE '%".$_POST['year']."%' LIMIT 0,1");
	
	

/*$extra_result1 =$DB->get_records_sql("SELECT c.id , gh.finalgrade AS 'recorded_grade', gi.itemname AS 'Item Name'
FROM mdl_user u
JOIN mdl_grade_grades_history gh ON u.id=gh.userid
LEFT JOIN mdl_grade_items gi ON gh.itemid=gi.id
JOIN mdl_course c ON gi.courseid=c.id
WHERE u.id='".$user_array->id."' AND c.id = '".$extra_course1->courseid."' and gh.finalgrade IS NOT NULL and gi.itemname IS NOT NULL
ORDER BY c.id");*/


$extra_result1 = $DB->get_records_sql("SELECT z.id as rowid , z.status as status , gg.usermodified , 
z.userid , z.timemodified as activitydate , gg.feedback , 
gg.timecreated as feedbackposted1 , gg.timemodified as feedbackposted1 , 
y.firstname , y.lastname , ag.grade as recorded_grade , 
gi.iteminstance as assignmentid , ag.timemodified, ax.name as assignmentname, 
ax.id as assignmentnameid , gi.courseid , gi.gradetype, gi.grademax,gi.grademin,gi.scaleid , 
y.username , ag.id as itemidother , count(app.id) as countsubmission FROM mdl_assign_submission as z 
LEFT JOIN mdl_grade_items as gi ON gi.iteminstance = z.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = 'assign' 
LEFT JOIN mdl_assign_grades as ag ON ag.assignment = z.assignment AND ag.userid = z.userid 
AND ag.id = (SELECT max(`id`) FROM mdl_assign_grades WHERE `assignment` = z.assignment 
AND `userid` = z.userid) LEFT JOIN mdl_grade_grades as gg ON gg.itemid = gi.id AND gg.userid = z.userid 
LEFT JOIN mdl_assign as ax ON ax.id = z.assignment LEFT JOIN mdl_user as y ON z.userid = y.id 
LEFT JOIN mdl_assign_submission as app ON app.assignment = ax.id AND app.userid = '".$user_array->id."' 
WHERE z.userid = '".$user_array->id."' AND ax.course = '".$extra_course1->courseid."' 
and z.timemodified = (SELECT max(`timemodified`) FROM mdl_assign_submission 
WHERE `assignment` = ax.id AND `userid` = '".$user_array->id."') AND REPLACE(ax.name,' ','') LIKE '%".preg_replace('/\s+/', '', $val['assignmentname'])."%' GROUP BY z.assignment , z.userid");




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


/*$extra_result2 =$DB->get_records_sql("SELECT c.fullname AS 'Course', u.firstname AS 'Firstname', u.lastname AS 'Lastname', gh.finalgrade AS 'recorded_grade', gi.itemname AS 'Item Name'
FROM mdl_user u
JOIN mdl_grade_grades_history gh ON u.id=gh.userid
LEFT JOIN mdl_grade_items gi ON gh.itemid=gi.id
JOIN mdl_course c ON gi.courseid=c.id
WHERE u.id='".$user_array->id."' AND c.id = '".$extra_course2->courseid."' and gh.finalgrade IS NOT NULL and gi.itemname IS NOT NULL
ORDER BY c.id");*/

$extra_result2 = $DB->get_records_sql("SELECT z.id as rowid , z.status as status , gg.usermodified , 
z.userid , z.timemodified as activitydate , gg.feedback , 
gg.timecreated as feedbackposted1 , gg.timemodified as feedbackposted1 , 
y.firstname , y.lastname , ag.grade as recorded_grade , 
gi.iteminstance as assignmentid , ag.timemodified, ax.name as assignmentname, 
ax.id as assignmentnameid , gi.courseid , gi.gradetype, gi.grademax,gi.grademin,gi.scaleid , 
y.username , ag.id as itemidother , count(app.id) as countsubmission FROM mdl_assign_submission as z 
LEFT JOIN mdl_grade_items as gi ON gi.iteminstance = z.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = 'assign' 
LEFT JOIN mdl_assign_grades as ag ON ag.assignment = z.assignment AND ag.userid = z.userid 
AND ag.id = (SELECT max(`id`) FROM mdl_assign_grades WHERE `assignment` = z.assignment 
AND `userid` = z.userid) LEFT JOIN mdl_grade_grades as gg ON gg.itemid = gi.id AND gg.userid = z.userid 
LEFT JOIN mdl_assign as ax ON ax.id = z.assignment LEFT JOIN mdl_user as y ON z.userid = y.id 
LEFT JOIN mdl_assign_submission as app ON app.assignment = ax.id AND app.userid = '".$user_array->id."' 
WHERE z.userid = '".$user_array->id."' AND ax.course = '".$extra_course2->courseid."' 
and z.timemodified = (SELECT max(`timemodified`) FROM mdl_assign_submission 
WHERE `assignment` = ax.id AND `userid` = '".$user_array->id."') AND REPLACE(ax.name,' ','') LIKE '%".preg_replace('/\s+/', '', $val['assignmentname'])."%' GROUP BY z.assignment , z.userid");


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

/*
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
WHERE gi.courseid = c.id AND gi.itemtype = 'course' AND c.id = '".$extra_course1->courseid."' AND u.id = '".$user_array->id."'");


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
WHERE gi.courseid = c.id AND gi.itemtype = 'course' AND c.id = '".$extra_course2->courseid."' AND u.id = '".$user_array->id."'");
*/



if(count($extra_result1)>0) 
{ 
if($sup_pass==1) 
{ 
$moodle_result="Satisfactory"; 
} 
else 
{ 
$moodle_result="Not Satisfactory"; 
} 
} 
if(count($extra_result2)>0) { if($scv_pass==1) { $moodle_result="Satisfactory"; } else { $moodle_result="Not Satisfactory"; } } 
if(count($extra_result1)==0 && count($extra_result2)==0) 
{
	$moodle_result="Not Satisfactory"; 
}


$arr_secondary2[] = $moodle_result;  
unset($moodle_result);
}  

			
			
		}
		
		//unset($arr_secondary);
		//unset($moodle_result_final);
		
	}
	
	else
	{
		//echo 'No results found!';
	}
	//echo "count-".$k.'|'.$data_array[$k][4].$data_array[$k][5].$data_array[$k][1].$data_array[$k][2]; 
	//echo '<pre>1';
	//print_r($arr_secondary);
	//echo '<pre>2';
	//print_r($arr_secondary2);
	$arr_final_result = [];
	$arr_final_result = array_merge(@$arr_secondary,@$arr_secondary2);
	//echo '<pre>3';
	//print_r($arr_final_result);
	//echo count($arr_final_result);
	$failed=0;
	$passed=0;
	$withdrawn = 0;
	if(count($arr_final_result)>0)
	{
		foreach($arr_final_result as $kk=>$mm)
		{
			if(trim($mm)=="Not Satisfactory")
			{
				$failed++;
			}
			else if((trim($mm)=="Satisfactory" || trim($mm)=="Outstanding") && (trim($mm)!="Not Satisfactory"))
			{
				$passed++;
			}
			else
			{
				$failed=0;
				$passed = 0;
				$withdrawn = 0;
				
			}
		}
	}
	else
	{
		$withdrawn++;
	}
	/*if(in_array('Not Satisfactory',$arr_final_result)==true)
		{
			$moodle_result_final = "Not Satisfactory";		
		}
		if(in_array('Not Satisfactory',$arr_final_result)==false && ( in_array('Satisfactory',$arr_final_result)==true || in_array('Outstanding',$arr_final_result)==true))
		{
			$moodle_result_final = "Satisfactory";
			
		}
		if(in_array('Not Satisfactory',$arr_final_result)==false && in_array('Satisfactory',$arr_final_result)==false && in_array('Outstanding',$arr_final_result)==false)
		{
			echo 'here';
			$moodle_result_final = "";
			
		}*/
	//	echo "--".$moodle_result_final; 
		//$new_array['moodleresult'] = [];
		if($passed>0 && $failed==0)
		{
			$moodle_result_final="Satisfactory";
		}
		else if($passed>0 && $failed>0)
		{
			$moodle_result_final="Not Satisfactory";
		}
		else if($passed==0 && $failed>0)
		{
			$moodle_result_final="Not Satisfactory";
		}
		else if($withdrawn>0)
		{
			$moodle_result_final="Withdrawn/discontinued/NA";
		}
		else
		{
			$moodle_result_final="";
		}
		$new_array['moodleresult'][]=$moodle_result_final;
		unset($arr_secondary);
		unset($arr_secondary2);
		unset($arr_final_result);
		unset($moodle_result_final);
		unset($passed);
		unset($failed);
		unset($withdrawn);
	}
	
	//echo '<pre>';
	//print_r($new_array);
	//die;
	unset($SESSION->compiled_array);
	$compiled_array=array();
	$SESSION->compiled_array=$new_array;
	$SESSION->term = $_POST['term'];
	$SESSION->year = $_POST['year'];
	//die;
	?>
	<script>
	window.location.href='parser2.php';
	</script>
	<?php

} else {
	echo SimpleXLSX::parseError();
}
}
}
?>
</body>
</html>