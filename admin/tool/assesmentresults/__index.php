<?php
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('index');
 
// Set up the page.
$title = get_string('pluginname', 'tool_assesmentresults');
$pagetitle = $title;
$url = new moodle_url("/admin/tool/assesmentresults/index.php");
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);
require_login();
$output = $PAGE->get_renderer('tool_assesmentresults');
 
echo $OUTPUT->header();
echo $OUTPUT->heading($pagetitle);
global $SESSION;

global $DB;


//echo $DB->count_records('mdl_assign',array('course'=>'118'));
//echo '<pre>';
//print_r($_POST);
if(isset($_GET['sorttype']) && $_GET['sorttype']=="date")
{
    if($_GET['sort']==1)
    {
       $sortval = 2;
       $sort = " order by ag.timemodified ASC";
    }
    else
    {
        $sortval = 1;
        $sort = " order by ag.timemodified DESC";
    }
}
else if(isset($_GET['sorttype']) && $_GET['sorttype']=="assignment")
{
    if($_GET['sort']==1)
    {
        $sortval = 2;
        $sort = " order by ax.name ASC";
    }
    else
    {
        $sortval = 1;
        $sort = " order by ax.name DESC";
    }
}
else
{
    $sortval = "2";
    $sort = "";
}
$SESSION->sort=$sort;
if(isset($_REQUEST["page"]))
$page = (int)$_REQUEST["page"];
else
$page = 1;
$setLimit = 10;
$pageLimit = ($page * $setLimit) - $setLimit;


$list_all_courses = $DB->get_records_sql('SELECT `id` as courseid , `fullname` as coursename FROM {course} WHERE `visible` = 1');
$coursestr = '';
foreach($list_all_courses as $list_all_courses)
{
    // $course_arr[] = array('coursename'=>$list_all_courses->coursename,'courseid'=>$list_all_courses->courseid);
    $coursestr = $coursestr.'"'.$list_all_courses->coursename."|".$list_all_courses->courseid.'",';
}
$coursestr = "[".$coursestr."]";

$list_all_students = $DB->get_records_sql("SELECT u.`id` as studentid , u.`firstname` , u.`lastname` FROM
{user} u");
$studentsstr = '';
foreach($list_all_students as $list_all_students)
{
    // $course_arr[] = array('coursename'=>$list_all_courses->coursename,'courseid'=>$list_all_courses->courseid);
    $studentsstr = $studentsstr.'"'.$list_all_students->firstname." ".$list_all_students->lastname."|".$list_all_students->studentid.'",';
}
$studentsstr = "[".$studentsstr."]";

//echo $studentsstr; 
//$sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , z.id as assignmentid , z.name as assignmentname , g.userid as uid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
       // . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid WHERE 1';
if(isset($_POST['search']) && $_POST['search']==" Search ")
{
    if(isset($_POST['courseid']) && $_POST['courseid']>0 && $_POST['coursename']!='')
    {
        $SESSION->courseid = '';
        $SESSION->courseid = $_POST['courseid'];
        $SESSION->coursename = '';
        $SESSION->coursename = $_POST['coursename'];
    }
    if($_POST['coursename']=='')
    {
        $SESSION->courseid = '';
    }
    if(isset($_POST['studentid']) && $_POST['studentid']>0 && $_POST['studentname']!='')
    {
        $SESSION->studentid = '';
        $SESSION->studentid = $_POST['studentid'];
        $SESSION->studentname = '';
        $SESSION->studentname = $_POST['studentname'];
    }
    if($_POST['studentname']=='')
    {
        $SESSION->studentid = '';
    }
}
if(isset($_POST['coursename']) && $_POST['coursename']=='')
{
    $SESSION->coursename = '';
}
if(isset($_POST['studentname']) && $_POST['studentname']=='')
{
    $SESSION->studentname = '';
}
if(isset($_GET['reset']) && $_GET['reset']=='1')
{
     $SESSION->courseid = '';
     $SESSION->studentid = '';
     $SESSION->studentname = '';
     $SESSION->coursename = '';
     $SESSION->sort = '';
}
if(@$SESSION->courseid>0 && @$SESSION->studentid=='')
{
    $contextid = get_context_instance(CONTEXT_COURSE, $SESSION->courseid);

 
      $sql_all =  'SELECT z.id as rowid , gg.usermodified , z.userid , y.firstname , gg.feedback , y.lastname , ag.grade as recorded_grade , gi.iteminstance as assignmentid , ag.timemodified, ax.name as assignmentname, '
            . '  gi.courseid , gi.gradetype, gi.grademax,gi.grademin,gi.scaleid , y.username '
            . ' FROM {assign_submission} as z '
            . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = z.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
               . ' LEFT JOIN {assign_grades} as ag ON  ag.assignment = z.assignment AND ag.userid = z.userid '
            .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = z.userid  AND ag.userid = z.userid AND ag.timemodified = (SELECT max(`timemodified`) FROM {assign_grades} WHERE `assignment` = z.assignment AND `userid` = z.userid)'
           . ' LEFT JOIN {assign} as ax ON  ax.id = z.assignment '
            .' LEFT JOIN {user} as y ON z.userid = y.id '
                 
            .' WHERE ax.course = '.$SESSION->courseid.' GROUP BY z.assignment , z.userid ';
 
   
    $sql =    'SELECT z.id as rowid , gg.usermodified , z.userid , y.firstname , y.lastname , gg.feedback, ag.grade as recorded_grade , gi.iteminstance as assignmentid , ag.timemodified, ax.name as assignmentname, '
            . '  gi.courseid , gi.gradetype, gi.grademax,gi.grademin,gi.scaleid , y.username '
            . ' FROM {assign_submission} as z '
            . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = z.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
               . ' LEFT JOIN {assign_grades} as ag ON  ag.assignment = z.assignment AND ag.userid = z.userid '
            .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = z.userid  AND ag.userid = z.userid AND ag.timemodified = (SELECT max(`timemodified`) FROM {assign_grades} WHERE `assignment` = z.assignment AND `userid` = z.userid)'
           . ' LEFT JOIN {assign} as ax ON  ax.id = z.assignment '
            .' LEFT JOIN {user} as y ON z.userid = y.id '
                 
            .' WHERE ax.course = '.$SESSION->courseid.' GROUP BY z.assignment , z.userid '
            .' '.$SESSION->sort;
 

}
else if(@$SESSION->courseid=='' && @$SESSION->studentid>0)
{
    
      $sql_all =   'SELECT z.id as rowid , gg.usermodified , z.userid , gg.feedback , y.firstname , y.lastname , ag.grade as recorded_grade , gi.iteminstance as assignmentid , ag.timemodified, ax.name as assignmentname, '
            . '  gi.courseid , gi.gradetype, gi.grademax,gi.grademin,gi.scaleid , y.username '
            . ' FROM {assign_submission} as z '
            . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = z.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
               . ' LEFT JOIN {assign_grades} as ag ON  ag.assignment = z.assignment AND ag.userid = z.userid AND ag.timemodified = (SELECT max(`timemodified`) FROM {assign_grades} WHERE `assignment` = z.assignment AND `userid` = z.userid)'
            .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = z.userid  '
           . ' LEFT JOIN {assign} as ax ON  ax.id = z.assignment '
            .' LEFT JOIN {user} as y ON z.userid = y.id '
                 
            .' WHERE z.userid = '.$SESSION->studentid.' GROUP BY z.assignment , z.userid ';
 
   
    $sql =   'SELECT z.id as rowid , gg.usermodified , z.userid , y.firstname , gg.feedback, y.lastname , ag.grade as recorded_grade , gi.iteminstance as assignmentid , ag.timemodified, ax.name as assignmentname, '
            . '  gi.courseid , gi.gradetype, gi.grademax,gi.grademin,gi.scaleid , y.username '
            . ' FROM {assign_submission} as z '
            . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = z.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
               . ' LEFT JOIN {assign_grades} as ag ON  ag.assignment = z.assignment AND ag.userid = z.userid AND ag.timemodified = (SELECT max(`timemodified`) FROM {assign_grades} WHERE `assignment` = z.assignment AND `userid` = z.userid)'
            .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = z.userid  '
           . ' LEFT JOIN {assign} as ax ON  ax.id = z.assignment '
            .' LEFT JOIN {user} as y ON z.userid = y.id '
                 
            .' WHERE z.userid = '.$SESSION->studentid.' GROUP BY z.assignment , z.userid '
            .' '.$SESSION->sort;
 
}
else if(@$SESSION->courseid>0 && @$SESSION->studentid>0)
{
    $contextid = get_context_instance(CONTEXT_COURSE, $SESSION->courseid);
    $sql_all =   'SELECT z.id as rowid , gg.usermodified , gg.feedback , z.userid , y.firstname , y.lastname , ag.grade as recorded_grade , gi.iteminstance as assignmentid , ag.timemodified, ax.name as assignmentname, '
            . '  gi.courseid , gi.gradetype, gi.grademax,gi.grademin,gi.scaleid , y.username '
            . ' FROM {assign_submission} as z '
            . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = z.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
               . ' LEFT JOIN {assign_grades} as ag ON  ag.assignment = z.assignment AND ag.userid = z.userid AND ag.userid = z.userid AND ag.timemodified = (SELECT max(`timemodified`) FROM {assign_grades} WHERE `assignment` = z.assignment AND `userid` = z.userid)'
            .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = z.userid  '
           . ' LEFT JOIN {assign} as ax ON  ax.id = z.assignment '
            .' LEFT JOIN {user} as y ON z.userid = y.id '                      
            .' WHERE z.userid = '.$SESSION->studentid.' AND ax.course = '.$SESSION->courseid.' GROUP BY z.assignment , z.userid ';
 
   
    $sql =    'SELECT z.id as rowid , gg.usermodified , z.userid , gg.feedback , y.firstname , y.lastname , ag.grade as recorded_grade , gi.iteminstance as assignmentid , ag.timemodified, ax.name as assignmentname, '
            . '  gi.courseid , gi.gradetype, gi.grademax,gi.grademin,gi.scaleid , y.username '
            . ' FROM {assign_submission} as z '
            . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = z.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
               . ' LEFT JOIN {assign_grades} as ag ON  ag.assignment = z.assignment AND ag.userid = z.userid AND ag.userid = z.userid AND ag.timemodified = (SELECT max(`timemodified`) FROM {assign_grades} WHERE `assignment` = z.assignment AND `userid` = z.userid)'
            .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = z.userid  '
           . ' LEFT JOIN {assign} as ax ON  ax.id = z.assignment '
            .' LEFT JOIN {user} as y ON z.userid = y.id '
                 
            .' WHERE z.userid = '.$SESSION->studentid.' AND ax.course = '.$SESSION->courseid.' GROUP BY z.assignment , z.userid '
            .' '.$SESSION->sort;
    
    
}
/*else
{
    $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , z.id as assignmentid , z.name as assignmentname , z.course , gi.scaleid as scaleid, gh.finalgrade AS Recorded_Grade, gi.itemname AS Item_Name FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id JOIN {grade_grades_history} gh ON y.id=gh.userid LEFT JOIN {grade_items} gi ON gh.itemid=gi.id JOIN {course} c ON gi.courseid=c.id WHERE gh.finalgrade IS NOT NULL and gi.itemname IS NOT NULL '.$SESSION->sort;
 
     $sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , z.id as assignmentid , z.name as assignmentname , z.course , gi.scaleid as scaleid, gh.finalgrade AS Recorded_Grade, gi.itemname AS Item_Name FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id JOIN {grade_grades_history} gh ON y.id=gh.userid LEFT JOIN {grade_items} gi ON gh.itemid=gi.id JOIN {course} c ON gi.courseid=c.id WHERE gh.finalgrade IS NOT NULL and gi.itemname IS NOT NULL '.$SESSION->sort.' LIMIT '.$pageLimit.' , '.$setLimit;
 

}*/
else
{
    $sql_all = '';
    $sql = '';
}
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
foreach($list as $list)
{
   
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
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text);
            unset($grade_val);
            unset($scale_text);
            unset($timemodified);
            unset($list_all_stu);
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
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text);
            unset($grade_val);
            unset($scale_text);
            unset($timemodified);           
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
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text);
            unset($grade_val);
            unset($scale_text);
            unset($timemodified);
            unset($list_all_stu);
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
            $grade_exists = '';
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text);
            unset($grade_val);
            unset($scale_text);
            unset($timemodified);           
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
$renderable = new \tool_assesmentresults\output\index_page(@$arr,$coursestr,$studentsstr,$page,@$list_all_count,@$SESSION->studentname,@$SESSION->coursename,@$SESSION->courseid,@$SESSION->studentid,@$sortval);
echo $output->render($renderable); 
//echo '<br/><br/>';
//echo displayPaginationHere(@$list_all_count,$setLimit,$page); // Call the Pagination Function to display Pagination.

echo $OUTPUT->footer();
